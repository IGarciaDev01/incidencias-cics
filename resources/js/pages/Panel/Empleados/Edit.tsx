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
import { index as chEmpleados, show as chShow, update as chUpdate } from '@/routes/panel/capital_humano/empleados';
import { index as sindicatoEmpleados, show as sindicatoShow, update as sindicatoUpdate } from '@/routes/panel/sindicato/empleados';
import { index as subdirEmpleados, show as subdirShow, update as subdirUpdate } from '@/routes/panel/subdireccion/empleados';

type Props = {
    empleado: {
        numero_empleado: string;
        nombre: string;
        email: string;
        tipo: string | null;
    };
};

export default function Edit({ empleado }: Props) {
    const { auth } = usePage().props as { auth: { user?: { rol?: string } } };
    const { data, setData, patch, processing, errors } = useForm({
        numero_empleado: empleado.numero_empleado,
        nombre: empleado.nombre,
        email: empleado.email,
        tipo: empleado.tipo ?? '',
        password: '',
        password_confirmation: '',
    });

    const rol = auth.user?.rol ?? '';

    const indexUrl = rol === 'capital_humano' ? chEmpleados.url() : rol === 'sindicato' ? sindicatoEmpleados.url() : subdirEmpleados.url();
    const showUrl = rol === 'capital_humano' ? chShow : rol === 'sindicato' ? sindicatoShow : subdirShow;
    const updateUrl = rol === 'capital_humano' ? chUpdate.url({ numeroEmpleado: empleado.numero_empleado }) : rol === 'sindicato' ? sindicatoUpdate.url({ numeroEmpleado: empleado.numero_empleado }) : subdirUpdate.url({ numeroEmpleado: empleado.numero_empleado });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        patch(updateUrl);
    }

    return (
        <>
            <Head title="Editar empleado" />

            <div className="p-4 md:p-6 max-w-2xl">
                <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">Editar empleado</h2>
                    <p className="text-sm text-gray-500">Actualiza los datos del empleado.</p>
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
                            <Label htmlFor="password">Nueva contraseña <span className="text-gray-400 text-xs">(dejar vacío para mantener)</span></Label>
                            <Input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Mínimo 8 caracteres"
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
                            />
                        </div>
                    </div>

                    <div className="flex items-center gap-3 pt-2">
                        <Button type="submit" disabled={processing}>
                            {processing && <Spinner />}
                            Guardar cambios
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

Edit.layout = {
    breadcrumbs: [
        { title: 'Panel Principal', href: dashboard.url() },
        { title: 'Empleados', href: '' },
        { title: `Editando empleado` },
    ],
};
