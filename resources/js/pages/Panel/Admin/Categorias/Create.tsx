import { Head, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import { index, store, create } from '@/routes/panel/subdireccion/admin/categorias';

type Props = {
    prioridades: string[];
};

const PRIORIDAD_LABELS: Record<string, string> = { alta: 'Alta', media: 'Media', baja: 'Baja' };

function toSlug(text: string) {
    return text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

export default function Create({ prioridades }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        nombre: '',
        slug: '',
        descripcion: '',
        prioridad_defecto: 'media',
        activa: true,
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(store.url());
    }

    return (
        <>
            <Head title="Nueva categoría" />

            <div className="p-4 md:p-6 max-w-xl">
                <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">Nueva categoría</h2>
                </div>

                <form onSubmit={handleSubmit} className="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                    <div className="grid gap-2">
                        <Label htmlFor="nombre">Nombre</Label>
                        <Input
                            id="nombre"
                            value={data.nombre}
                            onChange={(e) => {
                                setData('nombre', e.target.value);

                                if (!data.slug || data.slug === toSlug(data.nombre)) {
                                    setData('slug', toSlug(e.target.value));
                                }
                            }}
                            placeholder="Nombre de la categoría"
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
                            placeholder="Descripción opcional..."
                            rows={3}
                            className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm resize-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        />
                        <InputError message={errors.descripcion} />
                    </div>

                    <div className="grid gap-2">
                        <Label>Prioridad por defecto</Label>
                        <Select value={data.prioridad_defecto} onValueChange={(v) => setData('prioridad_defecto', v)}>
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {prioridades.map((p) => (
                                    <SelectItem key={p} value={p}>{PRIORIDAD_LABELS[p] ?? p}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.prioridad_defecto} />
                    </div>

                    <div className="flex items-center gap-2">
                        <Checkbox
                            id="activa"
                            checked={data.activa}
                            onCheckedChange={(v) => setData('activa', !!v)}
                        />
                        <Label htmlFor="activa" className="cursor-pointer">Categoría activa</Label>
                    </div>

                    <div className="flex items-center gap-3 pt-2">
                        <Button type="submit" disabled={processing}>
                            {processing && <Spinner />}
                            Crear categoría
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
        { title: 'Categorías', href: index.url() },
        { title: 'Nueva categoría', href: create.url() },
    ],
};
