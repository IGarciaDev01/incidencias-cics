import { Head, useForm, usePage } from '@inertiajs/react';
import { useRef, useState } from 'react';
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
import { TIPO_LABELS } from '@/lib/incidencias';
import { store, buscarEmpleado } from '@/routes/incidencias';

type Area = { id: number; nombre: string };

type Props = { areas: Area[] };

const TIPOS_INCIDENCIA = [
    { value: 'retardo',           descripcion: 'De 11 a 30 min, máximo 2 a la quincena', requiereMinutos: true },
    { value: 'permiso_economico', descripcion: 'Máx 3 al mes, 12 al año',               requiereMinutos: false },
    { value: 'comision_oficial',  descripcion: '',                                    requiereMinutos: false },
    { value: 'salida_anticipada', descripcion: '',                                    requiereMinutos: false },
    { value: 'permiso_sindical',  descripcion: '',                                    requiereMinutos: false },
    { value: 'incidencia_medica', descripcion: '',                                    requiereMinutos: false },
    { value: 'buena_conducta',    descripcion: '',                                    requiereMinutos: false },
];

export default function Create({ areas }: Props) {
    const { flash } = usePage().props as { flash?: { error?: string } };
    const { data, setData, post, processing, errors } = useForm({
        numero_empleado:   '',
        reportante_nombre: '',
        email_reportante:  '',
        tipo_empleado:     '',
        area_id:           '',
        fecha_incidencia:  '',
        hora_incidencia:   '',
        tipo_incidencia:   '',
        minutos_retardo:   '',
        descripcion:       '',
        archivos:          [] as File[],
    });

    const [validando, setValidando] = useState(false);
    const [empleadoValido, setEmpleadoValido] = useState(false);
    const [empleadoNoEncontrado, setEmpleadoNoEncontrado] = useState(false);
    const [empleadoIncompleto, setEmpleadoIncompleto] = useState(false);
    const timeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    function handleNumeroChange(value: string) {
        setData('numero_empleado', value);
        setEmpleadoValido(false);
        setEmpleadoNoEncontrado(false);
        setEmpleadoIncompleto(false);

        if (value.trim().length >= 3) {
            if (timeoutRef.current) {
                clearTimeout(timeoutRef.current);
            }

            timeoutRef.current = setTimeout(() => buscarEmpleadoExacto(value.trim()), 500);
        }
    }

    async function buscarEmpleadoExacto(numero: string) {
        setValidando(true);

        try {
            const res = await fetch(
                buscarEmpleado.url({ query: { numero } }),
                { headers: { Accept: 'application/json' } },
            );

            if (!res.ok) {
                return;
            }

            const json: { numero_empleado: string; reportante_nombre: string; email_reportante: string | null; tipo_empleado: string | null }[] = await res.json();
            const exacto = json.find((e) => e.numero_empleado === numero);

            if (exacto) {
                const datosCompletos = Boolean(exacto.email_reportante && exacto.tipo_empleado);

                setData((prev) => ({
                    ...prev,
                    numero_empleado: exacto.numero_empleado,
                    reportante_nombre: exacto.reportante_nombre,
                    email_reportante: exacto.email_reportante ?? '',
                    tipo_empleado: exacto.tipo_empleado ?? '',
                }));
                setEmpleadoValido(datosCompletos);
                setEmpleadoNoEncontrado(false);
                setEmpleadoIncompleto(!datosCompletos);
            } else {
                setData((prev) => ({
                    ...prev,
                    reportante_nombre: '',
                    email_reportante: '',
                    tipo_empleado: '',
                }));
                setEmpleadoValido(false);
                setEmpleadoNoEncontrado(true);
                setEmpleadoIncompleto(false);
            }
        } catch {
            setEmpleadoNoEncontrado(true);
            setEmpleadoValido(false);
            setEmpleadoIncompleto(false);
        } finally {
            setValidando(false);
        }
    }

    const tipoSeleccionado = TIPOS_INCIDENCIA.find((t) => t.value === data.tipo_incidencia);

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(store.url(), { forceFormData: true });
    }

    return (
        <>
            <Head title="Registrar Incidencia" />

            <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6 md:p-8">
                <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">Registrar Incidencia</h2>
                    <p className="text-sm text-gray-500 mt-1">
                        Ingresa tu número de empleado para autocompletar tus datos y registrar tu incidencia.
                    </p>
                </div>

                {flash?.error && (
                    <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                        {flash.error}
                    </div>
                )}

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
                                    onChange={(e) => handleNumeroChange(e.target.value)}
                                    placeholder="123457"
                                    required
                                    autoComplete="off"
                                />
                                {validando && (
                                    <span className="absolute right-3 top-1/2 -translate-y-1/2">
                                        <Spinner />
                                    </span>
                                )}
                            </div>
                            <InputError message={errors.numero_empleado} />
                        </div>

                        {/* Nombre */}
                        <div className="grid gap-2">
                            <Label htmlFor="reportante_nombre">Nombre completo <span className="text-red-500">*</span></Label>

                            {empleadoNoEncontrado && (
                                <div className="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                                    Empleado no registrado. Contacta a Subdirección Administrativa para registrarte.
                                </div>
                            )}

                            {empleadoIncompleto && (
                                <div className="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
                                    Tu expediente está incompleto. Contacta a Capital Humano para registrar tu correo y tipo de empleado.
                                </div>
                            )}

                            {data.reportante_nombre && empleadoValido && (
                                <div className="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-md text-sm">
                                    <span className="flex-1 text-gray-900">{data.reportante_nombre}</span>
                                </div>
                            )}

                            {!empleadoValido && !empleadoNoEncontrado && data.numero_empleado.length < 3 && (
                                <p className="text-xs text-gray-400">Ingresa el número de empleado para buscar tus datos.</p>
                            )}
                            <InputError message={errors.reportante_nombre} />
                        </div>
                    </div>

                    {/* Email */}
                    <div className="grid gap-2">
                        <Label htmlFor="email_reportante">
                            Correo electrónico <span className="text-red-500">*</span>
                        </Label>

                        {data.email_reportante && empleadoValido ? (
                            <div className="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-md text-sm">
                                <span className="flex-1 text-gray-900">{data.email_reportante}</span>
                            </div>
                        ) : (
                            <Input
                                id="email_reportante"
                                name="email_reportante"
                                type="email"
                                value={data.email_reportante}
                                onChange={(e) => setData('email_reportante', e.target.value)}
                                placeholder="correo@ejemplo.com"
                                required
                                disabled
                                className="bg-gray-100"
                            />
                        )}

                        <p className="text-xs text-gray-500">Se usará para notificarte sobre el estado de tu incidencia.</p>
                        <InputError message={errors.email_reportante} />
                    </div>

                    <div className="grid md:grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label>Tipo de empleado <span className="text-red-500">*</span></Label>

                            {data.tipo_empleado && empleadoValido ? (
                                <div className="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-md text-sm capitalize">
                                    <span className="flex-1 text-gray-900">{data.tipo_empleado}</span>
                                </div>
                            ) : (
                                <Select value={data.tipo_empleado} onValueChange={(v) => setData('tipo_empleado', v)} disabled required>
                                    <SelectTrigger className="bg-gray-100">
                                        <SelectValue placeholder="Selecciona..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="docente">Docente</SelectItem>
                                        <SelectItem value="administrativo">Administrativo</SelectItem>
                                    </SelectContent>
                                </Select>
                            )}
                            <input type="hidden" name="tipo_empleado" value={data.tipo_empleado} />
                            <InputError message={errors.tipo_empleado} />
                        </div>

                        <div className="grid gap-2">
                            <Label>Área de adscripción de la Incidencia <span className="text-red-500">*</span></Label>
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
                                    <Label htmlFor="hora_incidencia">Hora de la incidencia (opcional)</Label>
                                    <Input
                                        id="hora_incidencia"
                                        name="hora_incidencia"
                                        type="time"
                                        value={data.hora_incidencia}
                                        onChange={(e) => setData('hora_incidencia', e.target.value)}
                                    />
                                    <p className="text-xs text-gray-500">Si no se especifica, solo se registrará la fecha.</p>
                                    <InputError message={errors.hora_incidencia} />
                                </div>
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
                                                <span>{TIPO_LABELS[t.value] ?? t.value}</span>
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
                                    min={11}
                                    max={30}
                                    value={data.minutos_retardo}
                                    onChange={(e) => setData('minutos_retardo', e.target.value)}
                                    placeholder="Ej. 15"
                                    required
                                />
                                <p className="text-xs text-gray-500">Debe ser entre 11 y 30 minutos.</p>
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

                    <Button type="submit" className="w-full" disabled={processing || !empleadoValido}>
                        {processing && <Spinner />}
                        {empleadoValido ? 'Enviar incidencia' : 'Ingresa un número de empleado válido y completo'}
                    </Button>
                </form>
            </div>
        </>
    );
}
