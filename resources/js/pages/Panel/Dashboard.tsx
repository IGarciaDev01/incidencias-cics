import { Head, usePage } from '@inertiajs/react';
import { dashboard } from '@/routes/panel';
import { index as jefeIncidencias } from '@/routes/panel/jefe_inmediato/incidencias';
import { index as chIncidencias } from '@/routes/panel/capital_humano/incidencias';
import { index as subdirIncidencias } from '@/routes/panel/subdireccion/incidencias';

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
    tasa_aprobacion: number;
    charts: {
        por_estado: Record<string, number>;
        por_tipo: Record<string, number>;
        por_solicitante: Record<string, number>;
        solicitudes_mes: Record<string, { label: string; total: number }>;
    };
};

type CapitalHumanoStats = {
    pendientes: number;
    aprobadas: number;
    rechazadas: number;
    total: number;
    tasa_aprobacion: number;
    charts: {
        por_estado: Record<string, number>;
        por_tipo: Record<string, number>;
        por_area: Record<string, number>;
        solicitudes_mes: Record<string, { label: string; total: number }>;
    };
};

type SubdireccionStats = {
    pendientes: number;
    aprobadas: number;
    rechazadas: number;
    total: number;
    tasa_aprobacion: number;
    charts: {
        por_estado: Record<string, number>;
        por_tipo: Record<string, number>;
        por_area: Record<string, number>;
        por_solicitante: Record<string, number>;
        solicitudes_mes: Record<string, { label: string; total: number }>;
    };
};

type Props = {
    stats: AdminStats | JefeStats | CapitalHumanoStats | SubdireccionStats;
    rol: 'admin' | 'jefe_inmediato' | 'capital_humano' | 'subdirector';
};

const ESTADO_LABELS: Record<string, string> = {
    pendiente_jefe: 'Pendiente — Jefe',
    pendiente_capital_humano: 'Pendiente — C.H.',
    pendiente_subdireccion: 'Pendiente — Subdir.',
    aprobada: 'Aprobada',
    rechazada: 'Rechazada',
};

const ESTADO_COLORS: Record<string, string> = {
    pendiente_jefe: 'bg-yellow-400',
    pendiente_capital_humano: 'bg-orange-400',
    pendiente_subdireccion: 'bg-sky-500',
    aprobada: 'bg-emerald-500',
    rechazada: 'bg-red-500',
};

const TIPO_LABELS: Record<string, string> = {
    retardo: 'Retardo',
    permiso_economico: 'Permiso Económico',
    comision_oficial: 'Comisión Oficial',
    salida_anticipada: 'Salida Anticipada',
};

const TIPO_COLORS: Record<string, string> = {
    retardo: 'bg-amber-400',
    permiso_economico: 'bg-blue-400',
    comision_oficial: 'bg-violet-400',
    salida_anticipada: 'bg-rose-400',
};

const SOLICITANTE_LABELS: Record<string, string> = {
    docente: 'Docente',
    administrativo: 'Administrativo',
    paae: 'PAAE',
};

const SOLICITANTE_COLORS: Record<string, string> = {
    docente: 'bg-teal-500',
    administrativo: 'bg-indigo-400',
    paae: 'bg-rose-400',
};

