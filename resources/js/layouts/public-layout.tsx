import { Link } from '@inertiajs/react';
import { create as nuevaIncidencia } from '@/routes/incidencias';
import { index as seguimientoIndex } from '@/routes/seguimiento';

export default function PublicLayout({ children }: { children: React.ReactNode }) {
    return (
        <div className="min-h-screen bg-gray-50 flex flex-col">
            <header className="bg-white border-b border-gray-200 shadow-sm">
                <div className="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
                    <div>
                        <h1 className="text-lg font-semibold text-gray-900">Sistema de Incidencias</h1>
                        <p className="text-xs text-gray-500">Portal ciudadano</p>
                    </div>
                    <nav className="flex items-center gap-4">
                        <Link
                            href={nuevaIncidencia.url()}
                            className="text-sm text-gray-600 hover:text-gray-900 transition-colors"
                        >
                            Reportar incidencia
                        </Link>
                        <Link
                            href={seguimientoIndex.url()}
                            className="text-sm text-gray-600 hover:text-gray-900 transition-colors"
                        >
                            Seguimiento
                        </Link>
                    </nav>
                </div>
            </header>

            <main className="flex-1 max-w-4xl mx-auto w-full px-4 py-8">
                {children}
            </main>

            <footer className="bg-white border-t border-gray-200 py-4 text-center text-xs text-gray-400">
                Sistema de Gestión de Incidencias
            </footer>
        </div>
    );
}
