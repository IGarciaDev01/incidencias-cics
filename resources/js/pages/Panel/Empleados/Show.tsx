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
import {
    ESTADO_COLORS,
    ESTADO_LABELS,
    TIPO_LABELS,
    TIPO_SOLICITANTE_LABELS,
} from '@/lib/incidencias';
import { dashboard } from '@/routes/panel';
import {
    index as chEmpleados,
    show as chShow,
    edit as chEdit,
} from '@/routes/panel/capital_humano/empleados';
import { show as chVerIncidencia } from '@/routes/panel/capital_humano/incidencias';
import {
    index as jefeEmpleados,
    show as jefeShow,
} from '@/routes/panel/jefe_inmediato/empleados';
import { show as jefeVerIncidencia } from '@/routes/panel/jefe_inmediato/incidencias';
import {
    index as sindicatoEmpleados,
    show as sindicatoShow,
    edit as sindicatoEdit,
} from '@/routes/panel/sindicato/empleados';
import { show as sindicatoVerIncidencia } from '@/routes/panel/sindicato/incidencias';
import {
    index as subdirEmpleados,
    show as subdirShow,
    edit as subdirEdit,
} from '@/routes/panel/subdireccion/empleados';
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
    filtros: {
        fecha?: string;
        fecha_fin?: string;
        estado?: string;
        tipo?: string;
    };
    estados: Opcion[];
    tipos: Opcion[];
    permiso_economico_stats: {
        mensual: { usados: number; disponibles: number; total: number };
        anual: { usados: number; disponibles: number; total: number };
    } | null;
};

function useRolBackUrl(numeroEmpleado?: string) {
    const { auth } = usePage().props as { auth: { user?: { rol?: string } } };
    const rol = auth.user?.rol ?? '';

    if (rol === 'jefe_inmediato') {
        return {
            back: jefeEmpleados.url(),
            verIncidencia: jefeVerIncidencia,
            showUrl: jefeShow.url,
            editUrl: undefined,
            puedeEditar: false,
        };
    }

    if (rol === 'capital_humano') {
        return {
            back: chEmpleados.url(),
            verIncidencia: chVerIncidencia,
            showUrl: chShow.url,
            editUrl: numeroEmpleado
                ? chEdit.url({ numeroEmpleado })
                : undefined,
            puedeEditar: false,
        };
    }

    if (rol === 'sindicato') {
        return {
            back: sindicatoEmpleados.url(),
            verIncidencia: sindicatoVerIncidencia,
            showUrl: sindicatoShow.url,
            editUrl: numeroEmpleado
                ? sindicatoEdit.url({ numeroEmpleado })
                : undefined,
            puedeEditar: false,
        };
    }

    return {
        back: subdirEmpleados.url(),
        verIncidencia: subdirVerIncidencia,
        showUrl: subdirShow.url,
        editUrl: numeroEmpleado
            ? subdirEdit.url({ numeroEmpleado })
            : undefined,
        puedeEditar: true,
    };
}

