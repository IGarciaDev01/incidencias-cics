import { Head, useForm } from '@inertiajs/react';
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
import { index, store, create } from '@/routes/panel/subdireccion/admin/usuarios';

type Props = {
    roles: string[];
    areas: { id: number; nombre: string }[];
};

const ROL_LABELS: Record<string, string> = {
    admin: 'Administrador',
    jefe_inmediato: 'Jefe Inmediato',
    capital_humano: 'Capital Humano',
    sindicato: 'Sindicato',
    subdirector: 'Subdirección Administrativa',
};

export default function Create({ roles, areas }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        nombre: '',
        email: '',
        numero_empleado: '',
        password: '',
        password_confirmation: '',
        rol: '',
        area_ids: [] as string[],
        es_jefe: true,
        activo: true,
    });

    const mostrarAreas = data.rol === 'jefe_inmediato';

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();

        if (data.rol === 'jefe_inmediato' && !data.numero_empleado.trim()) {
            alert('El número de empleado es obligatorio para jefe de área.');

            return;
        }

        if (mostrarAreas) {
            setData('area_ids', data.area_ids);
        } else {
            setData('area_ids', []);
        }

        post(store.url());
    }

    function handleRolChange(v: string) {
        setData('rol', v);

        if (v !== 'jefe_inmediato') {
            setData('area_ids', []);
        }
    }

    function toggleArea(id: string) {
        const current = data.area_ids;

        if (current.includes(id)) {
            setData('area_ids', current.filter((a) => a !== id));
        } else {
            setData('area_ids', [...current, id]);
        }
    }

    return (
        <>
            <Head title="Nuevo usuario" />

            <div className="p-4 md:p-6 max-w-2xl">
                <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">Nuevo usuario</h2>
                    <p className="text-sm text-gray-500">Completa los datos para crear una nueva cuenta.</p>
                </div>

                <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                    <div className="grid gap-2">
                        <Label htmlFor="nombre">Nombre completo</Label>
                        <Input
                            id="nombre"
                            value={data.nombre}
                            onChange={(e) => setData('nombre', e.target.value)}
                            placeholder="Nombre completo"
                            required
                            autoFocus
                        />
                        <InputError message={errors.nombre} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="numero_empleado">Número de empleado</Label>
                        <Input
                            id="numero_empleado"
                            value={data.numero_empleado}
                            onChange={(e) => setData('numero_empleado', e.target.value)}
                            placeholder="Número de empleado"
                            required={data.rol === 'jefe_inmediato'}
                        />
                        <p className="text-xs text-gray-500">Obligatorio solo para jefes de área.</p>
                        <InputError message={errors.numero_empleado} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">Correo electrónico</Label>
                        <Input
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="usuario@ejemplo.com"
                            required
                        />
                        <InputError message={errors.email} />
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

                    <div className="grid gap-2">
                        <Label>Rol</Label>
                        <Select value={data.rol} onValueChange={handleRolChange} required>
                            <SelectTrigger>
                                <SelectValue placeholder="Selecciona un rol" />
                            </SelectTrigger>
                            <SelectContent>
                                {roles.map((r) => (
                                    <SelectItem key={r} value={r}>{ROL_LABELS[r] ?? r}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.rol} />
                    </div>

                    {mostrarAreas && (
                        <div className="grid gap-2">
                            <Label>Áreas a administrar</Label>
                            <div className="border rounded-lg p-3 space-y-2 max-h-48 overflow-y-auto">
                                {areas.map((a) => (
                                    <label key={a.id} className="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                                        <input
                                            type="checkbox"
                                            checked={data.area_ids.includes(String(a.id))}
                                            onChange={() => toggleArea(String(a.id))}
                                            className="rounded border-gray-300"
                                        />
                                        <span className="text-sm">{a.nombre}</span>
                                    </label>
                                ))}
                            </div>
                            <InputError message={errors.area_ids} />
                        </div>
                    )}

                    <div className="flex items-center gap-3 pt-2">
                        <Button type="submit" disabled={processing}>
                            {processing && <Spinner />}
                            Crear usuario
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
        { title: 'Usuarios', href: index.url() },
        { title: 'Nuevo usuario', href: create.url() },
    ],
};
