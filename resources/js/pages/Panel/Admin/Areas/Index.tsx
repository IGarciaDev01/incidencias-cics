import { Head, Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { dashboard } from '@/routes/panel';
import { index, create, destroy } from '@/routes/panel/admin/areas';

type Area = {
    id: number;
    nombre: string;
    activa: boolean;
    incidencias_count: number;
    usuarios_count: number;
    subdirector: { id: number; nombre: string } | null;
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
    areas: Paginado<Area>;
    filtros: { buscar?: string };
};

export default function Index({ areas, filtros }: Props) {
    const { flash } = usePage().props as { flash?: { success?: string } };

    function handleDelete(id: number, nombre: string) {
        if (!confirm(`¿Eliminar el área "${nombre}"?`)) return;
        router.delete(destroy.url(id));
    }

    return (
        <>
            <Head title="Áreas" />

            <div className="p-4 md:p-6 space-y-5">
                {flash?.success && (
                    <div className="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                        {flash.success}
                    </div>
                )}

                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-xl font-semibold text-gray-900">Áreas</h2>
                        <p className="text-sm text-gray-500">{areas.total} registros</p>
                    </div>
                    <Button asChild>
                        <Link href={create.url()}>Nueva área</Link>
                    </Button>
                </div>

                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        const fd = new FormData(e.currentTarget);
                        router.get(index.url(), { buscar: fd.get('buscar') as string || undefined }, { replace: true });
                    }}
                    className="flex gap-2"
                >
                    <Input name="buscar" defaultValue={filtros.buscar} placeholder="Buscar área..." className="w-64" />
                    <Button type="submit" variant="outline" size="sm">Buscar</Button>
                    {filtros.buscar && (
                        <Button variant="ghost" size="sm" onClick={() => router.get(index.url())}>Limpiar</Button>
                    )}
                </form>

                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subdirector</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuarios</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Incidencias</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th className="px-4 py-3" />
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {areas.data.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-gray-400">
                                        No se encontraron áreas
                                    </td>
                                </tr>
                            ) : (
                                areas.data.map((area) => (
                                    <tr key={area.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-gray-900">{area.nombre}</td>
                                        <td className="px-4 py-3 text-gray-600">{area.subdirector?.nombre ?? '—'}</td>
                                        <td className="px-4 py-3 text-gray-600">{area.usuarios_count}</td>
                                        <td className="px-4 py-3 text-gray-600">{area.incidencias_count}</td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${area.activa ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500'}`}>
                                                {area.activa ? 'Activa' : 'Inactiva'}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Button variant="outline" size="sm" asChild>
                                                    <Link href={`/panel/admin/areas/${area.id}/edit`}>Editar</Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    className="text-red-600 hover:text-red-700"
                                                    onClick={() => handleDelete(area.id, area.nombre)}
                                                    disabled={area.incidencias_count > 0 || area.usuarios_count > 0}
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

                {areas.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm">
                        <p className="text-gray-500">Mostrando {areas.from}–{areas.to} de {areas.total}</p>
                        <div className="flex gap-1">
                            {areas.links.map((link, i) => (
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
        { title: 'Áreas', href: index.url() },
    ],
};
