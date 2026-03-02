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

export type MarcaPneu = {
    MARP_CODIGO: number;
    MARP_DESCRICAO: string;
    MARP_TIPO: string;
    MARP_STATUS: LegacyStatus;
    USU_CODIGO: number;
    MARP_DATACADASTRO?: string | null;
};

export type MarcaPneuOption = {
    MARP_CODIGO: number;
    MARP_DESCRICAO: string;
    MARP_TIPO: string;
};

export type MarcaPneuPageProps = {
    marcas: MarcaPneu[];
    flash?: LegacyFlash;
};

export type ModeloPneu = {
    MODP_CODIGO: number;
    MODP_DESCRICAO: string;
    MODP_STATUS: LegacyStatus;
    MARP_CODIGO: number;
    MARCA_DESCRICAO: string;
    MARCA_TIPO?: string;
};

export type ModeloPneuPageProps = {
    modelos: ModeloPneu[];
    marcas: MarcaPneuOption[];
    flash?: LegacyFlash;
};

export type MedidaPneu = {
    MEDP_CODIGO: number;
    MEDP_DESCRICAO: string;
    CAL_RECOMENDADA: number | null;
    MEDP_STATUS: LegacyStatus;
    USU_CODIGO: number;
    MEDP_DATACADASTRO?: string | null;
};

export type MedidaPneuOption = {
    MEDP_CODIGO: number;
    MEDP_DESCRICAO: string;
    CAL_RECOMENDADA: number | null;
};

export type MedidaPneuPageProps = {
    medidas: MedidaPneu[];
    flash?: LegacyFlash;
};

export type ModeloPneuOption = {
    MODP_CODIGO: number;
    MODP_DESCRICAO: string;
    MARP_CODIGO: number;
};

export type TipoPneu = {
    TIPO_CODIGO: number;
    TIPO_STATUS: LegacyStatus;
    TIPO_DESCRICAO: string;
    TIPO_INSPECAO: string;
    MARP_CODIGO: number;
    MODP_CODIGO: number;
    MEDP_CODIGO: number;
    TIPO_DESENHO: string;
    TIPO_NSULCO: number;
    TIPO_MMSEGURANCA: number;
    TIPO_MMNOVO: number;
    TIPO_MMDESGEIXOS: number | null;
    TIPO_MMDESGPAR: number | null;
    MARCA_DESCRICAO: string;
    MODELO_DESCRICAO: string;
    MEDIDA_DESCRICAO: string;
    DESENHO_DESCRICAO?: string | null;
};

export type DesenhoBanda = {
    DESB_CODIGO: number;
    DESB_DESCRICAO: string;
    DESB_SIGLA: string;
    DESB_STATUS: LegacyStatus;
    USU_CODIGO: number;
    DESB_DATACADASTRO?: string | null;
};

export type DesenhoBandaOption = {
    DESB_CODIGO: number;
    DESB_DESCRICAO: string;
    DESB_SIGLA: string;
};

export type DesenhoBandaPageProps = {
    desenhos: DesenhoBanda[];
    flash?: LegacyFlash;
};

export type TipoPneuPageProps = {
    tipos: TipoPneu[];
    marcas: MarcaPneuOption[];
    modelos: ModeloPneuOption[];
    medidas: MedidaPneuOption[];
    desenhos: DesenhoBandaOption[];
    flash?: LegacyFlash;
};

export type UnidadeOption = {
    UNI_CODIGO: number;
    UNI_DESCRICAO: string;
};

export type TipoPneuCadastroOption = {
    TIPO_CODIGO: number;
    MARP_CODIGO: number;
    TIPO_DESCRICAO: string;
    TIPO_MMNOVO?: number;
    CAL_RECOMENDADA?: number | null;
    MARCA_DESCRICAO?: string;
    MODELO_DESCRICAO?: string;
    MEDIDA_DESCRICAO?: string;
};

export type PneuRapidoPageProps = {
    unidades: UnidadeOption[];
    marcasCarcaca: MarcaPneuOption[];
    marcasRecapagem: MarcaPneuOption[];
    tiposCarcaca: TipoPneuCadastroOption[];
    tiposRecapagem: TipoPneuCadastroOption[];
    flash?: LegacyFlash;
};

export type PneuConsultaDetalhes = {
    PNE_CODIGO: number;
    PNE_FOGO: string;
    UNI_CODIGO: number;
    UNI_DESCRICAO: string;
    TIPO_CODIGO: number;
    SKU_CARCACA: string;
    SKU_RECAPE: string | null;
    PNE_STATUS: string;
    PNE_STATUSCOMPRA: string;
    PNE_VIDACOMPRA: string;
    PNE_VIDAATUAL: string;
    PNE_DOT: string | null;
    PNE_VALORCOMPRA: number;
    PNE_CUSTOATUAL: number;
    PNE_MM: number;
    PNE_KM: number;
    TIPO_MMSEGURANCA: number | null;
    TIPO_MMNOVO: number | null;
};

export type PneuConsultaMovimentacao = {
    MOV_CODIGO: number;
    MOV_DATA: string | null;
    MOV_OPERACAO: string;
    MOV_KMPNEU: number | null;
    MOV_KMVEICULO: number | null;
    MOV_MM_MINIMA: number | null;
    MOV_COMENTARIO: string | null;
    POS_CODIGO: number | null;
    VEI_CODIGO: number | null;
};

