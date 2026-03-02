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
import type { LegacyStatus, MarcaVeiculo, MarcaVeiculoPageProps } from '@/types';

type Feedback = { type: 'success' | 'error'; message: string };

type FormErrors = Partial<Record<'MARV_DESCRICAO' | 'MARV_STATUS', string>>;

type MarcaVeiculoForm = {
    MARV_DESCRICAO: string;
    MARV_STATUS: LegacyStatus;
};

const initialForm: MarcaVeiculoForm = {
    MARV_DESCRICAO: '',
    MARV_STATUS: 'A',
};

export default function MarcaVeiculoIndex({ marcas, flash }: MarcaVeiculoPageProps) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingMarca, setEditingMarca] = useState<MarcaVeiculo | null>(null);
    const [form, setForm] = useState<MarcaVeiculoForm>(initialForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [submitting, setSubmitting] = useState(false);
    const [feedback, setFeedback] = useState<Feedback | null>(() => {
        if (flash?.success) return { type: 'success', message: flash.success };
        if (flash?.error) return { type: 'error', message: flash.error };
        return null;
    });

    const handleCreate = () => {
        setEditingMarca(null);
        setForm(initialForm);
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleEdit = (marca: MarcaVeiculo) => {
        setEditingMarca(marca);
        setForm({
            MARV_DESCRICAO: marca.MARV_DESCRICAO,
            MARV_STATUS: marca.MARV_STATUS,
        });
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleChange = (field: keyof MarcaVeiculoForm, value: string) => {
        setForm((current) => ({ ...current, [field]: value }));
    };

    const parseErrors = (payload: unknown): FormErrors => {
        if (!payload || typeof payload !== 'object' || !('errors' in payload)) return {};
        const payloadErrors = payload.errors as Record<string, string[]>;
        return {
            MARV_DESCRICAO: payloadErrors.MARV_DESCRICAO?.[0],
            MARV_STATUS: payloadErrors.MARV_STATUS?.[0],
        };
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        setFeedback(null);

        const url = editingMarca
            ? `/cadastros/marca-veiculo/${editingMarca.MARV_CODIGO}`
            : '/cadastros/marca-veiculo';
        const method = editingMarca ? 'PUT' : 'POST';

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
                setFeedback({ type: 'error', message: payload.message ?? 'Falha ao salvar marca.' });
                return;
            }

            setIsDialogOpen(false);
            setForm(initialForm);
            setFeedback({ type: 'success', message: payload.message ?? 'Marca salva com sucesso.' });
            router.reload({ only: ['marcas'] });
        } finally {
            setSubmitting(false);
        }
    };

    const toggleStatus = async (marca: MarcaVeiculo) => {
        setFeedback(null);

        const response = await fetchWithCsrf(
            `/cadastros/marca-veiculo/${marca.MARV_CODIGO}/status`,
            { method: 'PATCH', headers: { Accept: 'application/json' } },
        );

        const payload = (await response.json().catch(() => ({}))) as { message?: string };

        if (!response.ok) {
            setFeedback({ type: 'error', message: payload.message ?? 'Falha ao alterar status.' });
            return;
        }

        setFeedback({ type: 'success', message: payload.message ?? 'Status atualizado com sucesso.' });
        router.reload({ only: ['marcas'] });
    };

    return (
        <AppLayout>
            <Head title="Marcas de Veiculo" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Marcas de Veiculo"
                    description="Gerencie as marcas de veiculo do sistema."
                    actions={<Button onClick={handleCreate}>Nova marca</Button>}
                />

                {feedback && (
                    <Alert variant={feedback.type === 'error' ? 'destructive' : 'default'}>
                        <AlertTitle>{feedback.type === 'error' ? 'Erro' : 'Sucesso'}</AlertTitle>
                        <AlertDescription>{feedback.message}</AlertDescription>
                    </Alert>
                )}

                <DataCard contentClassName="p-0">
                    <DataTablePaginated data={marcas}>
                        {(rows) => (
                            <table className="w-full text-sm">
                                <thead className="bg-muted/45">
                                    <tr>
                                        <th className="px-4 py-2.5 text-left font-medium">Codigo</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Descricao</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Status</th>
                                        <th className="px-4 py-2.5 text-right font-medium">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.length === 0 && (
                                        <tr>
                                            <td className="px-4 py-6 text-center text-muted-foreground" colSpan={4}>
                                                Nenhuma marca cadastrada.
                                            </td>
                                        </tr>
                                    )}
                                    {rows.map((marca) => (
                                        <tr key={marca.MARV_CODIGO} className="border-t">
                                            <td className="px-4 py-2.5">{marca.MARV_CODIGO}</td>
                                            <td className="px-4 py-2.5">{marca.MARV_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">
                                                <Badge
                                                    variant="secondary"
                                                    className={marca.MARV_STATUS === 'A' ? 'border-primary/30 bg-primary/10 text-primary' : ''}
                                                >
                                                    {marca.MARV_STATUS === 'A' ? 'Ativa' : 'Inativa'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-2.5 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Button size="sm" variant="outline" onClick={() => handleEdit(marca)}>
                                                        Editar
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant={marca.MARV_STATUS === 'A' ? 'outline' : 'default'}
                                                        onClick={() => toggleStatus(marca)}
                                                    >
                                                        {marca.MARV_STATUS === 'A' ? 'Inativar' : 'Ativar'}
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
                        <DialogTitle>{editingMarca ? 'Editar Marca' : 'Nova Marca de Veiculo'}</DialogTitle>
                        <DialogDescription>
                            Preencha os campos abaixo para {editingMarca ? 'atualizar' : 'cadastrar'} a marca.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="MARV_DESCRICAO">Descricao</Label>
                            <Input
                                id="MARV_DESCRICAO"
                                value={form.MARV_DESCRICAO}
                                onChange={(e) => handleChange('MARV_DESCRICAO', e.target.value)}
                                maxLength={30}
                            />
                            <InputError message={errors.MARV_DESCRICAO} />
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
