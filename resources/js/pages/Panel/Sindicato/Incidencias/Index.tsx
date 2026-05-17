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
import { dashboard } from '@/routes/panel';
import { index, show } from '@/routes/panel/sindicato/incidencias';
import { formatDateOnly } from '@/utils/date';

type Incidencia = {
    id: number;
    folio: string;
    numero_empleado: string;
    reportante_nombre: string;
    tipo_solicitante: string;
    tipo_incidencia: string;
    fecha_incidencia: string;
    estado: string;
    area: { id: number; nombre: string } | null;
    created_at: string;
};

type Paginado<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type EnumItem = { value: string; name: string };

type Props = {
    incidencias: Paginado<Incidencia>;
    filtros: { estado?: string; tipo?: string; buscar?: string; area_id?: string; fecha_inicio?: string; fecha_fin?: string };
    estados: EnumItem[];
    tipos: EnumItem[];
    areas: { id: number; nombre: string }[];
};

const ESTADO_LABELS: Record<string, string> = {
    pendiente_jefe: 'Pendiente (Jefe inmediato)',
    pendiente_capital_humano: 'Pendiente (Capital Humano)',
    pendiente_sindicato: 'Pendiente (Sindicato)',
    pendiente_subdireccion: 'Pendiente (Subdirección)',
    aprobada: 'Aprobada',
    rechazada: 'Rechazada',
};
const ESTADO_COLORS: Record<string, string> = {
    pendiente_jefe: 'bg-yellow-100 text-yellow-800',
    pendiente_capital_humano: 'bg-orange-100 text-orange-800',
    pendiente_sindicato: 'bg-purple-100 text-purple-800',
    pendiente_subdireccion: 'bg-blue-100 text-blue-800',
    aprobada: 'bg-green-100 text-green-800',
    rechazada: 'bg-red-100 text-red-800',
};
const TIPO_LABELS: Record<string, string> = {
    retardo: 'Retardo',
    permiso_economico: 'Permiso Económico',
    comision_oficial: 'Comisión Oficial',
    salida_anticipada: 'Salida Anticipada',
    permiso_sindical: 'Permiso Sindical',
    incidencia_medica: 'Incidencia Médica',
    buena_conducta: 'Buena Conducta',
};

export function formatDate(d: string, forcedHour: boolean) {
    return formatDateOnly(d, forcedHour);
}

