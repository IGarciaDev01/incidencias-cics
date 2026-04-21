import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes/panel';
import {
    index,
    aprobar,
    rechazar,
    comentar,
} from '@/routes/panel/jefe_inmediato/incidencias';

type HistorialItem = {
    id: number;
    tipo_accion: string;
    comentario: string | null;
    es_interno: boolean;
    created_at: string;
    user: { id: number; nombre: string } | null;
};

type Incidencia = {
    id: number;
    folio: string;
    numero_empleado: string;
    reportante_nombre: string;
    tipo_solicitante: string;
    tipo_incidencia: string;
    minutos_retardo: number | null;
    fecha_incidencia: string;
    descripcion: string | null;
    estado: string;
    motivo_rechazo: string | null;
    created_at: string;
    area: { id: number; nombre: string } | null;
    revisado_por: { id: number; nombre: string } | null;
    historial: HistorialItem[];
    archivos: { id: number; nombre_original: string; tamanio_bytes: number }[];
};

type Props = { incidencia: Incidencia };

const ESTADO_LABELS: Record<string, string> = {
    pendiente_jefe: 'Pendiente (Jefe)',
    pendiente_capital_humano: 'Pendiente (Capital Humano)',
    pendiente_subdireccion: 'Pendiente (Subdirección)',
    aprobada: 'Aprobada',
    rechazada: 'Rechazada',
};
const ESTADO_COLORS: Record<string, string> = {
    pendiente_jefe: 'bg-yellow-100 text-yellow-800',
    pendiente_capital_humano: 'bg-orange-100 text-orange-800',
    pendiente_subdireccion: 'bg-blue-100 text-blue-800',
    aprobada: 'bg-green-100 text-green-800',
    rechazada: 'bg-red-100 text-red-800',
};
const TIPO_LABELS: Record<string, string> = {
    retardo: 'Retardo',
    permiso_economico: 'Permiso Económico',
    comision_oficial: 'Comisión Oficial',
    salida_anticipada: 'Salida Anticipada',
};
const ACCION_LABELS: Record<string, string> = {
    creada: 'Registrada', aprobada: 'Aprobada', rechazada: 'Rechazada',
    comentario: 'Comentario', archivo_adjunto: 'Archivo adjunto',
};

