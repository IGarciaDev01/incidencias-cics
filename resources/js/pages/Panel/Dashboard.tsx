import { Head, Link, usePage } from '@inertiajs/react';
import { AlertTriangle, BarChart2, Users } from 'lucide-react';
import { dashboard } from '@/routes/panel';
import { index as jefeIncidencias } from '@/routes/panel/jefe_inmediato/incidencias';
import { index as jefeEmpleados }   from '@/routes/panel/jefe_inmediato/empleados';
import { index as chIncidencias }   from '@/routes/panel/capital_humano/incidencias';
import { index as chEmpleados }     from '@/routes/panel/capital_humano/empleados';
import { index as subdirIncidencias } from '@/routes/panel/subdireccion/incidencias';
import { index as subdirReportes }    from '@/routes/panel/subdireccion/reportes';
import { index as subdirEmpleados }   from '@/routes/panel/subdireccion/empleados';

type AdminStats = {
    total_incidencias: number;
    pendientes_jefe: number;
    pendientes_capital_humano: number;
    pendientes_subdireccion: number;
    aprobadas: number;
    rechazadas: number;
    total_usuarios: number;
    total_areas: number;
};

type JefeStats = {
    pendientes: number;
    aprobadas: number;
    rechazadas: number;
    total: number;
};

type CapitalHumanoStats = {
    pendientes: number;
    aprobadas: number;
    rechazadas: number;
    total: number;
};

type SubdireccionStats = {
    pendientes: number;
    aprobadas: number;
    rechazadas: number;
    total: number;
};

type Props = {
    stats: AdminStats | JefeStats | CapitalHumanoStats | SubdireccionStats;
    rol: 'admin' | 'jefe_inmediato' | 'capital_humano' | 'subdirector';
};

function StatCard({
    label,
    value,
    color = 'gray',
    description,
}: {
    label: string;
    value: number;
    color?: 'blue' | 'green' | 'red' | 'orange' | 'purple' | 'gray' | 'yellow';
    description?: string;
}) {
    const colors = {
        blue:   'bg-sky-50 text-sky-800 border-sky-100',
        green:  'bg-emerald-50 text-emerald-700 border-emerald-100',
        red:    'bg-red-50 text-red-700 border-red-100',
        orange: 'bg-amber-50 text-amber-700 border-amber-100',
        purple: 'bg-violet-50 text-violet-700 border-violet-100',
        gray:   'bg-stone-50 text-stone-600 border-stone-100',
        yellow: 'bg-yellow-50 text-yellow-700 border-yellow-100',
    };

    return (
        <div className={`rounded-xl border p-5 ${colors[color]}`}>
            <p className="text-sm font-medium opacity-80">{label}</p>
            <p className="text-3xl font-bold mt-1">{value.toLocaleString()}</p>
            {description && <p className="text-xs opacity-70 mt-1">{description}</p>}
        </div>
    );
}

function QuickLink({ href, icon: Icon, label, description, badge }: {
    href: string;
    icon: React.ElementType;
    label: string;
    description: string;
    badge?: number;
}) {
    return (
        <Link
            href={href}
            className="flex items-center gap-4 bg-white rounded-xl border border-gray-200 p-4 hover:border-gray-300 hover:shadow-sm transition-all"
        >
            <div className="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                <Icon className="w-5 h-5 text-primary" />
            </div>
            <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2">
                    <p className="font-medium text-gray-900 text-sm">{label}</p>
                    {badge !== undefined && badge > 0 && (
                        <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                            {badge}
                        </span>
                    )}
                </div>
                <p className="text-xs text-gray-500 truncate">{description}</p>
            </div>
            <span className="text-gray-300 text-lg">→</span>
        </Link>
    );
}

function AdminDashboard({ stats }: { stats: AdminStats }) {
    return (
        <div className="space-y-6">
            <div>
                <h3 className="text-xs font-semibold text-gray-400 mb-3 uppercase tracking-widest">Flujo de aprobación</h3>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard label="Pendientes — Jefe" value={stats.pendientes_jefe} color="yellow" description="Esperando jefe inmediato" />
                    <StatCard label="Pendientes — Capital Humano" value={stats.pendientes_capital_humano} color="orange" description="Aprobadas por jefe" />
                    <StatCard label="Pendientes — Subdirección" value={stats.pendientes_subdireccion} color="blue" description="Aprobación final" />
                    <StatCard label="Total incidencias" value={stats.total_incidencias} color="gray" />
                </div>
            </div>
            <div>
                <h3 className="text-xs font-semibold text-gray-400 mb-3 uppercase tracking-widest">Resultados</h3>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard label="Aprobadas" value={stats.aprobadas} color="green" />
                    <StatCard label="Rechazadas" value={stats.rechazadas} color="red" />
                    <StatCard label="Usuarios activos" value={stats.total_usuarios} color="purple" />
                    <StatCard label="Áreas activas" value={stats.total_areas} color="gray" />
                </div>
            </div>
        </div>
    );
}

