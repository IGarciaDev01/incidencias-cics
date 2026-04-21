import { Head, Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { dashboard } from '@/routes/panel';
import { index, create, destroy } from '@/routes/panel/admin/categorias';

type Categoria = {
    id: number;
    nombre: string;
    slug: string;
    descripcion: string | null;
    prioridad_defecto: string;
    activa: boolean;
    incidencias_count: number;
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
    categorias: Paginado<Categoria>;
    filtros: { buscar?: string };
    prioridades: string[];
};

const PRIORIDAD_LABELS: Record<string, string> = { alta: 'Alta', media: 'Media', baja: 'Baja' };
const PRIORIDAD_COLORS: Record<string, string> = {
    alta: 'bg-red-100 text-red-800',
    media: 'bg-yellow-100 text-yellow-800',
    baja: 'bg-green-100 text-green-800',
};

export default function Index({ categorias, filtros }: Props) {
    const { flash } = usePage().props as { flash?: { success?: string } };

    function handleDelete(id: number, nombre: string) {
        if (!confirm(`¿Eliminar la categoría "${nombre}"?`)) return;
        router.delete(destroy.url(id));
    }

    return (
        <>
            <Head title="Categorías" />

            <div className="p-4 md:p-6 space-y-5">
                {flash?.success && (
                    <div className="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                        {flash.success}
                    </div>
                )}

                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-xl font-semibold text-gray-900">Categorías</h2>
                        <p className="text-sm text-gray-500">{categorias.total} registros</p>
                    </div>
                    <Button asChild>
                        <Link href={create.url()}>Nueva categoría</Link>
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
                    <Input
                        name="buscar"
                        defaultValue={filtros.buscar}
                        placeholder="Buscar categoría..."
                        className="w-64"
                    />
                    <Button type="submit" variant="outline" size="sm">Buscar</Button>
                    {filtros.buscar && (
                        <Button variant="ghost" size="sm" onClick={() => router.get(index.url())}>
                            Limpiar
                        </Button>
                    )}
                </form>

                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridad defecto</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Incidencias</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th className="px-4 py-3" />
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {categorias.data.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="px-4 py-8 text-center text-gray-400">
                                        No se encontraron categorías
                                    </td>
                                </tr>
                            ) : (
                                categorias.data.map((cat) => (
                                    <tr key={cat.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-gray-900">{cat.nombre}</td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${PRIORIDAD_COLORS[cat.prioridad_defecto] ?? 'bg-gray-100 text-gray-800'}`}>
                                                {PRIORIDAD_LABELS[cat.prioridad_defecto] ?? cat.prioridad_defecto}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-gray-600">{cat.incidencias_count}</td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${cat.activa ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500'}`}>
                                                {cat.activa ? 'Activa' : 'Inactiva'}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Button variant="outline" size="sm" asChild>
                                                    <Link href={`/panel/admin/categorias/${cat.id}/edit`}>Editar</Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    className="text-red-600 hover:text-red-700"
                                                    onClick={() => handleDelete(cat.id, cat.nombre)}
                                                    disabled={cat.incidencias_count > 0}
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

                {categorias.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm">
                        <p className="text-gray-500">Mostrando {categorias.from}–{categorias.to} de {categorias.total}</p>
                        <div className="flex gap-1">
                            {categorias.links.map((link, i) => (
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
        { title: 'Categorías', href: index.url() },
    ],
};