function formatDate(d: string) {
    return new Date(d).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
function formatBytes(b: number) {
    if (b < 1024 * 1024) return `${(b / 1024).toFixed(1)} KB`;
    return `${(b / (1024 * 1024)).toFixed(1)} MB`;
}

type Tab = 'aprobar' | 'rechazar' | 'comentar';

export default function Show({ incidencia }: Props) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };
    const [tab, setTab] = useState<Tab | null>(null);

    const aprobarForm  = useForm({ comentario: '' });
    const rechazarForm = useForm({ motivo: '' });
    const comentarForm = useForm({ comentario: '', es_interno: true });

    const puedeAprobar  = incidencia.estado === 'pendiente_jefe';
    const puedeRechazar = incidencia.estado === 'pendiente_jefe';
    const esFinal       = ['aprobada', 'rechazada'].includes(incidencia.estado);

    return (
        <>
            <Head title={`Incidencia ${incidencia.folio}`} />

            <div className="p-4 md:p-6 space-y-6 max-w-4xl">
                {flash?.success && (
                    <div className="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{flash.success}</div>
                )}
                {flash?.error && (
                    <div className="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{flash.error}</div>
                )}

                {/* Detalles */}
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <div className="flex flex-wrap items-start justify-between gap-3 mb-5">
                        <div>
                            <p className="text-xs text-gray-500">Folio</p>
                            <p className="text-xl font-mono font-bold text-gray-900">{incidencia.folio}</p>
                        </div>
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${ESTADO_COLORS[incidencia.estado] ?? 'bg-gray-100 text-gray-800'}`}>
                            {ESTADO_LABELS[incidencia.estado] ?? incidencia.estado}
                        </span>
                    </div>

                    <dl className="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <dt className="text-gray-500">No. empleado</dt>
                            <dd className="font-medium mt-0.5">{incidencia.numero_empleado}</dd>
                        </div>
                        <div>
                            <dt className="text-gray-500">Nombre</dt>
                            <dd className="font-medium mt-0.5">{incidencia.reportante_nombre}</dd>
                        </div>
                        <div>
                            <dt className="text-gray-500">Tipo de solicitante</dt>
                            <dd className="font-medium mt-0.5 capitalize">{incidencia.tipo_solicitante}</dd>
                        </div>
                        {incidencia.area && (
                            <div>
                                <dt className="text-gray-500">Área de adscripción</dt>
                                <dd className="font-medium mt-0.5">{incidencia.area.nombre}</dd>
                            </div>
                        )}
                        <div>
                            <dt className="text-gray-500">Fecha de incidencia</dt>
                            <dd className="font-medium mt-0.5">{new Date(incidencia.fecha_incidencia).toLocaleDateString('es-MX')}</dd>
                        </div>
                        <div>
                            <dt className="text-gray-500">Tipo de incidencia</dt>
                            <dd className="font-medium mt-0.5">
                                {TIPO_LABELS[incidencia.tipo_incidencia] ?? incidencia.tipo_incidencia}
                                {incidencia.minutos_retardo ? ` (${incidencia.minutos_retardo} min)` : ''}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-gray-500">Registrada</dt>
                            <dd className="font-medium mt-0.5">{formatDate(incidencia.created_at)}</dd>
                        </div>
                    </dl>

                    {incidencia.descripcion && (
                        <div className="mt-4 pt-4 border-t border-gray-100">
                            <p className="text-xs text-gray-500 mb-1">Notas adicionales</p>
                            <p className="text-sm text-gray-700">{incidencia.descripcion}</p>
                        </div>
                    )}

                    {incidencia.motivo_rechazo && (
                        <div className="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm">
                            <p className="font-medium text-red-700">Motivo de rechazo:</p>
                            <p className="text-red-600 mt-1">{incidencia.motivo_rechazo}</p>
                        </div>
                    )}
                </div>

                {/* Acciones */}
                {(puedeAprobar || puedeRechazar || !esFinal) && (
                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 className="font-semibold text-gray-900 mb-4">Acciones</h3>
                        <div className="flex flex-wrap gap-2 mb-4">
                            {puedeAprobar && (
                                <Button size="sm" variant={tab === 'aprobar' ? 'default' : 'outline'} onClick={() => setTab(tab === 'aprobar' ? null : 'aprobar')}>
                                    Aprobar
                                </Button>
                            )}
                            {puedeRechazar && (
                                <Button size="sm" variant={tab === 'rechazar' ? 'destructive' : 'outline'} onClick={() => setTab(tab === 'rechazar' ? null : 'rechazar')}>
                                    Rechazar
                                </Button>
                            )}
                            {!esFinal && (
                                <Button size="sm" variant={tab === 'comentar' ? 'default' : 'outline'} onClick={() => setTab(tab === 'comentar' ? null : 'comentar')}>
                                    Comentar
                                </Button>
                            )}
                        </div>

                        {tab === 'aprobar' && (
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    aprobarForm.post(aprobar.url(incidencia.id), { onSuccess: () => setTab(null) });
                                }}
                                className="border-t pt-4 space-y-3"
                            >
                                <div className="grid gap-1.5">
                                    <Label>Comentario (opcional)</Label>
                                    <textarea
                                        value={aprobarForm.data.comentario}
                                        onChange={(e) => aprobarForm.setData('comentario', e.target.value)}
                                        rows={2}
                                        placeholder="Observaciones al aprobar..."
                                        className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm resize-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    />
                                </div>
                                <p className="text-xs text-gray-500">Al aprobar, la incidencia pasará a Capital Humano.</p>
                                <Button type="submit" size="sm" disabled={aprobarForm.processing}>
                                    {aprobarForm.processing && <Spinner />} Confirmar aprobación
                                </Button>
                            </form>
                        )}

                        {tab === 'rechazar' && (
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    rechazarForm.post(rechazar.url(incidencia.id), { onSuccess: () => setTab(null) });
                                }}
                                className="border-t pt-4 space-y-3"
                            >
                                <div className="grid gap-1.5">
                                    <Label>Motivo de rechazo <span className="text-red-500">*</span></Label>
                                    <textarea
                                        value={rechazarForm.data.motivo}
                                        onChange={(e) => rechazarForm.setData('motivo', e.target.value)}
                                        rows={3} required minLength={10}
                                        placeholder="Explica el motivo del rechazo..."
                                        className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm resize-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    />
                                    <InputError message={rechazarForm.errors.motivo} />
                                </div>
                                <Button type="submit" size="sm" variant="destructive" disabled={rechazarForm.processing}>
                                    {rechazarForm.processing && <Spinner />} Confirmar rechazo
                                </Button>
                            </form>
                        )}

                        {tab === 'comentar' && (
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    comentarForm.post(comentar.url(incidencia.id), {
                                        preserveScroll: true,
                                        onSuccess: () => { setTab(null); comentarForm.reset(); },
                                    });
                                }}
                                className="border-t pt-4 space-y-3"
                            >
                                <div className="grid gap-1.5">
                                    <Label>Comentario <span className="text-red-500">*</span></Label>
                                    <textarea
                                        value={comentarForm.data.comentario}
                                        onChange={(e) => comentarForm.setData('comentario', e.target.value)}
                                        rows={3} required minLength={5}
                                        placeholder="Escribe tu comentario..."
                                        className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm resize-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    />
                                    <InputError message={comentarForm.errors.comentario} />
                                </div>
                                <div className="flex items-center gap-2">
                                    <input type="checkbox" id="es_interno"
                                        checked={comentarForm.data.es_interno}
                                        onChange={(e) => comentarForm.setData('es_interno', e.target.checked)}
                                        className="rounded border-gray-300"
                                    />
                                    <Label htmlFor="es_interno" className="cursor-pointer text-sm">Comentario interno</Label>
                                </div>
                                <Button type="submit" size="sm" disabled={comentarForm.processing}>
                                    {comentarForm.processing && <Spinner />} Agregar comentario
                                </Button>
                            </form>
                        )}
                    </div>
                )}

                {/* Historial */}
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 className="font-semibold text-gray-900 mb-4">Historial</h3>
                    {incidencia.historial.length === 0 ? (
                        <p className="text-sm text-gray-500">Sin registros de actividad.</p>
                    ) : (
                        <ol className="relative border-l border-gray-200 ml-3 space-y-4">
                            {incidencia.historial.map((item) => (
                                <li key={item.id} className="ml-6">
                                    <span className="absolute flex items-center justify-center w-3 h-3 bg-primary/20 rounded-full -left-1.5 ring-4 ring-white mt-1.5" />
                                    <div>
                                        <div className="flex items-center gap-2 flex-wrap">
                                            <p className="text-sm font-medium text-gray-900">
                                                {ACCION_LABELS[item.tipo_accion] ?? item.tipo_accion}
                                            </p>
                                            {item.es_interno && (
                                                <span className="text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded">interno</span>
                                            )}
                                        </div>
                                        {item.comentario && <p className="text-sm text-gray-600 mt-0.5">{item.comentario}</p>}
                                        <p className="text-xs text-gray-400 mt-0.5">
                                            {formatDate(item.created_at)} · {item.user?.nombre ?? 'Sistema'}
                                        </p>
                                    </div>
                                </li>
                            ))}
                        </ol>
                    )}
                </div>

                {incidencia.archivos.length > 0 && (
                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 className="font-semibold text-gray-900 mb-4">Archivos adjuntos</h3>
                        <ul className="divide-y divide-gray-100">
                            {incidencia.archivos.map((a) => (
                                <li key={a.id} className="py-2 flex items-center justify-between text-sm">
                                    <span className="text-gray-700">{a.nombre_original}</span>
                                    <span className="text-gray-400 text-xs">{formatBytes(a.tamanio_bytes)}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                <div>
                    <Button variant="outline" asChild>
                        <Link href={index.url()}>← Volver al listado</Link>
                    </Button>
                </div>
            </div>
        </>
    );
}

Show.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard.url() },
        { title: 'Incidencias', href: index.url() },
        { title: 'Detalle' },
    ],
};
