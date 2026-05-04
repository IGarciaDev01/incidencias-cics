import { Form, Head, Link, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { formatDateOnly, formatDateTime, formatTime } from '@/utils/date';
import { comentar, adjuntar, index as seguimientoIndex } from '@/routes/seguimiento';
import download from '@/routes/comprobante';

type HistorialItem = {
    id: number;
    tipo_accion: string;
    comentario: string | null;
    es_interno: boolean;
    created_at: string;
    user: { id: number; nombre: string } | null;
};

type Archivo = {
    id: number;
    nombre_original: string;
    mime_type: string;
    tamanio_bytes: number;
    ruta_storage: string;
    created_at: string;
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
    hora_incidencia: string | null;
    descripcion: string | null;
    estado: string;
    motivo_rechazo: string | null;
    created_at: string;
    area: { id: number; nombre: string } | null;
    historial: HistorialItem[];
    archivos: Archivo[];
};

type Props = {
    incidencia: Incidencia;
    puedeActuar: boolean;
};

const ESTADO_LABELS: Record<string, string> = {
    pendiente_jefe: 'Pendiente — Jefe Inmediato',
    pendiente_capital_humano: 'Pendiente — Capital Humano',
    pendiente_subdireccion: 'Pendiente — Subdirección Académica',
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
    permiso_sindical: 'Permiso Sindical',
    incidencia_medica: 'Incidencia Médica',
    buena_conducta: 'Buena Conducta',
};

const ACCION_LABELS: Record<string, string> = {
    creada: 'Incidencia registrada',
    aprobada: 'Aprobada',
    rechazada: 'Rechazada',
    comentario: 'Comentario agregado',
    archivo_adjunto: 'Archivo adjunto',
};

function formatBytes(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function formatDate(dateStr: string): string {
    if (!dateStr) return '—';
    return formatDateTime(dateStr);
}

export default function Show({ incidencia, puedeActuar }: Props) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };

    const estadoFinal = ['aprobada', 'rechazada'].includes(incidencia.estado);

    return (
        <>
            <Head title={`Folio ${incidencia.folio}`} />

            {flash?.success && (
                <div className="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    {flash.success}
                </div>
            )}

            <div className="space-y-6">
                {/* Header */}
                <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div className="flex flex-wrap items-start justify-between gap-3 mb-4">
                        <div>
                            <p className="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Folio</p>
                            <p className="text-2xl font-mono font-bold text-gray-900">{incidencia.folio}</p>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${ESTADO_COLORS[incidencia.estado] ?? 'bg-gray-100 text-gray-800'}`}>
                                {ESTADO_LABELS[incidencia.estado] ?? incidencia.estado}
                            </span>
                            <a
                                href={download.descargar.url(incidencia.folio)}
                                className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200"
                            >
                                <svg className="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                PDF
                            </a>
                        </div>
                    </div>

                    <dl className="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm pt-4 border-t border-gray-100">
                        <div>
                            <dt className="text-gray-500">No. empleado</dt>
                            <dd className="font-medium text-gray-900 mt-0.5">{incidencia.numero_empleado}</dd>
                        </div>
                        <div>
                            <dt className="text-gray-500">Nombre</dt>
                            <dd className="font-medium text-gray-900 mt-0.5">{incidencia.reportante_nombre}</dd>
                        </div>
                        <div>
                            <dt className="text-gray-500">Tipo solicitante</dt>
                            <dd className="font-medium text-gray-900 mt-0.5 capitalize">{incidencia.tipo_solicitante}</dd>
                        </div>
                        {incidencia.area && (
                            <div>
                                <dt className="text-gray-500">Área de adscripción</dt>
                                <dd className="font-medium text-gray-900 mt-0.5">{incidencia.area.nombre}</dd>
                            </div>
                        )}
                        <div>
                            <dt className="text-gray-500">Fecha incidencia</dt>
                            <dd className="font-medium text-gray-900 mt-0.5">
                                {incidencia.fecha_incidencia
                                    ? formatDateOnly(incidencia.fecha_incidencia, false)
                                    : '—'}
                                {incidencia.hora_incidencia && (
                                    <span className="ml-1 text-gray-500">— {formatTime(incidencia.hora_incidencia)}</span>
                                )}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-gray-500">Tipo</dt>
                            <dd className="font-medium text-gray-900 mt-0.5">
                                {TIPO_LABELS[incidencia.tipo_incidencia] ?? incidencia.tipo_incidencia}
                                {incidencia.minutos_retardo ? ` (${incidencia.minutos_retardo} min)` : ''}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-gray-500">Registrada</dt>
                            <dd className="font-medium text-gray-900 mt-0.5">{formatDateOnly(incidencia.created_at, true)}</dd>
                        </div>
                    </dl>

                    {incidencia.descripcion && (
                        <div className="mt-4 pt-4 border-t border-gray-100 text-sm">
                            <p className="text-gray-500 mb-1">Notas adicionales</p>
                            <p className="text-gray-700">{incidencia.descripcion}</p>
                        </div>
                    )}

                    {incidencia.motivo_rechazo && (
                        <div className="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm">
                            <p className="font-medium text-red-700">Motivo de rechazo:</p>
                            <p className="text-red-600 mt-1">{incidencia.motivo_rechazo}</p>
                        </div>
                    )}
                </div>

                {/* Historial */}
                <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 className="font-semibold text-gray-900 mb-4">Historial de seguimiento</h3>
                    {incidencia.historial.length === 0 ? (
                        <p className="text-sm text-gray-500">No hay actividad registrada aún.</p>
                    ) : (
                        <ol className="relative border-l border-gray-200 ml-3 space-y-4">
                            {incidencia.historial.map((item) => (
                                <li key={item.id} className="ml-6">
                                    <span className="absolute flex items-center justify-center w-3 h-3 bg-primary/20 rounded-full -left-1.5 ring-4 ring-white mt-1.5" />
                                    <div>
                                        <p className="text-sm font-medium text-gray-900">
                                            {ACCION_LABELS[item.tipo_accion] ?? item.tipo_accion}
                                        </p>
                                        {item.comentario && (
                                            <p className="text-sm text-gray-600 mt-1">{item.comentario}</p>
                                        )}
                                        <p className="text-xs text-gray-400 mt-1">{formatDate(item.created_at)}</p>
                                    </div>
                                </li>
                            ))}
                        </ol>
                    )}
                </div>

                {/* Archivos */}
                {incidencia.archivos.length > 0 && (
                    <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <h3 className="font-semibold text-gray-900 mb-4">Archivos adjuntos</h3>
                        <ul className="divide-y divide-gray-100">
                            {incidencia.archivos.map((archivo) => (
                                <li key={archivo.id} className="py-2 flex items-center justify-between text-sm">
                                    <span className="text-gray-700">{archivo.nombre_original}</span>
                                    <div className="flex items-center gap-3">
                                        <span className="text-gray-400 text-xs">{formatBytes(archivo.tamanio_bytes)}</span>
                            <a
                                href={download.ver_archivo.url({ folio: incidencia.folio, archivoId: archivo.id })}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-primary hover:text-primary/80 text-xs font-medium"
                            >
                                            Ver
                                        </a>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                {/* Acciones */}
                {puedeActuar && !estadoFinal && (
                    <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
                        <h3 className="font-semibold text-gray-900">Agregar información</h3>

                        <Form
                            action={comentar.url(incidencia.folio)}
                            method="post"
                            className="space-y-3"
                            resetOnSuccess
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <label className="text-sm font-medium text-gray-700">Agregar comentario</label>
                                        <textarea
                                            name="comentario"
                                            rows={3}
                                            placeholder="Escribe información adicional..."
                                            className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm resize-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                            required
                                            minLength={10}
                                        />
                                        <InputError message={errors.comentario} />
                                    </div>
                                    <Button type="submit" size="sm" disabled={processing}>
                                        {processing && <Spinner />}
                                        Enviar comentario
                                    </Button>
                                </>
                            )}
                        </Form>

                        <div className="border-t pt-4">
                            <Form
                                action={adjuntar.url(incidencia.folio)}
                                method="post"
                                encType="multipart/form-data"
                                className="space-y-3"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <label className="text-sm font-medium text-gray-700">Adjuntar archivo</label>
                                            <input
                                                name="archivo"
                                                type="file"
                                                accept="image/*,.pdf,.doc,.docx"
                                                required
                                                className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium"
                                            />
                                            <InputError message={errors.archivo} />
                                        </div>
                                        <Button type="submit" size="sm" variant="outline" disabled={processing}>
                                            {processing && <Spinner />}
                                            Adjuntar archivo
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </div>
                    </div>
                )}

                <div className="text-center">
                    <Link href={seguimientoIndex.url()} className="text-sm text-gray-500 hover:text-gray-700">
                        Consultar otra incidencia
                    </Link>
                </div>
            </div>
        </>
    );
}
