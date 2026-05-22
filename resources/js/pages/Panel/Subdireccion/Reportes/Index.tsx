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
import { dashboard } from '@/routes/panel';
import { index, exportar } from '@/routes/panel/subdireccion/reportes';

type Props = {
    filtros: { desde: string; hasta: string };
    porEstado: Record<string, number>;
    porTipoIncidencia: Record<string, number>;
    porTipoSolicitante: Record<string, number>;
    porArea: Record<string, number>;
};

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
                    <span className="text-xs text-gray-600 w-36 truncate">{labelMap?.[key] ?? key}</span>
                    <div className="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                        <div
                            className={`h-full rounded-full ${colorMap?.[key] ?? 'bg-blue-400'}`}
                            style={{ width: `${(value / max) * 100}%` }}
                        />
                    </div>
                    <span className="text-xs font-medium text-gray-900 w-8 text-right">{value}</span>
                </div>
            ))}
        </div>
    );
}

export default function Index({ filtros, porEstado, porTipoIncidencia, porTipoSolicitante, porArea }: Props) {
    const total = Object.values(porEstado).reduce((a, b) => a + b, 0);
    const aprobadas = porEstado['aprobada'] ?? 0;

    function handleFiltrar(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        const fd = new FormData(e.currentTarget);
        router.get(index.url(), { desde: fd.get('desde') as string, hasta: fd.get('hasta') as string });
    }

    return (
        <>
            <Head title="Reportes" />

            <div className="p-4 md:p-6 space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 className="text-xl font-semibold text-gray-900">Reportes</h2>
                        <p className="text-sm text-gray-500">
                            {new Date(filtros.desde + 'T12:00:00').toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric' })}
                            {' — '}
                            {new Date(filtros.hasta + 'T12:00:00').toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric' })}
                        </p>
                    </div>
                    <a
                        href={exportar.url({ query: { desde: filtros.desde, hasta: filtros.hasta } })}
                        className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md border border-gray-200 hover:bg-gray-50 transition-colors"
                    >
                        Exportar CSV
                    </a>
                </div>

                {/* Filtro */}
                <form onSubmit={handleFiltrar} className="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-4 items-end">
                    <div className="grid gap-1.5">
                        <Label className="text-xs">Desde</Label>
                        <Input type="date" name="desde" defaultValue={filtros.desde} className="h-8 w-40" />
                    </div>
                    <div className="grid gap-1.5">
                        <Label className="text-xs">Hasta</Label>
                        <Input type="date" name="hasta" defaultValue={filtros.hasta} className="h-8 w-40" />
                    </div>
                    <Button type="submit" size="sm" className="h-8">Aplicar</Button>
                </form>

                {/* KPIs */}
                <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div className="bg-white rounded-xl border border-gray-200 p-4 text-center">
                        <p className="text-2xl font-bold text-gray-900">{total}</p>
                        <p className="text-xs text-gray-500 mt-1">Total incidencias</p>
                    </div>
                    <div className="bg-emerald-50 rounded-xl border border-emerald-100 p-4 text-center">
                        <p className="text-2xl font-bold text-emerald-700">{aprobadas}</p>
                        <p className="text-xs text-gray-500 mt-1">Aprobadas</p>
                    </div>
                    <div className="bg-red-50 rounded-xl border border-red-100 p-4 text-center">
                        <p className="text-2xl font-bold text-red-600">{porEstado['rechazada'] ?? 0}</p>
                        <p className="text-xs text-gray-500 mt-1">Rechazadas</p>
                    </div>
                </div>

                {/* Gráficas */}
                <div className="grid md:grid-cols-2 gap-6">
                    <div className="bg-white rounded-xl border border-gray-200 p-5">
                        <h3 className="font-semibold text-gray-900 mb-4">Por estado</h3>
                        {Object.keys(porEstado).length === 0
                            ? <p className="text-sm text-gray-400">Sin datos en el período</p>
                            : <BarChart data={porEstado} colorMap={ESTADO_CHART_COLORS} labelMap={ESTADO_LABELS} />
                        }
                    </div>

                    <div className="bg-white rounded-xl border border-gray-200 p-5">
                        <h3 className="font-semibold text-gray-900 mb-4">Por tipo de incidencia</h3>
                        {Object.keys(porTipoIncidencia).length === 0
                            ? <p className="text-sm text-gray-400">Sin datos en el período</p>
                            : <BarChart data={porTipoIncidencia} colorMap={TIPO_COLORS} labelMap={TIPO_LABELS} />
                        }
                    </div>

                    <div className="bg-white rounded-xl border border-gray-200 p-5">
                        <h3 className="font-semibold text-gray-900 mb-4">Docentes vs Administrativos</h3>
                        {Object.keys(porTipoSolicitante).length === 0
                            ? <p className="text-sm text-gray-400">Sin datos en el período</p>
                            : <BarChart
                                data={porTipoSolicitante}
                                colorMap={SOLICITANTE_COLORS}
                                labelMap={TIPO_SOLICITANTE_LABELS}
                            />
                        }
                    </div>

                    <div className="bg-white rounded-xl border border-gray-200 p-5">
                        <h3 className="font-semibold text-gray-900 mb-4">Por área</h3>
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

Index.layout = {
    breadcrumbs: [
        { title: 'Panel Principal', href: dashboard.url() },
        { title: 'Reportes', href: index.url() },
    ],
};
