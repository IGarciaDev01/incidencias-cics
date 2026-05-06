import { Head, Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes/panel';
import { index as chEmpleados,   show as chShow }   from '@/routes/panel/capital_humano/empleados';
import { show as chVerIncidencia }   from '@/routes/panel/capital_humano/incidencias';
import { index as jefeEmpleados, show as jefeShow } from '@/routes/panel/jefe_inmediato/empleados';
import { show as jefeVerIncidencia } from '@/routes/panel/jefe_inmediato/incidencias';
import { index as subdirEmpleados, show as subdirShow } from '@/routes/panel/subdireccion/empleados';
import { show as subdirVerIncidencia } from '@/routes/panel/subdireccion/incidencias';
import { formatDateOnly } from '@/utils/date';

type Opcion = { value: string; name: string };
type Incidencia = {
    id: number;
    folio: string;
    tipo_incidencia: string;
    tipo_solicitante: string;
    fecha_incidencia: string;
    minutos_retardo: number | null;
    estado: string;
    created_at: string;
    area: { id: number; nombre: string } | null;
};

type Empleado = {
    numero_empleado: string;
    reportante_nombre: string;
    email_reportante: string | null;
    tipo: string | null;
};

type Props = {
    empleado: Empleado;
    incidencias: Incidencia[];
    filtros: { fecha?: string; fecha_fin?: string; estado?: string; tipo?: string };
    estados: Opcion[];
    tipos: Opcion[];
    permiso_economico_stats: {
        usados: number;
        disponibles: number;
        total: number;
    } | null;
};

const ESTADO_LABELS: Record<string, string> = {
    pendiente_jefe:           'Pendiente — Jefe',
    pendiente_capital_humano: 'Pendiente — Capital Humano',
    pendiente_subdireccion:   'Pendiente — Subdirección',
    aprobada:                 'Aprobada',
    rechazada:                'Rechazada',
};
const ESTADO_COLORS: Record<string, string> = {
    pendiente_jefe:           'bg-yellow-100 text-yellow-800',
    pendiente_capital_humano: 'bg-orange-100 text-orange-800',
    pendiente_subdireccion:   'bg-blue-100 text-blue-800',
    aprobada:                 'bg-green-100 text-green-800',
    rechazada:                'bg-red-100 text-red-800',
};
const TIPO_LABELS: Record<string, string> = {
    retardo:           'Retardo',
    permiso_economico: 'Permiso Económico',
    comision_oficial:  'Comisión Oficial',
    salida_anticipada: 'Salida Anticipada',
    permiso_sindical:  'Permiso Sindical',
    incidencia_medica: 'Incidencia Médica',
    buena_conducta:    'Incidencia de Buena Conducta',
};
const TIPO_SOLICITANTE_LABELS: Record<string, string> = {
    docente:        'Docente',
    administrativo: 'Administrativo',
};

function useRolBackUrl() {
    const { auth } = usePage().props as { auth: { user?: { rol?: string } } };
    const rol = auth.user?.rol ?? '';

    if (rol === 'jefe_inmediato') {
return { back: jefeEmpleados.url(), verIncidencia: jefeVerIncidencia, showUrl: jefeShow.url };
}

    if (rol === 'capital_humano') {
return { back: chEmpleados.url(), verIncidencia: chVerIncidencia, showUrl: chShow.url };
}

    return { back: subdirEmpleados.url(), verIncidencia: subdirVerIncidencia, showUrl: subdirShow.url };
}

export default function Show({ empleado, incidencias, filtros, estados, tipos, permiso_economico_stats }: Props) {
    const { back: backUrl, verIncidencia: verIncidenciaRoute, showUrl } = useRolBackUrl();

    function handleFiltro(key: string, value: string) {
        router.get(showUrl(empleado.numero_empleado), { ...filtros, [key]: value || undefined }, { preserveState: true, replace: true });
    }

    function handleLimpiar() {
        router.get(showUrl(empleado.numero_empleado), {}, { preserveScroll: true });
    }

    const aprobadas = incidencias.filter((i) => i.estado === 'aprobada').length;
    const rechazadas = incidencias.filter((i) => i.estado === 'rechazada').length;
    const pendientes = incidencias.filter((i) => !['aprobada', 'rechazada'].includes(i.estado)).length;

    const hayFiltros = filtros.fecha || filtros.estado || filtros.tipo;

    return (
        <>
            <Head title={`Empleado ${empleado.numero_empleado}`} />

            <div className="p-4 md:p-6 space-y-6">
                {/* Encabezado */}
                <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div className="flex flex-wrap items-start justify-between gap-3 mb-5">
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <span className="text-xl font-bold text-primary">
                                    {empleado.reportante_nombre.charAt(0).toUpperCase()}
                                </span>
                            </div>
                            <div>
                                <p className="text-xs text-gray-500 font-medium uppercase tracking-wide">
                                    No. Empleado <span className="font-mono text-gray-700">{empleado.numero_empleado}</span>
                                </p>
                                <h2 className="text-xl font-bold text-gray-900">{empleado.reportante_nombre}</h2>
                                {empleado.email_reportante && (
                                    <p className="text-sm text-gray-500 mt-0.5">{empleado.email_reportante}</p>
                                )}
                            </div>
                        </div>
                        <Link href={backUrl} className="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                            Volver a empleados
                        </Link>
                    </div>

                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
                        <div className="text-center p-3 bg-gray-50 rounded-lg">
                            <p className="text-2xl font-bold text-gray-900">{incidencias.length}</p>
                            <p className="text-xs text-gray-500 mt-0.5">Total</p>
                        </div>
                        <div className="text-center p-3 bg-yellow-50 rounded-lg">
                            <p className="text-2xl font-bold text-yellow-600">{pendientes}</p>
                            <p className="text-xs text-gray-500 mt-0.5">En proceso</p>
                        </div>
                        <div className="text-center p-3 bg-green-50 rounded-lg">
                            <p className="text-2xl font-bold text-green-600">{aprobadas}</p>
                            <p className="text-xs text-gray-500 mt-0.5">Aprobadas</p>
                        </div>
                        <div className="text-center p-3 bg-red-50 rounded-lg">
                            <p className="text-2xl font-bold text-red-600">{rechazadas}</p>
                            <p className="text-xs text-gray-500 mt-0.5">Rechazadas</p>
                        </div>
                    </div>

                    {empleado.tipo && (
                        <div className="mt-4 pt-4 border-t border-gray-100 flex items-center gap-2">
                            <span className="text-xs text-gray-500">Tipo de empleado:</span>
                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                {TIPO_SOLICITANTE_LABELS[empleado.tipo] ?? empleado.tipo}
                            </span>
                        </div>
                    )}

                    {/* Permiso Económico stats */}
                    {permiso_economico_stats && (
                        <div className="mt-4 pt-4 border-t border-gray-100">
                            <div className="flex items-center justify-between mb-2">
                                <span className="text-xs text-gray-500">Permisos Económicos del mes</span>
                                <span className="text-xs font-medium text-gray-700">
                                    {permiso_economico_stats.disponibles} de {permiso_economico_stats.total} disponibles
                                </span>
                            </div>
                            <div className="w-full bg-gray-100 rounded-full h-2.5">
                                <div
                                    className="bg-blue-500 h-2.5 rounded-full transition-all duration-300"
                                    style={{ width: `${permiso_economico_stats.total > 0 ? (permiso_economico_stats.disponibles / permiso_economico_stats.total) * 100 : 0}%` }}
                                />
                            </div>
                            <p className="text-xs text-gray-400 mt-1">
                                {permiso_economico_stats.usados} utilizados este mes
                            </p>
                        </div>
                    )}
                </div>

                {/* Historial */}
                <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div className="px-5 py-4 border-b border-gray-100">
                        <h3 className="font-semibold text-gray-900">Historial de incidencias</h3>
                    </div>

                    <div className="flex flex-wrap gap-3 items-center px-5 py-3 bg-gray-50 border-b border-gray-100">
                        <div className="flex flex-col gap-1">
                            <label className="text-xs text-gray-500 font-medium">Desde</label>
                            <Input
                                type="date"
                                className="h-8 w-36 text-sm"
                                value={filtros.fecha ?? ''}
                                onChange={(e) => handleFiltro('fecha', e.target.value)}
                            />
                        </div>
                        <div className="flex flex-col gap-1">
                            <label className="text-xs text-gray-500 font-medium">Hasta</label>
                            <Input
                                type="date"
                                className="h-8 w-36 text-sm"
                                value={filtros.fecha_fin ?? ''}
                                onChange={(e) => handleFiltro('fecha_fin', e.target.value)}
                            />
                        </div>
                        <div className="flex flex-col gap-1">
                            <label className="text-xs text-gray-500 font-medium">Estado</label>
                            <Select value={filtros.estado || '_all_'} onValueChange={(v) => handleFiltro('estado', v === '_all_' ? '' : v)}>
                                <SelectTrigger className="h-8 w-44 text-sm">
                                    <SelectValue placeholder="Todos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="_all_">Todos</SelectItem>
                                    {estados.map((e) => (
                                        <SelectItem key={e.value} value={e.value}>{ESTADO_LABELS[e.value] ?? e.value}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="flex flex-col gap-1">
                            <label className="text-xs text-gray-500 font-medium">Tipo</label>
                            <Select value={filtros.tipo || '_all_'} onValueChange={(v) => handleFiltro('tipo', v === '_all_' ? '' : v)}>
                                <SelectTrigger className="h-8 w-44 text-sm">
                                    <SelectValue placeholder="Todos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="_all_">Todos</SelectItem>
                                    {tipos.map((t) => (
                                        <SelectItem key={t.value} value={t.value}>{TIPO_LABELS[t.value] ?? t.value}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        {hayFiltros && (
                            <Button variant="ghost" size="sm" className="h-8 text-gray-500" onClick={handleLimpiar}>
                                Limpiar
                            </Button>
                        )}
                    </div>

                    {incidencias.length === 0 ? (
                        <p className="p-6 text-sm text-gray-500 text-center">Sin incidencias registradas.</p>
                    ) : (
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600">Folio</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600">Tipo</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600 hidden md:table-cell">Área</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600">Fecha</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600">Estado</th>
                                    <th className="px-4 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {incidencias.map((inc) => (
                                    <tr key={inc.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-4 py-3">
                                            <span className="font-mono text-xs text-primary font-medium">{inc.folio}</span>
                                        </td>
                                        <td className="px-4 py-3 text-gray-900">
                                            {TIPO_LABELS[inc.tipo_incidencia] ?? inc.tipo_incidencia}
                                            {inc.minutos_retardo ? (
                                                <span className="text-gray-400 text-xs ml-1">({inc.minutos_retardo} min)</span>
                                            ) : null}
                                        </td>
                                        <td className="px-4 py-3 text-gray-500 hidden md:table-cell">
                                            {inc.area?.nombre ?? <span className="text-gray-300 italic">Sin área</span>}
                                        </td>
                                        <td className="px-4 py-3 text-gray-500">
                                            {formatDateOnly(inc.fecha_incidencia, false)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${ESTADO_COLORS[inc.estado] ?? 'bg-gray-100 text-gray-600'}`}>
                                                {ESTADO_LABELS[inc.estado] ?? inc.estado}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={verIncidenciaRoute.url(inc.id)}>
                                                    Ver detalle
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
            </div>
        </>
    );
}

Show.layout = {
    breadcrumbs: [
        { title: 'Panel Principal',  href: dashboard.url() },
        { title: 'Empleados' },
    ],
};