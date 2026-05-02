const CDMX = 'America/Mexico_City';

export function formatDateOnly(dateStr: string): string {
    if (!dateStr) return '—';
    const [datePart] = dateStr.split(' ')[0].split('T');
    const [y, m, d] = datePart.split('-');
    if (!y || !m || !d) return '—';
    const date = new Date(Number(y), Number(m) - 1, Number(d));
    if (isNaN(date.getTime())) return '—';
    return date.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        timeZone: CDMX,
    });
}

export function formatDateTime(dateStr: string): string {
    if (!dateStr) return '—';
    const [datePart, timePart] = dateStr.split(' ');
    const [y, m, d] = datePart.split('-');
    const [h, i] = timePart ? timePart.split(':') : ['0', '0'];
    const date = new Date(Number(y), Number(m) - 1, Number(d), Number(h), Number(i));
    if (isNaN(date.getTime())) return '—';
    return date.toLocaleString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: CDMX,
    });
}

export function formatTime(timeStr: string): string {
    if (!timeStr) return '—';
    return timeStr.substring(0, 5);
}