import { Head, useForm, router } from '@inertiajs/react';
import { useRef, useState } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

const ROLES = [
    { value: 'jefe_inmediato', label: 'Jefe de Área', icon: '👤' },
    { value: 'capital_humano', label: 'Capital Humano', icon: '💼' },
    { value: 'subdirector', label: 'Subdirección Administrativa', icon: '📋' },
];

type Area = { id: number; nombre: string; slug: string };

export default function Login({ status, areas }: { status?: string; areas?: Area[] }) {
    const { data, setData, processing, errors } = useForm({
        rol: 'jefe_inmediato' as string,
        password: '',
        remember: false as boolean,
    });

    const [selectedArea, setSelectedArea] = useState<number | null>(null);
    const selectedAreaRef = useRef<number | null>(null);

    const showAreaSelector = data.rol === 'jefe_inmediato' && areas && areas.length > 0;

    function handleAreaClick(areaId: number) {
        setSelectedArea(areaId);
        selectedAreaRef.current = areaId;
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        console.log('Submitting with data:', {
            rol: data.rol,
            area_id: data.rol === 'jefe_inmediato' ? selectedAreaRef.current : null,
            password: data.password,
            remember: data.remember,
        });
        router.post('/login', {
            rol: data.rol,
            area_id: data.rol === 'jefe_inmediato' ? selectedAreaRef.current : null,
            password: data.password,
            remember: data.remember,
        });
    }

    return (
        <>
            <Head title="Iniciar sesión" />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <form onSubmit={handleSubmit} className="flex flex-col gap-6">
                <div className="space-y-3">
                    <Label>Selecciona tu rol</Label>
                    <div className="grid grid-cols-3 gap-3">
                        {ROLES.map((r) => (
                            <button
                                key={r.value}
                                type="button"
                                onClick={() => setData('rol', r.value)}
                                className={`flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all text-sm font-medium ${
                                    data.rol === r.value
                                        ? 'border-primary bg-primary/5 text-primary'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'
                                }`}
                            >
                                <span className="text-2xl">{r.icon}</span>
                                <span className="text-center leading-tight">{r.label}</span>
                            </button>
                        ))}
                    </div>
                    <InputError message={errors.rol} />
                </div>

                {showAreaSelector && (
                    <div className="space-y-3">
                        <Label>Selecciona tu área</Label>
                        <div className="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto">
                            {areas!.map((area) => (
                                <button
                                    key={area.id}
                                    type="button"
                                    onClick={() => {
                                        handleAreaClick(area.id);
                                    }}
                                    className={`p-2.5 rounded-lg border-2 text-sm text-left transition-all ${
                                        selectedArea === area.id
                                            ? 'border-primary bg-primary/5 text-primary'
                                            : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'
                                    }`}
                                >
                                    {area.nombre}
                                </button>
                            ))}
                        </div>
                        <InputError message={(errors as Record<string, string>).area_id} />
                    </div>
                )}

                <div className="grid gap-2">
                    <Label htmlFor="password">Contraseña</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        required
                        autoComplete="current-password"
                        placeholder="Contraseña"
                    />
                    <InputError message={errors.password} />
                </div>

                <div className="flex items-center space-x-3">
                    <Checkbox
                        id="remember"
                        checked={data.remember}
                        onCheckedChange={(checked) => setData('remember', !!checked)}
                    />
                    <Label htmlFor="remember">Recordarme</Label>
                </div>

                <Button type="submit" className="w-full" disabled={processing}>
                    {processing && <Spinner />}
                    Iniciar sesión
                </Button>
            </form>
        </>
    );
}

Login.layout = {
    title: 'CICS UST - Incidencias',
    description: 'Sistema de Gestión de Incidencias',
};