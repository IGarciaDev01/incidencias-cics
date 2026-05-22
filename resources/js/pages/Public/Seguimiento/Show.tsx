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
import { ESTADO_COLORS, ESTADO_LABELS, TIPO_LABELS, TIPO_SOLICITANTE_LABELS } from '@/lib/incidencias';
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
    filtros: { fecha?: string; fecha_fin?: string; estado?: string; tipo?: string };
    estadisticas: Estadisticas;
    estados: { value: string; label: string; color: string }[];
    tipos: { value: string; label: string }[];
};

export default function Show({ empleado, incidencias, filtros, estadisticas, estados, tipos }: Props) {
    function handleFiltro(key: string, value: string) {
        router.get(panel.url(), { ...filtros, [key]: value || undefined }, { preserveState: true, replace: true });
    }

    function handleLimpiar() {
        router.get(panel.url(), {}, { preserveScroll: true });
    }

    function handleLogout() {
        router.post(logout.url());
    }

    const hayFiltros = filtros.fecha || filtros.fecha_fin || filtros.estado || filtros.tipo;

    return (
        <>
            <Head title="Mis incidencias" />

            <div className="space-y-6">
                {/* Encabezado */}
                <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div className="flex flex-wrap items-start justify-between gap-3 mb-5">
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <span className="text-xl font-bold text-primary">
                                    {empleado.nombre.charAt(0).toUpperCase()}
                                </span>
                            </div>
                            <div>
                                <p className="text-xs text-gray-500 font-medium uppercase tracking-wide">
                                    No. Empleado <span className="font-mono text-gray-700">{empleado.numero_empleado}</span>
                                </p>
                                <h2 className="text-xl font-bold text-gray-900">{empleado.nombre}</h2>
                                {empleado.email && (
                                    <p className="text-sm text-gray-500 mt-0.5">{empleado.email}</p>
                                )}
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <Button variant="ghost" size="sm" onClick={handleLogout}>
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
                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                {TIPO_SOLICITANTE_LABELS[empleado.tipo] ?? empleado.tipo}
                            </span>
                        </div>
                    )}

                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
                        <div className="text-center p-3 bg-gray-50 rounded-lg">
                            <p className="text-2xl font-bold text-gray-900">{estadisticas.total}</p>
                            <p className="text-xs text-gray-500 mt-0.5">Total</p>
                        </div>
                        <div className="text-center p-3 bg-yellow-50 rounded-lg">
                            <p className="text-2xl font-bold text-yellow-600">{estadisticas.pendientes}</p>
                            <p className="text-xs text-gray-500 mt-0.5">En proceso</p>
                        </div>
                        <div className="text-center p-3 bg-green-50 rounded-lg">
                            <p className="text-2xl font-bold text-green-600">{estadisticas.aprobadas}</p>
                            <p className="text-xs text-gray-500 mt-0.5">Aprobadas</p>
                        </div>
                        <div className="text-center p-3 bg-red-50 rounded-lg">
                            <p className="text-2xl font-bold text-red-600">{estadisticas.rechazadas}</p>
                            <p className="text-xs text-gray-500 mt-0.5">Rechazadas</p>
                        </div>
                    </div>
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
                                        <SelectItem key={e.value} value={e.value}>{ESTADO_LABELS[e.value] ?? e.label}</SelectItem>
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
                                        <SelectItem key={t.value} value={t.value}>{TIPO_LABELS[t.value] ?? t.label}</SelectItem>
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

                    {incidencias.data.length === 0 ? (
                        <p className="p-6 text-sm text-gray-500 text-center">
                            {hayFiltros
                                ? 'No se encontraron incidencias con los filtros seleccionados.'
                                : 'Aún no tienes incidencias registradas.'}
                        </p>
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
                                {incidencias.data.map((inc) => (
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
                                            {inc.fecha_incidencia ? formatDateOnly(inc.fecha_incidencia, false) : '—'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${ESTADO_COLORS[inc.estado] ?? 'bg-gray-100 text-gray-600'}`}>
                                                {ESTADO_LABELS[inc.estado] ?? inc.estado}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={verDetalle.url(inc.folio)}>
                                                    Ver detalle
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}

                    {/* Paginación */}
                    {incidencias.last_page > 1 && (
                        <div className="flex justify-center gap-1 py-4 border-t border-gray-100">
                            {incidencias.links.map((link, i) => (
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
                </div>
            </div>
        </>
    );
}
