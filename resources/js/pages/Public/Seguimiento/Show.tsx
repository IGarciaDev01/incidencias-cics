import { Head, Link, router } from '@inertiajs/react';
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
import { create as nuevaIncidencia } from '@/routes/incidencias';
import { logout, show as verDetalle, panel } from '@/routes/seguimiento';
import { formatDateOnly } from '@/utils/date';

type Incidencia = {
    id: number;
    folio: string;
    tipo_incidencia: string;
    fecha_incidencia: string | null;
    hora_incidencia: string | null;
    minutos_retardo: number | null;
    estado: string;
    created_at: string;
    area: { id: number; nombre: string } | null;
    archivos_count: number;
};

type Paginado<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Empleado = {
    numero_empleado: string;
    nombre: string;
    email: string | null;
    tipo: string | null;
};

type Estadisticas = {
    total: number;
    pendientes: number;
    aprobadas: number;
    rechazadas: number;
};

type Props = {
    empleado: Empleado;
    incidencias: Paginado<Incidencia>;
    filtros: {
        fecha?: string;
        fecha_fin?: string;
        estado?: string;
        tipo?: string;
    };
    estadisticas: Estadisticas;
    estados: { value: string; label: string; color: string }[];
    tipos: { value: string; label: string }[];
};

export default function Show({
    empleado,
    incidencias,
    filtros,
    estadisticas,
    estados,
    tipos,
}: Props) {
    function handleFiltro(key: string, value: string) {
        router.get(
            panel.url(),
            { ...filtros, [key]: value || undefined },
            { preserveState: true, replace: true },
        );
    }

    function handleLimpiar() {
        router.get(panel.url(), {}, { preserveScroll: true });
    }

    function handleLogout() {
        router.post(logout.url());
    }

    const hayFiltros =
        filtros.fecha || filtros.fecha_fin || filtros.estado || filtros.tipo;

    return (
        <>
            <Head title="Mis incidencias" />

            <div className="space-y-6">
                {/* Encabezado */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-5 flex flex-wrap items-start justify-between gap-3">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                <span className="text-xl font-bold text-primary">
                                    {empleado.nombre.charAt(0).toUpperCase()}
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
                                    {empleado.nombre}
                                </h2>
                                {empleado.email && (
                                    <p className="mt-0.5 text-sm text-gray-500">
                                        {empleado.email}
                                    </p>
                                )}
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={handleLogout}
                            >
                                Cerrar sesión
                            </Button>
                            <Button variant="outline" size="sm" asChild>
                                <Link href={nuevaIncidencia.url()}>
                                    Nueva incidencia
                                </Link>
                            </Button>
                        </div>
                    </div>

                    {empleado.tipo && (
                        <div className="mb-4 flex items-center gap-2">
                            <span className="text-xs text-gray-500">Tipo:</span>
                            <span className="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                {TIPO_SOLICITANTE_LABELS[empleado.tipo] ??
                                    empleado.tipo}
                            </span>
                        </div>
                    )}

                    <div className="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 md:grid-cols-4">
                        <div className="rounded-lg bg-gray-50 p-3 text-center">
                            <p className="text-2xl font-bold text-gray-900">
                                {estadisticas.total}
                            </p>
                            <p className="mt-0.5 text-xs text-gray-500">
                                Total
                            </p>
                        </div>
                        <div className="rounded-lg bg-yellow-50 p-3 text-center">
                            <p className="text-2xl font-bold text-yellow-600">
                                {estadisticas.pendientes}
                            </p>
                            <p className="mt-0.5 text-xs text-gray-500">
                                En proceso
                            </p>
                        </div>
                        <div className="rounded-lg bg-green-50 p-3 text-center">
                            <p className="text-2xl font-bold text-green-600">
                                {estadisticas.aprobadas}
                            </p>
                            <p className="mt-0.5 text-xs text-gray-500">
                                Aprobadas
                            </p>
                        </div>
                        <div className="rounded-lg bg-red-50 p-3 text-center">
                            <p className="text-2xl font-bold text-red-600">
                                {estadisticas.rechazadas}
                            </p>
                            <p className="mt-0.5 text-xs text-gray-500">
                                Rechazadas
                            </p>
                        </div>
                    </div>
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
                                            {ESTADO_LABELS[e.value] ?? e.label}
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
                                            {TIPO_LABELS[t.value] ?? t.label}
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

                    {incidencias.data.length === 0 ? (
                        <p className="p-6 text-center text-sm text-gray-500">
                            {hayFiltros
                                ? 'No se encontraron incidencias con los filtros seleccionados.'
                                : 'Aún no tienes incidencias registradas.'}
                        </p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[760px] text-sm">
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
                                            Estado
                                        </th>
                                        <th className="px-4 py-3" />
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {incidencias.data.map((inc) => (
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
                                                {inc.fecha_incidencia
                                                    ? formatDateOnly(
                                                          inc.fecha_incidencia,
                                                          false,
                                                      )
                                                    : '—'}
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
                                                        href={verDetalle.url(
                                                            inc.folio,
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

                    {/* Paginación */}
                    {incidencias.last_page > 1 && (
                        <div className="flex justify-center gap-1 border-t border-gray-100 py-4">
                            {incidencias.links.map((link, i) => (
                                <button
                                    key={i}
                                    disabled={!link.url}
                                    onClick={() =>
                                        link.url && router.get(link.url)
                                    }
                                    className={`rounded px-3 py-1.5 text-xs font-medium transition-colors ${
                                        link.active
                                            ? 'bg-primary text-primary-foreground'
                                            : link.url
                                              ? 'border border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
                                              : 'cursor-not-allowed border border-gray-200 bg-white text-gray-300'
                                    }`}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
