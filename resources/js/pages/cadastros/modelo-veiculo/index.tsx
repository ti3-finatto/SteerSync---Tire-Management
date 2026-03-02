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
import type { LegacyStatus, ModeloVeiculo, ModeloVeiculoPageProps } from '@/types';

type Feedback = { type: 'success' | 'error'; message: string };
type FormErrors = Partial<Record<'MODV_DESCRICAO' | 'MARV_CODIGO' | 'VEIC_TIPO' | 'MODV_STATUS', string>>;

type ModeloVeiculoForm = {
    MODV_DESCRICAO: string;
    MARV_CODIGO: number | '';
    VEIC_TIPO: string;
    MODV_STATUS: LegacyStatus;
};

const selectClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50';

export default function ModeloVeiculoIndex({ modelos, marcas, tipos, flash }: ModeloVeiculoPageProps) {
    const initialForm: ModeloVeiculoForm = {
        MODV_DESCRICAO: '',
        MARV_CODIGO: '',
        VEIC_TIPO: tipos[0]?.TPVE_SIGLA ?? '',
        MODV_STATUS: 'A',
    };

    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingModelo, setEditingModelo] = useState<ModeloVeiculo | null>(null);
    const [form, setForm] = useState<ModeloVeiculoForm>(initialForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [submitting, setSubmitting] = useState(false);
    const [feedback, setFeedback] = useState<Feedback | null>(() => {
        if (flash?.success) return { type: 'success', message: flash.success };
        if (flash?.error)   return { type: 'error',   message: flash.error };
        return null;
    });

    const handleCreate = () => {
        setEditingModelo(null);
        setForm(initialForm);
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleEdit = (modelo: ModeloVeiculo) => {
        setEditingModelo(modelo);
        setForm({
            MODV_DESCRICAO: modelo.MODV_DESCRICAO,
            MARV_CODIGO: modelo.MARV_CODIGO,
            VEIC_TIPO: modelo.VEIC_TIPO,
            MODV_STATUS: modelo.MODV_STATUS,
        });
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleChange = (field: keyof ModeloVeiculoForm, value: string | number) => {
        setForm((current) => ({ ...current, [field]: value }));
    };

    const parseErrors = (payload: unknown): FormErrors => {
        if (!payload || typeof payload !== 'object' || !('errors' in payload)) return {};
        const payloadErrors = payload.errors as Record<string, string[]>;
        return {
            MODV_DESCRICAO: payloadErrors.MODV_DESCRICAO?.[0],
            MARV_CODIGO:    payloadErrors.MARV_CODIGO?.[0],
            VEIC_TIPO:      payloadErrors.VEIC_TIPO?.[0],
            MODV_STATUS:    payloadErrors.MODV_STATUS?.[0],
        };
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        setFeedback(null);

        const url    = editingModelo ? `/cadastros/modelo-veiculo/${editingModelo.MODV_CODIGO}` : '/cadastros/modelo-veiculo';
        const method = editingModelo ? 'PUT' : 'POST';

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
                setFeedback({ type: 'error', message: payload.message ?? 'Falha ao salvar modelo.' });
                return;
            }

            setIsDialogOpen(false);
            setForm(initialForm);
            setFeedback({ type: 'success', message: payload.message ?? 'Modelo salvo com sucesso.' });
            router.reload({ only: ['modelos'] });
        } finally {
            setSubmitting(false);
        }
    };

    const toggleStatus = async (modelo: ModeloVeiculo) => {
        setFeedback(null);

        const response = await fetchWithCsrf(
            `/cadastros/modelo-veiculo/${modelo.MODV_CODIGO}/status`,
            { method: 'PATCH', headers: { Accept: 'application/json' } },
        );

        const payload = (await response.json().catch(() => ({}))) as { message?: string };

        if (!response.ok) {
            setFeedback({ type: 'error', message: payload.message ?? 'Falha ao alterar status.' });
            return;
        }

        setFeedback({ type: 'success', message: payload.message ?? 'Status atualizado com sucesso.' });
        router.reload({ only: ['modelos'] });
    };

    return (
        <AppLayout>
            <Head title="Modelos de Veiculo" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Modelos de Veículo"
                    description="Gerencie os modelos de veículo do sistema."
                    actions={<Button onClick={handleCreate}>Novo modelo</Button>}
                />

                {feedback && (
                    <Alert variant={feedback.type === 'error' ? 'destructive' : 'default'}>
                        <AlertTitle>{feedback.type === 'error' ? 'Erro' : 'Sucesso'}</AlertTitle>
                        <AlertDescription>{feedback.message}</AlertDescription>
                    </Alert>
                )}

                <DataCard contentClassName="p-0">
                    <DataTablePaginated data={modelos}>
                        {(rows) => (
                            <table className="w-full text-sm">
                                <thead className="bg-muted/45">
                                    <tr>
                                        <th className="px-4 py-2.5 text-left font-medium">Codigo</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Descrição</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Marca</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Tipo de Veículo</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Status</th>
                                        <th className="px-4 py-2.5 text-right font-medium">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.length === 0 && (
                                        <tr>
                                            <td className="px-4 py-6 text-center text-muted-foreground" colSpan={6}>
                                                Nenhum modelo cadastrado.
                                            </td>
                                        </tr>
                                    )}
                                    {rows.map((modelo) => (
                                        <tr key={modelo.MODV_CODIGO} className="border-t">
                                            <td className="px-4 py-2.5">{modelo.MODV_CODIGO}</td>
                                            <td className="px-4 py-2.5">{modelo.MODV_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">{modelo.MARCA_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">
                                                <span className="mr-1.5 font-mono text-xs text-muted-foreground">
                                                    {modelo.VEIC_TIPO}
                                                </span>
                                                {modelo.TIPO_DESCRICAO}
                                            </td>
                                            <td className="px-4 py-2.5">
                                                <Badge
                                                    variant="secondary"
                                                    className={modelo.MODV_STATUS === 'A' ? 'border-primary/30 bg-primary/10 text-primary' : ''}
                                                >
                                                    {modelo.MODV_STATUS === 'A' ? 'Ativo' : 'Inativo'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-2.5 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Button size="sm" variant="outline" onClick={() => handleEdit(modelo)}>
                                                        Editar
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant={modelo.MODV_STATUS === 'A' ? 'outline' : 'default'}
                                                        onClick={() => toggleStatus(modelo)}
                                                    >
                                                        {modelo.MODV_STATUS === 'A' ? 'Inativar' : 'Ativar'}
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
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{editingModelo ? 'Editar Modelo' : 'Novo Modelo de Veículo'}</DialogTitle>
                        <DialogDescription>
                            Preencha os campos abaixo para {editingModelo ? 'atualizar' : 'cadastrar'} o modelo.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="MARV_CODIGO">Marca</Label>
                            <select
                                id="MARV_CODIGO"
                                className={selectClass}
                                value={form.MARV_CODIGO}
                                onChange={(e) => handleChange('MARV_CODIGO', e.target.value ? Number(e.target.value) : '')}
                            >
                                <option value="">Selecione uma marca...</option>
                                {marcas.map((marca) => (
                                    <option key={marca.MARV_CODIGO} value={marca.MARV_CODIGO}>
                                        {marca.MARV_DESCRICAO}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.MARV_CODIGO} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="MODV_DESCRICAO">Descrição</Label>
                            <Input
                                id="MODV_DESCRICAO"
                                value={form.MODV_DESCRICAO}
                                onChange={(e) => handleChange('MODV_DESCRICAO', e.target.value)}
                                maxLength={30}
                            />
                            <InputError message={errors.MODV_DESCRICAO} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="VEIC_TIPO">Tipo de Veículo</Label>
                            <select
                                id="VEIC_TIPO"
                                className={selectClass}
                                value={form.VEIC_TIPO}
                                onChange={(e) => handleChange('VEIC_TIPO', e.target.value)}
                            >
                                <option value="">Selecione um tipo...</option>
                                {tipos.map((tipo) => (
                                    <option key={tipo.TPVE_SIGLA} value={tipo.TPVE_SIGLA}>
                                        {tipo.TPVE_DESCRICAO}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.VEIC_TIPO} />
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
