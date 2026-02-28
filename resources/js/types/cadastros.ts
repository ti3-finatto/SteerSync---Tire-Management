export type LegacyStatus = 'A' | 'I';

export type LegacyFlash = {
    success?: string | null;
    error?: string | null;
};

export type Fornecedor = {
    FORN_CODIGO: number;
    FORN_CNPJ: string;
    FORN_RAZAO: string;
    FORN_TELEFONE: string | null;
    FORN_EMAIL: string | null;
    FORN_STATUS: LegacyStatus;
    USU_CODIGO: number;
    FORN_DATACADASTRO?: string | null;
};

export type FornecedorPageProps = {
    fornecedores: Fornecedor[];
    flash?: LegacyFlash;
};

export type Unidade = {
    UNI_CODIGO: number;
    UNI_DESCRICAO: string;
    UNI_STATUS: LegacyStatus;
    CLI_CNPJ: string;
    CLI_UF: string;
    CLI_CIDADE: string;
    pneus_count?: number;
    veiculos_count?: number;
};

export type UnidadePageProps = {
    unidades: Unidade[];
    flash?: LegacyFlash;
};
