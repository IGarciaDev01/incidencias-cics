import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { panel } from '@/routes/seguimiento';
import download from '@/routes/comprobante';
import { formatDateOnly, formatDateTime, formatTime } from '@/utils/date';

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
    fecha_incidencia: string | null;
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
};

const ESTADO_LABELS: Record<string, string> = {
    pendiente_jefe: 'Pendiente — Jefe Inmediato',
    pendiente_capital_humano: 'Pendiente — Capital Humano',
    pendiente_sindicato: 'Pendiente — Sindicato',
    pendiente_subdireccion: 'Pendiente — Subdirección Académica',
    aprobada: 'Aprobada',
    rechazada: 'Rechazada',
};

const ESTADO_COLORS: Record<string, string> = {
    pendiente_jefe: 'bg-yellow-100 text-yellow-800',
    pendiente_capital_humano: 'bg-orange-100 text-orange-800',
    pendiente_sindicato: 'bg-purple-100 text-purple-800',
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
    if (bytes < 1024) {
return `${bytes} B`;
}

    if (bytes < 1024 * 1024) {
return `${(bytes / 1024).toFixed(1)} KB`;
}

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export default function Detalle({ incidencia }: Props) {
    return (
        <>
            <Head title={`Folio ${incidencia.folio}`} />

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
                            <button
                                type="button"
                                onClick={() => window.open(download.descargar.url(incidencia.folio), '_blank')}
                                className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 cursor-pointer"
                            >
                                PDF
                            </button>
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
                                        <p className="text-xs text-gray-400 mt-1">{formatDateTime(item.created_at)}</p>
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

                <div className="text-center">
                    <Button variant="outline" asChild>
                        <Link href={panel.url()}>
                            Volver a mis incidencias
                        </Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
