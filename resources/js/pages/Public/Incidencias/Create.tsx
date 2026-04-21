import { useEffect, useRef, useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
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
import { store, buscarEmpleado } from '@/routes/incidencias';

type Area = { id: number; nombre: string };
type EmpleadoSugerido = { reportante_nombre: string; email_reportante: string | null };

type Props = { areas: Area[] };

const TIPOS_INCIDENCIA = [
    { value: 'retardo',           label: 'Retardo',           descripcion: 'Menor a 30 min, máximo 2 a la quincena', requiereMinutos: true },
    { value: 'permiso_economico', label: 'Permiso Económico', descripcion: '',    requiereMinutos: false },
    { value: 'comision_oficial',  label: 'Comisión Oficial',  descripcion: '',    requiereMinutos: false },
    { value: 'salida_anticipada', label: 'Salida Anticipada', descripcion: 'Exclusivo personal PAAE', requiereMinutos: false },
];

export default function Create({ areas }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        numero_empleado:   '',
        reportante_nombre: '',
        email_reportante:  '',
        tipo_solicitante:  '',
        area_id:           '',
        fecha_incidencia:  '',
        tipo_incidencia:   '',
        minutos_retardo:   '',
        descripcion:       '',
        archivos:          [] as File[],
    });

    const [buscando, setBuscando]           = useState(false);
    const [sugeridos, setSugeridos]         = useState<EmpleadoSugerido[]>([]);
    const [nombreManual, setNombreManual]   = useState(false);
    const [busquedaHecha, setBusquedaHecha] = useState(false);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    useEffect(() => {
        const numero = data.numero_empleado.trim();

        if (numero.length < 3) {
            setSugeridos([]);
            setBusquedaHecha(false);
            setNombreManual(false);
            return;
        }

        if (debounceRef.current) clearTimeout(debounceRef.current);

        debounceRef.current = setTimeout(async () => {
            setBuscando(true);
            try {
                const res = await fetch(buscarEmpleado.url({ numero }));
                const json: EmpleadoSugerido[] = await res.json();
                setSugeridos(json);
                setBusquedaHecha(true);
                if (json.length === 0) {
                    setNombreManual(true);
                    setData((prev) => ({ ...prev, reportante_nombre: '', email_reportante: '' }));
                } else {
                    setNombreManual(false);
                }
            } catch {
                setBusquedaHecha(true);
                setNombreManual(true);
            } finally {
                setBuscando(false);
            }
        }, 500);

        return () => { if (debounceRef.current) clearTimeout(debounceRef.current); };
    }, [data.numero_empleado]);

    function seleccionarSugerido(sugerido: EmpleadoSugerido) {
        setData((prev) => ({
            ...prev,
            reportante_nombre: sugerido.reportante_nombre,
            email_reportante:  sugerido.email_reportante ?? '',
        }));
        setSugeridos([]);
    }

    function activarNombreManual() {
        setNombreManual(true);
        setSugeridos([]);
        setData((prev) => ({ ...prev, reportante_nombre: '', email_reportante: '' }));
    }

    const tipoSeleccionado = TIPOS_INCIDENCIA.find((t) => t.value === data.tipo_incidencia);

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(store.url(), { forceFormData: true });
    }

    return (
        <>
            <Head title="Registrar incidencia" />

            <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 md:p-8">
                <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">Registrar incidencia</h2>
                    <p className="text-sm text-gray-500 mt-1">
                        Completa el formulario con los datos de la incidencia. Recibirás un número de folio para dar seguimiento.
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-5" encType="multipart/form-data">

                    {/* Número de empleado */}
                    <div className="grid md:grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="numero_empleado">Número de empleado <span className="text-red-500">*</span></Label>
                            <div className="relative">
                                <Input
                                    id="numero_empleado"
                                    name="numero_empleado"
                                    value={data.numero_empleado}
                                    onChange={(e) => setData('numero_empleado', e.target.value)}
                                    placeholder="Ej. 12345"
                                    required
                                    autoComplete="off"
                                />
                                {buscando && (
                                    <span className="absolute right-3 top-1/2 -translate-y-1/2">
                                        <Spinner />
                                    </span>
                                )}
                            </div>
                            <InputError message={errors.numero_empleado} />
                        </div>

                        {/* Nombre — sugerencias o input manual */}
                        <div className="grid gap-2">
                            <Label htmlFor="reportante_nombre">Nombre completo <span className="text-red-500">*</span></Label>

                            {/* Lista de sugeridos */}
                            {sugeridos.length > 0 && !nombreManual && (
                                <div className="border border-gray-200 rounded-md divide-y divide-gray-100 bg-white shadow-sm">
                                    {sugeridos.map((s, i) => (
                                        <button
                                            key={i}
                                            type="button"
                                            onClick={() => seleccionarSugerido(s)}
                                            className="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 transition-colors"
                                        >
                                            <span className="font-medium text-gray-900">{s.reportante_nombre}</span>
                                            {s.email_reportante && (
                                                <span className="text-gray-400 ml-2 text-xs">{s.email_reportante}</span>
                                            )}
                                        </button>
                                    ))}
                                    <button
                                        type="button"
                                        onClick={activarNombreManual}
                                        className="w-full text-left px-3 py-2 text-xs text-primary hover:bg-gray-50 transition-colors"
                                    >
                                        + Ingresar nombre manualmente
                                    </button>
                                </div>
                            )}

                            {/* Nombre ya seleccionado (chip) */}
                            {data.reportante_nombre && sugeridos.length === 0 && !nombreManual && (
                                <div className="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-md text-sm">
                                    <span className="flex-1 text-gray-900">{data.reportante_nombre}</span>
                                    <button
                                        type="button"
                                        onClick={activarNombreManual}
                                        className="text-xs text-gray-400 hover:text-gray-600"
                                    >
                                        Cambiar
                                    </button>
                                </div>
                            )}

                            {/* Input manual */}
                            {(nombreManual || (busquedaHecha && sugeridos.length === 0 && !data.reportante_nombre)) && (
                                <Input
                                    id="reportante_nombre"
                                    name="reportante_nombre"
                                    value={data.reportante_nombre}
                                    onChange={(e) => setData('reportante_nombre', e.target.value)}
                                    placeholder="Escribe el nombre completo"
                                    required
                                />
                            )}

                            {/* Input oculto cuando se seleccionó de sugeridos */}
                            {data.reportante_nombre && !nombreManual && sugeridos.length === 0 && (
                                <input type="hidden" name="reportante_nombre" value={data.reportante_nombre} />
                            )}

                            {!nombreManual && !busquedaHecha && data.numero_empleado.length < 3 && (
                                <p className="text-xs text-gray-400">Ingresa el número de empleado para buscar el nombre.</p>
                            )}
                            <InputError message={errors.reportante_nombre} />
                        </div>
                    </div>

                    {/* Email */}
                    <div className="grid gap-2">
                        <Label htmlFor="email_reportante">
                            Correo electrónico <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="email_reportante"
                            name="email_reportante"
                            type="email"
                            value={data.email_reportante}
                            onChange={(e) => setData('email_reportante', e.target.value)}
                            placeholder="correo@ejemplo.com"
                            required
                        />
                        <p className="text-xs text-gray-500">Se usará para notificarte sobre el estado de tu incidencia.</p>
                        <InputError message={errors.email_reportante} />
                    </div>

                    <div className="grid md:grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label>Tipo de solicitante <span className="text-red-500">*</span></Label>
                            <Select value={data.tipo_solicitante} onValueChange={(v) => setData('tipo_solicitante', v)} required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecciona..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="docente">Docente</SelectItem>
                                    <SelectItem value="administrativo">Administrativo</SelectItem>
                                </SelectContent>
                            </Select>
                            <input type="hidden" name="tipo_solicitante" value={data.tipo_solicitante} />
                            <InputError message={errors.tipo_solicitante} />
                        </div>

                        <div className="grid gap-2">
                            <Label>Área de adscripción <span className="text-red-500">*</span></Label>
                            <Select value={data.area_id} onValueChange={(v) => setData('area_id', v)} required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecciona el área" />
                                </SelectTrigger>
                                <SelectContent>
                                    {areas.map((area) => (
                                        <SelectItem key={area.id} value={String(area.id)}>
                                            {area.nombre}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <input type="hidden" name="area_id" value={data.area_id} />
                            <InputError message={errors.area_id} />
                        </div>
                    </div>

                    {/* Datos de la incidencia */}
                    <div className="border-t border-gray-100 pt-5">
                        <h3 className="text-sm font-medium text-gray-700 mb-4">Datos de la incidencia</h3>

                        <div className="grid md:grid-cols-2 gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="fecha_incidencia">Fecha de la incidencia <span className="text-red-500">*</span></Label>
                                <Input
                                    id="fecha_incidencia"
                                    name="fecha_incidencia"
                                    type="date"
                                    value={data.fecha_incidencia}
                                    max={new Date().toISOString().split('T')[0]}
                                    onChange={(e) => setData('fecha_incidencia', e.target.value)}
                                    required
                                />
                                <InputError message={errors.fecha_incidencia} />
                            </div>

                            <div className="grid gap-2">
                                <Label>Tipo de incidencia <span className="text-red-500">*</span></Label>
                                <Select
                                    value={data.tipo_incidencia}
                                    onValueChange={(v) => {
                                        setData((prev) => ({ ...prev, tipo_incidencia: v, minutos_retardo: '' }));
                                    }}
                                    required
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona el tipo" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {TIPOS_INCIDENCIA.map((t) => (
                                            <SelectItem key={t.value} value={t.value}>
                                                <span>{t.label}</span>
                                                {t.descripcion && (
                                                    <span className="text-xs text-gray-400 ml-1">— {t.descripcion}</span>
                                                )}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <input type="hidden" name="tipo_incidencia" value={data.tipo_incidencia} />
                                <InputError message={errors.tipo_incidencia} />
                            </div>
                        </div>

                        {tipoSeleccionado?.requiereMinutos && (
                            <div className="grid gap-2 mt-4 md:w-1/2">
                                <Label htmlFor="minutos_retardo">Minutos de retardo <span className="text-red-500">*</span></Label>
                                <Input
                                    id="minutos_retardo"
                                    name="minutos_retardo"
                                    type="number"
                                    min={1}
                                    max={29}
                                    value={data.minutos_retardo}
                                    onChange={(e) => setData('minutos_retardo', e.target.value)}
                                    placeholder="Ej. 15"
                                    required
                                />
                                <p className="text-xs text-gray-500">Debe ser menor a 30 minutos.</p>
                                <InputError message={errors.minutos_retardo} />
                            </div>
                        )}
                    </div>

                    {/* Notas opcionales */}
                    <div className="grid gap-2">
                        <Label htmlFor="descripcion">Notas adicionales (opcional)</Label>
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            value={data.descripcion}
                            onChange={(e) => setData('descripcion', e.target.value)}
                            rows={3}
                            placeholder="Agrega cualquier información relevante..."
                            className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 resize-none"
                        />
                        <InputError message={errors.descripcion} />
                    </div>

                    {/* Archivos */}
                    <div className="grid gap-2">
                        <Label htmlFor="archivos">Documentos adjuntos (opcional)</Label>
                        <input
                            id="archivos"
                            name="archivos[]"
                            type="file"
                            multiple
                            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
                            onChange={(e) => setData('archivos', Array.from(e.target.files ?? []))}
                            className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        />
                        <p className="text-xs text-gray-500">Imágenes, PDF o documentos de Office. Máximo 5 archivos, 10 MB cada uno.</p>
                        <InputError message={errors.archivos} />
                    </div>

                    <Button type="submit" className="w-full" disabled={processing}>
                        {processing && <Spinner />}
                        Enviar incidencia
                    </Button>
                </form>
            </div>
        </>
    );
}
