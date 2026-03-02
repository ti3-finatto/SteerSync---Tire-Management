import { Head, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import DataCard from '@/components/DataCard';
import DataTablePaginated from '@/components/DataTablePaginated';
import InputError from '@/components/input-error';
import PageContainer from '@/components/PageContainer';
import PageHeaderMinimal from '@/components/PageHeaderMinimal';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { fetchWithCsrf } from '@/lib/http';
import type { LegacyStatus, TipoPneu, TipoPneuPageProps } from '@/types';

type Feedback = { type: 'success' | 'error'; message: string };

type FormErrors = Partial<
    Record<
        | 'MARP_CODIGO'
        | 'MODP_CODIGO'
        | 'MEDP_CODIGO'
        | 'TIPO_DESENHO'
        | 'TIPO_INSPECAO'
        | 'TIPO_NSULCO'
        | 'TIPO_MMNOVO'
        | 'TIPO_MMSEGURANCA'
        | 'TIPO_MMDESGPAR'
        | 'TIPO_MMDESGEIXOS'
        | 'TIPO_STATUS',
        string
    >
>;

type TipoPneuForm = {
    MARP_CODIGO: number | '';
    MODP_CODIGO: number | '';
    MEDP_CODIGO: number | '';
    TIPO_DESENHO: string;
    TIPO_INSPECAO: string;
    TIPO_NSULCO: number | '';
    TIPO_MMNOVO: number | '';
    TIPO_MMSEGURANCA: number | '';
    TIPO_MMDESGPAR: number | '';
    TIPO_MMDESGEIXOS: number | '';
    TIPO_STATUS: LegacyStatus;
};

const initialForm: TipoPneuForm = {
    MARP_CODIGO: '',
    MODP_CODIGO: '',
    MEDP_CODIGO: '',
    TIPO_DESENHO: '',
    TIPO_INSPECAO: '',
    TIPO_NSULCO: '',
    TIPO_MMNOVO: '',
    TIPO_MMSEGURANCA: '',
    TIPO_MMDESGPAR: '',
    TIPO_MMDESGEIXOS: '',
    TIPO_STATUS: 'A',
};

const selectClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring';

export default function ConfiguracaoPneuIndex({ tipos, marcas, modelos, medidas, desenhos, flash }: TipoPneuPageProps) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingTipo, setEditingTipo] = useState<TipoPneu | null>(null);
    const [form, setForm] = useState<TipoPneuForm>(initialForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [submitting, setSubmitting] = useState(false);
    const [feedback, setFeedback] = useState<Feedback | null>(() => {
        if (flash?.success) return { type: 'success', message: flash.success };
        if (flash?.error) return { type: 'error', message: flash.error };
        return null;
    });

    const modelosDisponiveis = useMemo(() => {
        if (!form.MARP_CODIGO) return [];
        return modelos.filter((modelo) => modelo.MARP_CODIGO === form.MARP_CODIGO);
    }, [form.MARP_CODIGO, modelos]);

    const handleCreate = () => {
        setEditingTipo(null);
        setForm(initialForm);
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleEdit = (tipo: TipoPneu) => {
        setEditingTipo(tipo);
        setForm({
            MARP_CODIGO: tipo.MARP_CODIGO,
            MODP_CODIGO: tipo.MODP_CODIGO,
            MEDP_CODIGO: tipo.MEDP_CODIGO,
            TIPO_DESENHO: tipo.TIPO_DESENHO,
            TIPO_INSPECAO: tipo.TIPO_INSPECAO,
            TIPO_NSULCO: tipo.TIPO_NSULCO,
            TIPO_MMNOVO: tipo.TIPO_MMNOVO,
            TIPO_MMSEGURANCA: tipo.TIPO_MMSEGURANCA,
            TIPO_MMDESGPAR: tipo.TIPO_MMDESGPAR ?? '',
            TIPO_MMDESGEIXOS: tipo.TIPO_MMDESGEIXOS ?? '',
            TIPO_STATUS: tipo.TIPO_STATUS,
        });
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleChange = (field: keyof TipoPneuForm, value: string | number) => {
        setForm((current) => ({ ...current, [field]: value }));
    };

    const handleMarcaChange = (value: number | '') => {
        setForm((current) => {
            const next = { ...current, MARP_CODIGO: value };
            const modeloValido = typeof next.MODP_CODIGO === 'number'
                && modelos.some((modelo) => modelo.MODP_CODIGO === next.MODP_CODIGO && modelo.MARP_CODIGO === value);

            if (!modeloValido) {
                next.MODP_CODIGO = '';
            }

            return next;
        });
    };

    const parseErrors = (payload: unknown): FormErrors => {
        if (!payload || typeof payload !== 'object' || !('errors' in payload)) return {};
        const payloadErrors = payload.errors as Record<string, string[]>;
        return {
            MARP_CODIGO: payloadErrors.MARP_CODIGO?.[0],
            MODP_CODIGO: payloadErrors.MODP_CODIGO?.[0],
            MEDP_CODIGO: payloadErrors.MEDP_CODIGO?.[0],
            TIPO_DESENHO: payloadErrors.TIPO_DESENHO?.[0],
            TIPO_INSPECAO: payloadErrors.TIPO_INSPECAO?.[0],
            TIPO_NSULCO: payloadErrors.TIPO_NSULCO?.[0],
            TIPO_MMNOVO: payloadErrors.TIPO_MMNOVO?.[0],
            TIPO_MMSEGURANCA: payloadErrors.TIPO_MMSEGURANCA?.[0],
            TIPO_MMDESGPAR: payloadErrors.TIPO_MMDESGPAR?.[0],
            TIPO_MMDESGEIXOS: payloadErrors.TIPO_MMDESGEIXOS?.[0],
            TIPO_STATUS: payloadErrors.TIPO_STATUS?.[0],
        };
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        setFeedback(null);

        const url = editingTipo
            ? `/cadastros/configuracao-pneu/${editingTipo.TIPO_CODIGO}`
            : '/cadastros/configuracao-pneu';
        const method = editingTipo ? 'PUT' : 'POST';

        try {
            const response = await fetchWithCsrf(url, {
                method,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(form),
            });

            const payload = (await response.json().catch(() => ({}))) as {
                message?: string;
                errors?: Record<string, string[]>;
            };

            if (response.status === 422) {
                setErrors(parseErrors(payload));
                setFeedback({ type: 'error', message: payload.message ?? 'Existem campos invalidos no formulario.' });
                return;
            }

            if (response.status === 409) {
                setFeedback({ type: 'error', message: payload.message ?? 'Conflito de dados detectado.' });
                return;
            }

            if (!response.ok) {
                setFeedback({ type: 'error', message: payload.message ?? 'Falha ao salvar configuracao.' });
                return;
            }

            setIsDialogOpen(false);
            setForm(initialForm);
            setFeedback({ type: 'success', message: payload.message ?? 'Configuracao salva com sucesso.' });
            router.reload({ only: ['tipos'] });
        } finally {
            setSubmitting(false);
        }
    };

    const toggleStatus = async (tipo: TipoPneu) => {
        setFeedback(null);

        const response = await fetchWithCsrf(
            `/cadastros/configuracao-pneu/${tipo.TIPO_CODIGO}/status`,
            { method: 'PATCH', headers: { Accept: 'application/json' } },
        );

        const payload = (await response.json().catch(() => ({}))) as { message?: string };

        if (response.status === 409) {
            setFeedback({ type: 'error', message: payload.message ?? 'Nao foi possivel alterar o status.' });
            return;
        }

        if (!response.ok) {
            setFeedback({ type: 'error', message: payload.message ?? 'Falha ao alterar status.' });
            return;
        }

        setFeedback({ type: 'success', message: payload.message ?? 'Status atualizado com sucesso.' });
        router.reload({ only: ['tipos'] });
    };

    return (
        <AppLayout>
            <Head title="Configuracoes de Pneu" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Configuracoes de Pneu"
                    description="Gerencie as configuracoes (SKU) dos pneus."
                    actions={<Button onClick={handleCreate}>Nova configuracao</Button>}
                />

                {feedback && (
                    <Alert variant={feedback.type === 'error' ? 'destructive' : 'default'}>
                        <AlertTitle>{feedback.type === 'error' ? 'Erro' : 'Sucesso'}</AlertTitle>
                        <AlertDescription>{feedback.message}</AlertDescription>
                    </Alert>
                )}

                <DataCard contentClassName="p-0">
                    <DataTablePaginated data={tipos}>
                        {(rows) => (
                            <table className="w-full text-sm">
                                <thead className="bg-muted/45">
                                    <tr>
                                        <th className="px-4 py-2.5 text-left font-medium">Codigo</th>
                                        <th className="px-4 py-2.5 text-left font-medium">SKU</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Marca</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Modelo</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Medida</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Desenho</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Solicitacao</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Status</th>
                                        <th className="px-4 py-2.5 text-right font-medium">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.length === 0 && (
                                        <tr>
                                            <td className="px-4 py-6 text-center text-muted-foreground" colSpan={9}>
                                                Nenhuma configuracao cadastrada.
                                            </td>
                                        </tr>
                                    )}
                                    {rows.map((tipo) => (
                                        <tr key={tipo.TIPO_CODIGO} className="border-t">
                                            <td className="px-4 py-2.5">{tipo.TIPO_CODIGO}</td>
                                            <td className="px-4 py-2.5">{tipo.TIPO_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">{tipo.MARCA_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">{tipo.MODELO_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">{tipo.MEDIDA_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">{tipo.DESENHO_DESCRICAO ?? tipo.TIPO_DESENHO}</td>
                                            <td className="px-4 py-2.5">{tipo.TIPO_INSPECAO === 'M' ? 'Minimo' : 'Todos'}</td>
                                            <td className="px-4 py-2.5">
                                                <Badge
                                                    variant="secondary"
                                                    className={tipo.TIPO_STATUS === 'A' ? 'border-primary/30 bg-primary/10 text-primary' : ''}
                                                >
                                                    {tipo.TIPO_STATUS === 'A' ? 'Ativa' : 'Inativa'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-2.5 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Button size="sm" variant="outline" onClick={() => handleEdit(tipo)}>
                                                        Editar
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant={tipo.TIPO_STATUS === 'A' ? 'outline' : 'default'}
                                                        onClick={() => toggleStatus(tipo)}
                                                    >
                                                        {tipo.TIPO_STATUS === 'A' ? 'Inativar' : 'Ativar'}
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </DataTablePaginated>
                </DataCard>
            </PageContainer>

            <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
                <DialogContent className="max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>{editingTipo ? 'Editar Configuracao' : 'Nova Configuracao de Pneu'}</DialogTitle>
                        <DialogDescription>
                            Preencha os campos abaixo para {editingTipo ? 'atualizar' : 'cadastrar'} a configuracao.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4 md:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="MARP_CODIGO">Marca</Label>
                            <select
                                id="MARP_CODIGO"
                                className={selectClass}
                                value={form.MARP_CODIGO}
                                onChange={(e) => handleMarcaChange(e.target.value ? Number(e.target.value) : '')}
                            >
                                <option value="">Selecione uma marca...</option>
                                {marcas.map((marca) => (
                                    <option key={marca.MARP_CODIGO} value={marca.MARP_CODIGO}>
                                        {marca.MARP_DESCRICAO} ({marca.MARP_TIPO === 'P' ? 'Pneu novo' : 'Recapagem'})
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.MARP_CODIGO} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="MODP_CODIGO">Modelo</Label>
                            <select
                                id="MODP_CODIGO"
                                className={selectClass}
                                value={form.MODP_CODIGO}
                                onChange={(e) => handleChange('MODP_CODIGO', e.target.value ? Number(e.target.value) : '')}
                            >
                                <option value="">Selecione um modelo...</option>
                                {modelosDisponiveis.map((modelo) => (
                                    <option key={modelo.MODP_CODIGO} value={modelo.MODP_CODIGO}>
                                        {modelo.MODP_DESCRICAO}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.MODP_CODIGO} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="MEDP_CODIGO">Medida</Label>
                            <select
                                id="MEDP_CODIGO"
                                className={selectClass}
                                value={form.MEDP_CODIGO}
                                onChange={(e) => handleChange('MEDP_CODIGO', e.target.value ? Number(e.target.value) : '')}
                            >
                                <option value="">Selecione uma medida...</option>
                                {medidas.map((medida) => (
                                    <option key={medida.MEDP_CODIGO} value={medida.MEDP_CODIGO}>
                                        {medida.MEDP_DESCRICAO}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.MEDP_CODIGO} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="TIPO_DESENHO">Desenho</Label>
                            <select
                                id="TIPO_DESENHO"
                                className={selectClass}
                                value={form.TIPO_DESENHO}
                                onChange={(e) => handleChange('TIPO_DESENHO', e.target.value)}
                            >
                                <option value="">Selecione um desenho...</option>
                                {desenhos.map((desenho) => (
                                    <option key={desenho.DESB_CODIGO} value={desenho.DESB_SIGLA}>
                                        {desenho.DESB_DESCRICAO} ({desenho.DESB_SIGLA})
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.TIPO_DESENHO} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="TIPO_INSPECAO">Tipo de Solicitacao</Label>
                            <select
                                id="TIPO_INSPECAO"
                                className={selectClass}
                                value={form.TIPO_INSPECAO}
                                onChange={(e) => handleChange('TIPO_INSPECAO', e.target.value)}
                            >
                                <option value="">Selecione...</option>
                                <option value="M">Minimo (M)</option>
                                <option value="T">Todos (T)</option>
                            </select>
                            <InputError message={errors.TIPO_INSPECAO} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="TIPO_NSULCO">Numero de Sulcos</Label>
                            <Input
                                id="TIPO_NSULCO"
                                type="number"
                                min={1}
                                max={15}
                                value={form.TIPO_NSULCO}
                                onChange={(e) => handleChange('TIPO_NSULCO', e.target.value ? Number(e.target.value) : '')}
                            />
                            <InputError message={errors.TIPO_NSULCO} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="TIPO_MMNOVO">MM Novo</Label>
                            <Input
                                id="TIPO_MMNOVO"
                                type="number"
                                min={1}
                                max={30}
                                step="0.1"
                                value={form.TIPO_MMNOVO}
                                onChange={(e) => handleChange('TIPO_MMNOVO', e.target.value ? Number(e.target.value) : '')}
                            />
                            <InputError message={errors.TIPO_MMNOVO} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="TIPO_MMSEGURANCA">MM Seguranca</Label>
                            <Input
                                id="TIPO_MMSEGURANCA"
                                type="number"
                                min={1}
                                max={30}
                                step="0.1"
                                value={form.TIPO_MMSEGURANCA}
                                onChange={(e) => handleChange('TIPO_MMSEGURANCA', e.target.value ? Number(e.target.value) : '')}
                            />
                            <InputError message={errors.TIPO_MMSEGURANCA} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="TIPO_MMDESGPAR">MM Desgaste Paralelo</Label>
                            <Input
                                id="TIPO_MMDESGPAR"
                                type="number"
                                min={1}
                                max={30}
                                step="0.1"
                                value={form.TIPO_MMDESGPAR}
                                onChange={(e) => handleChange('TIPO_MMDESGPAR', e.target.value ? Number(e.target.value) : '')}
                            />
                            <InputError message={errors.TIPO_MMDESGPAR} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="TIPO_MMDESGEIXOS">MM Desgaste Eixos</Label>
                            <Input
                                id="TIPO_MMDESGEIXOS"
                                type="number"
                                min={1}
                                max={30}
                                step="0.1"
                                value={form.TIPO_MMDESGEIXOS}
                                onChange={(e) => handleChange('TIPO_MMDESGEIXOS', e.target.value ? Number(e.target.value) : '')}
                            />
                            <InputError message={errors.TIPO_MMDESGEIXOS} />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setIsDialogOpen(false)}>
                            Cancelar
                        </Button>
                        <Button type="button" onClick={submit} disabled={submitting}>
                            {submitting ? 'Salvando...' : 'Salvar'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
