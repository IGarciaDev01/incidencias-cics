import { Head, useForm, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes/panel';
import { index as chEmpleados, create as chCreate, store as chStore } from '@/routes/panel/capital_humano/empleados';
import { index as subdirEmpleados, create as subdirCreate, store as subdirStore } from '@/routes/panel/subdireccion/empleados';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        numero_empleado: '',
        nombre: '',
        email: '',
        tipo: '',
        password: '',
        password_confirmation: '',
    });

    const { auth } = usePage().props as { auth: { user?: { rol?: string } } };
    const rol = auth.user?.rol ?? '';

    const backUrl = rol === 'capital_humano' ? chEmpleados.url() : subdirEmpleados.url();
    const storeUrl = rol === 'capital_humano' ? chStore.url() : subdirStore.url();

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(storeUrl);
    }

    return (
        <>
            <Head title="Nuevo empleado" />

            <div className="p-4 md:p-6 max-w-2xl">
                <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">Nuevo empleado</h2>
                    <p className="text-sm text-gray-500">Registra un nuevo empleado en el sistema.</p>
                </div>

                <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                    <div className="grid gap-2">
                        <Label htmlFor="numero_empleado">Número de empleado</Label>
                        <Input
                            id="numero_empleado"
                            value={data.numero_empleado}
                            onChange={(e) => setData('numero_empleado', e.target.value)}
                            placeholder="Ej. 12345"
                            required
                            autoFocus
                        />
                        <InputError message={errors.numero_empleado} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="nombre">Nombre completo</Label>
                        <Input
                            id="nombre"
                            value={data.nombre}
                            onChange={(e) => setData('nombre', e.target.value)}
                            placeholder="Nombre completo"
                            required
                        />
                        <InputError message={errors.nombre} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">Correo electrónico</Label>
                        <Input
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="correo@ejemplo.com"
                            required
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label>Tipo de empleado</Label>
                        <Select value={data.tipo} onValueChange={(v) => setData('tipo', v)} required>
                            <SelectTrigger>
                                <SelectValue placeholder="Selecciona..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="docente">Docente</SelectItem>
                                <SelectItem value="administrativo">Administrativo</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError message={errors.tipo} />
                    </div>

                    <div className="grid md:grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="password">Contraseña</Label>
                            <Input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Mínimo 8 caracteres"
                                required
                            />
                            <InputError message={errors.password} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">Confirmar contraseña</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                placeholder="Repite la contraseña"
                                required
                            />
                        </div>
                    </div>

                    <div className="flex items-center gap-3 pt-2">
                        <Button type="submit" disabled={processing}>
                            {processing && <Spinner />}
                            Crear empleado
                        </Button>
                        <Button variant="outline" type="button" onClick={() => history.back()}>
                            Cancelar
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

Create.layout = {
    breadcrumbs: [
        { title: 'Panel Principal', href: dashboard.url() },
        { title: 'Empleados', href: '' },
        { title: 'Nuevo empleado' },
    ],
};
