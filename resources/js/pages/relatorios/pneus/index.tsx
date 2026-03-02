import { Head, router } from '@inertiajs/react';
import { Download, FileSpreadsheet, Printer, Search, Settings2, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import PageContainer from '@/components/PageContainer';
import PageHeaderMinimal from '@/components/PageHeaderMinimal';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import type {
    MarcaSimples,
    PneuRelatorioFiltros,
    PneuRelatorioPageProps,
    PneuRelatorioRow,
    StatusPneuOption,
    UnidadeSimples,
} from '@/types';

// ---------------------------------------------------------------------------
// Mapeamento de variante de badge por sigla de status
// ---------------------------------------------------------------------------

type BadgeVariant = 'default' | 'secondary' | 'destructive' | 'outline';

const STATUS_BADGE: Record<string, BadgeVariant> = {
    D:  'secondary',    // Disponível
    M:  'default',      // Montado
    B:  'outline',      // Baixado
    R:  'outline',      // Recapagem Pendente
    C:  'outline',      // Conserto Pendente
    S:  'destructive',  // Sucateamento Pendente
    F:  'outline',      // No Fornecedor
    G:  'secondary',    // Garantia
    T:  'secondary',    // Em Transferência
    PR: 'secondary',    // Em Processo de Retorno
    NL: 'destructive',  // Não Localizado
    DE: 'destructive',  // Divergência de Estoque
};

// ---------------------------------------------------------------------------
// Definição das colunas disponíveis
// ---------------------------------------------------------------------------

type ColKey = keyof PneuRelatorioRow;

type ColDef = {
    key: ColKey;
    label: string;
    defaultVisible: boolean;
    render?: (row: PneuRelatorioRow) => React.ReactNode;
    align?: 'left' | 'right' | 'center';
};

const COL_DEFS: ColDef[] = [
    { key: 'PNE_CODIGO',       label: 'Código',           defaultVisible: false, align: 'right' },
    { key: 'PNE_FOGO',         label: 'Nº de Fogo',       defaultVisible: true },
    { key: 'UNI_DESCRICAO',    label: 'Unidade',          defaultVisible: true },
    { key: 'MARCA_CARCACA',    label: 'Marca',            defaultVisible: true },
    { key: 'MODELO_CARCACA',   label: 'Modelo',           defaultVisible: false },
    { key: 'MEDIDA',           label: 'Medida',           defaultVisible: true },
    { key: 'SKU_CARCACA',      label: 'SKU Carcaça',      defaultVisible: true },
    {
        key: 'STATUS_DESCRICAO',
        label: 'Status',
        defaultVisible: true,
        align: 'center',
        render: (row) => (
            <Badge variant={STATUS_BADGE[row.PNE_STATUS] ?? 'outline'}>
                {row.STATUS_DESCRICAO}
            </Badge>
        ),
    },
    {
        key: 'PNE_STATUSCOMPRA',
        label: 'Compra',
        defaultVisible: false,
        align: 'center',
        render: (row) => (
            <Badge variant={row.PNE_STATUSCOMPRA === 'N' ? 'default' : 'secondary'}>
                {row.PNE_STATUSCOMPRA === 'N' ? 'Novo' : 'Usado'}
            </Badge>
        ),
    },
    { key: 'PNE_VIDACOMPRA',   label: 'Vida Compra',      defaultVisible: false, align: 'center' },
    { key: 'PNE_VIDAATUAL',    label: 'Vida Atual',       defaultVisible: true,  align: 'center' },
    { key: 'PNE_DOT',          label: 'DOT',              defaultVisible: false, align: 'center' },
    {
        key: 'PNE_VALORCOMPRA',
        label: 'Vl. Compra',
        defaultVisible: true,
        align: 'right',
        render: (row) =>
            row.PNE_VALORCOMPRA.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
    },
    {
        key: 'PNE_CUSTOATUAL',
        label: 'Custo Atual',
        defaultVisible: false,
        align: 'right',
        render: (row) =>
            row.PNE_CUSTOATUAL.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
    },
    {
        key: 'PNE_MM',
        label: 'MM Atual',
        defaultVisible: true,
        align: 'right',
        render: (row) => `${row.PNE_MM.toFixed(1)} mm`,
    },
    {
        key: 'PNE_KM',
        label: 'KM Atual',
        defaultVisible: true,
        align: 'right',
        render: (row) => row.PNE_KM.toLocaleString('pt-BR'),
    },
    { key: 'MARCA_RECAPAGEM',  label: 'Marca Recapagem',  defaultVisible: false },
    { key: 'SKU_RECAPAGEM',    label: 'SKU Recapagem',    defaultVisible: false },
    { key: 'TIPO_MMNOVO',      label: 'MM Novo',          defaultVisible: false, align: 'right' },
    { key: 'TIPO_MMSEGURANCA', label: 'MM Segurança',     defaultVisible: false, align: 'right' },
];

const STORAGE_KEY = 'relatorio-pneus-colunas';

function loadVisibleCols(): Record<ColKey, boolean> {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) return JSON.parse(stored) as Record<ColKey, boolean>;
    } catch {
        // ignore
    }
    return Object.fromEntries(
        COL_DEFS.map((c) => [c.key, c.defaultVisible]),
    ) as Record<ColKey, boolean>;
}

