export type User = {
    id: number;
    name: string;
    email: string;
    USU_CODIGO?: number | null;
    USU_TIPO?: string | null;
    cpf: string;
    status: 'ATIVO' | 'INATIVO';
    username?: string | null;
    phone?: string | null;
    profile_photo_path?: string | null;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
