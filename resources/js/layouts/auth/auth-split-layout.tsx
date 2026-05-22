import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
const home = () => '/';
import type { AuthLayoutProps } from '@/types';

export default function AuthSplitLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div className="relative hidden h-full flex-col bg-muted p-10 text-white lg:flex">
                <div className="absolute inset-0 bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900" />
                <Link
                    href={home()}
                    className="relative z-20 flex items-center text-lg font-medium"
                >
                    <AppLogoIcon className="mr-3 size-10 fill-current text-white" />
                    <div>
                        <div className="font-bold text-lg">CICS UST - IPN</div>
                        <div className="text-xs text-blue-200">Centro Intersdisciplinario de Ciencias de la Salud</div>
                    </div>
                </Link>
                <div className="relative z-20 mt-auto">
                    <div className="rounded-xl bg-white/10 backdrop-blur p-6 text-white">
                        <h2 className="text-xl font-bold mb-2">Sistema de Gestión de Incidencias</h2>
                        <p className="text-sm text-blue-200">Control y seguimiento de incidencias del personal académico y administrativo.</p>
                    </div>
                </div>
            </div>
            <div className="w-full lg:p-8">
                <div className="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <Link
                        href={home()}
                        className="relative z-20 flex items-center justify-center lg:hidden"
                    >
                        <AppLogoIcon className="h-10 fill-current text-blue-900 sm:h-12" />
                    </Link>
                    <div className="flex flex-col items-start gap-2 text-left sm:items-center sm:text-center">
                        <h1 className="text-xl font-medium">{title}</h1>
                        <p className="text-sm text-balance text-muted-foreground">
                            {description}
                        </p>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
