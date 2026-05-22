import { Head, usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { login } from '@/routes';
import { dashboard } from '@/routes/panel';

export default function Welcome() {
    const { auth } = usePage().props;

    useEffect(() => {
        window.location.href = auth.user ? dashboard.url() : login.url();
    }, [auth.user]);

    return (
        <Head title="Redirigiendo..." />
    );
}
