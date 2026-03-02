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
import type { LegacyStatus, MarcaPneuOption, ModeloPneu, ModeloPneuPageProps } from '@/types';

type Feedback = { type: 'success' | 'error'; message: string };

type FormErrors = Partial<Record<'MODP_DESCRICAO' | 'MARP_CODIGO' | 'MODP_STATUS', string>>;

type ModeloPneuForm = {
    MODP_DESCRICAO: string;
    MARP_CODIGO: number | '';
    MODP_STATUS: LegacyStatus;
};

const initialForm: ModeloPneuForm = {
    MODP_DESCRICAO: '',
    MARP_CODIGO: '',
    MODP_STATUS: 'A',
};

const selectClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring';

export default function ModeloPneuIndex({ modelos, marcas, flash }: ModeloPneuPageProps) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingModelo, setEditingModelo] = useState<ModeloPneu | null>(null);
    const [form, setForm] = useState<ModeloPneuForm>(initialForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [submitting, setSubmitting] = useState(false);
    const [feedback, setFeedback] = useState<Feedback | null>(() => {
        if (flash?.success) return { type: 'success', message: flash.success };
        if (flash?.error) return { type: 'error', message: flash.error };
        return null;
    });

    const handleCreate = () => {
        setEditingModelo(null);
        setForm(initialForm);
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleEdit = (modelo: ModeloPneu) => {
        setEditingModelo(modelo);
        setForm({
            MODP_DESCRICAO: modelo.MODP_DESCRICAO,
            MARP_CODIGO: modelo.MARP_CODIGO,
            MODP_STATUS: modelo.MODP_STATUS,
        });
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleChange = (field: keyof ModeloPneuForm, value: string | number) => {
        setForm((current) => ({ ...current, [field]: value }));
    };

    const parseErrors = (payload: unknown): FormErrors => {
        if (!payload || typeof payload !== 'object' || !('errors' in payload)) return {};
        const payloadErrors = payload.errors as Record<string, string[]>;
        return {
            MODP_DESCRICAO: payloadErrors.MODP_DESCRICAO?.[0],
            MARP_CODIGO: payloadErrors.MARP_CODIGO?.[0],
            MODP_STATUS: payloadErrors.MODP_STATUS?.[0],
        };
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        setFeedback(null);

        const url = editingModelo
            ? `/cadastros/modelo-pneu/${editingModelo.MODP_CODIGO}`
            : '/cadastros/modelo-pneu';
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

    const toggleStatus = async (modelo: ModeloPneu) => {
        setFeedback(null);

        const response = await fetchWithCsrf(
            `/cadastros/modelo-pneu/${modelo.MODP_CODIGO}/status`,
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
        router.reload({ only: ['modelos'] });
    };

    return (
        <AppLayout>
            <Head title="Modelos de Pneu" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Modelos de Pneu"
                    description="Gerencie os modelos de pneu do sistema."
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
                                        <th className="px-4 py-2.5 text-left font-medium">Descricao</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Marca</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Status</th>
                                        <th className="px-4 py-2.5 text-right font-medium">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.length === 0 && (
                                        <tr>
                                            <td className="px-4 py-6 text-center text-muted-foreground" colSpan={5}>
                                                Nenhum modelo cadastrado.
                                            </td>
                                        </tr>
                                    )}
                                    {rows.map((modelo) => (
                                        <tr key={modelo.MODP_CODIGO} className="border-t">
                                            <td className="px-4 py-2.5">{modelo.MODP_CODIGO}</td>
                                            <td className="px-4 py-2.5">{modelo.MODP_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">{modelo.MARCA_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">
                                                <Badge
                                                    variant="secondary"
                                                    className={modelo.MODP_STATUS === 'A' ? 'border-primary/30 bg-primary/10 text-primary' : ''}
                                                >
                                                    {modelo.MODP_STATUS === 'A' ? 'Ativo' : 'Inativo'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-2.5 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Button size="sm" variant="outline" onClick={() => handleEdit(modelo)}>
                                                        Editar
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant={modelo.MODP_STATUS === 'A' ? 'outline' : 'default'}
                                                        onClick={() => toggleStatus(modelo)}
                                                    >
                                                        {modelo.MODP_STATUS === 'A' ? 'Inativar' : 'Ativar'}
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
                        <DialogTitle>{editingModelo ? 'Editar Modelo' : 'Novo Modelo de Pneu'}</DialogTitle>
                        <DialogDescription>
                            Preencha os campos abaixo para {editingModelo ? 'atualizar' : 'cadastrar'} o modelo.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="MARP_CODIGO">Marca</Label>
                            <select
                                id="MARP_CODIGO"
                                className={selectClass}
                                value={form.MARP_CODIGO}
                                onChange={(e) => handleChange('MARP_CODIGO', e.target.value ? Number(e.target.value) : '')}
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
                            <Label htmlFor="MODP_DESCRICAO">Descricao</Label>
                            <Input
                                id="MODP_DESCRICAO"
                                value={form.MODP_DESCRICAO}
                                onChange={(e) => handleChange('MODP_DESCRICAO', e.target.value)}
                                maxLength={30}
                            />
                            <InputError message={errors.MODP_DESCRICAO} />
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
