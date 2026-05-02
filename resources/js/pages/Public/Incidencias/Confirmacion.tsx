import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { index as seguimientoIndex } from '@/routes/seguimiento';
import { create as nuevaIncidencia } from '@/routes/incidencias';
import download from '@/routes/comprobante';

type Props = {
    folio: string;
    numero_empleado: string;
    tipo_incidencia: string;
    fecha_incidencia: string;
    hora_incidencia: string | null;
};

export default function Confirmacion({ folio, numero_empleado, tipo_incidencia, fecha_incidencia, hora_incidencia }: Props) {
    const handleDownloadPdf = () => {
        window.location.href = download.descargar.url(folio);
    };

    return (
        <>
            <Head title="Incidencia enviada" />

            <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
                <div className="flex justify-center mb-4">
                    <div className="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                        <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <h2 className="text-2xl font-semibold text-gray-900 mb-2">¡Incidencia registrada!</h2>
                <p className="text-gray-600 mb-6">
                    Tu incidencia ha sido recibida y está pendiente de aprobación por tu jefe inmediato.
                </p>

                <div className="bg-gray-50 rounded-lg p-6 mb-6 text-left space-y-3">
                    <div>
                        <p className="text-xs text-gray-500 uppercase tracking-wide font-medium">Número de folio</p>
                        <p className="text-2xl font-mono font-bold text-gray-900 mt-1">{folio}</p>
                        <p className="text-xs text-gray-500 mt-1">Guarda este número para dar seguimiento</p>
                    </div>

                    <div className="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide font-medium">No. empleado</p>
                            <p className="text-sm font-medium text-gray-900 mt-0.5">{numero_empleado}</p>
                        </div>
                        <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide font-medium">Tipo</p>
                            <p className="text-sm font-medium text-gray-900 mt-0.5">{tipo_incidencia}</p>
                        </div>
                        <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide font-medium">Fecha</p>
                            <p className="text-sm font-medium text-gray-900 mt-0.5">
                                {fecha_incidencia}
                                {hora_incidencia && <span className="text-gray-500 ml-1">— {hora_incidencia.substring(0, 5)}</span>}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6 text-sm text-blue-700 text-left">
                    <strong>Proceso de aprobación:</strong> Tu incidencia pasará por tres niveles de aprobación:
                    Jefe Inmediato → Capital Humano → Subdirección Administrativa.
                </div>

                <div className="flex flex-col sm:flex-row gap-3 justify-center">
                    <Button onClick={handleDownloadPdf} variant="outline">
                        <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Descargar comprobante PDF
                    </Button>
                    <Button asChild>
                        <Link href={`${seguimientoIndex.url()}?folio=${folio}`}>
                            Dar seguimiento
                        </Link>
                    </Button>
                    <Button variant="ghost" asChild>
                        <Link href={nuevaIncidencia.url()}>
                            Registrar otra incidencia
                        </Link>
                    </Button>
                </div>
            </div>
        </>
    );
}

