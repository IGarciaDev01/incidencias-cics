import { Head, usePage } from '@inertiajs/react';
import { login } from '@/routes';
import { dashboard } from '@/routes/panel';

export default function Welcome() {
    const { auth } = usePage().props;

    if (auth.user) {
        window.location.href = dashboard.url();

        return null;
    }

    window.location.href = login.url();

    return (
        <Head title="Redirigiendo..." />
    );
}
