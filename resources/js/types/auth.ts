export type User = {
    id: number;
    nombre: string;
    email: string;
    rol: 'jefe_inmediato' | 'capital_humano' | 'subdirector' | null;
    area: { id: number } | null;
};

export type Auth = {
    user: User | null;
};
