import { Head, router } from '@inertiajs/react';
import { Lock } from 'lucide-react';
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
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import AppLayout from '@/layouts/app-layout';
import { fetchWithCsrf } from '@/lib/http';
import type { TipoVeiculo, TipoVeiculoPageProps } from '@/types';

type Feedback = { type: 'success' | 'error'; message: string };
type FormErrors = Partial<Record<'TPVE_SIGLA' | 'TPVE_DESCRICAO', string>>;

type TipoVeiculoForm = {
    TPVE_SIGLA: string;
    TPVE_DESCRICAO: string;
};

const initialForm: TipoVeiculoForm = { TPVE_SIGLA: '', TPVE_DESCRICAO: '' };

export default function TipoVeiculoIndex({ tipos, flash }: TipoVeiculoPageProps) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editing, setEditing] = useState<TipoVeiculo | null>(null);
    const [form, setForm] = useState<TipoVeiculoForm>(initialForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [submitting, setSubmitting] = useState(false);
    const [feedback, setFeedback] = useState<Feedback | null>(() => {
        if (flash?.success) return { type: 'success', message: flash.success };
        if (flash?.error)   return { type: 'error',   message: flash.error };
        return null;
    });

    const handleCreate = () => {
        setEditing(null);
        setForm(initialForm);
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleEdit = (tipo: TipoVeiculo) => {
        setEditing(tipo);
        setForm({ TPVE_SIGLA: tipo.TPVE_SIGLA, TPVE_DESCRICAO: tipo.TPVE_DESCRICAO });
        setErrors({});
        setIsDialogOpen(true);
    };

    const parseErrors = (payload: unknown): FormErrors => {
        if (!payload || typeof payload !== 'object' || !('errors' in payload)) return {};
        const e = payload.errors as Record<string, string[]>;
        return {
            TPVE_SIGLA:     e.TPVE_SIGLA?.[0],
            TPVE_DESCRICAO: e.TPVE_DESCRICAO?.[0],
        };
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        setFeedback(null);

        const url    = editing ? `/cadastros/tipo-veiculo/${editing.TPVE_SIGLA}` : '/cadastros/tipo-veiculo';
        const method = editing ? 'PUT' : 'POST';
        const body   = editing ? { TPVE_DESCRICAO: form.TPVE_DESCRICAO } : form;

        try {
            const response = await fetchWithCsrf(url, {
                method,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(body),
            });

            const payload = (await response.json().catch(() => ({}))) as {
                message?: string;
                errors?: Record<string, string[]>;
            };

            if (response.status === 422) {
                setErrors(parseErrors(payload));
                setFeedback({ type: 'error', message: payload.message ?? 'Existem campos invalidos.' });
                return;
            }

            if (response.status === 409) {
                setFeedback({ type: 'error', message: payload.message ?? 'Conflito de dados.' });
                return;
            }

            if (!response.ok) {
                setFeedback({ type: 'error', message: payload.message ?? 'Falha ao salvar tipo.' });
                return;
            }

            setIsDialogOpen(false);
            setForm(initialForm);
            setFeedback({ type: 'success', message: payload.message ?? 'Tipo salvo com sucesso.' });
            router.reload({ only: ['tipos'] });
        } finally {
            setSubmitting(false);
        }
    };

    const toggleStatus = async (tipo: TipoVeiculo) => {
        setFeedback(null);

        const response = await fetchWithCsrf(
            `/cadastros/tipo-veiculo/${tipo.TPVE_SIGLA}/status`,
            { method: 'PATCH', headers: { Accept: 'application/json' } },
        );

        const payload = (await response.json().catch(() => ({}))) as { message?: string };

        if (!response.ok) {
            setFeedback({ type: 'error', message: payload.message ?? 'Falha ao alterar status.' });
            return;
        }

        setFeedback({ type: 'success', message: payload.message ?? 'Status atualizado.' });
        router.reload({ only: ['tipos'] });
    };

    return (
        <AppLayout>
            <Head title="Tipos de Veiculo" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Tipos de Veículo"
                    description="Gerencie os tipos de veículo disponíveis. Tipos padrão não podem ser editados ou inativados."
                    actions={<Button onClick={handleCreate}>Novo tipo</Button>}
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
                            <TooltipProvider>
                                <table className="w-full text-sm">
                                    <thead className="bg-muted/45">
                                        <tr>
                                            <th className="px-4 py-2.5 text-left font-medium">Sigla</th>
                                            <th className="px-4 py-2.5 text-left font-medium">Descrição</th>
                                            <th className="px-4 py-2.5 text-left font-medium">Tipo</th>
                                            <th className="px-4 py-2.5 text-left font-medium">Status</th>
                                            <th className="px-4 py-2.5 text-right font-medium">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {rows.length === 0 && (
                                            <tr>
                                                <td className="px-4 py-6 text-center text-muted-foreground" colSpan={5}>
                                                    Nenhum tipo cadastrado.
                                                </td>
                                            </tr>
                                        )}
                                        {rows.map((tipo) => (
                                            <tr key={tipo.TPVE_SIGLA} className="border-t">
                                                <td className="px-4 py-2.5 font-mono font-medium">
                                                    {tipo.TPVE_SIGLA}
                                                </td>
                                                <td className="px-4 py-2.5">{tipo.TPVE_DESCRICAO}</td>
                                                <td className="px-4 py-2.5">
                                                    {tipo.TPVE_PADRAO ? (
                                                        <Badge variant="outline" className="gap-1 text-xs">
                                                            <Lock className="h-3 w-3" />
                                                            Padrão
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="secondary" className="text-xs">
                                                            Personalizado
                                                        </Badge>
                                                    )}
                                                </td>
                                                <td className="px-4 py-2.5">
                                                    <Badge
                                                        variant="secondary"
                                                        className={tipo.TPVE_STATUS === 'A' ? 'border-primary/30 bg-primary/10 text-primary' : ''}
                                                    >
                                                        {tipo.TPVE_STATUS === 'A' ? 'Ativo' : 'Inativo'}
                                                    </Badge>
                                                </td>
                                                <td className="px-4 py-2.5 text-right">
                                                    <div className="flex justify-end gap-2">
                                                        {tipo.TPVE_PADRAO ? (
                                                            <Tooltip>
                                                                <TooltipTrigger asChild>
                                                                    <span>
                                                                        <Button size="sm" variant="outline" disabled>
                                                                            Editar
                                                                        </Button>
                                                                    </span>
                                                                </TooltipTrigger>
                                                                <TooltipContent>Tipos padrão não podem ser editados</TooltipContent>
                                                            </Tooltip>
                                                        ) : (
                                                            <Button size="sm" variant="outline" onClick={() => handleEdit(tipo)}>
                                                                Editar
                                                            </Button>
                                                        )}

                                                        {tipo.TPVE_PADRAO ? (
                                                            <Tooltip>
                                                                <TooltipTrigger asChild>
                                                                    <span>
                                                                        <Button size="sm" variant="outline" disabled>
                                                                            {tipo.TPVE_STATUS === 'A' ? 'Inativar' : 'Ativar'}
                                                                        </Button>
                                                                    </span>
                                                                </TooltipTrigger>
                                                                <TooltipContent>Tipos padrão não podem ser inativados</TooltipContent>
                                                            </Tooltip>
                                                        ) : (
                                                            <Button
                                                                size="sm"
                                                                variant={tipo.TPVE_STATUS === 'A' ? 'outline' : 'default'}
                                                                onClick={() => toggleStatus(tipo)}
                                                            >
                                                                {tipo.TPVE_STATUS === 'A' ? 'Inativar' : 'Ativar'}
                                                            </Button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </TooltipProvider>
                        )}
                    </DataTablePaginated>
                </DataCard>
            </PageContainer>

            <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{editing ? 'Editar Tipo de Veículo' : 'Novo Tipo de Veículo'}</DialogTitle>
                        <DialogDescription>
                            {editing
                                ? 'Altere a descrição do tipo de veículo.'
                                : 'Defina a sigla e a descrição do novo tipo.'}
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        {!editing && (
                            <div className="grid gap-2">
                                <Label htmlFor="TPVE_SIGLA">
                                    Sigla
                                    <span className="ml-1 text-xs text-muted-foreground">(máx. 5 caracteres, única)</span>
                                </Label>
                                <Input
                                    id="TPVE_SIGLA"
                                    maxLength={5}
                                    placeholder="ex: VAN"
                                    value={form.TPVE_SIGLA}
                                    onChange={(e) =>
                                        setForm((f) => ({
                                            ...f,
                                            TPVE_SIGLA: e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase(),
                                        }))
                                    }
                                />
                                <InputError message={errors.TPVE_SIGLA} />
                            </div>
                        )}

                        <div className="grid gap-2">
                            <Label htmlFor="TPVE_DESCRICAO">Descrição</Label>
                            <Input
                                id="TPVE_DESCRICAO"
                                maxLength={50}
                                placeholder="ex: Van"
                                value={form.TPVE_DESCRICAO}
                                onChange={(e) =>
                                    setForm((f) => ({ ...f, TPVE_DESCRICAO: e.target.value }))
                                }
                            />
                            <InputError message={errors.TPVE_DESCRICAO} />
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
