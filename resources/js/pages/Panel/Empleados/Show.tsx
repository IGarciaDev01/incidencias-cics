import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard } from '@/routes/panel';
import { index as jefeEmpleados } from '@/routes/panel/jefe_inmediato/empleados';
import { index as chEmpleados }   from '@/routes/panel/capital_humano/empleados';
import { index as subdirEmpleados } from '@/routes/panel/subdireccion/empleados';

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
};

type Props = {
    empleado: Empleado;
    incidencias: Incidencia[];
};

const ESTADO_LABELS: Record<string, string> = {
    pendiente_jefe:           'Pendiente — Jefe',
    pendiente_capital_humano: 'Pendiente — Capital Humano',
    pendiente_subdireccion:   'Pendiente — Subdirección',
    aprobada:                 'Aprobada',
    rechazada:                'Rechazada',
};
const ESTADO_COLORS: Record<string, string> = {
    pendiente_jefe:           'bg-yellow-100 text-yellow-800',
    pendiente_capital_humano: 'bg-orange-100 text-orange-800',
    pendiente_subdireccion:   'bg-blue-100 text-blue-800',
    aprobada:                 'bg-green-100 text-green-800',
    rechazada:                'bg-red-100 text-red-800',
};
const TIPO_LABELS: Record<string, string> = {
    retardo:           'Retardo',
    permiso_economico: 'Permiso Económico',
    comision_oficial:  'Comisión Oficial',
    salida_anticipada: 'Salida Anticipada',
};

function useRolBackUrl() {
    const { auth } = usePage().props as { auth: { user?: { rol?: string } } };
    const rol = auth.user?.rol ?? '';
    if (rol === 'jefe_inmediato') return jefeEmpleados.url();
    if (rol === 'capital_humano') return chEmpleados.url();
    return subdirEmpleados.url();
}

export default function Show({ empleado, incidencias }: Props) {
    const backUrl = useRolBackUrl();

    const aprobadas = incidencias.filter((i) => i.estado === 'aprobada').length;
    const rechazadas = incidencias.filter((i) => i.estado === 'rechazada').length;
    const pendientes = incidencias.filter((i) => !['aprobada', 'rechazada'].includes(i.estado)).length;

    return (
        <>
            <Head title={`Empleado ${empleado.numero_empleado}`} />

            <div className="p-4 md:p-6 space-y-6">
                {/* Encabezado */}
                <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div className="flex flex-wrap items-start justify-between gap-3 mb-4">
                        <div>
                            <p className="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">
                                No. Empleado: <span className="font-mono">{empleado.numero_empleado}</span>
                            </p>
                            <h2 className="text-2xl font-bold text-gray-900">{empleado.reportante_nombre}</h2>
                            {empleado.email_reportante && (
                                <p className="text-sm text-gray-500 mt-1">{empleado.email_reportante}</p>
                            )}
                        </div>
                        <Link href={backUrl} className="text-sm text-gray-500 hover:text-gray-700">
                            ← Volver a empleados
                        </Link>
                    </div>

                    <div className="grid grid-cols-3 gap-4 pt-4 border-t border-gray-100 text-center">
                        <div>
                            <p className="text-2xl font-bold text-gray-900">{incidencias.length}</p>
                            <p className="text-xs text-gray-500 mt-0.5">Total</p>
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-amber-600">{pendientes}</p>
                            <p className="text-xs text-gray-500 mt-0.5">En proceso</p>
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-green-600">{aprobadas}</p>
                            <p className="text-xs text-gray-500 mt-0.5">Aprobadas</p>
                        </div>
                    </div>
                </div>

                {/* Historial */}
                <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div className="px-5 py-4 border-b border-gray-100">
                        <h3 className="font-semibold text-gray-900">Historial de incidencias</h3>
                    </div>

                    {incidencias.length === 0 ? (
                        <p className="p-6 text-sm text-gray-500">Sin incidencias registradas.</p>
                    ) : (
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600">Folio</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600">Tipo</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600 hidden md:table-cell">Área</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600 hidden md:table-cell">Fecha</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600">Estado</th>
                                    <th className="text-left px-4 py-3 font-medium text-gray-600 hidden lg:table-cell">Registrada</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {incidencias.map((inc) => (
                                    <tr key={inc.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-4 py-3 font-mono text-xs text-gray-700">{inc.folio}</td>
                                        <td className="px-4 py-3 text-gray-900">
                                            {TIPO_LABELS[inc.tipo_incidencia] ?? inc.tipo_incidencia}
                                            {inc.minutos_retardo ? (
                                                <span className="text-gray-400 text-xs ml-1">({inc.minutos_retardo} min)</span>
                                            ) : null}
                                        </td>
                                        <td className="px-4 py-3 text-gray-500 hidden md:table-cell">
                                            {inc.area?.nombre ?? <span className="text-gray-300 italic">Sin área</span>}
                                        </td>
                                        <td className="px-4 py-3 text-gray-500 hidden md:table-cell">
                                            {new Date(inc.fecha_incidencia + 'T12:00:00').toLocaleDateString('es-MX')}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${ESTADO_COLORS[inc.estado] ?? 'bg-gray-100 text-gray-600'}`}>
                                                {ESTADO_LABELS[inc.estado] ?? inc.estado}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-gray-400 text-xs hidden lg:table-cell">
                                            {new Date(inc.created_at).toLocaleDateString('es-MX')}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
            </div>
        </>
    );
}

Show.layout = {
    breadcrumbs: [
        { title: 'Dashboard',  href: dashboard.url() },
        { title: 'Empleados' },
    ],
};