const SOLICITANTE_BG: Record<string, string> = {
    docente: 'bg-teal-50 text-teal-700',
    administrativo: 'bg-indigo-50 text-indigo-700',
    paae: 'bg-rose-50 text-rose-700',
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

function BarChart({ data, colorMap, labelMap }: {
    data: Record<string, number>;
    colorMap?: Record<string, string>;
    labelMap?: Record<string, string>;
}) {
    const max = Math.max(...Object.values(data), 1);
    return (
        <div className="space-y-2.5">
            {Object.entries(data).map(([key, value]) => (
                <div key={key} className="flex items-center gap-3">
                    <span className="text-xs text-gray-600 w-36 truncate">{labelMap?.[key] ?? key}</span>
                    <div className="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                        <div
                            className={`h-full rounded-full ${colorMap?.[key] ?? 'bg-blue-400'}`}
                            style={{ width: `${Math.max((value / max) * 100, value > 0 ? 4 : 0)}%` }}
                        />
                    </div>
                    <span className="text-xs font-medium text-gray-900 w-8 text-right">{value}</span>
                </div>
            ))}
        </div>
    );
}

function DonutChart({ data, colorMap, labelMap }: {
    data: Record<string, number>;
    colorMap?: Record<string, string>;
    labelMap?: Record<string, string>;
}) {
    const total = Object.values(data).reduce((a, b) => a + b, 0);
    if (total === 0) {
        return <p className="text-sm text-gray-400 text-center py-4">Sin datos</p>;
    }

    let cumulative = 0;
    const segments = Object.entries(data)
        .filter(([, v]) => v > 0)
        .map(([key, value]) => {
            const pct = value / total;
            const start = cumulative;
            cumulative += pct;
            return { key, value, pct, start, end: cumulative };
        });

    return (
        <div className="flex items-center gap-4">
            <div className="relative w-20 h-20 flex-shrink-0">
                <svg viewBox="0 0 36 36" className="w-20 h-20 -rotate-90">
                    {segments.map(({ key, start, end }) => (
                        <circle
                            key={key}
                            r="14"
                            cx="18"
                            cy="18"
                            fill="none"
                            stroke={colorMap?.[key] ?? '#94a3b8'}
                            strokeWidth="6"
                            strokeDasharray={`${(end - start) * 87.96} ${87.96 - (end - start) * 87.96}`}
                            strokeDashoffset={`${-start * 87.96}`}
                        />
                    ))}
                </svg>
            </div>
            <div className="flex-1 space-y-1.5">
                {segments.map(({ key, value }) => (
                    <div key={key} className="flex items-center gap-2 text-xs">
                        <span className={`w-2 h-2 rounded-full flex-shrink-0 ${colorMap?.[key] ?? 'bg-gray-400'}`} />
                        <span className="text-gray-600">{labelMap?.[key] ?? key}</span>
                        <span className="ml-auto font-medium text-gray-900">{value}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}

function TrendChart({ data }: { data: Record<string, { label: string; total: number }> }) {
    const values = Object.values(data).map(d => d.total);
    const max = Math.max(...values, 1);

    return (
        <div className="space-y-1">
            <div className="flex items-end gap-1 h-24">
                {Object.entries(data).map(([key, { label, total }]) => {
                    const height = Math.max((total / max) * 100, total > 0 ? 8 : 0);
                    return (
                        <div key={key} className="flex-1 flex flex-col items-center gap-1">
                            <div
                                className="w-full bg-sky-400 rounded-t-sm transition-all"
                                style={{ height: `${height}%` }}
                            />
                        </div>
                    );
                })}
            </div>
            <div className="flex gap-1">
                {Object.entries(data).map(([key, { label, total }]) => (
                    <div key={key} className="flex-1 text-center">
                        <span className="text-xs text-gray-400">{label}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}

function ChartCard({ title, children }: { title: string; children: React.ReactNode }) {
    return (
        <div className="bg-white rounded-xl border border-gray-200 p-4">
            <h4 className="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">{title}</h4>
            {children}
        </div>
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

            <div className="grid md:grid-cols-3 gap-4">
                <ChartCard title="Por estado">
                    <BarChart data={stats.charts.por_estado} colorMap={ESTADO_COLORS} labelMap={ESTADO_LABELS} />
                </ChartCard>
                <ChartCard title="Por tipo">
                    <BarChart data={stats.charts.por_tipo} colorMap={TIPO_COLORS} labelMap={TIPO_LABELS} />
                </ChartCard>
                <ChartCard title="Solicitudes por mes">
                    <TrendChart data={stats.charts.solicitudes_mes} />
                </ChartCard>
            </div>

            <div className="grid md:grid-cols-2 gap-4">
                <ChartCard title="Por tipo de solicitante">
                    <DonutChart data={stats.charts.por_solicitante} colorMap={SOLICITANTE_COLORS} labelMap={SOLICITANTE_LABELS} />
                </ChartCard>
                <ChartCard title="Tasa de aprobación">
                    <div className="flex flex-col items-center justify-center py-2">
                        <div className="relative w-28 h-28">
                            <svg viewBox="0 0 36 36" className="w-28 h-28 -rotate-90">
                                <circle r="14" cx="18" cy="18" fill="none" stroke="#e5e7eb" strokeWidth="6" />
                                <circle
                                    r="14"
                                    cx="18"
                                    cy="18"
                                    fill="none"
                                    stroke="#10b981"
                                    strokeWidth="6"
                                    strokeDasharray={`${(stats.tasa_aprobacion / 100) * 87.96} ${87.96 - (stats.tasa_aprobacion / 100) * 87.96}`}
                                />
                            </svg>
                            <div className="absolute inset-0 flex flex-col items-center justify-center">
                                <span className="text-2xl font-bold text-gray-900">{stats.tasa_aprobacion}%</span>
                                <span className="text-xs text-gray-400">aprobadas</span>
                            </div>
                        </div>
                        <div className="mt-3 flex gap-4 text-xs text-gray-500">
                            <span>Total: <strong className="text-gray-900">{stats.total}</strong></span>
                            <span>Aprobadas: <strong className="text-emerald-600">{stats.aprobadas}</strong></span>
                        </div>
                    </div>
                </ChartCard>
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

            <div className="grid md:grid-cols-3 gap-4">
                <ChartCard title="Por estado">
                    <BarChart data={stats.charts.por_estado} colorMap={ESTADO_COLORS} labelMap={ESTADO_LABELS} />
                </ChartCard>
                <ChartCard title="Por tipo">
                    <BarChart data={stats.charts.por_tipo} colorMap={TIPO_COLORS} labelMap={TIPO_LABELS} />
                </ChartCard>
                <ChartCard title="Solicitudes por mes">
                    <TrendChart data={stats.charts.solicitudes_mes} />
                </ChartCard>
            </div>

            <div className="grid md:grid-cols-2 gap-4">
                <ChartCard title="Top áreas con más solicitudes">
                    <BarChart data={stats.charts.por_area} labelMap={{}} />
                </ChartCard>
                <ChartCard title="Tasa de aprobación global">
                    <div className="flex flex-col items-center justify-center py-2">
                        <div className="relative w-28 h-28">
                            <svg viewBox="0 0 36 36" className="w-28 h-28 -rotate-90">
                                <circle r="14" cx="18" cy="18" fill="none" stroke="#e5e7eb" strokeWidth="6" />
                                <circle
                                    r="14"
                                    cx="18"
                                    cy="18"
                                    fill="none"
                                    stroke="#f59e0b"
                                    strokeWidth="6"
                                    strokeDasharray={`${(stats.tasa_aprobacion / 100) * 87.96} ${87.96 - (stats.tasa_aprobacion / 100) * 87.96}`}
                                />
                            </svg>
                            <div className="absolute inset-0 flex flex-col items-center justify-center">
                                <span className="text-2xl font-bold text-gray-900">{stats.tasa_aprobacion}%</span>
                                <span className="text-xs text-gray-400">aprobadas</span>
                            </div>
                        </div>
                        <div className="mt-3 flex gap-4 text-xs text-gray-500">
                            <span>Total: <strong className="text-gray-900">{stats.total}</strong></span>
                            <span>Aprobadas: <strong className="text-emerald-600">{stats.aprobadas}</strong></span>
                        </div>
                    </div>
                </ChartCard>
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

            <div className="grid md:grid-cols-3 gap-4">
                <ChartCard title="Por estado">
                    <BarChart data={stats.charts.por_estado} colorMap={ESTADO_COLORS} labelMap={ESTADO_LABELS} />
                </ChartCard>
                <ChartCard title="Por tipo">
                    <BarChart data={stats.charts.por_tipo} colorMap={TIPO_COLORS} labelMap={TIPO_LABELS} />
                </ChartCard>
                <ChartCard title="Solicitudes por mes">
                    <TrendChart data={stats.charts.solicitudes_mes} />
                </ChartCard>
            </div>

            <div className="grid md:grid-cols-2 gap-4">
                <ChartCard title="Top áreas con más solicitudes">
                    <BarChart data={stats.charts.por_area} labelMap={{}} />
                </ChartCard>
                <ChartCard title="Tasa de aprobación global">
                    <div className="flex flex-col items-center justify-center py-2">
                        <div className="relative w-28 h-28">
                            <svg viewBox="0 0 36 36" className="w-28 h-28 -rotate-90">
                                <circle r="14" cx="18" cy="18" fill="none" stroke="#e5e7eb" strokeWidth="6" />
                                <circle
                                    r="14"
                                    cx="18"
                                    cy="18"
                                    fill="none"
                                    stroke="#0ea5e9"
                                    strokeWidth="6"
                                    strokeDasharray={`${(stats.tasa_aprobacion / 100) * 87.96} ${87.96 - (stats.tasa_aprobacion / 100) * 87.96}`}
                                />
                            </svg>
                            <div className="absolute inset-0 flex flex-col items-center justify-center">
                                <span className="text-2xl font-bold text-gray-900">{stats.tasa_aprobacion}%</span>
                                <span className="text-xs text-gray-400">aprobadas</span>
                            </div>
                        </div>
                        <div className="mt-3 flex gap-4 text-xs text-gray-500">
                            <span>Total: <strong className="text-gray-900">{stats.total}</strong></span>
                            <span>Aprobadas: <strong className="text-emerald-600">{stats.aprobadas}</strong></span>
                        </div>
                    </div>
                </ChartCard>
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