import { Head, Link, router, usePage } from '@inertiajs/react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/panel';
import { index as jefeEmpleados, show as jefeShow }   from '@/routes/panel/jefe_inmediato/empleados';
import { index as chEmpleados,   show as chShow }     from '@/routes/panel/capital_humano/empleados';
import { index as subdirEmpleados, show as subdirShow } from '@/routes/panel/subdireccion/empleados';
import { formatDateOnly } from '@/utils/date';

type Empleado = {
    numero_empleado: string;
    reportante_nombre: string;
    email_reportante: string | null;
    total_incidencias: number;
    ultima_incidencia: string | null;
};

type Paginado<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    empleados: Paginado<Empleado>;
    filtros: { buscar: string };
};

function useRolRoutes() {
    const { auth } = usePage().props as { auth: { user?: { rol?: string } } };
    const rol = auth.user?.rol ?? '';
    if (rol === 'jefe_inmediato')         return { indexUrl: jefeEmpleados.url, showUrl: jefeShow.url };
    if (rol === 'capital_humano')         return { indexUrl: chEmpleados.url, showUrl: chShow.url };
    return { indexUrl: subdirEmpleados.url, showUrl: subdirShow.url };
}

export default function Index({ empleados, filtros }: Props) {
    const { indexUrl, showUrl } = useRolRoutes();

    function handleBuscar(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        const fd = new FormData(e.currentTarget);
        router.get(indexUrl(), { buscar: fd.get('buscar') as string });
    }

    return (
        <>
            <Head title="Empleados" />

            <div className="p-4 md:p-6 space-y-5">
                <div>
                    <h2 className="text-xl font-semibold text-gray-900">Empleados</h2>
                    <p className="text-sm text-gray-500">Historial de incidencias por empleado.</p>
                </div>

                {/* Buscador */}
                <form onSubmit={handleBuscar} className="flex gap-2 max-w-md">
                    <Input
                        name="buscar"
                        defaultValue={filtros.buscar}
                        placeholder="Buscar por nombre o número de empleado..."
                        className="h-9"
                    />
                    <Button type="submit" size="sm" className="h-9">Buscar</Button>
                    {filtros.buscar && (
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="h-9"
                            onClick={() => router.get(indexUrl())}
                        >
                            Limpiar
                        </Button>
                    )}
                </form>

                {/* Tabla */}
                <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    {empleados.data.length === 0 ? (
                        <div className="p-8 text-center text-sm text-gray-500">
                            {filtros.buscar
                                ? 'No se encontraron empleados con esa búsqueda.'
                                : 'No hay registros de empleados aún.'}
                        </div>
                    ) : (
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600">No. Empleado</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600">Nombre</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600 hidden md:table-cell">Correo</th>
                                    <th className="text-center px-4 py-3 font-medium text-gray-600">Incidencias</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600 hidden md:table-cell">Última</th>
                                    <th className="px-4 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {empleados.data.map((emp) => (
                                    <tr key={emp.numero_empleado} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-4 py-3 font-mono text-gray-700">{emp.numero_empleado}</td>
                                        <td className="px-4 py-3 font-medium text-gray-900">{emp.reportante_nombre}</td>
                                        <td className="px-4 py-3 text-gray-500 hidden md:table-cell">
                                            {emp.email_reportante ?? <span className="text-gray-300 italic">Sin correo</span>}
                                        </td>
                                        <td className="px-4 py-3 text-center">
                                            <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                                {emp.total_incidencias}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-gray-500 text-xs hidden md:table-cell">
                                            {emp.ultima_incidencia
                                                ? formatDateOnly(emp.ultima_incidencia)
                                                : <span className="text-gray-300 italic">—</span>}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={showUrl(emp.numero_empleado)}>
                                                    Ver historial
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>

                {/* Paginación */}
                {empleados.last_page > 1 && (
                    <div className="flex justify-center gap-1">
                        {empleados.links.map((link, i) => (
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

                <p className="text-xs text-gray-400 text-center">{empleados.total} empleado(s) encontrado(s)</p>
            </div>
        </>
    );
}

Index.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard.url() },
        { title: 'Empleados' },
    ],
};
