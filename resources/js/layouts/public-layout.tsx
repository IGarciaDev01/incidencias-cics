import { Link } from '@inertiajs/react';
import { create as nuevaIncidencia } from '@/routes/incidencias';
import { dashboard as IniciarSesion } from '@/routes/panel';
import { index as seguimientoIndex } from '@/routes/seguimiento';

export default function PublicLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return (
        <div className="flex min-h-screen flex-col bg-gray-50">
            <header className="border-b border-gray-200 bg-white shadow-sm">
                <div className="flex w-full flex-col items-center justify-between gap-3 px-4 py-4 sm:flex-row sm:px-6 lg:px-8">
                    <div className="min-w-0 text-center sm:text-left">
                        <div className="flex items-center justify-center gap-3 sm:justify-start">
                            <img
                                src="/ipn.png"
                                alt="Instituto Politécnico Nacional"
                                className="size-10 shrink-0"
                            />
                            <h1 className="text-lg font-semibold text-gray-900">
                                Sistema de Incidencias
                            </h1>
                            <img
                                src="/logocicsstf.png"
                                alt="CICS UST - IPN"
                                className="size-10 shrink-0"
                            />
                        </div>
                        <p className="text-xs text-gray-500 pt-2">
                            Centro Interdisciplinario de Ciencias de la Salud -
                            UST IPN
                        </p>
                    </div>
                    <nav className="flex items-center gap-4">
                        <Link
                            href={nuevaIncidencia.url()}
                            className="text-sm text-gray-600 transition-colors hover:text-gray-900"
                            aria-label="Levantar incidencia"
                        >
                            Levantar incidencia
                        </Link>
                        <Link
                            href={seguimientoIndex.url()}
                            className="text-sm text-gray-600 transition-colors hover:text-gray-900"
                        >
                            Seguimiento
                        </Link>
                        <Link
                            href={IniciarSesion.url()}
                            className="rounded-md bg-primary px-2 py-1 text-sm text-white transition-colors hover:text-gray-200"
                        >
                            Iniciar Sesión
                        </Link>
                    </nav>
                </div>
            </header>

            <main className="mx-auto w-full max-w-4xl flex-1 px-4 py-8">
                {children}
            </main>

            <footer className="border-t border-gray-200 bg-white py-4 text-center text-xs text-gray-400">
                Sistema de Gestión de Incidencias - Centro Interdisciplinario de
                Ciencias de la Salud - UST IPN
            </footer>
        </div>
    );
}