// ---------------------------------------------------------------------------
// Sub-componente: Painel de Filtros
// ---------------------------------------------------------------------------

type FiltrosProps = {
    filtros: PneuRelatorioFiltros;
    unidades: UnidadeSimples[];
    marcas: MarcaSimples[];
    statuses: StatusPneuOption[];
    total: number;
};

const selectClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50';

const VIDA_OPTIONS = [
    { value: '',   label: 'Todas as vidas' },
    { value: 'N',  label: 'Nova (N)' },
    { value: 'R1', label: '1ª Recapagem (R1)' },
    { value: 'R2', label: '2ª Recapagem (R2)' },
    { value: 'R3', label: '3ª Recapagem (R3)' },
    { value: 'R4', label: '4ª Recapagem (R4)' },
    { value: 'R5', label: '5ª Recapagem (R5)' },
];

function PainelFiltros({ filtros, unidades, marcas, statuses, total }: FiltrosProps) {
    const [local, setLocal] = useState<PneuRelatorioFiltros>(filtros);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const applyFilters = useCallback((f: PneuRelatorioFiltros) => {
        const params: Record<string, string> = {};
        if (f.unidade) params.unidade = f.unidade;
        if (f.status)  params.status  = f.status;
        if (f.vida)    params.vida    = f.vida;
        if (f.marca)   params.marca   = f.marca;
        if (f.fogo)    params.fogo    = f.fogo;
        router.get('/relatorios/pneus', params, { preserveScroll: true, replace: true });
    }, []);

    const handleChange = (key: keyof PneuRelatorioFiltros, value: string) => {
        const next = { ...local, [key]: value };
        setLocal(next);
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => applyFilters(next), 400);
    };

    const hasActiveFilters = Object.values(local).some((v) => v && v !== '');

    const clearFilters = () => {
        const empty: PneuRelatorioFiltros = {};
        setLocal(empty);
        router.get('/relatorios/pneus', {}, { preserveScroll: true, replace: true });
    };

    useEffect(() => () => { if (debounceRef.current) clearTimeout(debounceRef.current); }, []);

    return (
        <div className="rounded-lg border bg-card p-4 print:hidden">
            <div className="mb-3 flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <Search className="h-4 w-4 text-muted-foreground" />
                    <span className="text-sm font-medium">Filtros</span>
                    {hasActiveFilters && (
                        <Badge variant="secondary" className="text-xs">ativos</Badge>
                    )}
                </div>
                <span className="text-xs text-muted-foreground">
                    {total} {total === 1 ? 'registro' : 'registros'}
                </span>
            </div>

            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div className="grid gap-1.5">
                    <Label htmlFor="f-fogo" className="text-xs">Nº de Fogo</Label>
                    <div className="relative">
                        <Input
                            id="f-fogo"
                            placeholder="Buscar..."
                            value={local.fogo ?? ''}
                            onChange={(e) => handleChange('fogo', e.target.value.toUpperCase())}
                            className="pr-7"
                        />
                        {local.fogo && (
                            <button
                                type="button"
                                onClick={() => handleChange('fogo', '')}
                                className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            >
                                <X className="h-3.5 w-3.5" />
                            </button>
                        )}
                    </div>
                </div>

                <div className="grid gap-1.5">
                    <Label htmlFor="f-unidade" className="text-xs">Unidade</Label>
                    <select
                        id="f-unidade"
                        className={selectClass}
                        value={local.unidade ?? ''}
                        onChange={(e) => handleChange('unidade', e.target.value)}
                    >
                        <option value="">Todas as unidades</option>
                        {unidades.map((u) => (
                            <option key={u.UNI_CODIGO} value={u.UNI_CODIGO}>
                                {u.UNI_DESCRICAO}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="grid gap-1.5">
                    <Label htmlFor="f-marca" className="text-xs">Marca</Label>
                    <select
                        id="f-marca"
                        className={selectClass}
                        value={local.marca ?? ''}
                        onChange={(e) => handleChange('marca', e.target.value)}
                    >
                        <option value="">Todas as marcas</option>
                        {marcas.map((m) => (
                            <option key={m.MARP_CODIGO} value={m.MARP_CODIGO}>
                                {m.MARP_DESCRICAO}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Status populado dinamicamente via t_statuspneu */}
                <div className="grid gap-1.5">
                    <Label htmlFor="f-status" className="text-xs">Status</Label>
                    <select
                        id="f-status"
                        className={selectClass}
                        value={local.status ?? ''}
                        onChange={(e) => handleChange('status', e.target.value)}
                    >
                        <option value="">Todos os status</option>
                        {statuses.map((s) => (
                            <option key={s.STP_SIGLA} value={s.STP_SIGLA}>
                                {s.STP_DESCRICAO}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="grid gap-1.5">
                    <Label htmlFor="f-vida" className="text-xs">Vida Atual</Label>
                    <select
                        id="f-vida"
                        className={selectClass}
                        value={local.vida ?? ''}
                        onChange={(e) => handleChange('vida', e.target.value)}
                    >
                        {VIDA_OPTIONS.map((o) => (
                            <option key={o.value} value={o.value}>{o.label}</option>
                        ))}
                    </select>
                </div>
            </div>

            {hasActiveFilters && (
                <div className="mt-3 flex justify-end">
                    <Button variant="ghost" size="sm" onClick={clearFilters} className="h-7 gap-1 text-xs">
                        <X className="h-3 w-3" />
                        Limpar filtros
                    </Button>
                </div>
            )}
        </div>
    );
}

// ---------------------------------------------------------------------------
// Componente principal
// ---------------------------------------------------------------------------

export default function PneuRelatorioIndex({
    pneus,
    filtros,
    unidades,
    marcas,
    statuses,
}: PneuRelatorioPageProps) {
    const [visibleCols, setVisibleCols] = useState<Record<ColKey, boolean>>(loadVisibleCols);

    const activeCols = COL_DEFS.filter((c) => visibleCols[c.key]);

    const toggleCol = (key: ColKey) => {
        setVisibleCols((prev) => {
            const next = { ...prev, [key]: !prev[key] };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
            return next;
        });
    };

    const exportarExcel = () => {
        const params = new URLSearchParams();
        if (filtros.unidade) params.set('unidade', filtros.unidade);
        if (filtros.status)  params.set('status',  filtros.status);
        if (filtros.vida)    params.set('vida',    filtros.vida);
        if (filtros.marca)   params.set('marca',   filtros.marca);
        if (filtros.fogo)    params.set('fogo',    filtros.fogo);
        const qs = params.toString();
        window.location.href = `/relatorios/pneus/exportar-excel${qs ? `?${qs}` : ''}`;
    };

    return (
        <AppLayout>
            <Head title="Relatório de Pneus" />

            <style>{`
                @media print {
                    body * { visibility: hidden; }
                    #print-area, #print-area * { visibility: visible; }
                    #print-area { position: fixed; inset: 0; padding: 16px; }
                    #print-header { margin-bottom: 12px; }
                    #print-header h1 { font-size: 16px; font-weight: 700; }
                    #print-header p { font-size: 11px; color: #666; }
                    #print-table { width: 100%; border-collapse: collapse; font-size: 10px; }
                    #print-table th { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; font-weight: 600; }
                    #print-table td { border: 1px solid #e2e8f0; padding: 3px 6px; }
                    #print-table tr:nth-child(even) td { background: #f8fafc; }
                }
            `}</style>

            <PageContainer>
                <PageHeaderMinimal
                    title="Relatório de Pneus"
                    description="Visualize, filtre e exporte os dados de todos os pneus cadastrados."
                    actions={
                        <div className="flex items-center gap-2 print:hidden">
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="outline" size="sm" className="gap-1.5">
                                        <Settings2 className="h-4 w-4" />
                                        Colunas
                                        <Badge variant="secondary" className="ml-0.5 h-4 px-1.5 text-xs">
                                            {activeCols.length}
                                        </Badge>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" className="w-56">
                                    <DropdownMenuLabel className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                        Colunas visíveis
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <div className="max-h-72 overflow-y-auto">
                                        {COL_DEFS.map((col) => (
                                            <div
                                                key={col.key}
                                                className="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 hover:bg-accent"
                                                onClick={() => toggleCol(col.key)}
                                            >
                                                <Checkbox
                                                    checked={visibleCols[col.key]}
                                                    onCheckedChange={() => toggleCol(col.key)}
                                                    id={`col-${col.key}`}
                                                />
                                                <label
                                                    htmlFor={`col-${col.key}`}
                                                    className="cursor-pointer text-sm"
                                                >
                                                    {col.label}
                                                </label>
                                            </div>
                                        ))}
                                    </div>
                                    <DropdownMenuSeparator />
                                    <div className="flex gap-1 px-2 py-1.5">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            className="h-6 flex-1 text-xs"
                                            onClick={() => {
                                                const all = Object.fromEntries(COL_DEFS.map((c) => [c.key, true])) as Record<ColKey, boolean>;
                                                setVisibleCols(all);
                                                localStorage.setItem(STORAGE_KEY, JSON.stringify(all));
                                            }}
                                        >
                                            Todas
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            className="h-6 flex-1 text-xs"
                                            onClick={() => {
                                                const defaults = Object.fromEntries(COL_DEFS.map((c) => [c.key, c.defaultVisible])) as Record<ColKey, boolean>;
                                                setVisibleCols(defaults);
                                                localStorage.setItem(STORAGE_KEY, JSON.stringify(defaults));
                                            }}
                                        >
                                            Padrão
                                        </Button>
                                    </div>
                                </DropdownMenuContent>
                            </DropdownMenu>

                            <Button variant="outline" size="sm" className="gap-1.5" onClick={exportarExcel}>
                                <FileSpreadsheet className="h-4 w-4 text-emerald-600" />
                                Excel
                            </Button>

                            <Button variant="outline" size="sm" className="gap-1.5" onClick={() => window.print()}>
                                <Printer className="h-4 w-4 text-rose-600" />
                                PDF
                            </Button>
                        </div>
                    }
                />

                <PainelFiltros
                    filtros={filtros}
                    unidades={unidades}
                    marcas={marcas}
                    statuses={statuses}
                    total={pneus.length}
                />

                <div id="print-area" className="rounded-lg border bg-card shadow-sm">
                    <div id="print-header" className="hidden px-4 pt-4 print:block">
                        <h1>Relatório de Pneus — SteerSync</h1>
                        <p>
                            Gerado em{' '}
                            {new Date().toLocaleDateString('pt-BR', {
                                day: '2-digit', month: '2-digit', year: 'numeric',
                                hour: '2-digit', minute: '2-digit',
                            })}
                            {' '}· {pneus.length} registros
                        </p>
                    </div>

                    {pneus.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-16 text-center text-muted-foreground">
                            <Download className="mb-3 h-10 w-10 opacity-30" />
                            <p className="font-medium">Nenhum pneu encontrado</p>
                            <p className="text-sm">Ajuste os filtros para visualizar resultados.</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table id="print-table" className="w-full text-sm">
                                <thead>
                                    <tr className="border-b bg-muted/40">
                                        {activeCols.map((col) => (
                                            <th
                                                key={col.key}
                                                className={`px-3 py-2.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground whitespace-nowrap ${
                                                    col.align === 'right'
                                                        ? 'text-right'
                                                        : col.align === 'center'
                                                          ? 'text-center'
                                                          : 'text-left'
                                                }`}
                                            >
                                                {col.label}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {pneus.map((row, idx) => (
                                        <tr
                                            key={row.PNE_CODIGO}
                                            className={`border-b transition-colors last:border-0 hover:bg-muted/30 ${
                                                idx % 2 === 0 ? '' : 'bg-muted/10'
                                            }`}
                                        >
                                            {activeCols.map((col) => (
                                                <td
                                                    key={col.key}
                                                    className={`px-3 py-2 ${
                                                        col.align === 'right'
                                                            ? 'text-right'
                                                            : col.align === 'center'
                                                              ? 'text-center'
                                                              : 'text-left'
                                                    }`}
                                                >
                                                    {col.render
                                                        ? col.render(row)
                                                        : (row[col.key] ?? '—')}
                                                </td>
                                            ))}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {pneus.length > 0 && (
                        <div className="flex items-center justify-between border-t px-4 py-2.5 print:hidden">
                            <span className="text-xs text-muted-foreground">
                                {pneus.length} registro{pneus.length !== 1 ? 's' : ''}
                            </span>
                            <span className="text-xs text-muted-foreground">
                                {activeCols.length} coluna{activeCols.length !== 1 ? 's' : ''} visível{activeCols.length !== 1 ? 'is' : ''}
                            </span>
                        </div>
                    )}
                </div>
            </PageContainer>
        </AppLayout>
    );
}
