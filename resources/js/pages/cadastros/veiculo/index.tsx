import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import DataCard from '@/components/DataCard';
import DataTablePaginated from '@/components/DataTablePaginated';
import InputError from '@/components/input-error';
import PageContainer from '@/components/PageContainer';
import PageHeaderMinimal from '@/components/PageHeaderMinimal';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { fetchWithCsrf } from '@/lib/http';
import type { LegacyStatus, Veiculo, VeiculoPageProps } from '@/types';

type Feedback = { type: 'success' | 'error'; message: string };
type FormErrors = Partial<Record<string, string>>;
type ModeloOption = { id: number; name: string; type: string };
type ConfiguracaoOption = { id: number; name: string; description: string };
type MapPos = { id: number; code: number; description: string; short: string; side: 'left' | 'right' | 'center'; is_double: boolean };
type MapResponse = { axles: Array<{ axle_number: number; positions: MapPos[] }>; spares: Array<{ id: number; code: number; description: string; short: string }> };
type FormData = {
    registration_method: 'plate' | 'chassis';
    VEI_PLACA: string;
    VEI_CHASSI: string;
    VEI_FROTA: string;
    UNI_CODIGO: number | '';
    MARV_CODIGO: number | '';
    MODV_CODIGO: number | '';
    VEIC_CODIGO: number | '';
    CAL_RECOMENDADA: number | '';
    VEI_ODOMETRO: 'S' | 'N';
    VEI_KM: number | '';
    VEI_OBS: string;
    VEI_STATUS: LegacyStatus;
};

const selectClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50';
const initialForm: FormData = {
    registration_method: 'plate',
    VEI_PLACA: '',
    VEI_CHASSI: '',
    VEI_FROTA: '',
    UNI_CODIGO: '',
    MARV_CODIGO: '',
    MODV_CODIGO: '',
    VEIC_CODIGO: '',
    CAL_RECOMENDADA: '',
    VEI_ODOMETRO: 'S',
    VEI_KM: 0,
    VEI_OBS: '',
    VEI_STATUS: 'A',
};

