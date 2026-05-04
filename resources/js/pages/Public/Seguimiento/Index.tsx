import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { buscar } from '@/routes/seguimiento';
export default function Index() {
    return (
        <>
            <Head title="Seguimiento de incidencia" />

            <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 md:p-8">
                <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">Consultar estado de incidencia</h2>
                    <p className="text-sm text-gray-500 mt-1">
                        Ingresa tu folio y número de empleado o correo electrónico para ver el estado de tu reporte.
                    </p>
                </div>

                <Form {...buscar.form()} className="space-y-5">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="folio">Número de folio <span className="text-red-500">*</span></Label>
                                <Input
                                    id="folio"
                                    name="folio"
                                    placeholder="Ej. INC-2024-001"
                                    required
                                    autoFocus
                                />
                                <InputError message={errors.folio} />
                            </div>

                            <div className="relative flex items-center gap-3">
                                <div className="flex-1 border-t border-gray-200" />
                                <span className="text-xs text-gray-400 uppercase tracking-wide">Verificación (elige una)</span>
                                <div className="flex-1 border-t border-gray-200" />
                            </div>

                            <div className="grid md:grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="numero_empleado">Número de empleado</Label>
                                    <Input
                                        id="numero_empleado"
                                        name="numero_empleado"
                                        placeholder="Ej. 12345"
                                        autoComplete="off"
                                    />
                                    <InputError message={errors.numero_empleado} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="email">Correo electrónico</Label>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        placeholder="correo@ejemplo.com"
                                        autoComplete="email"
                                    />
                                    <InputError message={errors.email} />
                                </div>
                            </div>

                            <Button type="submit" className="w-full" disabled={processing}>
                                {processing && <Spinner />}
                                Consultar incidencia
                            </Button>
                        </>
                    )}
                </Form>
            </div>

            <div className="mt-4 text-center text-sm text-gray-500">
                ¿Necesitas reportar una incidencia?{' '}
                <a href="/nueva-incidencia" className="text-primary hover:underline font-medium">
                    Haz clic aquí para reportarla
                </a>
            </div>
        </>
    );
}

