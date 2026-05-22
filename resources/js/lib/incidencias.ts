export const ESTADO_LABELS: Record<string, string> = {
    pendiente_jefe: 'Pendiente — Jefe de Área',
    pendiente_capital_humano: 'Pendiente — Capital Humano',
    pendiente_sindicato: 'Pendiente — Sindicato',
    pendiente_subdireccion: 'Pendiente — Subdirección',
    aprobada: 'Aprobada',
    rechazada: 'Rechazada',
};

export const ESTADO_COLORS: Record<string, string> = {
    pendiente_jefe: 'bg-yellow-100 text-yellow-800',
    pendiente_capital_humano: 'bg-orange-100 text-orange-800',
    pendiente_sindicato: 'bg-purple-100 text-purple-800',
    pendiente_subdireccion: 'bg-blue-100 text-blue-800',
    aprobada: 'bg-green-100 text-green-800',
    rechazada: 'bg-red-100 text-red-800',
};

export const ESTADO_CHART_COLORS: Record<string, string> = {
    pendiente_jefe: 'bg-yellow-400',
    pendiente_capital_humano: 'bg-orange-400',
    pendiente_sindicato: 'bg-purple-500',
    pendiente_subdireccion: 'bg-sky-500',
    aprobada: 'bg-emerald-500',
    rechazada: 'bg-red-500',
};

export const TIPO_LABELS: Record<string, string> = {
    retardo: 'Retardo',
    permiso_economico: 'Permiso Económico',
    comision_oficial: 'Comisión Oficial',
    salida_anticipada: 'Salida Anticipada',
    permiso_sindical: 'Permiso Sindical',
    incidencia_medica: 'Incidencia Médica',
    buena_conducta: 'Incidencia de Buena Conducta',
};

export const TIPO_COLORS: Record<string, string> = {
    retardo: 'bg-amber-400',
    permiso_economico: 'bg-blue-400',
    comision_oficial: 'bg-violet-400',
    salida_anticipada: 'bg-rose-400',
    permiso_sindical: 'bg-green-400',
    incidencia_medica: 'bg-yellow-400',
    buena_conducta: 'bg-sky-400',
};

export const TIPO_SOLICITANTE_LABELS: Record<string, string> = {
    docente: 'Docente',
    administrativo: 'Administrativo',
};

export const SOLICITANTE_COLORS: Record<string, string> = {
    docente: 'bg-teal-500',
    administrativo: 'bg-indigo-400',
};

export const SOLICITANTE_BG: Record<string, string> = {
    docente: 'bg-teal-50 text-teal-700',
    administrativo: 'bg-indigo-50 text-indigo-700',
};

export const ACCION_LABELS: Record<string, string> = {
    creada: 'Registrada',
    aprobada: 'Aprobada',
    rechazada: 'Rechazada',
    asignada: 'Asignada',
    comentario: 'Comentario',
    archivo_adjunto: 'Archivo adjunto',
};
