<?php

namespace App\Http\Controllers\Panel;

use App\Enums\AuditAction;
use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmpleadoRequest;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmpleadoController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $buscar = $request->string('buscar')->trim();

        $incidenciasBase = Incidencia::query()
            ->whereColumn('incidencias.numero_empleado', 'empleados.numero_empleado');

        if ($user->esJefeInmediato()) {
            abort_if(! $user->tieneArea(), 403, 'No tienes un área asignada.');
            $incidenciasBase->where('area_id', $user->area_id);
        }

        if ($user->esSindicato()) {
            $incidenciasBase
                ->enviadasSindicato()
                ->where('tipo_incidencia', '!=', TipoIncidencia::PermisoEconomico->value);
        }

        $empleados = Empleado::query()
            ->selectRaw('numero_empleado, nombre as reportante_nombre, email as email_reportante')
            ->when($buscar->isNotEmpty(), function ($q) use ($buscar) {
                $q->where(function ($q2) use ($buscar) {
                    $q2->where('numero_empleado', 'like', "%{$buscar}%")
                        ->orWhere('nombre', 'like', "%{$buscar}%");
                });
            })
            ->addSelect([
                'total_incidencias' => (clone $incidenciasBase)->selectRaw('COUNT(*)'),
                'ultima_incidencia' => (clone $incidenciasBase)->selectRaw('MAX(created_at)'),
            ])
            ->when(
                $user->esJefeInmediato(),
                fn ($q) => $q->whereExists((clone $incidenciasBase)->selectRaw('1'))
            )
            ->when(
                $user->esSindicato(),
                fn ($q) => $q->whereExists((clone $incidenciasBase)->selectRaw('1'))
            )
            ->orderByDesc('ultima_incidencia')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Panel/Empleados/Index', [
            'empleados' => $empleados,
            'filtros' => ['buscar' => (string) $buscar],
        ]);
    }

    public function show(Request $request, string $numeroEmpleado): Response
    {
        $user = $request->user();
        $empleadoModel = Empleado::where('numero_empleado', $numeroEmpleado)->firstOrFail();

        $query = Incidencia::where('numero_empleado', $numeroEmpleado)
            ->with('area:id,nombre');

        if ($user->esJefeInmediato()) {
            abort_if(! $user->tieneArea(), 403, 'No tienes un área asignada.');
            $query->where('area_id', $user->area_id);
        }

        if ($user->esSindicato()) {
            $query
                ->enviadasSindicato()
                ->where('tipo_incidencia', '!=', TipoIncidencia::PermisoEconomico->value);
        }

        $sindicatoTieneIncidencias = $user->esSindicato()
            ? (clone $query)->exists()
            : true;

        if ($request->filled('fecha') && $request->filled('fecha_fin')) {
            $query->where('fecha_incidencia', '>=', $request->fecha)
                ->where('fecha_incidencia', '<=', $request->fecha_fin);
        } elseif ($request->filled('fecha')) {
            $fecha = Carbon::parse($request->fecha);
            $inicio = $fecha->copy()->startOfMonth();
            $fin = $fecha->copy()->endOfMonth();
            $query->where('fecha_incidencia', '>=', $inicio)
                ->where('fecha_incidencia', '<=', $fin);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_incidencia', $request->tipo);
        }

        $incidencias = $query->orderByDesc('created_at')->get();

        if ($user->esJefeInmediato()) {
            $existeEnArea = Incidencia::where('numero_empleado', $numeroEmpleado)
                ->where('area_id', $user->area_id)
                ->exists();

            if (! $existeEnArea) {
                abort(403, 'No tienes permiso para acceder a este empleado.');
            }
        }

        if (! $sindicatoTieneIncidencias) {
            abort(403, 'No tienes incidencias de sindicato para este empleado.');
        }

        $permisoEconomicoStats = null;

        if (! $user->esJefeInmediato() && ! $user->esSindicato()) {
            if ($request->filled('fecha') && $request->filled('fecha_fin')) {
                $inicio = Carbon::parse($request->fecha)->startOfDay();
                $fin = Carbon::parse($request->fecha_fin)->endOfDay();
            } elseif ($request->filled('fecha')) {
                $fecha = Carbon::parse($request->fecha);
                $inicio = $fecha->copy()->startOfMonth();
                $fin = $fecha->copy()->endOfMonth();
            } else {
                $inicio = Carbon::now()->startOfMonth();
                $fin = Carbon::now()->endOfMonth();
            }

            $permisoEconomicoMensualUsados = Incidencia::where('numero_empleado', $numeroEmpleado)
                ->whereBetween('fecha_incidencia', [$inicio, $fin])
                ->where('tipo_incidencia', TipoIncidencia::PermisoEconomico->value)
                ->whereNotIn('estado', [EstadoIncidencia::Rechazada->value])
                ->count();

            $inicioAnio = Carbon::now()->startOfYear();
            $finAnio = Carbon::now()->endOfYear();

            $permisoEconomicoAnualUsados = Incidencia::where('numero_empleado', $numeroEmpleado)
                ->whereBetween('fecha_incidencia', [$inicioAnio, $finAnio])
                ->where('tipo_incidencia', TipoIncidencia::PermisoEconomico->value)
                ->whereNotIn('estado', [EstadoIncidencia::Rechazada->value])
                ->count();

            $permisoEconomicoStats = [
                'mensual' => [
                    'usados' => $permisoEconomicoMensualUsados,
                    'disponibles' => max(0, 3 - $permisoEconomicoMensualUsados),
                    'total' => 3,
                ],
                'anual' => [
                    'usados' => $permisoEconomicoAnualUsados,
                    'disponibles' => max(0, 12 - $permisoEconomicoAnualUsados),
                    'total' => 12,
                ],
            ];
        }

        $empleado = [
            'numero_empleado' => $numeroEmpleado,
            'reportante_nombre' => $empleadoModel->nombre,
            'email_reportante' => $empleadoModel->email,
            'tipo' => $empleadoModel->tipo?->value,
        ];

        return Inertia::render('Panel/Empleados/Show', [
            'empleado' => $empleado,
            'incidencias' => $incidencias,
            'filtros' => $request->only(['fecha', 'fecha_fin', 'estado', 'tipo']),
            'estados' => array_map(fn ($e) => ['value' => $e->value, 'name' => $e->name], EstadoIncidencia::cases()),
            'tipos' => array_map(
                fn ($t) => ['value' => $t->value, 'name' => $t->name],
                array_values(array_filter(
                    TipoIncidencia::cases(),
                    fn (TipoIncidencia $tipo) => ! $user->esSindicato() || $tipo !== TipoIncidencia::PermisoEconomico,
                ))
            ),
            'permiso_economico_stats' => $permisoEconomicoStats,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeCreate($request->user());

        return Inertia::render('Panel/Empleados/Create');
    }

    public function store(StoreEmpleadoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $empleado = Empleado::create($data);

        $this->auditLogService->record(
            action: AuditAction::EmpleadoCreado,
            description: "Empleado {$empleado->numero_empleado} creado.",
            subject: $empleado,
            metadata: ['numero_empleado' => $empleado->numero_empleado, 'tipo' => $empleado->tipo?->value],
        );

        $rol = $request->user()->rol->value;

        $route = match ($rol) {
            'capital_humano' => 'panel.capital_humano.empleados.index',
            'sindicato' => 'panel.sindicato.empleados.index',
            default => 'panel.subdireccion.empleados.index',
        };

        return redirect()->route($route)->with('success', 'Empleado creado correctamente.');
    }

    public function edit(Request $request, string $numeroEmpleado): Response
    {
        $this->authorizeCreate($request->user());

        $empleado = Empleado::where('numero_empleado', $numeroEmpleado)->firstOrFail();

        return Inertia::render('Panel/Empleados/Edit', [
            'empleado' => [
                'numero_empleado' => $empleado->numero_empleado,
                'nombre' => $empleado->nombre,
                'email' => $empleado->email,
                'tipo' => $empleado->tipo?->value,
            ],
        ]);
    }

    public function update(StoreEmpleadoRequest $request, string $numeroEmpleado): RedirectResponse
    {
        $data = $request->validated();

        $empleado = Empleado::where('numero_empleado', $numeroEmpleado)->firstOrFail();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $empleado->update($data);

        $this->auditLogService->record(
            action: AuditAction::EmpleadoActualizado,
            description: "Empleado {$empleado->numero_empleado} actualizado.",
            subject: $empleado,
            metadata: [
                'numero_empleado' => $empleado->numero_empleado,
                'cambios' => $empleado->getChanges(),
            ],
        );

        $rol = $request->user()->rol->value;

        $route = match ($rol) {
            'capital_humano' => 'panel.capital_humano.empleados.index',
            'sindicato' => 'panel.sindicato.empleados.index',
            default => 'panel.subdireccion.empleados.index',
        };

        return redirect()->route($route)->with('success', 'Empleado actualizado correctamente.');
    }

    public function descargarPlantilla(Request $request): BinaryFileResponse
    {
        $this->authorizeCreate($request->user());

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Empleados');

        $headers = ['numero_empleado', 'nombre', 'email', 'tipo', 'password'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.'1', $header);
            $sheet->getStyle($col.'1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $sheet->setCellValue('A2', '12345');
        $sheet->setCellValue('B2', 'Ejemplo Empleado');
        $sheet->setCellValue('C2', 'ejemplo@correo.com');
        $sheet->setCellValue('D2', 'docente');
        $sheet->setCellValue('E2', 'password123');

        $sheet->setCellValue('A3', '');
        $sheet->setCellValue('B3', '');
        $sheet->setCellValue('C3', '');
        $sheet->setCellValue('D3', 'administrativo');
        $sheet->setCellValue('E3', '');

        $writer = new Xlsx($spreadsheet);
        $tempPath = tempnam(sys_get_temp_dir(), 'plantilla_empleados_').'.xlsx';
        $writer->save($tempPath);

        return response()->download($tempPath, 'plantilla_empleados.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function importarExcel(Request $request): JsonResponse
    {
        $this->authorizeCreate($request->user());

        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ]);

        $file = $request->file('archivo');
        $importados = 0;
        $errores = [];

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            $total = count($rows) - 1;

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;
                }

                $numeroEmpleado = trim((string) ($row[0] ?? ''));
                $nombre = trim((string) ($row[1] ?? ''));
                $email = trim((string) ($row[2] ?? ''));
                $tipo = trim((string) ($row[3] ?? ''));
                $password = (string) ($row[4] ?? '');

                $fila = $index + 1;
                $rowErrors = [];

                if (empty($numeroEmpleado)) {
                    $rowErrors[] = 'El número de empleado es obligatorio.';
                } elseif (Empleado::where('numero_empleado', $numeroEmpleado)->exists()) {
                    $rowErrors[] = "El número de empleado '{$numeroEmpleado}' ya está registrado.";
                }

                if (empty($nombre)) {
                    $rowErrors[] = 'El nombre es obligatorio.';
                }

                if (empty($email)) {
                    $rowErrors[] = 'El correo electrónico es obligatorio.';
                } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = "El correo '{$email}' no es válido.";
                } elseif (Empleado::where('email', $email)->exists()) {
                    $rowErrors[] = "El correo '{$email}' ya está registrado.";
                }

                $tiposValidos = ['docente', 'administrativo'];
                if (empty($tipo)) {
                    $rowErrors[] = 'El tipo es obligatorio (docente o administrativo).';
                } elseif (! in_array($tipo, $tiposValidos, true)) {
                    $rowErrors[] = "El tipo '{$tipo}' no es válido. Debe ser 'docente' o 'administrativo'.";
                }

                if (empty($password)) {
                    $rowErrors[] = 'La contraseña es obligatoria.';
                } elseif (strlen($password) < 8) {
                    $rowErrors[] = 'La contraseña debe tener al menos 8 caracteres.';
                }

                if (! empty($rowErrors)) {
                    $errores[] = [
                        'fila' => $fila,
                        'numero_empleado' => $numeroEmpleado ?: '—',
                        'nombre' => $nombre ?: '—',
                        'errores' => $rowErrors,
                    ];

                    continue;
                }

                Empleado::create([
                    'numero_empleado' => $numeroEmpleado,
                    'nombre' => $nombre,
                    'email' => $email,
                    'tipo' => $tipo,
                    'password' => $password,
                ]);

                $importados++;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: '.$e->getMessage(),
                'importados' => 0,
                'errores' => [],
            ], 422);
        }

        $this->auditLogService->record(
            action: AuditAction::EmpleadosImportados,
            description: "Importación de empleados finalizada con {$importados} registros importados.",
            metadata: [
                'importados' => $importados,
                'total' => $total,
                'errores' => count($errores),
                'archivo' => $file?->getClientOriginalName(),
            ],
        );

        return response()->json([
            'success' => true,
            'message' => "Se importaron {$importados} de {$total} empleados correctamente.",
            'importados' => $importados,
            'total' => $total,
            'errores' => $errores,
        ]);
    }

    private function authorizeCreate($user): void
    {
        abort_unless($user && ($user->esCapitalHumano() || $user->esSindicato() || $user->esSubdirector()), 403, 'No tienes permiso para realizar esta acción.');
    }
}
