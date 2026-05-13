import { Head, Link, router, usePage } from '@inertiajs/react';
import { useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes/panel';
import { index as chEmpleados,   show as chShow,   create as chCreate,   plantilla as chPlantilla,   importar as chImportar }    from '@/routes/panel/capital_humano/empleados';
import { index as jefeEmpleados, show as jefeShow }                        from '@/routes/panel/jefe_inmediato/empleados';
import { index as sindicatoEmpleados, show as sindicatoShow, create as sindicatoCreate, plantilla as sindicatoPlantilla, importar as sindicatoImportar } from '@/routes/panel/sindicato/empleados';
import { index as subdirEmpleados, show as subdirShow, create as subdirCreate, plantilla as subdirPlantilla, importar as subdirImportar } from '@/routes/panel/subdireccion/empleados';
import { formatDateOnly } from '@/utils/date';

type Empleado = {
    numero_empleado: string;
    reportante_nombre: string;
    email_reportante: string | null;
    total_incidencias: number;
    ultima_incidencia: string | null;
};

type Paginado<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type ErrorFila = {
    fila: number;
    numero_empleado: string;
    nombre: string;
    errores: string[];
};

type Props = {
    empleados: Paginado<Empleado>;
    filtros: { buscar: string };
};

function useRolRoutes() {
    const { auth } = usePage().props as { auth: { user?: { rol?: string } } };
    const rol = auth.user?.rol ?? '';

    if (rol === 'jefe_inmediato') {
return { indexUrl: jefeEmpleados.url, showUrl: jefeShow.url, createUrl: undefined, plantillaUrl: undefined, importarUrl: undefined, puedeCrear: false };
}

    if (rol === 'capital_humano') {
return { indexUrl: chEmpleados.url, showUrl: chShow.url, createUrl: chCreate.url(), plantillaUrl: chPlantilla.url(), importarUrl: chImportar.url(), puedeCrear: true };
}

    if (rol === 'sindicato') {
return { indexUrl: sindicatoEmpleados.url, showUrl: sindicatoShow.url, createUrl: sindicatoCreate.url(), plantillaUrl: sindicatoPlantilla.url(), importarUrl: sindicatoImportar.url(), puedeCrear: true };
}

    return { indexUrl: subdirEmpleados.url, showUrl: subdirShow.url, createUrl: subdirCreate.url(), plantillaUrl: subdirPlantilla.url(), importarUrl: subdirImportar.url(), puedeCrear: true };
}

export default function Index({ empleados, filtros }: Props) {
    const { indexUrl, showUrl, createUrl, plantillaUrl, importarUrl, puedeCrear } = useRolRoutes();
    const [modalAbierto, setModalAbierto] = useState(false);
    const [archivo, setArchivo] = useState<File | null>(null);
    const [importando, setImportando] = useState(false);
    const [progreso, setProgreso] = useState(0);
    const [resultado, setResultado] = useState<{ type: 'success' | 'error'; message: string; errores: ErrorFila[] } | null>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    function handleBuscar(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        const fd = new FormData(e.currentTarget);
        router.get(indexUrl(), { buscar: fd.get('buscar') as string });
    }

    function handleFileChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0] ?? null;
        setArchivo(file);
        setResultado(null);
    }

    async function handleImportar() {
        if (!archivo || !importarUrl) return;

        setImportando(true);
        setProgreso(0);
        setResultado(null);

        const formData = new FormData();
        formData.append('archivo', archivo);

        const xhr = new XMLHttpRequest();

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                setProgreso(Math.round((e.loaded / e.total) * 100));
            }
        };

        return new Promise<void>((resolve) => {
            xhr.onload = () => {
                setImportando(false);
                setProgreso(100);

                try {
                    const resp = JSON.parse(xhr.responseText);

                    if (xhr.status === 200) {
                        setResultado({
                            type: 'success',
                            message: resp.message,
                            errores: resp.errores ?? [],
                        });
                        router.reload({ only: ['empleados'] });
                    } else {
                        setResultado({
                            type: 'error',
                            message: resp.message || 'Error al procesar el archivo.',
                            errores: resp.errores ?? [],
                        });
                    }
                } catch {
                    setResultado({
                        type: 'error',
                        message: 'Error al procesar la respuesta del servidor.',
                        errores: [],
                    });
                }

                resolve();
            };

            xhr.onerror = () => {
                setImportando(false);
                setResultado({
                    type: 'error',
                    message: 'Error de conexión al importar.',
                    errores: [],
                });
                resolve();
            };

            xhr.open('POST', importarUrl, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) {
                xhr.setRequestHeader('X-CSRF-TOKEN', token);
            }

            xhr.send(formData);
        });
    }

    function cerrarModal() {
        setModalAbierto(false);
        setArchivo(null);
        setProgreso(0);
        setResultado(null);
        if (fileInputRef.current) fileInputRef.current.value = '';
    }

    return (
        <>
            <Head title="Empleados" />

            <div className="p-4 md:p-6 space-y-5">
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <h2 className="text-xl font-semibold text-gray-900">Empleados</h2>
                        <p className="text-sm text-gray-500">Historial de incidencias por empleado.</p>
                    </div>
                    {puedeCrear && createUrl && (
                        <div className="flex items-center gap-2">
                            <Button variant="outline" onClick={() => setModalAbierto(true)}>
                                Cargar desde Excel
                            </Button>
                            <Button asChild>
                                <Link href={createUrl}>
                                    Nuevo empleado
                                </Link>
                            </Button>
                        </div>
                    )}
                </div>

                {/* Buscador */}
                <form onSubmit={handleBuscar} className="flex gap-2 max-w-md">
                    <Input
                        name="buscar"
                        defaultValue={filtros.buscar}
                        placeholder="Buscar por nombre o número de empleado..."
                        className="h-9"
                    />
                    <Button type="submit" size="sm" className="h-9">Buscar</Button>
                    {filtros.buscar && (
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="h-9"
                            onClick={() => router.get(indexUrl())}
                        >
                            Limpiar
                        </Button>
                    )}
                </form>

                {/* Tabla */}
                <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    {empleados.data.length === 0 ? (
                        <div className="p-8 text-center text-sm text-gray-500">
                            {filtros.buscar
                                ? 'No se encontraron empleados con esa búsqueda.'
                                : 'No hay registros de empleados aún.'}
                        </div>
                    ) : (
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600">No. Empleado</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600">Nombre</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600 hidden md:table-cell">Correo</th>
                                    <th className="text-center px-4 py-3 font-medium text-gray-600">Incidencias</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600 hidden md:table-cell">Última</th>
                                    <th className="px-4 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {empleados.data.map((emp) => (
                                    <tr key={emp.numero_empleado} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-4 py-3 font-mono text-gray-700">{emp.numero_empleado}</td>
                                        <td className="px-4 py-3 font-medium text-gray-900">{emp.reportante_nombre}</td>
                                        <td className="px-4 py-3 text-gray-500 hidden md:table-cell">
                                            {emp.email_reportante ?? <span className="text-gray-300 italic">Sin correo</span>}
                                        </td>
                                        <td className="px-4 py-3 text-center">
                                            <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                                {emp.total_incidencias}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-gray-500 text-xs hidden md:table-cell">
                                            {emp.ultima_incidencia
                                                ? formatDateOnly(emp.ultima_incidencia, true)
                                                : <span className="text-gray-300 italic">—</span>}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={showUrl(emp.numero_empleado)}>
                                                    Ver historial
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>

                {/* Paginación */}
                {empleados.last_page > 1 && (
                    <div className="flex justify-center gap-1">
                        {empleados.links.map((link, i) => (
                            <button
                                key={i}
                                disabled={!link.url}
                                onClick={() => link.url && router.get(link.url)}
                                className={`px-3 py-1.5 rounded text-xs font-medium transition-colors ${
                                    link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : link.url
                                            ? 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
                                            : 'bg-white border border-gray-200 text-gray-300 cursor-not-allowed'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}

                <p className="text-xs text-gray-400 text-center">{empleados.total} empleado(s) encontrado(s)</p>
            </div>

            {/* Modal de importación */}
            {modalAbierto && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40" onClick={cerrarModal}>
                    <div className="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6 space-y-5" onClick={(e) => e.stopPropagation()}>
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">Cargar empleados desde Excel</h3>
                            <button type="button" onClick={cerrarModal} className="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                        </div>

                        {!resultado && (
                            <>
                                <p className="text-sm text-gray-500">
                                    Selecciona un archivo Excel (.xlsx, .xls, .csv) con las columnas en este orden:
                                    numero_empleado, nombre, email, tipo (docente/administrativo), password.
                                </p>

                                <div className="space-y-3">
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        accept=".xlsx,.xls,.csv"
                                        onChange={handleFileChange}
                                        className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium"
                                    />

                                    {importando && (
                                        <div className="space-y-1">
                                            <div className="flex justify-between text-xs text-gray-500">
                                                <span>Subiendo archivo...</span>
                                                <span>{progreso}%</span>
                                            </div>
                                            <div className="w-full bg-gray-100 rounded-full h-2">
                                                <div className="bg-primary h-2 rounded-full transition-all duration-300" style={{ width: `${progreso}%` }} />
                                            </div>
                                        </div>
                                    )}
                                </div>

                                <div className="flex items-center gap-3 pt-2">
                                    <Button onClick={handleImportar} disabled={!archivo || importando}>
                                        {importando && <Spinner />}
                                        {importando ? 'Importando...' : 'Importar'}
                                    </Button>
                                    {plantillaUrl && (
                                        <Button variant="outline" asChild>
                                            <a href={plantillaUrl} target="_blank" rel="noopener noreferrer">
                                                Descargar plantilla
                                            </a>
                                        </Button>
                                    )}
                                    <Button variant="ghost" type="button" onClick={cerrarModal}>
                                        Cancelar
                                    </Button>
                                </div>
                            </>
                        )}

                        {resultado && (
                            <div className="space-y-4">
                                <div className={`p-4 rounded-lg text-sm ${
                                    resultado.type === 'success'
                                        ? 'bg-green-50 border border-green-200 text-green-700'
                                        : 'bg-red-50 border border-red-200 text-red-700'
                                }`}>
                                    {resultado.message}
                                </div>

                                {resultado.errores.length > 0 && (
                                    <div className="max-h-64 overflow-y-auto space-y-2">
                                        <p className="text-sm font-medium text-gray-700">Errores por fila:</p>
                                        {resultado.errores.map((err, i) => (
                                            <div key={i} className="p-3 bg-red-50 border border-red-100 rounded-lg text-sm">
                                                <p className="font-medium text-red-700">
                                                    Fila {err.fila} — {err.numero_empleado} ({err.nombre})
                                                </p>
                                                <ul className="list-disc list-inside text-red-600 mt-1 text-xs space-y-0.5">
                                                    {err.errores.map((e, j) => (
                                                        <li key={j}>{e}</li>
                                                    ))}
                                                </ul>
                                            </div>
                                        ))}
                                    </div>
                                )}

                                <Button onClick={cerrarModal} className="w-full">
                                    Cerrar
                                </Button>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </>
    );
}

Index.layout = {
    breadcrumbs: [
        { title: 'Panel Principal', href: dashboard.url() },
        { title: 'Empleados' },
    ],
};
