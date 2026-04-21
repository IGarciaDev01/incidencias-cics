import { Head, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes/panel';
import { index, update } from '@/routes/panel/admin/areas';

type Area = {
    id: number;
    nombre: string;
    slug: string;
    descripcion: string | null;
    subdirector_id: number | null;
    activa: boolean;
    subdirector: { id: number; nombre: string } | null;
};

type Props = {
    area: Area;
    subdirectores: { id: number; nombre: string }[];
};

export default function Edit({ area, subdirectores }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        nombre: area.nombre,
        slug: area.slug,
        descripcion: area.descripcion ?? '',
        subdirector_id: area.subdirector_id ? String(area.subdirector_id) : '',
        activa: area.activa,
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(update.url(area.id));
    }

    return (
        <>
            <Head title={`Editar ${area.nombre}`} />

            <div className="p-4 md:p-6 max-w-xl">
                <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">Editar área</h2>
                </div>

                <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                    <div className="grid gap-2">
                        <Label htmlFor="nombre">Nombre del área</Label>
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
                        <Label htmlFor="slug">Slug</Label>
                        <Input
                            id="slug"
                            value={data.slug}
                            onChange={(e) => setData('slug', e.target.value)}
                            required
                        />
                        <InputError message={errors.slug} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="descripcion">Descripción</Label>
                        <textarea
                            id="descripcion"
                            value={data.descripcion}
                            onChange={(e) => setData('descripcion', e.target.value)}
                            rows={3}
                            className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm resize-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        />
                        <InputError message={errors.descripcion} />
                    </div>

                    <div className="grid gap-2">
                        <Label>Subdirector responsable</Label>
                        <Select value={data.subdirector_id || '_none_'} onValueChange={(v) => setData('subdirector_id', v === '_none_' ? '' : v)}>
                            <SelectTrigger>
                                <SelectValue placeholder="Sin subdirector" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="_none_">Sin subdirector</SelectItem>
                                {subdirectores.map((s) => (
                                    <SelectItem key={s.id} value={String(s.id)}>{s.nombre}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.subdirector_id} />
                    </div>

                    <div className="flex items-center gap-2">
                        <Checkbox
                            id="activa"
                            checked={data.activa}
                            onCheckedChange={(v) => setData('activa', !!v)}
                        />
                        <Label htmlFor="activa" className="cursor-pointer">Área activa</Label>
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
        { title: 'Áreas', href: index.url() },
        { title: 'Editar área' },
    ],
};
