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
import { ESTADO_COLORS, ESTADO_LABELS } from '@/lib/incidencias';
import { dashboard } from '@/routes/panel';
import { index } from '@/routes/panel/subdireccion/admin/logs';

type LogItem = {
    id: number;
    action: string;
    action_label: string;
    action_category: string;
    description: string;
    actor_type: string;
    actor_identifier: string | null;
    created_at: string;
    incidencia: {
        id: number;
        folio: string;
        reportante_nombre: string;
        estado: string;
    } | null;
    actor: { id: number; nombre: string; rol: string } | null;
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
        action?: string;
        actor_user_id?: string;
        folio?: string;
        desde?: string;
        hasta?: string;
    };
    acciones: { value: string; label: string; category: string }[];
    usuarios: { id: number; nombre: string }[];
};

const ACTOR_TYPE_LABELS: Record<string, string> = {
    user: 'Usuario interno',
    empleado: 'Empleado',
    system: 'Sistema',
};

export default function Index({ logs, filtros, acciones, usuarios }: Props) {
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

            <div className="space-y-5 p-4 md:p-6">
                <div>
                    <h2 className="text-xl font-semibold text-gray-900">
                        Logs de actividad
                    </h2>
                    <p className="text-sm text-gray-500">
                        {logs.total} registros
                    </p>
                </div>

                {/* Filtros */}
                <div className="space-y-3 rounded-xl border border-gray-200 bg-white p-4">
                    <form
                        onSubmit={handleBuscar}
                        className="grid gap-3 md:grid-cols-4"
                    >
                        <div className="grid gap-1">
                            <Label className="text-xs">Folio</Label>
                            <Input
                                name="folio"
                                defaultValue={filtros.folio}
                                placeholder="INC-2024-001"
                                className="h-8 text-xs"
                            />
                        </div>
                        <div className="grid gap-1">
                            <Label className="text-xs">Desde</Label>
                            <Input
                                name="desde"
                                type="date"
                                defaultValue={filtros.desde}
                                className="h-8 text-xs"
                            />
                        </div>
                        <div className="grid gap-1">
                            <Label className="text-xs">Hasta</Label>
                            <Input
                                name="hasta"
                                type="date"
                                defaultValue={filtros.hasta}
                                className="h-8 text-xs"
                            />
                        </div>
                        <div className="flex items-end gap-2">
                            <Button type="submit" size="sm" className="h-8">
                                Buscar
                            </Button>
                            {hayFiltros && (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-8"
                                    onClick={() => router.get(index.url())}
                                >
                                    Limpiar
                                </Button>
                            )}
                        </div>
                    </form>

                    <div className="grid gap-3 md:grid-cols-2">
                        <div className="grid gap-1">
                            <Label className="text-xs">Tipo de acción</Label>
                            <Select
                                value={filtros.action || '_all_'}
                                onValueChange={(v) =>
                                    handleFiltro(
                                        'action',
                                        v === '_all_' ? '' : v,
                                    )
                                }
                            >
                                <SelectTrigger className="h-8 text-xs">
                                    <SelectValue placeholder="Todos los tipos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="_all_">
                                        Todos los tipos
                                    </SelectItem>
                                    {acciones.map((accion) => (
                                        <SelectItem
                                            key={accion.value}
                                            value={accion.value}
                                        >
                                            {accion.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="grid gap-1">
                            <Label className="text-xs">Usuario</Label>
                            <Select
                                value={filtros.actor_user_id || '_all_'}
                                onValueChange={(v) =>
                                    handleFiltro(
                                        'actor_user_id',
                                        v === '_all_' ? '' : v,
                                    )
                                }
                            >
                                <SelectTrigger className="h-8 text-xs">
                                    <SelectValue placeholder="Todos los usuarios" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="_all_">
                                        Todos los usuarios
                                    </SelectItem>
                                    {usuarios.map((u) => (
                                        <SelectItem
                                            key={u.id}
                                            value={String(u.id)}
                                        >
                                            {u.nombre}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                {/* Tabla */}
                <div className="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[760px] divide-y divide-gray-200 text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Fecha
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Folio
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Acción
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Actor
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Descripción
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {logs.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={5}
                                            className="px-4 py-8 text-center text-gray-400"
                                        >
                                            No se encontraron registros
                                        </td>
                                    </tr>
                                ) : (
                                    logs.data.map((log) => (
                                        <tr
                                            key={log.id}
                                            className="hover:bg-gray-50"
                                        >
                                            <td className="px-4 py-3 whitespace-nowrap text-gray-500">
                                                {new Date(
                                                    log.created_at,
                                                ).toLocaleString('es-MX', {
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
                                                        <p className="font-mono text-xs font-medium text-primary">
                                                            {
                                                                log.incidencia
                                                                    .folio
                                                            }
                                                        </p>
                                                        <span
                                                            className={`inline-flex items-center rounded px-1.5 py-0.5 text-xs ${ESTADO_COLORS[log.incidencia.estado] ?? 'bg-gray-100 text-gray-800'}`}
                                                        >
                                                            {ESTADO_LABELS[
                                                                log.incidencia
                                                                    .estado
                                                            ] ??
                                                                log.incidencia
                                                                    .estado}
                                                        </span>
                                                    </div>
                                                ) : (
                                                    '—'
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                <span className="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                                    {log.action_label}
                                                </span>
                                                <span className="ml-1 text-xs text-gray-400">
                                                    {log.action_category}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-gray-700">
                                                {log.actor?.nombre ??
                                                    log.actor_identifier ??
                                                    ACTOR_TYPE_LABELS[
                                                        log.actor_type
                                                    ] ??
                                                    'Sistema'}
                                            </td>
                                            <td className="max-w-xs truncate px-4 py-3 text-gray-500">
                                                {log.description}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {logs.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm">
                        <p className="text-gray-500">
                            Mostrando {logs.from}–{logs.to} de {logs.total}
                        </p>
                        <div className="flex gap-1">
                            {logs.links.map((link, i) => (
                                <button
                                    key={i}
                                    disabled={!link.url}
                                    onClick={() =>
                                        link.url && router.get(link.url)
                                    }
                                    className={`rounded border px-2.5 py-1 text-xs ${link.active ? 'border-primary bg-primary text-primary-foreground' : link.url ? 'border-gray-200 hover:bg-accent' : 'cursor-not-allowed border-gray-100 text-gray-300'}`}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
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
        { title: 'Logs de actividad', href: index.url() },
    ],
};
