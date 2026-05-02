import { Head, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes/panel';
import { index, update } from '@/routes/panel/subdireccion/admin/usuarios';

type Usuario = {
    id: number;
    nombre: string;
    email: string;
    rol: string;
    activo: boolean;
    area_id: number | null;
    area: { id: number; nombre: string } | null;
};

type Props = {
    usuario: Usuario;
    roles: string[];
    areas: { id: number; nombre: string }[];
};

const ROL_LABELS: Record<string, string> = {
    admin: 'Administrador',
    jefe_inmediato: 'Jefe Inmediato',
    capital_humano: 'Capital Humano',
    subdireccion_academica: 'Subdirección Académica',
};

export default function Edit({ usuario, roles, areas }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        nombre: usuario.nombre,
        email: usuario.email,
        password: '',
        password_confirmation: '',
        rol: usuario.rol,
        area_id: usuario.area_id ? String(usuario.area_id) : '',
        activo: usuario.activo,
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(update.url(usuario.id));
    }

    return (
        <>
            <Head title={`Editar ${usuario.nombre}`} />

            <div className="p-4 md:p-6 max-w-2xl">
                <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">Editar usuario</h2>
                    <p className="text-sm text-gray-500">{usuario.email}</p>
                </div>

                <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                    <div className="grid gap-2">
                        <Label htmlFor="nombre">Nombre completo</Label>
                        <Input
                            id="nombre"
                            value={data.nombre}
                            onChange={(e) => setData('nombre', e.target.value)}
                            required
                            autoFocus
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
                            required
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid md:grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="password">Nueva contraseña</Label>
                            <Input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Dejar vacío para mantener la actual"
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

                    <div className="grid md:grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label>Rol</Label>
                            <Select value={data.rol} onValueChange={(v) => setData('rol', v)} required>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {roles.map((r) => (
                                        <SelectItem key={r} value={r}>{ROL_LABELS[r] ?? r}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.rol} />
                        </div>
                        <div className="grid gap-2">
                            <Label>Área</Label>
                            <Select value={data.area_id || '_none_'} onValueChange={(v) => setData('area_id', v === '_none_' ? '' : v)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Sin área" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="_none_">Sin área</SelectItem>
                                    {areas.map((a) => (
                                        <SelectItem key={a.id} value={String(a.id)}>{a.nombre}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.area_id} />
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
        { title: 'Dashboard', href: dashboard.url() },
        { title: 'Usuarios', href: index.url() },
        { title: 'Editar usuario' },
    ],
};
