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
import type { DesenhoBanda, DesenhoBandaPageProps, LegacyStatus } from '@/types';

type Feedback = { type: 'success' | 'error'; message: string };

type FormErrors = Partial<Record<'DESB_DESCRICAO' | 'DESB_SIGLA' | 'DESB_STATUS', string>>;

type DesenhoBandaForm = {
    DESB_DESCRICAO: string;
    DESB_SIGLA: string;
    DESB_STATUS: LegacyStatus;
};

const initialForm: DesenhoBandaForm = {
    DESB_DESCRICAO: '',
    DESB_SIGLA: '',
    DESB_STATUS: 'A',
};

export default function DesenhoBandaIndex({ desenhos, flash }: DesenhoBandaPageProps) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingDesenho, setEditingDesenho] = useState<DesenhoBanda | null>(null);
    const [form, setForm] = useState<DesenhoBandaForm>(initialForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [submitting, setSubmitting] = useState(false);
    const [feedback, setFeedback] = useState<Feedback | null>(() => {
        if (flash?.success) return { type: 'success', message: flash.success };
        if (flash?.error) return { type: 'error', message: flash.error };
        return null;
    });

    const handleCreate = () => {
        setEditingDesenho(null);
        setForm(initialForm);
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleEdit = (desenho: DesenhoBanda) => {
        setEditingDesenho(desenho);
        setForm({
            DESB_DESCRICAO: desenho.DESB_DESCRICAO,
            DESB_SIGLA: desenho.DESB_SIGLA,
            DESB_STATUS: desenho.DESB_STATUS,
        });
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleChange = (field: keyof DesenhoBandaForm, value: string) => {
        setForm((current) => ({ ...current, [field]: value }));
    };

    const parseErrors = (payload: unknown): FormErrors => {
        if (!payload || typeof payload !== 'object' || !('errors' in payload)) return {};
        const payloadErrors = payload.errors as Record<string, string[]>;
        return {
            DESB_DESCRICAO: payloadErrors.DESB_DESCRICAO?.[0],
            DESB_SIGLA: payloadErrors.DESB_SIGLA?.[0],
            DESB_STATUS: payloadErrors.DESB_STATUS?.[0],
        };
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        setFeedback(null);

        const url = editingDesenho
            ? `/cadastros/desenho-banda/${editingDesenho.DESB_CODIGO}`
            : '/cadastros/desenho-banda';
        const method = editingDesenho ? 'PUT' : 'POST';

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
                setFeedback({ type: 'error', message: payload.message ?? 'Falha ao salvar desenho.' });
                return;
            }

            setIsDialogOpen(false);
            setForm(initialForm);
            setFeedback({ type: 'success', message: payload.message ?? 'Desenho salvo com sucesso.' });
            router.reload({ only: ['desenhos'] });
        } finally {
            setSubmitting(false);
        }
    };

    const toggleStatus = async (desenho: DesenhoBanda) => {
        setFeedback(null);

        const response = await fetchWithCsrf(
            `/cadastros/desenho-banda/${desenho.DESB_CODIGO}/status`,
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
        router.reload({ only: ['desenhos'] });
    };

    return (
        <AppLayout>
            <Head title="Desenhos de Banda" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Desenhos de Banda"
                    description="Gerencie os desenhos de banda usados nos SKUs de pneu."
                    actions={<Button onClick={handleCreate}>Novo desenho</Button>}
                />

                {feedback && (
                    <Alert variant={feedback.type === 'error' ? 'destructive' : 'default'}>
                        <AlertTitle>{feedback.type === 'error' ? 'Erro' : 'Sucesso'}</AlertTitle>
                        <AlertDescription>{feedback.message}</AlertDescription>
                    </Alert>
                )}

                <DataCard contentClassName="p-0">
                    <DataTablePaginated data={desenhos}>
                        {(rows) => (
                            <table className="w-full text-sm">
                                <thead className="bg-muted/45">
                                    <tr>
                                        <th className="px-4 py-2.5 text-left font-medium">Codigo</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Descricao</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Sigla</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Status</th>
                                        <th className="px-4 py-2.5 text-right font-medium">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.length === 0 && (
                                        <tr>
                                            <td className="px-4 py-6 text-center text-muted-foreground" colSpan={5}>
                                                Nenhum desenho cadastrado.
                                            </td>
                                        </tr>
                                    )}
                                    {rows.map((desenho) => (
                                        <tr key={desenho.DESB_CODIGO} className="border-t">
                                            <td className="px-4 py-2.5">{desenho.DESB_CODIGO}</td>
                                            <td className="px-4 py-2.5">{desenho.DESB_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">{desenho.DESB_SIGLA}</td>
                                            <td className="px-4 py-2.5">
                                                <Badge
                                                    variant="secondary"
                                                    className={desenho.DESB_STATUS === 'A' ? 'border-primary/30 bg-primary/10 text-primary' : ''}
                                                >
                                                    {desenho.DESB_STATUS === 'A' ? 'Ativo' : 'Inativo'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-2.5 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Button size="sm" variant="outline" onClick={() => handleEdit(desenho)}>
                                                        Editar
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant={desenho.DESB_STATUS === 'A' ? 'outline' : 'default'}
                                                        onClick={() => toggleStatus(desenho)}
                                                    >
                                                        {desenho.DESB_STATUS === 'A' ? 'Inativar' : 'Ativar'}
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
                        <DialogTitle>{editingDesenho ? 'Editar Desenho' : 'Novo Desenho de Banda'}</DialogTitle>
                        <DialogDescription>
                            Preencha os campos abaixo para {editingDesenho ? 'atualizar' : 'cadastrar'} o desenho.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="DESB_DESCRICAO">Descricao</Label>
                            <Input
                                id="DESB_DESCRICAO"
                                value={form.DESB_DESCRICAO}
                                onChange={(e) => handleChange('DESB_DESCRICAO', e.target.value)}
                                maxLength={30}
                            />
                            <InputError message={errors.DESB_DESCRICAO} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="DESB_SIGLA">Sigla (1 caractere)</Label>
                            <Input
                                id="DESB_SIGLA"
                                value={form.DESB_SIGLA}
                                onChange={(e) => handleChange('DESB_SIGLA', e.target.value.toUpperCase())}
                                maxLength={1}
                            />
                            <InputError message={errors.DESB_SIGLA} />
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
