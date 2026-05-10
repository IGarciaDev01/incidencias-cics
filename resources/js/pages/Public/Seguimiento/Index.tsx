import { Form, Head, Link, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { create as nuevaIncidencia } from '@/routes/incidencias';
import { login } from '@/routes/seguimiento';

export default function Index() {
    const { flash } = usePage().props as { flash?: { success?: string } };

    return (
        <>
            <Head title="Iniciar sesión" />

            {flash?.success && (
                <div className="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    {flash.success}
                </div>
            )}

            <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 md:p-8">
                <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">Consulta tus incidencias</h2>
                    <p className="text-sm text-gray-500 mt-1">
                        Ingresa tu número de empleado y contraseña para ver el historial de tus incidencias.
                    </p>
                </div>

                <Form {...login.form()} className="space-y-5">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="numero_empleado">Número de empleado <span className="text-red-500">*</span></Label>
                                <Input
                                    id="numero_empleado"
                                    name="numero_empleado"
                                    placeholder="Ej. 12345"
                                    required
                                    autoFocus
                                    autoComplete="username"
                                />
                                <InputError message={errors.numero_empleado} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Contraseña <span className="text-red-500">*</span></Label>
                                <Input
                                    id="password"
                                    name="password"
                                    type="password"
                                    placeholder="Tu contraseña"
                                    required
                                    autoComplete="current-password"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <Button type="submit" className="w-full" disabled={processing}>
                                {processing && <Spinner />}
                                Consultar mis incidencias
                            </Button>
                        </>
                    )}
                </Form>
            </div>

            <div className="mt-4 text-center text-sm text-gray-500">
                ¿Necesitas reportar una incidencia?{' '}
                <Link href={nuevaIncidencia.url()} className="text-primary hover:underline font-medium">
                    Haz clic aquí para reportarla
                </Link>
            </div>
        </>
    );
}
