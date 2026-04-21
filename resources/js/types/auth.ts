export type User = {
    id: number;
    nombre: string;
    email: string;
    rol: 'admin' | 'subdirector' | null;
    area: { id: number } | null;
};

export type Auth = {
    user: User | null;
};