export type PneuConsultaVida = {
    VIPN_CODIGO: number | null;
    VIDA: string;
    TIPO_CODIGO: number | null;
    SKU: string | null;
    KM: number | null;
    MM: number | null;
    CUSTO: number | null;
    DATA_EVENTO: string | null;
};

export type PneuConsultaAlerta = {
    tipo: 'info' | 'warning' | 'critical';
    titulo: string;
    mensagem: string;
};

export type PneuConsultaPageProps = {
    filtroFogo: string;
    pneu: PneuConsultaDetalhes | null;
    movimentacoes: PneuConsultaMovimentacao[];
    alertas: PneuConsultaAlerta[];
    vidas: PneuConsultaVida[];
    flash?: LegacyFlash;
};

export type StatusPneuOption = {
    STP_SIGLA: string;
    STP_DESCRICAO: string;
};

export type PneuRelatorioRow = {
    PNE_CODIGO: number;
    PNE_FOGO: string;
    UNI_DESCRICAO: string;
    MARCA_CARCACA: string;
    MODELO_CARCACA: string;
    MEDIDA: string;
    SKU_CARCACA: string;
    PNE_STATUS: string;
    STATUS_DESCRICAO: string;
    PNE_STATUSCOMPRA: 'N' | 'U';
    PNE_VIDACOMPRA: string;
    PNE_VIDAATUAL: string;
    PNE_DOT: string | null;
    PNE_VALORCOMPRA: number;
    PNE_CUSTOATUAL: number;
    PNE_MM: number;
    PNE_KM: number;
    MARCA_RECAPAGEM: string | null;
    SKU_RECAPAGEM: string | null;
    TIPO_MMNOVO: number | null;
    TIPO_MMSEGURANCA: number | null;
};

export type UnidadeSimples = {
    UNI_CODIGO: number;
    UNI_DESCRICAO: string;
};

export type MarcaSimples = {
    MARP_CODIGO: number;
    MARP_DESCRICAO: string;
};

export type PneuRelatorioFiltros = {
    unidade?: string;
    status?: string;
    vida?: string;
    marca?: string;
    fogo?: string;
};

export type PneuRelatorioPageProps = {
    pneus: PneuRelatorioRow[];
    filtros: PneuRelatorioFiltros;
    unidades: UnidadeSimples[];
    marcas: MarcaSimples[];
    statuses: StatusPneuOption[];
    flash?: LegacyFlash;
};

export type MarcaVeiculo = {
    MARV_CODIGO: number;
    MARV_DESCRICAO: string;
    MARV_STATUS: LegacyStatus;
    USU_CODIGO: number;
    MARV_DATACADASTRO?: string | null;
};

export type MarcaVeiculoOption = {
    MARV_CODIGO: number;
    MARV_DESCRICAO: string;
};

export type MarcaVeiculoPageProps = {
    marcas: MarcaVeiculo[];
    flash?: LegacyFlash;
};

export type TipoVeiculo = {
    TPVE_SIGLA: string;
    TPVE_DESCRICAO: string;
    TPVE_STATUS: LegacyStatus;
    TPVE_PADRAO: boolean;
    TPVE_ORDEM: number;
};

export type TipoVeiculoOption = {
    TPVE_SIGLA: string;
    TPVE_DESCRICAO: string;
};

export type TipoVeiculoPageProps = {
    tipos: TipoVeiculo[];
    flash?: LegacyFlash;
};

export type ModeloVeiculo = {
    MODV_CODIGO: number;
    MODV_DESCRICAO: string;
    MODV_STATUS: LegacyStatus;
    MARV_CODIGO: number;
    VEIC_TIPO: string;
    MARCA_DESCRICAO: string;
    TIPO_DESCRICAO: string;
};

export type ModeloVeiculoPageProps = {
    modelos: ModeloVeiculo[];
    marcas: MarcaVeiculoOption[];
    tipos: TipoVeiculoOption[];
    flash?: LegacyFlash;
};

export type Veiculo = {
    VEI_CODIGO: number;
    VEI_PLACA: string;
    VEI_CHASSI: string | null;
    VEI_FROTA: string | null;
    VEI_STATUS: LegacyStatus;
    CAL_RECOMENDADA: number | null;
    MODV_CODIGO: number;
    MARV_CODIGO: number;
    UNI_CODIGO: number;
    VEIC_CODIGO: number;
    VEI_KM: number;
    VEI_OBS: string | null;
    VEI_ODOMETRO: 'S' | 'N';
    MODELO_DESCRICAO: string;
    MARCA_DESCRICAO: string;
    UNI_DESCRICAO: string;
    CONFIGURACAO_DESCRICAO: string;
    VEIC_TIPO: string;
};

export type VeiculoPageProps = {
    veiculos: Veiculo[];
    unidades: UnidadeOption[];
    marcas: MarcaVeiculoOption[];
    tipos: TipoVeiculoOption[];
    flash?: LegacyFlash;
};

export type Usuario = {
    id: number;
    name: string;
    email: string;
    username: string | null;
    cpf: string | null;
    phone: string | null;
    USU_TIPO: string;
    status: 'ATIVO' | 'INATIVO';
};

export type UsuarioPageProps = {
    usuarios: Usuario[];
    flash?: LegacyFlash;
};
