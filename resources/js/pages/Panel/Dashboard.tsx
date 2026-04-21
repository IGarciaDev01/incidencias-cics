import { Head } from '@inertiajs/react';
import { dashboard } from '@/routes/panel';

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
    rol: 'admin' | 'jefe_inmediato' | 'capital_humano' | 'subdireccion_academica';
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

function AdminDashboard({ stats }: { stats: AdminStats }) {
    return (
        <div className="space-y-6">
            <div>
                <h3 className="text-xs font-semibold text-gray-400 mb-3 uppercase tracking-widest">Flujo de aprobación</h3>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <StatCard label="Pendientes — Jefe" value={stats.pendientes_jefe} color="yellow" description="Esperando revisión del jefe inmediato" />
                    <StatCard label="Pendientes — Capital Humano" value={stats.pendientes_capital_humano} color="orange" description="Aprobadas por jefe, en Capital Humano" />
                    <StatCard label="Pendientes — Subdirección" value={stats.pendientes_subdireccion} color="blue" description="En espera de aprobación final" />
                    <StatCard label="Total de incidencias" value={stats.total_incidencias} color="gray" />
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
    return (
        <div className="space-y-4">
            <p className="text-sm text-gray-500">Incidencias de tu área de adscripción.</p>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <StatCard label="Pendientes de revisión" value={stats.pendientes} color="yellow" description="Requieren tu aprobación o rechazo" />
                <StatCard label="Aprobadas" value={stats.aprobadas} color="green" />
                <StatCard label="Rechazadas" value={stats.rechazadas} color="red" />
                <StatCard label="Total en tu área" value={stats.total} color="gray" />
            </div>
        </div>
    );
}

function CapitalHumanoDashboard({ stats }: { stats: CapitalHumanoStats }) {
    return (
        <div className="space-y-4">
            <p className="text-sm text-gray-500">Incidencias aprobadas por jefes inmediatos que requieren tu revisión.</p>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <StatCard label="Pendientes de revisión" value={stats.pendientes} color="orange" description="Aprobadas por jefe, esperan tu acción" />
                <StatCard label="Aprobadas" value={stats.aprobadas} color="green" />
                <StatCard label="Rechazadas" value={stats.rechazadas} color="red" />
                <StatCard label="Total revisadas" value={stats.total} color="gray" />
            </div>
        </div>
    );
}

function SubdireccionDashboard({ stats }: { stats: SubdireccionStats }) {
    return (
        <div className="space-y-4">
            <p className="text-sm text-gray-500">Incidencias en la etapa final del flujo de aprobación.</p>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <StatCard label="Pendientes de aprobación" value={stats.pendientes} color="blue" description="Listas para aprobación definitiva" />
                <StatCard label="Aprobadas definitivamente" value={stats.aprobadas} color="green" />
                <StatCard label="Rechazadas" value={stats.rechazadas} color="red" />
                <StatCard label="Total en el sistema" value={stats.total} color="gray" />
            </div>
        </div>
    );
}

const ROL_LABEL: Record<string, string> = {
    admin:                  'Administrador',
    jefe_inmediato:         'Jefe Inmediato',
    capital_humano:         'Capital Humano',
    subdireccion_academica: 'Subdirección Académica',
};

export default function Dashboard({ stats, rol }: Props) {
    return (
        <>
            <Head title="Dashboard" />
            <div className="p-4 md:p-6 space-y-6">
                <div>
                    <h2 className="text-xl font-semibold text-gray-900">Resumen general</h2>
                    <p className="text-sm text-gray-500">Vista para {ROL_LABEL[rol] ?? rol}</p>
                </div>

                {rol === 'admin'                  && <AdminDashboard stats={stats as AdminStats} />}
                {rol === 'jefe_inmediato'          && <JefeDashboard stats={stats as JefeStats} />}
                {rol === 'capital_humano'          && <CapitalHumanoDashboard stats={stats as CapitalHumanoStats} />}
                {rol === 'subdireccion_academica'  && <SubdireccionDashboard stats={stats as SubdireccionStats} />}
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: dashboard.url() }],
};