function JefeDashboard({ stats }: { stats: JefeStats }) {
    const { auth } = usePage().props as { auth: { user?: { nombre?: string } } };

    return (
        <div className="space-y-6">
            <div>
                <p className="text-sm text-gray-500 mb-4">Incidencias registradas en tu área de adscripción.</p>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard label="Requieren tu revisión" value={stats.pendientes} color="yellow" description="Pendientes de aprobación" />
                    <StatCard label="Aprobadas" value={stats.aprobadas} color="green" />
                    <StatCard label="Rechazadas" value={stats.rechazadas} color="red" />
                    <StatCard label="Total en tu área" value={stats.total} color="gray" />
                </div>
            </div>
            <div>
                <h3 className="text-xs font-semibold text-gray-400 mb-3 uppercase tracking-widest">Accesos rápidos</h3>
                <div className="grid md:grid-cols-2 gap-3">
                    <QuickLink
                        href={jefeIncidencias.url()}
                        icon={AlertTriangle}
                        label="Ver incidencias"
                        description="Revisa y aprueba las incidencias de tu área"
                        badge={stats.pendientes}
                    />
                    <QuickLink
                        href={jefeEmpleados.url()}
                        icon={Users}
                        label="Consultar empleados"
                        description="Busca empleados y revisa su historial"
                    />
                </div>
            </div>
        </div>
    );
}

function CapitalHumanoDashboard({ stats }: { stats: CapitalHumanoStats }) {
    return (
        <div className="space-y-6">
            <div>
                <p className="text-sm text-gray-500 mb-4">Incidencias aprobadas por jefes inmediatos que requieren tu revisión.</p>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard label="Requieren tu revisión" value={stats.pendientes} color="orange" description="Aprobadas por jefe" />
                    <StatCard label="Aprobadas" value={stats.aprobadas} color="green" />
                    <StatCard label="Rechazadas" value={stats.rechazadas} color="red" />
                    <StatCard label="Total revisadas" value={stats.total} color="gray" />
                </div>
            </div>
            <div>
                <h3 className="text-xs font-semibold text-gray-400 mb-3 uppercase tracking-widest">Accesos rápidos</h3>
                <div className="grid md:grid-cols-2 gap-3">
                    <QuickLink
                        href={chIncidencias.url()}
                        icon={AlertTriangle}
                        label="Ver incidencias"
                        description="Revisa las incidencias pendientes de tu validación"
                        badge={stats.pendientes}
                    />
                    <QuickLink
                        href={chEmpleados.url()}
                        icon={Users}
                        label="Consultar empleados"
                        description="Busca empleados y revisa su historial de incidencias"
                    />
                </div>
            </div>
        </div>
    );
}

function SubdireccionDashboard({ stats }: { stats: SubdireccionStats }) {
    return (
        <div className="space-y-6">
            <div>
                <p className="text-sm text-gray-500 mb-4">Incidencias en la etapa final del flujo de aprobación.</p>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard label="Requieren aprobación final" value={stats.pendientes} color="blue" description="Listas para aprobación definitiva" />
                    <StatCard label="Aprobadas definitivamente" value={stats.aprobadas} color="green" />
                    <StatCard label="Rechazadas" value={stats.rechazadas} color="red" />
                    <StatCard label="Total en el sistema" value={stats.total} color="gray" />
                </div>
            </div>
            <div>
                <h3 className="text-xs font-semibold text-gray-400 mb-3 uppercase tracking-widest">Accesos rápidos</h3>
                <div className="grid md:grid-cols-3 gap-3">
                    <QuickLink
                        href={subdirIncidencias.url()}
                        icon={AlertTriangle}
                        label="Ver incidencias"
                        description="Aprueba o rechaza definitivamente"
                        badge={stats.pendientes}
                    />
                    <QuickLink
                        href={subdirEmpleados.url()}
                        icon={Users}
                        label="Consultar empleados"
                        description="Historial de incidencias por empleado"
                    />
                    <QuickLink
                        href={subdirReportes.url()}
                        icon={BarChart2}
                        label="Reportes"
                        description="Estadísticas y exportación de datos"
                    />
                </div>
            </div>
        </div>
    );
}

const ROL_LABEL: Record<string, string> = {
    admin:          'Administrador',
    jefe_inmediato: 'Jefe Inmediato',
    capital_humano: 'Capital Humano',
    subdirector:    'Subdirector',
};

export default function Dashboard({ stats, rol }: Props) {
    const { auth } = usePage().props as { auth: { user?: { nombre?: string } } };

    return (
        <>
            <Head title="Dashboard" />
            <div className="p-4 md:p-6 space-y-6">
                <div>
                    <h2 className="text-xl font-semibold text-gray-900">
                        Bienvenido{auth.user?.nombre ? `, ${auth.user.nombre.split(' ')[0]}` : ''}
                    </h2>
                    <p className="text-sm text-gray-500">{ROL_LABEL[rol] ?? rol}</p>
                </div>

                {rol === 'admin'          && <AdminDashboard stats={stats as AdminStats} />}
                {rol === 'jefe_inmediato'  && <JefeDashboard stats={stats as JefeStats} />}
                {rol === 'capital_humano'   && <CapitalHumanoDashboard stats={stats as CapitalHumanoStats} />}
                {rol === 'subdirector'      && <SubdireccionDashboard stats={stats as SubdireccionStats} />}
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: dashboard.url() }],
};
