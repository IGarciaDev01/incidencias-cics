import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes/panel';
import { index } from '@/routes/panel/admin/logs';

type LogItem = {
    id: number;
    tipo_accion: string;
    comentario: string | null;
    es_interno: boolean;
    created_at: string;
    incidencia: { id: number; folio: string; titulo: string; estado: string } | null;
    user: { id: number; nombre: string } | null;
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

type Props = {
    logs: Paginado<LogItem>;
    filtros: {
        tipo_accion?: string;
        user_id?: string;
        folio?: string;
        desde?: string;
        hasta?: string;
    };
    tiposAccion: string[];
    usuarios: { id: number; nombre: string }[];
};

const ACCION_LABELS: Record<string, string> = {
    creada: 'Incidencia creada',
    aprobada: 'Aprobada',
    rechazada: 'Rechazada',
    asignada: 'Asignada',
    reasignada: 'Reasignada',
    en_proceso: 'En proceso',
    resuelta: 'Resuelta',
    cerrada: 'Cerrada',
    reabierta: 'Reabierta',
    comentario: 'Comentario',
    solicitud_info: 'Solicitud de info',
    archivo_adjunto: 'Archivo adjunto',
};

const ESTADO_COLORS: Record<string, string> = {
    abierta: 'bg-blue-100 text-blue-800',
    en_revision: 'bg-purple-100 text-purple-800',
    aprobada: 'bg-indigo-100 text-indigo-800',
    rechazada: 'bg-red-100 text-red-800',
    en_proceso: 'bg-orange-100 text-orange-800',
    resuelta: 'bg-green-100 text-green-800',
    cerrada: 'bg-gray-100 text-gray-800',
};

export default function Index({ logs, filtros, tiposAccion, usuarios }: Props) {
    function handleFiltro(key: string, value: string) {
        router.get(
            index.url(),
            { ...filtros, [key]: value || undefined },
            { preserveState: true, replace: true },
        );
    }

    function handleBuscar(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        const fd = new FormData(e.currentTarget);
        router.get(
            index.url(),
            {
                ...filtros,
                folio: (fd.get('folio') as string) || undefined,
                desde: (fd.get('desde') as string) || undefined,
                hasta: (fd.get('hasta') as string) || undefined,
            },
            { preserveState: true, replace: true },
        );
    }

    const hayFiltros = Object.values(filtros).some(Boolean);

    return (
        <>
            <Head title="Logs de actividad" />

            <div className="p-4 md:p-6 space-y-5">
                <div>
                    <h2 className="text-xl font-semibold text-gray-900">Logs de actividad</h2>
                    <p className="text-sm text-gray-500">{logs.total} registros</p>
                </div>

                {/* Filtros */}
                <div className="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                    <form onSubmit={handleBuscar} className="grid md:grid-cols-4 gap-3">
                        <div className="grid gap-1">
                            <Label className="text-xs">Folio</Label>
                            <Input name="folio" defaultValue={filtros.folio} placeholder="INC-2024-001" className="h-8 text-xs" />
                        </div>
                        <div className="grid gap-1">
                            <Label className="text-xs">Desde</Label>
                            <Input name="desde" type="date" defaultValue={filtros.desde} className="h-8 text-xs" />
                        </div>
                        <div className="grid gap-1">
                            <Label className="text-xs">Hasta</Label>
                            <Input name="hasta" type="date" defaultValue={filtros.hasta} className="h-8 text-xs" />
                        </div>
                        <div className="flex items-end gap-2">
                            <Button type="submit" size="sm" className="h-8">Buscar</Button>
                            {hayFiltros && (
                                <Button variant="ghost" size="sm" className="h-8" onClick={() => router.get(index.url())}>
                                    Limpiar
                                </Button>
                            )}
                        </div>
                    </form>

                    <div className="grid md:grid-cols-2 gap-3">
                        <div className="grid gap-1">
                            <Label className="text-xs">Tipo de acción</Label>
                            <Select value={filtros.tipo_accion || '_all_'} onValueChange={(v) => handleFiltro('tipo_accion', v === '_all_' ? '' : v)}>
                                <SelectTrigger className="h-8 text-xs">
                                    <SelectValue placeholder="Todos los tipos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="_all_">Todos los tipos</SelectItem>
                                    {tiposAccion.map((t) => (
                                        <SelectItem key={t} value={t}>{ACCION_LABELS[t] ?? t}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="grid gap-1">
                            <Label className="text-xs">Usuario</Label>
                            <Select value={filtros.user_id || '_all_'} onValueChange={(v) => handleFiltro('user_id', v === '_all_' ? '' : v)}>
                                <SelectTrigger className="h-8 text-xs">
                                    <SelectValue placeholder="Todos los usuarios" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="_all_">Todos los usuarios</SelectItem>
                                    {usuarios.map((u) => (
                                        <SelectItem key={u.id} value={String(u.id)}>{u.nombre}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                {/* Tabla */}
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comentario</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {logs.data.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="px-4 py-8 text-center text-gray-400">
                                        No se encontraron registros
                                    </td>
                                </tr>
                            ) : (
                                logs.data.map((log) => (
                                    <tr key={log.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 text-gray-500 whitespace-nowrap">
                                            {new Date(log.created_at).toLocaleString('es-MX', {
                                                day: '2-digit',
                                                month: '2-digit',
                                                year: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })}
                                        </td>
                                        <td className="px-4 py-3">
                                            {log.incidencia ? (
                                                <div>
                                                    <p className="font-mono text-xs text-primary font-medium">{log.incidencia.folio}</p>
                                                    <span className={`inline-flex items-center px-1.5 py-0.5 rounded text-xs ${ESTADO_COLORS[log.incidencia.estado] ?? 'bg-gray-100 text-gray-800'}`}>
                                                        {log.incidencia.estado}
                                                    </span>
                                                </div>
                                            ) : '—'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                {ACCION_LABELS[log.tipo_accion] ?? log.tipo_accion}
                                            </span>
                                            {log.es_interno && (
                                                <span className="ml-1 text-xs text-gray-400">(interno)</span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 text-gray-700">{log.user?.nombre ?? 'Sistema'}</td>
                                        <td className="px-4 py-3 text-gray-500 max-w-xs truncate">
                                            {log.comentario ?? '—'}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {logs.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm">
                        <p className="text-gray-500">Mostrando {logs.from}–{logs.to} de {logs.total}</p>
                        <div className="flex gap-1">
                            {logs.links.map((link, i) => (
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
        { title: 'Dashboard', href: dashboard.url() },
        { title: 'Logs de actividad', href: index.url() },
    ],
};
