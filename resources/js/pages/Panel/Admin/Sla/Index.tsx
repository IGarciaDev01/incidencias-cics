import { Head, useForm, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Checkbox } from '@/components/ui/checkbox';
import { dashboard } from '@/routes/panel';
import { index, update } from '@/routes/panel/admin/sla';

type SlaConfig = {
    id: number;
    prioridad: string;
    horas_primera_respuesta: number;
    horas_resolucion: number;
    activa: boolean;
};

type Props = {
    configuraciones: SlaConfig[];
    prioridades: string[];
};

const PRIORIDAD_LABELS: Record<string, string> = { alta: 'Alta', media: 'Media', baja: 'Baja' };
const PRIORIDAD_COLORS: Record<string, string> = {
    alta: 'text-red-600',
    media: 'text-yellow-600',
    baja: 'text-green-600',
};

export default function Index({ configuraciones }: Props) {
    const { flash } = usePage().props as { flash?: { success?: string } };

    const { data, setData, put, processing, errors } = useForm({
        sla: configuraciones.map((c) => ({
            prioridad: c.prioridad,
            horas_primera_respuesta: c.horas_primera_respuesta,
            horas_resolucion: c.horas_resolucion,
            activa: c.activa,
        })),
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(update.url());
    }

    function updateConfig(index: number, field: string, value: number | boolean) {
        setData('sla', data.sla.map((item, i) =>
            i === index ? { ...item, [field]: value } : item,
        ));
    }

    return (
        <>
            <Head title="Configuración SLA" />

            <div className="p-4 md:p-6 max-w-2xl space-y-5">
                {flash?.success && (
                    <div className="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                        {flash.success}
                    </div>
                )}

                <div>
                    <h2 className="text-xl font-semibold text-gray-900">Configuración de SLA</h2>
                    <p className="text-sm text-gray-500">
                        Define los tiempos de respuesta y resolución según la prioridad de cada incidencia.
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-4">
                    {data.sla.map((config, i) => (
                        <div key={config.prioridad} className="bg-white rounded-xl border border-gray-200 p-5">
                            <div className="flex items-center justify-between mb-4">
                                <h3 className={`font-semibold text-base ${PRIORIDAD_COLORS[config.prioridad] ?? 'text-gray-900'}`}>
                                    Prioridad {PRIORIDAD_LABELS[config.prioridad] ?? config.prioridad}
                                </h3>
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id={`activa_${i}`}
                                        checked={config.activa}
                                        onCheckedChange={(v) => updateConfig(i, 'activa', !!v)}
                                    />
                                    <Label htmlFor={`activa_${i}`} className="cursor-pointer text-sm">
                                        Activa
                                    </Label>
                                </div>
                            </div>

                            <div className="grid md:grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor={`respuesta_${i}`}>
                                        Horas para primera respuesta
                                    </Label>
                                    <div className="flex items-center gap-2">
                                        <Input
                                            id={`respuesta_${i}`}
                                            type="number"
                                            min={1}
                                            max={720}
                                            value={config.horas_primera_respuesta}
                                            onChange={(e) =>
                                                updateConfig(i, 'horas_primera_respuesta', Number(e.target.value))
                                            }
                                            className="w-24"
                                        />
                                        <span className="text-sm text-gray-500">horas</span>
                                    </div>
                                    <InputError message={(errors as Record<string, string>)[`sla.${i}.horas_primera_respuesta`]} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor={`resolucion_${i}`}>
                                        Horas para resolución
                                    </Label>
                                    <div className="flex items-center gap-2">
                                        <Input
                                            id={`resolucion_${i}`}
                                            type="number"
                                            min={1}
                                            max={2160}
                                            value={config.horas_resolucion}
                                            onChange={(e) =>
                                                updateConfig(i, 'horas_resolucion', Number(e.target.value))
                                            }
                                            className="w-24"
                                        />
                                        <span className="text-sm text-gray-500">horas</span>
                                    </div>
                                    <InputError message={(errors as Record<string, string>)[`sla.${i}.horas_resolucion`]} />
                                </div>
                            </div>
                        </div>
                    ))}

                    <Button type="submit" disabled={processing}>
                        {processing && <Spinner />}
                        Guardar configuración
                    </Button>
                </form>
            </div>
        </>
    );
}

Index.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard.url() },
        { title: 'Configuración SLA', href: index.url() },
    ],
};
