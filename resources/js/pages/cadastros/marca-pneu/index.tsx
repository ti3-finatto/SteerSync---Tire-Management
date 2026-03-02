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
import type { LegacyStatus, MarcaPneu, MarcaPneuPageProps } from '@/types';

type Feedback = { type: 'success' | 'error'; message: string };

type FormErrors = Partial<Record<'MARP_DESCRICAO' | 'MARP_TIPO' | 'MARP_STATUS', string>>;

type MarcaPneuForm = {
    MARP_DESCRICAO: string;
    MARP_TIPO: string;
    MARP_STATUS: LegacyStatus;
};

const initialForm: MarcaPneuForm = {
    MARP_DESCRICAO: '',
    MARP_TIPO: 'P',
    MARP_STATUS: 'A',
};

const selectClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring';

export default function MarcaPneuIndex({ marcas, flash }: MarcaPneuPageProps) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingMarca, setEditingMarca] = useState<MarcaPneu | null>(null);
    const [form, setForm] = useState<MarcaPneuForm>(initialForm);
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

    const handleEdit = (marca: MarcaPneu) => {
        setEditingMarca(marca);
        setForm({
            MARP_DESCRICAO: marca.MARP_DESCRICAO,
            MARP_TIPO: marca.MARP_TIPO,
            MARP_STATUS: marca.MARP_STATUS,
        });
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleChange = (field: keyof MarcaPneuForm, value: string) => {
        setForm((current) => ({ ...current, [field]: value }));
    };

    const parseErrors = (payload: unknown): FormErrors => {
        if (!payload || typeof payload !== 'object' || !('errors' in payload)) return {};
        const payloadErrors = payload.errors as Record<string, string[]>;
        return {
            MARP_DESCRICAO: payloadErrors.MARP_DESCRICAO?.[0],
            MARP_TIPO: payloadErrors.MARP_TIPO?.[0],
            MARP_STATUS: payloadErrors.MARP_STATUS?.[0],
        };
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        setFeedback(null);

        const url = editingMarca
            ? `/cadastros/marca-pneu/${editingMarca.MARP_CODIGO}`
            : '/cadastros/marca-pneu';
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

    const toggleStatus = async (marca: MarcaPneu) => {
        setFeedback(null);

        const response = await fetchWithCsrf(
            `/cadastros/marca-pneu/${marca.MARP_CODIGO}/status`,
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
        router.reload({ only: ['marcas'] });
    };

    return (
        <AppLayout>
            <Head title="Marcas de Pneu" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Marcas de Pneu"
                    description="Gerencie as marcas de pneu do sistema."
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
                                        <th className="px-4 py-2.5 text-left font-medium">Tipo</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Status</th>
                                        <th className="px-4 py-2.5 text-right font-medium">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.length === 0 && (
                                        <tr>
                                            <td className="px-4 py-6 text-center text-muted-foreground" colSpan={5}>
                                                Nenhuma marca cadastrada.
                                            </td>
                                        </tr>
                                    )}
                                    {rows.map((marca) => (
                                        <tr key={marca.MARP_CODIGO} className="border-t">
                                            <td className="px-4 py-2.5">{marca.MARP_CODIGO}</td>
                                            <td className="px-4 py-2.5">{marca.MARP_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">
                                                {marca.MARP_TIPO === 'P' ? 'Pneu novo' : 'Recapagem'}
                                            </td>
                                            <td className="px-4 py-2.5">
                                                <Badge
                                                    variant="secondary"
                                                    className={marca.MARP_STATUS === 'A' ? 'border-primary/30 bg-primary/10 text-primary' : ''}
                                                >
                                                    {marca.MARP_STATUS === 'A' ? 'Ativa' : 'Inativa'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-2.5 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Button size="sm" variant="outline" onClick={() => handleEdit(marca)}>
                                                        Editar
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant={marca.MARP_STATUS === 'A' ? 'outline' : 'default'}
                                                        onClick={() => toggleStatus(marca)}
                                                    >
                                                        {marca.MARP_STATUS === 'A' ? 'Inativar' : 'Ativar'}
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
                        <DialogTitle>{editingMarca ? 'Editar Marca' : 'Nova Marca de Pneu'}</DialogTitle>
                        <DialogDescription>
                            Preencha os campos abaixo para {editingMarca ? 'atualizar' : 'cadastrar'} a marca.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="MARP_DESCRICAO">Descricao</Label>
                            <Input
                                id="MARP_DESCRICAO"
                                value={form.MARP_DESCRICAO}
                                onChange={(e) => handleChange('MARP_DESCRICAO', e.target.value)}
                                maxLength={30}
                            />
                            <InputError message={errors.MARP_DESCRICAO} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="MARP_TIPO">Tipo</Label>
                            <select
                                id="MARP_TIPO"
                                className={selectClass}
                                value={form.MARP_TIPO}
                                onChange={(e) => handleChange('MARP_TIPO', e.target.value)}
                            >
                                <option value="P">Pneu novo</option>
                                <option value="R">Recapagem</option>
                            </select>
                            <InputError message={errors.MARP_TIPO} />
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
