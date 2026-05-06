import { Head, useForm, usePage, router } from '@inertiajs/react';
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
    const { props } = usePage();
    const errors = (props.errors as Record<string, string>) || {};
    const { data, setData, processing } = useForm({
        rol: 'subdirector' as string,
        area_id: null as number | null,
        password: '',
        remember: false as boolean,
    });

    const showAreaSelector = data.rol === 'jefe_inmediato';

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();

        const payload = {
            rol: data.rol,
            password: data.password,
            remember: data.remember,
            area_id: data.area_id
        };

        if (data.rol === 'jefe_inmediato' && data.area_id) {
            payload.area_id = data.area_id;
        }

        router.post('/login', payload);
    }

    return (
        <>
            <Head title="Iniciar sesión" />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                    {status}
                </div>
            )}

            {errors && Object.keys(errors).length > 0 && (
                <div className="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                    <p className="font-medium mb-1">Error de autenticación</p>
                    <ul className="list-disc list-inside space-y-0.5">
                        {Object.values(errors).map((msg, i) => (
                            <li key={i}>{msg}</li>
                        ))}
                    </ul>
                </div>
            )}

            <form onSubmit={handleSubmit} className="flex flex-col gap-6">
                <div className="space-y-3">
                    <Label htmlFor="rol"> Selecciona tu rol</Label>
                    <div className="grid grid-cols-3 gap-3" role="group" aria-label="Selecciona tu rol">
                        {ROLES.map((r) => (
                            <button
                                key={r.value}
                                type="button"
                                onClick={() => {
                                    setData('rol', r.value);
                                    setData('area_id', null);
                                    setData('password', '');
                                }}
                                className={`flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all text-sm font-medium ${
                                    data.rol === r.value
                                        ? 'border-primary bg-primary/5 text-primary'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'
                                }`}
                            >
                                <span className="text-2xl" aria-hidden="true">{r.icon}</span>
                                <span className="text-center leading-tight">{r.label}</span>
                            </button>
                        ))}
                    </div>
                    <InputError message={(errors as Record<string, string>).rol} />
                </div>

                {showAreaSelector && (
                    <div className="space-y-3">
                        <Label htmlFor="area_id">Selecciona tu área</Label>
                        {areas && areas.length > 0 ? (
                            <div className="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto">
                                {areas!.map((area) => (
                                    <button
                                        key={area.id}
                                        type="button"
                                        onClick={() => setData('area_id', area.id)}
                                        className={`p-2.5 rounded-lg border-2 text-sm text-left transition-all ${
                                            data.area_id === area.id
                                                ? 'border-primary bg-primary/5 text-primary'
                                                : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'
                                        }`}
                                    >
                                        {area.nombre}
                                    </button>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-yellow-600 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                No hay áreas disponibles con jefe asignado.
                            </p>
                        )}
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