export default function Index({ incidencias, filtros, estados, tipos, areas }: Props) {
    function handleFiltro(key: string, value: string) {
        router.get(index.url(), { ...filtros, [key]: value || undefined }, { preserveState: true, replace: true });
    }

    function handleBuscar(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        const fd = new FormData(e.currentTarget);
        router.get(index.url(), { ...filtros, buscar: fd.get('buscar') as string || undefined }, { preserveState: true, replace: true });
    }

    function handleLimpiarFechas() {
        router.get(index.url(), { ...filtros, fecha_inicio: undefined, fecha_fin: undefined }, { preserveState: true, replace: true });
    }

    const hayFiltros = Object.values(filtros).some(Boolean);

    return (
        <>
            <Head title="Incidencias — Sindicato" />

            <div className="p-4 md:p-6 space-y-5">
                <div>
                    <h2 className="text-xl font-semibold text-gray-900">Incidencias — Sindicato</h2>
                    <p className="text-sm text-gray-500">{incidencias.total} registros enviados por Capital Humano</p>
                </div>

                {/* Filtros */}
                <div className="flex flex-wrap gap-3">
                    <form onSubmit={handleBuscar} className="flex gap-2">
                        <Input name="buscar" defaultValue={filtros.buscar} placeholder="Folio, empleado o nombre..." className="w-64" />
                        <Button type="submit" variant="outline" size="sm">Buscar</Button>
                    </form>

                    <Select value={filtros.area_id || '_all_'} onValueChange={(v) => handleFiltro('area_id', v === '_all_' ? '' : v)}>
                        <SelectTrigger className="w-48">
                            <SelectValue placeholder="Todas las áreas" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="_all_">Todas las áreas</SelectItem>
                            {areas.map((a) => (
                                <SelectItem key={a.id} value={String(a.id)}>{a.nombre}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select value={filtros.estado || '_all_'} onValueChange={(v) => handleFiltro('estado', v === '_all_' ? '' : v)}>
                        <SelectTrigger className="w-52">
                            <SelectValue placeholder="Todos los estados" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="_all_">Todos los estados</SelectItem>
                            {estados.map((e) => (
                                <SelectItem key={e.value} value={e.value}>{ESTADO_LABELS[e.value] ?? e.value}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select value={filtros.tipo || '_all_'} onValueChange={(v) => handleFiltro('tipo', v === '_all_' ? '' : v)}>
                        <SelectTrigger className="w-48">
                            <SelectValue placeholder="Todos los tipos" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="_all_">Todos los tipos</SelectItem>
                            {tipos.map((t) => (
                                <SelectItem key={t.value} value={t.value}>{TIPO_LABELS[t.value] ?? t.value}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <div className="flex flex-col gap-1">
                        <label className="text-xs text-gray-500 font-medium">Desde</label>
                        <Input
                            type="date"
                            className="h-9 w-36"
                            value={filtros.fecha_inicio ?? ''}
                            onChange={(e) => handleFiltro('fecha_inicio', e.target.value)}
                        />
                    </div>

                    <div className="flex flex-col gap-1">
                        <label className="text-xs text-gray-500 font-medium">Hasta</label>
                        <Input
                            type="date"
                            className="h-9 w-36"
                            value={filtros.fecha_fin ?? ''}
                            onChange={(e) => handleFiltro('fecha_fin', e.target.value)}
                        />
                    </div>

                    {(filtros.fecha_inicio || filtros.fecha_fin) && (
                        <Button variant="ghost" size="sm" className="h-9 self-end" onClick={handleLimpiarFechas}>
                            Limpiar fechas
                        </Button>
                    )}

                    {hayFiltros && (
                        <Button variant="ghost" size="sm" onClick={() => router.get(index.url())}>
                            Limpiar filtros
                        </Button>
                    )}
                </div>

                {/* Tabla */}
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empleado</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Área</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registrado</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th className="px-4 py-3" />
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {incidencias.data.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="px-4 py-8 text-center text-gray-400">
                                        No se encontraron incidencias
                                    </td>
                                </tr>
                            ) : (
                                incidencias.data.map((inc) => (
                                    <tr key={inc.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-mono text-xs text-primary font-medium">{inc.folio}</td>
                                        <td className="px-4 py-3">
                                            <p className="font-medium text-gray-900">{inc.reportante_nombre}</p>
                                            <p className="text-xs text-gray-500">#{inc.numero_empleado} · {inc.tipo_solicitante}</p>
                                        </td>
                                        <td className="px-4 py-3 text-gray-600">{inc.area?.nombre ?? '—'}</td>
                                        <td className="px-4 py-3 text-gray-700">{TIPO_LABELS[inc.tipo_incidencia] ?? inc.tipo_incidencia}</td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(inc.fecha_incidencia, false)}</td>
                                        <td className="px-4 py-3 text-gray-500 text-xs">{formatDate(inc.created_at, true)}</td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${ESTADO_COLORS[inc.estado] ?? 'bg-gray-100 text-gray-800'}`}>
                                                {ESTADO_LABELS[inc.estado] ?? inc.estado}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={show.url(inc.id)}>Ver</Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {incidencias.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm">
                        <p className="text-gray-500">Mostrando {incidencias.from}–{incidencias.to} de {incidencias.total}</p>
                        <div className="flex gap-1">
                            {incidencias.links.map((link, i) => (
                                <button
                                    key={i}
                                    disabled={!link.url}
                                    onClick={() => link.url && router.get(link.url)}
                                    className={`px-2.5 py-1 rounded text-xs border ${link.active ? 'bg-primary text-primary-foreground border-primary' : link.url ? 'border-gray-200 hover:bg-accent' : 'border-gray-100 text-gray-300 cursor-not-allowed'}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

Index.layout = {
    breadcrumbs: [
        { title: 'Panel Principal', href: dashboard.url() },
        { title: 'Incidencias', href: index.url() },
    ],
};
