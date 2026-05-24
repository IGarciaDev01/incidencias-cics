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
import {
    index,
    create,
    destroy,
    toggleActivo,
    edit,
} from '@/routes/panel/subdireccion/admin/usuarios';

type Usuario = {
    id: number;
    nombre: string;
    email: string;
    numero_empleado: string | null;
    rol: string;
    activo: boolean;
    created_at: string;
    areas: { id: number; nombre: string }[];
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
    usuarios: Paginado<Usuario>;
    filtros: { rol?: string; activo?: string; buscar?: string };
    roles: string[];
    areas: { id: number; nombre: string }[];
};

const ROL_LABELS: Record<string, string> = {
    admin: 'Administrador',
    jefe_inmediato: 'Jefe Inmediato',
    capital_humano: 'Capital Humano',
    sindicato: 'Sindicato',
    subdirector: 'Subdirección Administrativa',
};

export default function Index({ usuarios, filtros, roles }: Props) {
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string };
    };

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
            { ...filtros, buscar: (fd.get('buscar') as string) || undefined },
            { preserveState: true, replace: true },
        );
    }

    function handleDelete(id: number, nombre: string) {
        if (!confirm(`¿Eliminar el usuario "${nombre}"?`)) {
            return;
        }

        router.delete(destroy.url(id));
    }

    function handleToggle(id: number) {
        router.patch(toggleActivo.url(id), {}, { preserveScroll: true });
    }

    return (
        <>
            <Head title="Usuarios" />

            <div className="space-y-5 p-4 md:p-6">
                {flash?.success && (
                    <div className="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                        {flash.success}
                    </div>
                )}

                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-xl font-semibold text-gray-900">
                            Usuarios
                        </h2>
                        <p className="text-sm text-gray-500">
                            {usuarios.total} registros
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={create.url()}>Nuevo usuario</Link>
                    </Button>
                </div>

                {/* Filtros */}
                <div className="flex flex-wrap gap-3">
                    <form onSubmit={handleBuscar} className="flex gap-2">
                        <Input
                            name="buscar"
                            defaultValue={filtros.buscar}
                            placeholder="Buscar por nombre o email..."
                            className="w-64"
                        />
                        <Button type="submit" variant="outline" size="sm">
                            Buscar
                        </Button>
                    </form>

                    <Select
                        value={filtros.rol || '_all_'}
                        onValueChange={(v) =>
                            handleFiltro('rol', v === '_all_' ? '' : v)
                        }
                    >
                        <SelectTrigger className="w-44">
                            <SelectValue placeholder="Todos los roles" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="_all_">
                                Todos los roles
                            </SelectItem>
                            {roles.map((r) => (
                                <SelectItem key={r} value={r}>
                                    {ROL_LABELS[r] ?? r}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select
                        value={filtros.activo || '_all_'}
                        onValueChange={(v) =>
                            handleFiltro('activo', v === '_all_' ? '' : v)
                        }
                    >
                        <SelectTrigger className="w-36">
                            <SelectValue placeholder="Todos" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="_all_">Todos</SelectItem>
                            <SelectItem value="1">Activos</SelectItem>
                            <SelectItem value="0">Inactivos</SelectItem>
                        </SelectContent>
                    </Select>

                    {(filtros.rol || filtros.activo || filtros.buscar) && (
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => router.get(index.url())}
                        >
                            Limpiar filtros
                        </Button>
                    )}
                </div>

                {/* Tabla */}
                <div className="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[900px] divide-y divide-gray-200 text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Nombre
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        No. Empleado
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Email
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Rol
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Áreas
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Estado
                                    </th>
                                    <th className="px-4 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {usuarios.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={7}
                                            className="px-4 py-8 text-center text-gray-400"
                                        >
                                            No se encontraron usuarios
                                        </td>
                                    </tr>
                                ) : (
                                    usuarios.data.map((u) => (
                                        <tr
                                            key={u.id}
                                            className="hover:bg-gray-50"
                                        >
                                            <td className="px-4 py-3 font-medium text-gray-900">
                                                {u.nombre}
                                            </td>
                                            <td className="px-4 py-3 text-gray-600">
                                                {u.numero_empleado ?? '—'}
                                            </td>
                                            <td className="px-4 py-3 text-gray-600">
                                                {u.email}
                                            </td>
                                            <td className="px-4 py-3">
                                                <span className="inline-flex items-center rounded-full bg-secondary px-2 py-0.5 text-xs font-medium text-secondary-foreground">
                                                    {ROL_LABELS[u.rol] ?? u.rol}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-gray-600">
                                                {u.areas.length > 0
                                                    ? u.areas
                                                          .map((a) => a.nombre)
                                                          .join(', ')
                                                    : '—'}
                                            </td>
                                            <td className="px-4 py-3">
                                                <span
                                                    className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${u.activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500'}`}
                                                >
                                                    {u.activo
                                                        ? 'Activo'
                                                        : 'Inactivo'}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() =>
                                                            handleToggle(u.id)
                                                        }
                                                    >
                                                        {u.activo
                                                            ? 'Desactivar'
                                                            : 'Activar'}
                                                    </Button>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={edit.url(
                                                                u.id,
                                                            )}
                                                        >
                                                            Editar
                                                        </Link>
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        className="text-red-600 hover:text-red-700"
                                                        onClick={() =>
                                                            handleDelete(
                                                                u.id,
                                                                u.nombre,
                                                            )
                                                        }
                                                    >
                                                        Eliminar
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Paginación */}
                {usuarios.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm">
                        <p className="text-gray-500">
                            Mostrando {usuarios.from}–{usuarios.to} de{' '}
                            {usuarios.total}
                        </p>
                        <div className="flex gap-1">
                            {usuarios.links.map((link, i) => (
                                <button
                                    key={i}
                                    disabled={!link.url}
                                    onClick={() =>
                                        link.url && router.get(link.url)
                                    }
                                    className={`rounded border px-2.5 py-1 text-xs transition-colors ${
                                        link.active
                                            ? 'border-primary bg-primary text-primary-foreground'
                                            : link.url
                                              ? 'border-gray-200 hover:bg-gray-50'
                                              : 'cursor-not-allowed border-gray-100 text-gray-300'
                                    }`}
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
        { title: 'Usuarios', href: index.url() },
    ],
};
