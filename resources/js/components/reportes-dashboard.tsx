import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    ESTADO_CHART_COLORS,
    ESTADO_LABELS,
    SOLICITANTE_COLORS,
    TIPO_COLORS,
    TIPO_LABELS,
    TIPO_SOLICITANTE_LABELS,
} from '@/lib/incidencias';

type Filtros = {
    desde: string;
    hasta: string;
    fecha: string;
    estado: string;
    tipo_incidencia: string;
    tipo_solicitante: string;
    area_id: string;
};

type Opciones = {
    estados: { value: string; label: string }[];
    tiposIncidencia: { value: string; label: string }[];
    tiposSolicitante: { value: string; label: string }[];
    areas: { id: number; nombre: string }[];
};

type Props = {
    filtros: Filtros;
    estadisticas: {
        total: number;
        aprobadas: number;
        rechazadas: number;
        pendientes: number;
    };
    porEstado: Record<string, number>;
    porTipoIncidencia: Record<string, number>;
    porTipoSolicitante: Record<string, number>;
    porArea: Record<string, number>;
    porDia: Record<string, number>;
    opciones: Opciones;
    indexUrl: string;
    exportUrl: (query: Record<string, string>) => string;
};

type Query = Record<string, string>;

function cleanQuery(query: Query): Query {
    return Object.fromEntries(Object.entries(query).filter(([, value]) => value !== ''));
}