export default function VeiculoIndex({ veiculos, unidades, marcas, flash }: VeiculoPageProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [editing, setEditing] = useState<Veiculo | null>(null);
    const [form, setForm] = useState<FormData>(initialForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [submitting, setSubmitting] = useState(false);
    const [feedback, setFeedback] = useState<Feedback | null>(
        flash?.success ? { type: 'success', message: flash.success } : flash?.error ? { type: 'error', message: flash.error } : null,
    );
    const [modelos, setModelos] = useState<ModeloOption[]>([]);
    const [configuracoes, setConfiguracoes] = useState<ConfiguracaoOption[]>([]);
    const [map, setMap] = useState<MapResponse | null>(null);
    const [loadingMap, setLoadingMap] = useState(false);

    const setField = <K extends keyof FormData>(key: K, value: FormData[K]) => setForm((c) => ({ ...c, [key]: value }));
    const parseErrors = (payload: unknown): FormErrors => ((payload && typeof payload === 'object' && 'errors' in payload) ? Object.fromEntries(Object.entries((payload as { errors: Record<string, string[]> }).errors).map(([k, v]) => [k, v?.[0]])) : {});
    const loadModelos = async (brandId: number) => {
        const r = await fetchWithCsrf(`/api/vehicle-models?brand_id=${brandId}`, { headers: { Accept: 'application/json' } });
        const p = r.ok ? ((await r.json()) as ModeloOption[]) : [];
        setModelos(p);
        return p;
    };
    const loadConfiguracoes = async (type: string) => {
        const r = await fetchWithCsrf(`/api/vehicle-configurations?type=${type}`, { headers: { Accept: 'application/json' } });
        const p = r.ok ? ((await r.json()) as ConfiguracaoOption[]) : [];
        setConfiguracoes(p);
        return p;
    };
    const loadMap = async (id: number) => {
        setLoadingMap(true);
        const r = await fetchWithCsrf(`/api/configuration-positions?configuration_id=${id}`, { headers: { Accept: 'application/json' } });
        setMap(r.ok ? ((await r.json()) as MapResponse) : null);
        setLoadingMap(false);
    };

    const openCreate = () => { setEditing(null); setForm(initialForm); setErrors({}); setModelos([]); setConfiguracoes([]); setMap(null); setIsOpen(true); };
    const openEdit = async (v: Veiculo) => {
        setEditing(v);
        setErrors({});
        setForm({
            registration_method: v.VEI_PLACA ? 'plate' : 'chassis',
            VEI_PLACA: v.VEI_PLACA ?? '', VEI_CHASSI: v.VEI_CHASSI ?? '', VEI_FROTA: v.VEI_FROTA ?? '',
            UNI_CODIGO: v.UNI_CODIGO, MARV_CODIGO: v.MARV_CODIGO, MODV_CODIGO: v.MODV_CODIGO, VEIC_CODIGO: v.VEIC_CODIGO,
            CAL_RECOMENDADA: v.CAL_RECOMENDADA ?? '', VEI_ODOMETRO: v.VEI_ODOMETRO, VEI_KM: v.VEI_KM, VEI_OBS: v.VEI_OBS ?? '', VEI_STATUS: v.VEI_STATUS,
        });
        setIsOpen(true);
        const ms = await loadModelos(v.MARV_CODIGO);
        const selected = ms.find((m) => m.id === v.MODV_CODIGO);
        if (selected) await loadConfiguracoes(selected.type);
        await loadMap(v.VEIC_CODIGO);
    };

    const onBrand = async (value: string) => { const id = value ? Number(value) : ''; setField('MARV_CODIGO', id); setField('MODV_CODIGO', ''); setField('VEIC_CODIGO', ''); setModelos([]); setConfiguracoes([]); setMap(null); if (id) await loadModelos(Number(id)); };
    const onModel = async (value: string) => { const id = value ? Number(value) : ''; setField('MODV_CODIGO', id); setField('VEIC_CODIGO', ''); setConfiguracoes([]); setMap(null); const m = modelos.find((x) => x.id === id); if (m) await loadConfiguracoes(m.type); };
    const onConfig = async (value: string) => { const id = value ? Number(value) : ''; setField('VEIC_CODIGO', id); setMap(null); if (id) await loadMap(Number(id)); };

    const submit = async () => {
        setSubmitting(true); setErrors({}); setFeedback(null);
        const url = editing ? `/cadastros/veiculo/${editing.VEI_CODIGO}` : '/cadastros/veiculo';
        const method = editing ? 'PUT' : 'POST';
        try {
            const r = await fetchWithCsrf(url, { method, headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify(form) });
            const p = (await r.json().catch(() => ({}))) as { message?: string; errors?: Record<string, string[]> };
            if (r.status === 422) { setErrors(parseErrors(p)); setFeedback({ type: 'error', message: p.message ?? 'Campos invalidos.' }); return; }
            if (r.status === 409) { setFeedback({ type: 'error', message: p.message ?? 'Conflito de dados.' }); return; }
            if (!r.ok) { setFeedback({ type: 'error', message: p.message ?? 'Falha ao salvar.' }); return; }
            setIsOpen(false); setForm(initialForm); setMap(null); setFeedback({ type: 'success', message: p.message ?? 'Veiculo salvo com sucesso.' }); router.reload({ only: ['veiculos'] });
        } finally { setSubmitting(false); }
    };
    const toggleStatus = async (v: Veiculo) => {
        const r = await fetchWithCsrf(`/cadastros/veiculo/${v.VEI_CODIGO}/status`, { method: 'PATCH', headers: { Accept: 'application/json' } });
        const p = (await r.json().catch(() => ({}))) as { message?: string };
        setFeedback({ type: r.ok ? 'success' : 'error', message: p.message ?? (r.ok ? 'Status atualizado.' : 'Falha ao alterar status.') });
        if (r.ok) router.reload({ only: ['veiculos'] });
    };
    const slot = (p: MapPos | { id: number; code: number; description: string; short: string; is_double?: boolean }) => (
        <div key={p.id} className={['min-w-[58px] rounded border px-2 py-1 text-center text-[11px] font-medium', p.is_double ? 'border-primary/30 bg-primary/10 text-primary' : 'border-border bg-muted/40'].join(' ')} title={`${p.code} - ${p.description}`}>{p.short}</div>
    );

    return (
        <AppLayout>
            <Head title="Cadastro de Veiculos" />
            <PageContainer>
                <PageHeaderMinimal title="Cadastro de Veiculos" description="Cadastro com selecoes dependentes e mapa dinamico de posicoes de pneus." actions={<Button onClick={openCreate}>Novo veiculo</Button>} />
                {feedback && <Alert variant={feedback.type === 'error' ? 'destructive' : 'default'}><AlertTitle>{feedback.type === 'error' ? 'Erro' : 'Sucesso'}</AlertTitle><AlertDescription>{feedback.message}</AlertDescription></Alert>}
                <DataCard contentClassName="p-0">
                    <DataTablePaginated data={veiculos}>
                        {(rows) => (
                            <table className="w-full text-sm">
                                <thead className="bg-muted/45"><tr><th className="px-4 py-2.5 text-left font-medium">Codigo</th><th className="px-4 py-2.5 text-left font-medium">Identificacao</th><th className="px-4 py-2.5 text-left font-medium">Unidade</th><th className="px-4 py-2.5 text-left font-medium">Modelo</th><th className="px-4 py-2.5 text-left font-medium">Configuracao</th><th className="px-4 py-2.5 text-left font-medium">Status</th><th className="px-4 py-2.5 text-right font-medium">Acoes</th></tr></thead>
                                <tbody>
                                    {rows.length === 0 && <tr><td className="px-4 py-6 text-center text-muted-foreground" colSpan={7}>Nenhum veiculo cadastrado.</td></tr>}
                                    {rows.map((v) => <tr key={v.VEI_CODIGO} className="border-t"><td className="px-4 py-2.5">{v.VEI_CODIGO}</td><td className="px-4 py-2.5"><span className="font-mono">{v.VEI_PLACA || v.VEI_CHASSI || '-'}</span></td><td className="px-4 py-2.5">{v.UNI_DESCRICAO}</td><td className="px-4 py-2.5"><div>{v.MODELO_DESCRICAO}</div><div className="text-xs text-muted-foreground">{v.MARCA_DESCRICAO}</div></td><td className="px-4 py-2.5">{v.CONFIGURACAO_DESCRICAO}</td><td className="px-4 py-2.5"><Badge variant="secondary" className={v.VEI_STATUS === 'A' ? 'border-primary/30 bg-primary/10 text-primary' : ''}>{v.VEI_STATUS === 'A' ? 'Ativo' : 'Inativo'}</Badge></td><td className="px-4 py-2.5 text-right"><div className="flex justify-end gap-2"><Button size="sm" variant="outline" onClick={() => void openEdit(v)}>Editar</Button><Button size="sm" variant={v.VEI_STATUS === 'A' ? 'outline' : 'default'} onClick={() => void toggleStatus(v)}>{v.VEI_STATUS === 'A' ? 'Inativar' : 'Ativar'}</Button></div></td></tr>)}
                                </tbody>
                            </table>
                        )}
                    </DataTablePaginated>
                </DataCard>
            </PageContainer>
            <Dialog open={isOpen} onOpenChange={setIsOpen}>
                <DialogContent className="max-h-[95vh] overflow-y-auto lg:max-w-5xl">
                    <DialogHeader><DialogTitle>{editing ? 'Editar Veiculo' : 'Novo Veiculo'}</DialogTitle><DialogDescription>Cadastro por placa/chassi com mapa dinamico.</DialogDescription></DialogHeader>
                    <div className="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                        <div className="space-y-4">
                            <div className="rounded-md border p-3"><div className="flex items-center gap-3"><Checkbox id="registration_method" checked={form.registration_method === 'chassis'} onCheckedChange={(v) => { setField('registration_method', v === true ? 'chassis' : 'plate'); if (v === true) setField('VEI_PLACA', ''); }} /><Label htmlFor="registration_method" className="cursor-pointer">Cadastrar por Chassi</Label></div><InputError message={errors.registration_method} /></div>
                            {form.registration_method === 'plate' && <div className="grid gap-2"><Label htmlFor="VEI_PLACA">Placa *</Label><Input id="VEI_PLACA" value={form.VEI_PLACA} onChange={(e) => setField('VEI_PLACA', e.target.value.toUpperCase())} maxLength={7} /><InputError message={errors.VEI_PLACA} /></div>}
                            <div className="grid gap-2"><Label htmlFor="VEI_CHASSI">Chassi{form.registration_method === 'chassis' ? ' *' : ''}</Label><Input id="VEI_CHASSI" value={form.VEI_CHASSI} onChange={(e) => setField('VEI_CHASSI', e.target.value.toUpperCase())} maxLength={17} /><InputError message={errors.VEI_CHASSI} /></div>
                            <div className="grid gap-2"><Label htmlFor="VEI_FROTA">Frota</Label><Input id="VEI_FROTA" value={form.VEI_FROTA} onChange={(e) => setField('VEI_FROTA', e.target.value.toUpperCase())} maxLength={25} /><InputError message={errors.VEI_FROTA} /></div>
                            <div className="grid gap-2"><Label htmlFor="UNI_CODIGO">Unidade *</Label><select id="UNI_CODIGO" className={selectClass} value={form.UNI_CODIGO} onChange={(e) => setField('UNI_CODIGO', e.target.value ? Number(e.target.value) : '')}><option value="">Selecione...</option>{unidades.map((u) => <option key={u.UNI_CODIGO} value={u.UNI_CODIGO}>{u.UNI_DESCRICAO}</option>)}</select><InputError message={errors.UNI_CODIGO} /></div>
                            <div className="grid gap-2"><Label htmlFor="MARV_CODIGO">Marca *</Label><select id="MARV_CODIGO" className={selectClass} value={form.MARV_CODIGO} onChange={(e) => void onBrand(e.target.value)}><option value="">Selecione...</option>{marcas.map((m) => <option key={m.MARV_CODIGO} value={m.MARV_CODIGO}>{m.MARV_DESCRICAO}</option>)}</select><InputError message={errors.MARV_CODIGO} /></div>
                            <div className="grid gap-2"><Label htmlFor="MODV_CODIGO">Modelo *</Label><select id="MODV_CODIGO" className={selectClass} value={form.MODV_CODIGO} onChange={(e) => void onModel(e.target.value)} disabled={!form.MARV_CODIGO}><option value="">Selecione...</option>{modelos.map((m) => <option key={m.id} value={m.id}>{m.name} ({m.type})</option>)}</select><InputError message={errors.MODV_CODIGO} /></div>
                            <div className="grid gap-2"><Label htmlFor="VEIC_CODIGO">Configuracao *</Label><select id="VEIC_CODIGO" className={selectClass} value={form.VEIC_CODIGO} onChange={(e) => void onConfig(e.target.value)} disabled={!form.MODV_CODIGO}><option value="">Selecione...</option>{configuracoes.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}</select><InputError message={errors.VEIC_CODIGO} /></div>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2"><div className="grid gap-2"><Label htmlFor="CAL_RECOMENDADA">Calibragem</Label><Input id="CAL_RECOMENDADA" type="number" min={0} value={form.CAL_RECOMENDADA} onChange={(e) => setField('CAL_RECOMENDADA', e.target.value ? Number(e.target.value) : '')} /><InputError message={errors.CAL_RECOMENDADA} /></div><div className="grid gap-2"><Label htmlFor="VEI_KM">KM</Label><Input id="VEI_KM" type="number" min={0} value={form.VEI_KM} onChange={(e) => setField('VEI_KM', e.target.value ? Number(e.target.value) : '')} /><InputError message={errors.VEI_KM} /></div></div>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2"><div className="grid gap-2"><Label htmlFor="VEI_ODOMETRO">Usa odometro</Label><select id="VEI_ODOMETRO" className={selectClass} value={form.VEI_ODOMETRO} onChange={(e) => setField('VEI_ODOMETRO', e.target.value as 'S' | 'N')}><option value="S">Sim</option><option value="N">Nao</option></select><InputError message={errors.VEI_ODOMETRO} /></div><div className="grid gap-2"><Label htmlFor="VEI_STATUS">Status</Label><select id="VEI_STATUS" className={selectClass} value={form.VEI_STATUS} onChange={(e) => setField('VEI_STATUS', e.target.value as LegacyStatus)}><option value="A">Ativo</option><option value="I">Inativo</option></select><InputError message={errors.VEI_STATUS} /></div></div>
                            <div className="grid gap-2"><Label htmlFor="VEI_OBS">Observacoes</Label><textarea id="VEI_OBS" className="min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" value={form.VEI_OBS} onChange={(e) => setField('VEI_OBS', e.target.value)} /><InputError message={errors.VEI_OBS} /></div>
                        </div>
                        <div className="rounded-md border p-3">
                            <h4 className="text-sm font-semibold">Mapa de Posicoes</h4>
                            <p className="mb-3 text-xs text-muted-foreground">Gerado de `t_posicaoxconfiguracao`.</p>
                            {loadingMap && <p className="text-sm text-muted-foreground">Carregando mapa...</p>}
                            {!loadingMap && !form.VEIC_CODIGO && <p className="text-sm text-muted-foreground">Selecione a configuracao.</p>}
                            {!loadingMap && form.VEIC_CODIGO && (!map || (map.axles.length === 0 && map.spares.length === 0)) && <p className="text-sm text-muted-foreground">Sem posicoes para esta configuracao.</p>}
                            {!loadingMap && map && (map.axles.length > 0 || map.spares.length > 0) && <div className="space-y-3">{map.axles.map((a) => <div key={a.axle_number} className="rounded-md border p-3"><div className="mb-2 text-xs font-semibold uppercase text-muted-foreground">Eixo {a.axle_number}</div><div className="grid items-center gap-3 md:grid-cols-[1fr_auto_1fr]"><div className="flex flex-wrap justify-end gap-1.5">{a.positions.filter((p) => p.side === 'left').map((p) => slot(p))}</div><div className="h-1 min-w-10 rounded bg-border" /><div className="flex flex-wrap gap-1.5">{a.positions.filter((p) => p.side === 'right').map((p) => slot(p))}</div></div></div>)}{map.spares.length > 0 && <div className="rounded-md border p-3"><div className="mb-2 text-xs font-semibold uppercase text-muted-foreground">Estepes</div><div className="flex flex-wrap gap-1.5">{map.spares.map((s) => slot(s))}</div></div>}</div>}
                        </div>
                    </div>
                    <DialogFooter><Button type="button" variant="outline" onClick={() => setIsOpen(false)}>Cancelar</Button><Button type="button" onClick={() => void submit()} disabled={submitting}>{submitting ? 'Salvando...' : 'Salvar'}</Button></DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
