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
import { index, store, create } from '@/routes/panel/subdireccion/admin/areas';

type Props = {
    jefes: { id: number; nombre: string }[];
};

function toSlug(text: string) {
    return text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

export default function Create({ jefes }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        nombre: '',
        slug: '',
        descripcion: '',
        jefe_id: '',
        activa: true,
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(store.url());
    }

    return (
        <>
            <Head title="Nueva área" />

            <div className="p-4 md:p-6 max-w-xl">
                <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">Nueva área</h2>
                </div>

                <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                    <div className="grid gap-2">
                        <Label htmlFor="nombre">Nombre del área</Label>
                        <Input
                            id="nombre"
                            value={data.nombre}
                            onChange={(e) => {
                                setData('nombre', e.target.value);
                                if (!data.slug || data.slug === toSlug(data.nombre)) {
                                    setData('slug', toSlug(e.target.value));
                                }
                            }}
                            placeholder="Ej. Servicios Públicos"
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
                            placeholder="identificador-unico"
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
                            placeholder="Descripción opcional del área..."
                            rows={3}
                            className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm resize-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        />
                        <InputError message={errors.descripcion} />
                    </div>

                    <div className="grid gap-2">
                        <Label>Jefe responsable</Label>
                        <Select value={data.jefe_id || '_none_'} onValueChange={(v) => setData('jefe_id', v === '_none_' ? '' : v)}>
                            <SelectTrigger>
                                <SelectValue placeholder="Sin jefe asignado" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="_none_">Sin jefe</SelectItem>
                                {jefes.map((j) => (
                                    <SelectItem key={j.id} value={String(j.id)}>{j.nombre}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.jefe_id} />
                    </div>

                    <div className="flex items-center gap-3 pt-2">
                        <Button type="submit" disabled={processing}>
                            {processing && <Spinner />}
                            Crear área
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
        { title: 'Dashboard', href: dashboard.url() },
        { title: 'Áreas', href: index.url() },
        { title: 'Nueva área', href: create.url() },
    ],
};