function formatDateInput(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function addDays(date: Date, days: number): Date {
    const next = new Date(date);
    next.setDate(next.getDate() + days);

    return next;
}

function startOfWeek(date: Date): Date {
    const next = new Date(date);
    const day = next.getDay() || 7;
    next.setDate(next.getDate() - day + 1);

    return next;
}

function startOfMonth(date: Date): Date {
    return new Date(date.getFullYear(), date.getMonth(), 1);
}

function formatHumanDate(date: string): string {
    return new Date(`${date}T12:00:00`).toLocaleDateString('es-MX', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

function activeFilters(filtros: Filtros): Query {
    return cleanQuery({
        estado: filtros.estado,
        tipo_incidencia: filtros.tipo_incidencia,
        tipo_solicitante: filtros.tipo_solicitante,
        area_id: filtros.area_id,
    });
}

function BarChart({ data, colorMap, labelMap }: {
    data: Record<string, number>;
    colorMap?: Record<string, string>;
    labelMap?: Record<string, string>;
}) {
    const max = Math.max(...Object.values(data), 1);

    return (
        <div className="space-y-2.5">
            {Object.entries(data).map(([key, value]) => (
                <div key={key} className="flex items-center gap-3">
                    <span className="w-36 truncate text-xs text-gray-600">
                        {labelMap?.[key] ?? key}
                    </span>
                    <div className="h-4 flex-1 overflow-hidden rounded-full bg-gray-100">
                        <div
                            className={`h-full rounded-full ${colorMap?.[key] ?? 'bg-blue-400'}`}
                            style={{ width: `${(value / max) * 100}%` }}
                        />
                    </div>
                    <span className="w-8 text-right text-xs font-medium text-gray-900">
                        {value}
                    </span>
                </div>
            ))}
        </div>
    );
}

function NativeSelect({ name, label, value, options }: {
    name: keyof Filtros;
    label: string;
    value: string;
    options: { value: string; label: string }[];
}) {
    return (
        <div className="grid gap-1.5">
            <Label className="text-xs">{label}</Label>
            <select
                name={name}
                defaultValue={value}
                className="h-8 rounded-md border border-input bg-background px-2 text-xs"
            >
                <option value="">Todos</option>
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        </div>
    );
}

export default function ReportesDashboard({
    filtros,
    estadisticas,
    porEstado,
    porTipoIncidencia,
    porTipoSolicitante,
    porArea,
    porDia,
    opciones,
    indexUrl,
    exportUrl,
}: Props) {
    const queryActual = cleanQuery(filtros);

    function visit(query: Query) {
        router.get(indexUrl, cleanQuery(query), { preserveState: true, replace: true });
    }

    function handleFiltrar(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        const fd = new FormData(e.currentTarget);

        visit({
            fecha: fd.get('fecha') as string,
            desde: fd.get('desde') as string,
            hasta: fd.get('hasta') as string,
            estado: fd.get('estado') as string,
            tipo_incidencia: fd.get('tipo_incidencia') as string,
            tipo_solicitante: fd.get('tipo_solicitante') as string,
            area_id: fd.get('area_id') as string,
        });
    }

    const today = new Date();
    const quickFilters = [
        { label: 'Hoy', query: { fecha: formatDateInput(today), ...activeFilters(filtros) } },
        { label: 'Ayer', query: { fecha: formatDateInput(addDays(today, -1)), ...activeFilters(filtros) } },
        {
            label: 'Esta semana',
            query: {
                desde: formatDateInput(startOfWeek(today)),
                hasta: formatDateInput(today),
                ...activeFilters(filtros),
            },
        },
        {
            label: 'Este mes',
            query: {
                desde: formatDateInput(startOfMonth(today)),
                hasta: formatDateInput(today),
                ...activeFilters(filtros),
            },
        },
    ];

    return (
        <>
            <Head title="Reportes" />

            <div className="space-y-6 p-4 md:p-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 className="text-xl font-semibold text-gray-900">Reportes</h2>
                        <p className="text-sm text-gray-500">
                            {filtros.fecha
                                ? `Día ${formatHumanDate(filtros.fecha)}`
                                : `${formatHumanDate(filtros.desde)} - ${formatHumanDate(filtros.hasta)}`}
                        </p>
                    </div>
                    <a
                        href={exportUrl(queryActual)}
                        className="inline-flex items-center gap-2 rounded-md border border-gray-200 px-4 py-2 text-sm font-medium transition-colors hover:bg-gray-50"
                    >
                        Exportar CSV
                    </a>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-4">
                    <div className="mb-4 flex flex-wrap gap-2">
                        {quickFilters.map((filter) => (
                            <Button
                                key={filter.label}
                                type="button"
                                variant="outline"
                                size="sm"
                                className="h-8"
                                onClick={() => visit(filter.query)}
                            >
                                {filter.label}
                            </Button>
                        ))}
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="h-8"
                            onClick={() => router.get(indexUrl)}
                        >
                            Limpiar filtros
                        </Button>
                    </div>

                    <form onSubmit={handleFiltrar} className="grid gap-4 lg:grid-cols-[1fr_1.4fr]">
                        <div className="rounded-lg border border-gray-100 bg-gray-50 p-3">
                            <p className="mb-3 text-xs font-medium text-gray-700">Reporte por día</p>
                            <div className="flex flex-wrap items-end gap-3">
                                <div className="grid gap-1.5">
                                    <Label className="text-xs">Día</Label>
                                    <Input type="date" name="fecha" defaultValue={filtros.fecha} className="h-8 w-40" />
                                </div>
                                <p className="pb-2 text-xs text-gray-400">Si eliges un día, se ignora el rango.</p>
                            </div>
                        </div>

                        <div className="rounded-lg border border-gray-100 bg-gray-50 p-3">
                            <p className="mb-3 text-xs font-medium text-gray-700">Rango de fechas</p>
                            <div className="flex flex-wrap items-end gap-3">
                                <div className="grid gap-1.5">
                                    <Label className="text-xs">Desde</Label>
                                    <Input type="date" name="desde" defaultValue={filtros.desde} className="h-8 w-40" />
                                </div>
                                <div className="grid gap-1.5">
                                    <Label className="text-xs">Hasta</Label>
                                    <Input type="date" name="hasta" defaultValue={filtros.hasta} className="h-8 w-40" />
                                </div>
                            </div>
                        </div>

                        <div className="grid gap-3 md:grid-cols-2 lg:col-span-2 lg:grid-cols-4">
                            <NativeSelect name="estado" label="Estado" value={filtros.estado} options={opciones.estados} />
                            <NativeSelect name="tipo_incidencia" label="Tipo de incidencia" value={filtros.tipo_incidencia} options={opciones.tiposIncidencia} />
                            <NativeSelect name="tipo_solicitante" label="Tipo de solicitante" value={filtros.tipo_solicitante} options={opciones.tiposSolicitante} />
                            <NativeSelect
                                name="area_id"
                                label="Área"
                                value={filtros.area_id}
                                options={opciones.areas.map((area) => ({ value: String(area.id), label: area.nombre }))}
                            />
                        </div>

                        <div className="flex items-end gap-2 lg:col-span-2">
                            <Button type="submit" size="sm" className="h-8">Aplicar filtros</Button>
                            <a
                                href={exportUrl(queryActual)}
                                className="inline-flex h-8 items-center rounded-md border border-gray-200 px-3 text-xs font-medium hover:bg-gray-50"
                            >
                                Descargar este reporte
                            </a>
                        </div>
                    </form>
                </div>

                <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div className="rounded-xl border border-gray-200 bg-white p-4 text-center">
                        <p className="text-2xl font-bold text-gray-900">{estadisticas.total}</p>
                        <p className="mt-1 text-xs text-gray-500">Total incidencias</p>
                    </div>
                    <div className="rounded-xl border border-yellow-100 bg-yellow-50 p-4 text-center">
                        <p className="text-2xl font-bold text-yellow-700">{estadisticas.pendientes}</p>
                        <p className="mt-1 text-xs text-gray-500">Pendientes</p>
                    </div>
                    <div className="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-center">
                        <p className="text-2xl font-bold text-emerald-700">{estadisticas.aprobadas}</p>
                        <p className="mt-1 text-xs text-gray-500">Aprobadas</p>
                    </div>
                    <div className="rounded-xl border border-red-100 bg-red-50 p-4 text-center">
                        <p className="text-2xl font-bold text-red-600">{estadisticas.rechazadas}</p>
                        <p className="mt-1 text-xs text-gray-500">Rechazadas</p>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <div className="rounded-xl border border-gray-200 bg-white p-5">
                        <h3 className="mb-4 font-semibold text-gray-900">Por día</h3>
                        {Object.keys(porDia).length === 0
                            ? <p className="text-sm text-gray-400">Sin datos en el período</p>
                            : <BarChart data={porDia} />
                        }
                    </div>
                    <div className="rounded-xl border border-gray-200 bg-white p-5">
                        <h3 className="mb-4 font-semibold text-gray-900">Por estado</h3>
                        {Object.keys(porEstado).length === 0
                            ? <p className="text-sm text-gray-400">Sin datos en el período</p>
                            : <BarChart data={porEstado} colorMap={ESTADO_CHART_COLORS} labelMap={ESTADO_LABELS} />
                        }
                    </div>
                    <div className="rounded-xl border border-gray-200 bg-white p-5">
                        <h3 className="mb-4 font-semibold text-gray-900">Por tipo de incidencia</h3>
                        {Object.keys(porTipoIncidencia).length === 0
                            ? <p className="text-sm text-gray-400">Sin datos en el período</p>
                            : <BarChart data={porTipoIncidencia} colorMap={TIPO_COLORS} labelMap={TIPO_LABELS} />
                        }
                    </div>
                    <div className="rounded-xl border border-gray-200 bg-white p-5">
                        <h3 className="mb-4 font-semibold text-gray-900">Docentes vs Administrativos</h3>
                        {Object.keys(porTipoSolicitante).length === 0
                            ? <p className="text-sm text-gray-400">Sin datos en el período</p>
                            : <BarChart data={porTipoSolicitante} colorMap={SOLICITANTE_COLORS} labelMap={TIPO_SOLICITANTE_LABELS} />
                        }
                    </div>
                    <div className="rounded-xl border border-gray-200 bg-white p-5 md:col-span-2">
                        <h3 className="mb-4 font-semibold text-gray-900">Por área</h3>
                        {Object.keys(porArea).length === 0
                            ? <p className="text-sm text-gray-400">Sin datos en el período</p>
                            : <BarChart data={porArea} />
                        }
                    </div>
                </div>
            </div>
        </>
    );
}
