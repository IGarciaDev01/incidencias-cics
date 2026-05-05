import type { HTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

export default function InputError({
    message,
    className = '',
    ...props
}: HTMLAttributes<HTMLParagraphElement> & { message?: string }) {
    return message ? (
        <p
            {...props}
            className={cn('text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2', className)}
        >
            {message}
        </p>
    ) : null;
}
