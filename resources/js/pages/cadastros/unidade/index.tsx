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
import type { LegacyStatus, Unidade, UnidadePageProps } from '@/types';

type Feedback = {
    type: 'success' | 'error';
    message: string;
};

type FormErrors = Partial<
    Record<
        'UNI_DESCRICAO' | 'CLI_CNPJ' | 'CLI_UF' | 'CLI_CIDADE' | 'UNI_STATUS',
        string
    >
>;

type UnidadeForm = {
    UNI_DESCRICAO: string;
    CLI_CNPJ: string;
    CLI_UF: string;
    CLI_CIDADE: string;
    UNI_STATUS: LegacyStatus;
};

const initialForm: UnidadeForm = {
    UNI_DESCRICAO: '',
    CLI_CNPJ: '',
    CLI_UF: '',
    CLI_CIDADE: '',
    UNI_STATUS: 'A',
};

export default function UnidadeIndex({ unidades, flash }: UnidadePageProps) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingUnidade, setEditingUnidade] = useState<Unidade | null>(null);
    const [form, setForm] = useState<UnidadeForm>(initialForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [submitting, setSubmitting] = useState(false);
    const [feedback, setFeedback] = useState<Feedback | null>(() => {
        if (flash?.success) return { type: 'success', message: flash.success };
        if (flash?.error) return { type: 'error', message: flash.error };
        return null;
    });

    const handleCreate = () => {
        setEditingUnidade(null);
        setForm(initialForm);
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleEdit = (unidade: Unidade) => {
        setEditingUnidade(unidade);
        setForm({
            UNI_DESCRICAO: unidade.UNI_DESCRICAO,
            CLI_CNPJ: unidade.CLI_CNPJ ?? '',
            CLI_UF: unidade.CLI_UF ?? '',
            CLI_CIDADE: unidade.CLI_CIDADE ?? '',
            UNI_STATUS: unidade.UNI_STATUS,
        });
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleChange = (field: keyof UnidadeForm, value: string) => {
        setForm((current) => ({
            ...current,
            [field]: field === 'CLI_UF' ? value.toUpperCase() : value,
        }));
    };

    const parseErrors = (payload: unknown): FormErrors => {
        if (!payload || typeof payload !== 'object' || !('errors' in payload)) return {};
        const payloadErrors = payload.errors as Record<string, string[]>;
        return {
            UNI_DESCRICAO: payloadErrors.UNI_DESCRICAO?.[0],
            CLI_CNPJ: payloadErrors.CLI_CNPJ?.[0],
            CLI_UF: payloadErrors.CLI_UF?.[0],
            CLI_CIDADE: payloadErrors.CLI_CIDADE?.[0],
            UNI_STATUS: payloadErrors.UNI_STATUS?.[0],
        };
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        setFeedback(null);

        const url = editingUnidade
            ? `/cadastros/unidade/${editingUnidade.UNI_CODIGO}`
            : '/cadastros/unidade';
        const method = editingUnidade ? 'PUT' : 'POST';

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
                setFeedback({ type: 'error', message: payload.message ?? 'Falha ao salvar unidade.' });
                return;
            }

            setIsDialogOpen(false);
            setForm(initialForm);
            setFeedback({ type: 'success', message: payload.message ?? 'Unidade salva com sucesso.' });
            router.reload({ only: ['unidades'] });
        } finally {
            setSubmitting(false);
        }
    };

    const toggleStatus = async (unidade: Unidade) => {
        setFeedback(null);

        const response = await fetchWithCsrf(
            `/cadastros/unidade/${unidade.UNI_CODIGO}/status`,
            { method: 'PATCH', headers: { Accept: 'application/json' } },
        );

        const payload = (await response.json().catch(() => ({}))) as { message?: string };

        if (response.status === 409) {
            setFeedback({ type: 'error', message: payload.message ?? 'Nao foi possivel alterar o status.' });
            return;
        }

        if (!response.ok) {
            setFeedback({ type: 'error', message: payload.message ?? 'Falha ao alterar status da unidade.' });
            return;
        }

        setFeedback({ type: 'success', message: payload.message ?? 'Status atualizado com sucesso.' });
        router.reload({ only: ['unidades'] });
    };

    return (
        <AppLayout>
            <Head title="Unidades" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Unidades"
                    description="Gerencie as unidades do sistema."
                    actions={<Button onClick={handleCreate}>Nova unidade</Button>}
                />

                {feedback && (
                    <Alert variant={feedback.type === 'error' ? 'destructive' : 'default'}>
                        <AlertTitle>{feedback.type === 'error' ? 'Erro' : 'Sucesso'}</AlertTitle>
                        <AlertDescription>{feedback.message}</AlertDescription>
                    </Alert>
                )}

                <DataCard contentClassName="p-0">
                    <DataTablePaginated data={unidades}>
                        {(rows) => (
                            <table className="w-full text-sm">
                                <thead className="bg-muted/45">
                                    <tr>
                                        <th className="px-4 py-2.5 text-left font-medium">Codigo</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Descricao</th>
                                        <th className="px-4 py-2.5 text-left font-medium">CNPJ</th>
                                        <th className="px-4 py-2.5 text-left font-medium">UF</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Cidade</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Vinculos</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Status</th>
                                        <th className="px-4 py-2.5 text-right font-medium">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.length === 0 && (
                                        <tr>
                                            <td className="px-4 py-6 text-center text-muted-foreground" colSpan={8}>
                                                Nenhuma unidade cadastrada.
                                            </td>
                                        </tr>
                                    )}
                                    {rows.map((unidade) => (
                                        <tr key={unidade.UNI_CODIGO} className="border-t">
                                            <td className="px-4 py-2.5">{unidade.UNI_CODIGO}</td>
                                            <td className="px-4 py-2.5">{unidade.UNI_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">{unidade.CLI_CNPJ || '-'}</td>
                                            <td className="px-4 py-2.5">{unidade.CLI_UF || '-'}</td>
                                            <td className="px-4 py-2.5">{unidade.CLI_CIDADE || '-'}</td>
                                            <td className="px-4 py-2.5">
                                                P: {unidade.pneus_count ?? 0} | V: {unidade.veiculos_count ?? 0}
                                            </td>
                                            <td className="px-4 py-2.5">
                                                <Badge
                                                    variant="secondary"
                                                    className={unidade.UNI_STATUS === 'A' ? 'border-primary/30 bg-primary/10 text-primary' : ''}
                                                >
                                                    {unidade.UNI_STATUS === 'A' ? 'Ativa' : 'Inativa'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-2.5 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Button size="sm" variant="outline" onClick={() => handleEdit(unidade)}>
                                                        Editar
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant={unidade.UNI_STATUS === 'A' ? 'outline' : 'default'}
                                                        onClick={() => toggleStatus(unidade)}
                                                    >
                                                        {unidade.UNI_STATUS === 'A' ? 'Inativar' : 'Ativar'}
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
                        <DialogTitle>{editingUnidade ? 'Editar Unidade' : 'Nova Unidade'}</DialogTitle>
                        <DialogDescription>
                            Preencha os campos abaixo para {editingUnidade ? 'atualizar' : 'cadastrar'} a unidade.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="UNI_DESCRICAO">Descricao</Label>
                            <Input id="UNI_DESCRICAO" value={form.UNI_DESCRICAO} onChange={(e) => handleChange('UNI_DESCRICAO', e.target.value)} maxLength={40} />
                            <InputError message={errors.UNI_DESCRICAO} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="CLI_CNPJ">CNPJ</Label>
                            <Input id="CLI_CNPJ" value={form.CLI_CNPJ} onChange={(e) => handleChange('CLI_CNPJ', e.target.value)} maxLength={18} />
                            <InputError message={errors.CLI_CNPJ} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="CLI_UF">UF</Label>
                            <Input id="CLI_UF" value={form.CLI_UF} onChange={(e) => handleChange('CLI_UF', e.target.value)} maxLength={2} />
                            <InputError message={errors.CLI_UF} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="CLI_CIDADE">Cidade</Label>
                            <Input id="CLI_CIDADE" value={form.CLI_CIDADE} onChange={(e) => handleChange('CLI_CIDADE', e.target.value)} maxLength={60} />
                            <InputError message={errors.CLI_CIDADE} />
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