export default function Show({
    empleado,
    incidencias,
    filtros,
    estados,
    tipos,
    permiso_economico_stats,
}: Props) {
    const {
        back: backUrl,
        verIncidencia: verIncidenciaRoute,
        showUrl,
        editUrl,
        puedeEditar,
    } = useRolBackUrl(empleado.numero_empleado);

    function handleFiltro(key: string, value: string) {
        router.get(
            showUrl(empleado.numero_empleado),
            { ...filtros, [key]: value || undefined },
            { preserveState: true, replace: true },
        );
    }

    function handleLimpiar() {
        router.get(
            showUrl(empleado.numero_empleado),
            {},
            { preserveScroll: true },
        );
    }

    const aprobadas = incidencias.filter((i) => i.estado === 'aprobada').length;
    const rechazadas = incidencias.filter(
        (i) => i.estado === 'rechazada',
    ).length;
    const pendientes = incidencias.filter(
        (i) => !['aprobada', 'rechazada'].includes(i.estado),
    ).length;

    const hayFiltros =
        filtros.fecha || filtros.fecha_fin || filtros.estado || filtros.tipo;

    return (
        <>
            <Head title={`Empleado ${empleado.numero_empleado}`} />

            <div className="space-y-6 p-4 md:p-6">
                {/* Encabezado */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-5 flex flex-wrap items-start justify-between gap-3">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                <span className="text-xl font-bold text-primary">
                                    {empleado.reportante_nombre
                                        .charAt(0)
                                        .toUpperCase()}
                                </span>
                            </div>
                            <div>
                                <p className="text-xs font-medium tracking-wide text-gray-500 uppercase">
                                    No. Empleado{' '}
                                    <span className="font-mono text-gray-700">
                                        {empleado.numero_empleado}
                                    </span>
                                </p>
                                <h2 className="text-xl font-bold text-gray-900">
                                    {empleado.reportante_nombre}
                                </h2>
                                {empleado.email_reportante && (
                                    <p className="mt-0.5 text-sm text-gray-500">
                                        {empleado.email_reportante}
                                    </p>
                                )}
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            {puedeEditar && editUrl && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={editUrl}>Editar empleado</Link>
                                </Button>
                            )}
                            <Link
                                href={backUrl}
                                className="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700"
                            >
                                Volver a empleados
                            </Link>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 md:grid-cols-4">
                        <div className="rounded-lg bg-gray-50 p-3 text-center">
                            <p className="text-2xl font-bold text-gray-900">
                                {incidencias.length}
                            </p>
                            <p className="mt-0.5 text-xs text-gray-500">
                                Total
                            </p>
                        </div>
                        <div className="rounded-lg bg-yellow-50 p-3 text-center">
                            <p className="text-2xl font-bold text-yellow-600">
                                {pendientes}
                            </p>
                            <p className="mt-0.5 text-xs text-gray-500">
                                En proceso
                            </p>
                        </div>
                        <div className="rounded-lg bg-green-50 p-3 text-center">
                            <p className="text-2xl font-bold text-green-600">
                                {aprobadas}
                            </p>
                            <p className="mt-0.5 text-xs text-gray-500">
                                Aprobadas
                            </p>
                        </div>
                        <div className="rounded-lg bg-red-50 p-3 text-center">
                            <p className="text-2xl font-bold text-red-600">
                                {rechazadas}
                            </p>
                            <p className="mt-0.5 text-xs text-gray-500">
                                Rechazadas
                            </p>
                        </div>
                    </div>

                    {empleado.tipo && (
                        <div className="mt-4 flex items-center gap-2 border-t border-gray-100 pt-4">
                            <span className="text-xs text-gray-500">
                                Tipo de empleado:
                            </span>
                            <span className="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                {TIPO_SOLICITANTE_LABELS[empleado.tipo] ??
                                    empleado.tipo}
                            </span>
                        </div>
                    )}

                    {/* Permiso Económico stats */}
                    {permiso_economico_stats && (
                        <div className="mt-4 space-y-3 border-t border-gray-100 pt-4">
                            <div>
                                <div className="mb-1 flex items-center justify-between">
                                    <span className="text-xs text-gray-500">
                                        Permisos Económicos del mes
                                    </span>
                                    <span className="text-xs font-medium text-gray-700">
                                        {
                                            permiso_economico_stats.mensual
                                                .disponibles
                                        }{' '}
                                        de{' '}
                                        {permiso_economico_stats.mensual.total}{' '}
                                        disponibles
                                    </span>
                                </div>
                                <div className="h-2 w-full rounded-full bg-gray-100">
                                    <div
                                        className="h-2 rounded-full bg-blue-500 transition-all duration-300"
                                        style={{
                                            width: `${permiso_economico_stats.mensual.total > 0 ? (permiso_economico_stats.mensual.disponibles / permiso_economico_stats.mensual.total) * 100 : 0}%`,
                                        }}
                                    />
                                </div>
                                <p className="mt-0.5 text-xs text-gray-400">
                                    {permiso_economico_stats.mensual.usados}{' '}
                                    utilizados este mes
                                </p>
                            </div>
                            <div>
                                <div className="mb-1 flex items-center justify-between">
                                    <span className="text-xs text-gray-500">
                                        Permisos Económicos del año
                                    </span>
                                    <span className="text-xs font-medium text-gray-700">
                                        {
                                            permiso_economico_stats.anual
                                                .disponibles
                                        }{' '}
                                        de {permiso_economico_stats.anual.total}{' '}
                                        disponibles
                                    </span>
                                </div>
                                <div className="h-2 w-full rounded-full bg-gray-100">
                                    <div
                                        className="h-2 rounded-full bg-indigo-500 transition-all duration-300"
                                        style={{
                                            width: `${permiso_economico_stats.anual.total > 0 ? (permiso_economico_stats.anual.disponibles / permiso_economico_stats.anual.total) * 100 : 0}%`,
                                        }}
                                    />
                                </div>
                                <p className="mt-0.5 text-xs text-gray-400">
                                    {permiso_economico_stats.anual.usados}{' '}
                                    utilizados este año
                                </p>
                            </div>
                        </div>
                    )}
                </div>

                {/* Historial */}
                <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div className="border-b border-gray-100 px-5 py-4">
                        <h3 className="font-semibold text-gray-900">
                            Historial de incidencias
                        </h3>
                    </div>

                    <div className="flex flex-wrap items-center gap-3 border-b border-gray-100 bg-gray-50 px-5 py-3">
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-medium text-gray-500">
                                Desde
                            </label>
                            <Input
                                type="date"
                                className="h-8 w-36 text-sm"
                                value={filtros.fecha ?? ''}
                                onChange={(e) =>
                                    handleFiltro('fecha', e.target.value)
                                }
                            />
                        </div>
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-medium text-gray-500">
                                Hasta
                            </label>
                            <Input
                                type="date"
                                className="h-8 w-36 text-sm"
                                value={filtros.fecha_fin ?? ''}
                                onChange={(e) =>
                                    handleFiltro('fecha_fin', e.target.value)
                                }
                            />
                        </div>
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-medium text-gray-500">
                                Estado
                            </label>
                            <Select
                                value={filtros.estado || '_all_'}
                                onValueChange={(v) =>
                                    handleFiltro(
                                        'estado',
                                        v === '_all_' ? '' : v,
                                    )
                                }
                            >
                                <SelectTrigger className="h-8 w-44 text-sm">
                                    <SelectValue placeholder="Todos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="_all_">Todos</SelectItem>
                                    {estados.map((e) => (
                                        <SelectItem
                                            key={e.value}
                                            value={e.value}
                                        >
                                            {ESTADO_LABELS[e.value] ?? e.value}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-medium text-gray-500">
                                Tipo
                            </label>
                            <Select
                                value={filtros.tipo || '_all_'}
                                onValueChange={(v) =>
                                    handleFiltro('tipo', v === '_all_' ? '' : v)
                                }
                            >
                                <SelectTrigger className="h-8 w-44 text-sm">
                                    <SelectValue placeholder="Todos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="_all_">Todos</SelectItem>
                                    {tipos.map((t) => (
                                        <SelectItem
                                            key={t.value}
                                            value={t.value}
                                        >
                                            {TIPO_LABELS[t.value] ?? t.value}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        {hayFiltros && (
                            <Button
                                variant="ghost"
                                size="sm"
                                className="h-8 text-gray-500"
                                onClick={handleLimpiar}
                            >
                                Limpiar
                            </Button>
                        )}
                    </div>

                    {incidencias.length === 0 ? (
                        <p className="p-6 text-center text-sm text-gray-500">
                            Sin incidencias registradas.
                        </p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[820px] text-sm">
                                <thead className="border-b border-gray-100 bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-medium text-gray-600">
                                            Folio
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium text-gray-600">
                                            Tipo
                                        </th>
                                        <th className="hidden px-4 py-3 text-left font-medium text-gray-600 md:table-cell">
                                            Área
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium text-gray-600">
                                            Fecha
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium text-gray-600">
                                            Registrada
                                        </th>
                                        <th className="px-4 py-3 text-left font-medium text-gray-600">
                                            Estado
                                        </th>
                                        <th className="px-4 py-3" />
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {incidencias.map((inc) => (
                                        <tr
                                            key={inc.id}
                                            className="transition-colors hover:bg-gray-50"
                                        >
                                            <td className="px-4 py-3">
                                                <span className="font-mono text-xs font-medium text-primary">
                                                    {inc.folio}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-gray-900">
                                                {TIPO_LABELS[
                                                    inc.tipo_incidencia
                                                ] ?? inc.tipo_incidencia}
                                                {inc.minutos_retardo ? (
                                                    <span className="ml-1 text-xs text-gray-400">
                                                        ({inc.minutos_retardo}{' '}
                                                        min)
                                                    </span>
                                                ) : null}
                                            </td>
                                            <td className="hidden px-4 py-3 text-gray-500 md:table-cell">
                                                {inc.area?.nombre ?? (
                                                    <span className="text-gray-300 italic">
                                                        Sin área
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-gray-500">
                                                {formatDateOnly(
                                                    inc.fecha_incidencia,
                                                    false,
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-gray-500">
                                                {formatDateOnly(
                                                    inc.created_at,
                                                    true,
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                <span
                                                    className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${ESTADO_COLORS[inc.estado] ?? 'bg-gray-100 text-gray-600'}`}
                                                >
                                                    {ESTADO_LABELS[
                                                        inc.estado
                                                    ] ?? inc.estado}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link
                                                        href={verIncidenciaRoute.url(
                                                            inc.id,
                                                        )}
                                                    >
                                                        Ver detalle
                                                    </Link>
                                                </Button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

Show.layout = {
    breadcrumbs: [
        { title: 'Panel Principal', href: dashboard.url() },
        { title: 'Empleados' },
    ],
};
