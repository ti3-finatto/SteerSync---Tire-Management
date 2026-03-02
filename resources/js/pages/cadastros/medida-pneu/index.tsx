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
import type { LegacyStatus, MedidaPneu, MedidaPneuPageProps } from '@/types';

type Feedback = { type: 'success' | 'error'; message: string };

type FormErrors = Partial<Record<'MEDP_DESCRICAO' | 'CAL_RECOMENDADA' | 'MEDP_STATUS', string>>;

type MedidaPneuForm = {
    MEDP_DESCRICAO: string;
    CAL_RECOMENDADA: number | '';
    MEDP_STATUS: LegacyStatus;
};

const initialForm: MedidaPneuForm = {
    MEDP_DESCRICAO: '',
    CAL_RECOMENDADA: '',
    MEDP_STATUS: 'A',
};

export default function MedidaPneuIndex({ medidas, flash }: MedidaPneuPageProps) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingMedida, setEditingMedida] = useState<MedidaPneu | null>(null);
    const [form, setForm] = useState<MedidaPneuForm>(initialForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [submitting, setSubmitting] = useState(false);
    const [feedback, setFeedback] = useState<Feedback | null>(() => {
        if (flash?.success) return { type: 'success', message: flash.success };
        if (flash?.error) return { type: 'error', message: flash.error };
        return null;
    });

    const handleCreate = () => {
        setEditingMedida(null);
        setForm(initialForm);
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleEdit = (medida: MedidaPneu) => {
        setEditingMedida(medida);
        setForm({
            MEDP_DESCRICAO: medida.MEDP_DESCRICAO,
            CAL_RECOMENDADA: medida.CAL_RECOMENDADA ?? '',
            MEDP_STATUS: medida.MEDP_STATUS,
        });
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleChange = (field: keyof MedidaPneuForm, value: string | number) => {
        setForm((current) => ({ ...current, [field]: value }));
    };

    const parseErrors = (payload: unknown): FormErrors => {
        if (!payload || typeof payload !== 'object' || !('errors' in payload)) return {};
        const payloadErrors = payload.errors as Record<string, string[]>;
        return {
            MEDP_DESCRICAO: payloadErrors.MEDP_DESCRICAO?.[0],
            CAL_RECOMENDADA: payloadErrors.CAL_RECOMENDADA?.[0],
            MEDP_STATUS: payloadErrors.MEDP_STATUS?.[0],
        };
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        setFeedback(null);

        const url = editingMedida
            ? `/cadastros/medida-pneu/${editingMedida.MEDP_CODIGO}`
            : '/cadastros/medida-pneu';
        const method = editingMedida ? 'PUT' : 'POST';

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
                setFeedback({ type: 'error', message: payload.message ?? 'Falha ao salvar medida.' });
                return;
            }

            setIsDialogOpen(false);
            setForm(initialForm);
            setFeedback({ type: 'success', message: payload.message ?? 'Medida salva com sucesso.' });
            router.reload({ only: ['medidas'] });
        } finally {
            setSubmitting(false);
        }
    };

    const toggleStatus = async (medida: MedidaPneu) => {
        setFeedback(null);

        const response = await fetchWithCsrf(
            `/cadastros/medida-pneu/${medida.MEDP_CODIGO}/status`,
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
        router.reload({ only: ['medidas'] });
    };

    return (
        <AppLayout>
            <Head title="Medidas de Pneu" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Medidas de Pneu"
                    description="Gerencie as medidas e calibragens recomendadas dos pneus."
                    actions={<Button onClick={handleCreate}>Nova medida</Button>}
                />

                {feedback && (
                    <Alert variant={feedback.type === 'error' ? 'destructive' : 'default'}>
                        <AlertTitle>{feedback.type === 'error' ? 'Erro' : 'Sucesso'}</AlertTitle>
                        <AlertDescription>{feedback.message}</AlertDescription>
                    </Alert>
                )}

                <DataCard contentClassName="p-0">
                    <DataTablePaginated data={medidas}>
                        {(rows) => (
                            <table className="w-full text-sm">
                                <thead className="bg-muted/45">
                                    <tr>
                                        <th className="px-4 py-2.5 text-left font-medium">Codigo</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Descricao</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Calibragem Recomendada</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Status</th>
                                        <th className="px-4 py-2.5 text-right font-medium">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.length === 0 && (
                                        <tr>
                                            <td className="px-4 py-6 text-center text-muted-foreground" colSpan={5}>
                                                Nenhuma medida cadastrada.
                                            </td>
                                        </tr>
                                    )}
                                    {rows.map((medida) => (
                                        <tr key={medida.MEDP_CODIGO} className="border-t">
                                            <td className="px-4 py-2.5">{medida.MEDP_CODIGO}</td>
                                            <td className="px-4 py-2.5">{medida.MEDP_DESCRICAO}</td>
                                            <td className="px-4 py-2.5">
                                                {medida.CAL_RECOMENDADA !== null ? medida.CAL_RECOMENDADA : '-'}
                                            </td>
                                            <td className="px-4 py-2.5">
                                                <Badge
                                                    variant="secondary"
                                                    className={medida.MEDP_STATUS === 'A' ? 'border-primary/30 bg-primary/10 text-primary' : ''}
                                                >
                                                    {medida.MEDP_STATUS === 'A' ? 'Ativa' : 'Inativa'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-2.5 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Button size="sm" variant="outline" onClick={() => handleEdit(medida)}>
                                                        Editar
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant={medida.MEDP_STATUS === 'A' ? 'outline' : 'default'}
                                                        onClick={() => toggleStatus(medida)}
                                                    >
                                                        {medida.MEDP_STATUS === 'A' ? 'Inativar' : 'Ativar'}
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
                        <DialogTitle>{editingMedida ? 'Editar Medida' : 'Nova Medida de Pneu'}</DialogTitle>
                        <DialogDescription>
                            Preencha os campos abaixo para {editingMedida ? 'atualizar' : 'cadastrar'} a medida.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="MEDP_DESCRICAO">Descricao</Label>
                            <Input
                                id="MEDP_DESCRICAO"
                                value={form.MEDP_DESCRICAO}
                                onChange={(e) => handleChange('MEDP_DESCRICAO', e.target.value)}
                                maxLength={30}
                            />
                            <InputError message={errors.MEDP_DESCRICAO} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="CAL_RECOMENDADA">Calibragem Recomendada</Label>
                            <Input
                                id="CAL_RECOMENDADA"
                                type="number"
                                min={30}
                                max={150}
                                step="0.1"
                                value={form.CAL_RECOMENDADA}
                                onChange={(e) => handleChange('CAL_RECOMENDADA', e.target.value ? Number(e.target.value) : '')}
                            />
                            <InputError message={errors.CAL_RECOMENDADA} />
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
