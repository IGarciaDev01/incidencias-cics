const CDMX = 'America/Mexico_City';

export function formatDateOnly(dateStr: string, forcedHour: boolean): string {
    if(!dateStr) {
return '—';
}

    if (forcedHour) {
        const date = new Date(`${dateStr}`);

        return date.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: CDMX,
    });
    } else {
        const [datePart] = dateStr.split(' ')[0].split('T');
        const [y, m, d] = datePart.split('-');

        if (!y || !m || !d) {
return '—';
}

        const date = new Date(`${datePart}T00:00:00`);

        if (isNaN(date.getTime())) {
return '—';
}

        return date.toLocaleDateString('es-MX', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            timeZone: CDMX,
        });
    }
}

export function formatDateTime(dateStr: string): string {
    if (!dateStr) {
return '—';
}

    let date = new Date(dateStr);

    if (isNaN(date.getTime())) {
        const [datePart, timePart] = dateStr.split('T');

        if (!datePart) {
return '—';
}

        const [y, m, d] = datePart.split('-').map(Number);
        const timeParts = timePart ? timePart.split(':') : ['0', '0', '0'];
        const [h = 0, i = 0, s = 0] = timeParts.map((p: string) => Number(p.split('.')[0]));
        date = new Date(y, m - 1, d, h, i, s);
    }

    if (isNaN(date.getTime())) {
return '—';
}

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
    if (!timeStr) {
return '—';
}

    return timeStr.substring(0, 5);
}