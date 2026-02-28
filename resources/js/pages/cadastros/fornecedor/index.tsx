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
import type { Fornecedor, FornecedorPageProps, LegacyStatus } from '@/types';

type Feedback = {
    type: 'success' | 'error';
    message: string;
};

type FormErrors = Partial<
    Record<'FORN_RAZAO' | 'FORN_CNPJ' | 'FORN_TELEFONE' | 'FORN_EMAIL' | 'FORN_STATUS', string>
>;

type FornecedorForm = {
    FORN_RAZAO: string;
    FORN_CNPJ: string;
    FORN_EMAIL: string;
    FORN_TELEFONE: string;
    FORN_STATUS: LegacyStatus;
};

const initialForm: FornecedorForm = {
    FORN_RAZAO: '',
    FORN_CNPJ: '',
    FORN_EMAIL: '',
    FORN_TELEFONE: '',
    FORN_STATUS: 'A',
};

export default function FornecedorIndex({ fornecedores, flash }: FornecedorPageProps) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingFornecedor, setEditingFornecedor] = useState<Fornecedor | null>(null);
    const [form, setForm] = useState<FornecedorForm>(initialForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [submitting, setSubmitting] = useState(false);
    const [feedback, setFeedback] = useState<Feedback | null>(() => {
        if (flash?.success) return { type: 'success', message: flash.success };
        if (flash?.error) return { type: 'error', message: flash.error };
        return null;
    });

    const handleCreate = () => {
        setEditingFornecedor(null);
        setForm(initialForm);
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleEdit = (fornecedor: Fornecedor) => {
        setEditingFornecedor(fornecedor);
        setForm({
            FORN_RAZAO: fornecedor.FORN_RAZAO,
            FORN_CNPJ: fornecedor.FORN_CNPJ ?? '',
            FORN_EMAIL: fornecedor.FORN_EMAIL ?? '',
            FORN_TELEFONE: fornecedor.FORN_TELEFONE ?? '',
            FORN_STATUS: fornecedor.FORN_STATUS,
        });
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleChange = (field: keyof FornecedorForm, value: string) => {
        setForm((current) => ({ ...current, [field]: value }));
    };

    const parseErrors = (payload: unknown): FormErrors => {
        if (!payload || typeof payload !== 'object' || !('errors' in payload)) return {};
        const payloadErrors = payload.errors as Record<string, string[]>;
        return {
            FORN_RAZAO: payloadErrors.FORN_RAZAO?.[0],
            FORN_CNPJ: payloadErrors.FORN_CNPJ?.[0],
            FORN_TELEFONE: payloadErrors.FORN_TELEFONE?.[0],
            FORN_EMAIL: payloadErrors.FORN_EMAIL?.[0],
            FORN_STATUS: payloadErrors.FORN_STATUS?.[0],
        };
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        setFeedback(null);

        const url = editingFornecedor
            ? `/cadastros/fornecedor/${editingFornecedor.FORN_CODIGO}`
            : '/cadastros/fornecedor';
        const method = editingFornecedor ? 'PUT' : 'POST';

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
                setFeedback({ type: 'error', message: payload.message ?? 'Falha ao salvar fornecedor.' });
                return;
            }

            setIsDialogOpen(false);
            setForm(initialForm);
            setFeedback({ type: 'success', message: payload.message ?? 'Fornecedor salvo com sucesso.' });
            router.reload({ only: ['fornecedores'] });
        } finally {
            setSubmitting(false);
        }
    };

    const toggleStatus = async (fornecedor: Fornecedor) => {
        setFeedback(null);

        const response = await fetchWithCsrf(
            `/cadastros/fornecedor/${fornecedor.FORN_CODIGO}/status`,
            { method: 'PATCH', headers: { Accept: 'application/json' } },
        );

        const payload = (await response.json().catch(() => ({}))) as { message?: string };

        if (response.status === 409) {
            setFeedback({ type: 'error', message: payload.message ?? 'Nao foi possivel alterar o status.' });
            return;
        }

        if (!response.ok) {
            setFeedback({ type: 'error', message: payload.message ?? 'Falha ao alterar status do fornecedor.' });
            return;
        }

        setFeedback({ type: 'success', message: payload.message ?? 'Status atualizado com sucesso.' });
        router.reload({ only: ['fornecedores'] });
    };

    return (
        <AppLayout>
            <Head title="Fornecedores" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Fornecedores"
                    description="Gerencie os fornecedores do sistema."
                    actions={<Button onClick={handleCreate}>Novo fornecedor</Button>}
                />

                {feedback && (
                    <Alert variant={feedback.type === 'error' ? 'destructive' : 'default'}>
                        <AlertTitle>{feedback.type === 'error' ? 'Erro' : 'Sucesso'}</AlertTitle>
                        <AlertDescription>{feedback.message}</AlertDescription>
                    </Alert>
                )}

                <DataCard contentClassName="p-0">
                    <DataTablePaginated data={fornecedores}>
                        {(rows) => (
                            <table className="w-full text-sm">
                                <thead className="bg-muted/45">
                                    <tr>
                                        <th className="px-4 py-2.5 text-left font-medium">Codigo</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Razao Social</th>
                                        <th className="px-4 py-2.5 text-left font-medium">CNPJ</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Email</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Telefone</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Status</th>
                                        <th className="px-4 py-2.5 text-right font-medium">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.length === 0 && (
                                        <tr>
                                            <td className="px-4 py-6 text-center text-muted-foreground" colSpan={7}>
                                                Nenhum fornecedor cadastrado.
                                            </td>
                                        </tr>
                                    )}
                                    {rows.map((fornecedor) => (
                                        <tr key={fornecedor.FORN_CODIGO} className="border-t">
                                            <td className="px-4 py-2.5">{fornecedor.FORN_CODIGO}</td>
                                            <td className="px-4 py-2.5">{fornecedor.FORN_RAZAO}</td>
                                            <td className="px-4 py-2.5">{fornecedor.FORN_CNPJ || '-'}</td>
                                            <td className="px-4 py-2.5">{fornecedor.FORN_EMAIL || '-'}</td>
                                            <td className="px-4 py-2.5">{fornecedor.FORN_TELEFONE || '-'}</td>
                                            <td className="px-4 py-2.5">
                                                <Badge
                                                    variant="secondary"
                                                    className={fornecedor.FORN_STATUS === 'A' ? 'border-primary/30 bg-primary/10 text-primary' : ''}
                                                >
                                                    {fornecedor.FORN_STATUS === 'A' ? 'Ativo' : 'Inativo'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-2.5 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Button size="sm" variant="outline" onClick={() => handleEdit(fornecedor)}>
                                                        Editar
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant={fornecedor.FORN_STATUS === 'A' ? 'outline' : 'default'}
                                                        onClick={() => toggleStatus(fornecedor)}
                                                    >
                                                        {fornecedor.FORN_STATUS === 'A' ? 'Inativar' : 'Ativar'}
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
                        <DialogTitle>{editingFornecedor ? 'Editar Fornecedor' : 'Novo Fornecedor'}</DialogTitle>
                        <DialogDescription>
                            Preencha os campos abaixo para {editingFornecedor ? 'atualizar' : 'cadastrar'} o fornecedor.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="FORN_RAZAO">Razao Social</Label>
                            <Input id="FORN_RAZAO" value={form.FORN_RAZAO} onChange={(e) => handleChange('FORN_RAZAO', e.target.value)} maxLength={50} />
                            <InputError message={errors.FORN_RAZAO} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="FORN_CNPJ">CNPJ</Label>
                            <Input id="FORN_CNPJ" value={form.FORN_CNPJ} onChange={(e) => handleChange('FORN_CNPJ', e.target.value)} maxLength={18} />
                            <InputError message={errors.FORN_CNPJ} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="FORN_EMAIL">Email</Label>
                            <Input id="FORN_EMAIL" type="email" value={form.FORN_EMAIL} onChange={(e) => handleChange('FORN_EMAIL', e.target.value)} maxLength={35} />
                            <InputError message={errors.FORN_EMAIL} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="FORN_TELEFONE">Telefone</Label>
                            <Input id="FORN_TELEFONE" value={form.FORN_TELEFONE} onChange={(e) => handleChange('FORN_TELEFONE', e.target.value)} maxLength={15} />
                            <InputError message={errors.FORN_TELEFONE} />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setIsDialogOpen(false)}>Cancelar</Button>
                        <Button type="button" onClick={submit} disabled={submitting}>
                            {submitting ? 'Salvando...' : 'Salvar'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}