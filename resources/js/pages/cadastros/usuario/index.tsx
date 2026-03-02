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
import type { LegacyFlash, Usuario, UsuarioPageProps } from '@/types';

type Feedback = { type: 'success' | 'error'; message: string };

type FormErrors = Partial<
    Record<'name' | 'email' | 'username' | 'cpf' | 'phone' | 'USU_TIPO' | 'status', string>
>;

type UsuarioForm = {
    name: string;
    email: string;
    username: string;
    cpf: string;
    phone: string;
    USU_TIPO: string;
    status: string;
};

const initialForm: UsuarioForm = {
    name: '',
    email: '',
    username: '',
    cpf: '',
    phone: '',
    USU_TIPO: 'N',
    status: 'ATIVO',
};

const selectClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring';

export default function UsuarioIndex({ usuarios, flash }: UsuarioPageProps) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [editingUsuario, setEditingUsuario] = useState<Usuario | null>(null);
    const [form, setForm] = useState<UsuarioForm>(initialForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [submitting, setSubmitting] = useState(false);
    const [feedback, setFeedback] = useState<Feedback | null>(() => {
        if (flash?.success) return { type: 'success', message: flash.success };
        if (flash?.error) return { type: 'error', message: flash.error };
        return null;
    });

    const handleCreate = () => {
        setEditingUsuario(null);
        setForm(initialForm);
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleEdit = (usuario: Usuario) => {
        setEditingUsuario(usuario);
        setForm({
            name: usuario.name,
            email: usuario.email,
            username: usuario.username ?? '',
            cpf: usuario.cpf ?? '',
            phone: usuario.phone ?? '',
            USU_TIPO: usuario.USU_TIPO,
            status: usuario.status,
        });
        setErrors({});
        setIsDialogOpen(true);
    };

    const handleChange = (field: keyof UsuarioForm, value: string) => {
        setForm((current) => ({ ...current, [field]: value }));
    };

    const parseErrors = (payload: unknown): FormErrors => {
        if (!payload || typeof payload !== 'object' || !('errors' in payload)) return {};
        const payloadErrors = payload.errors as Record<string, string[]>;
        return {
            name: payloadErrors.name?.[0],
            email: payloadErrors.email?.[0],
            username: payloadErrors.username?.[0],
            cpf: payloadErrors.cpf?.[0],
            phone: payloadErrors.phone?.[0],
            USU_TIPO: payloadErrors.USU_TIPO?.[0],
            status: payloadErrors.status?.[0],
        };
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        setFeedback(null);

        const url = editingUsuario
            ? `/cadastros/usuario/${editingUsuario.id}`
            : '/cadastros/usuario';
        const method = editingUsuario ? 'PUT' : 'POST';

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
                setFeedback({ type: 'error', message: payload.message ?? 'Falha ao salvar usuario.' });
                return;
            }

            setIsDialogOpen(false);
            setForm(initialForm);
            setFeedback({ type: 'success', message: payload.message ?? 'Usuario salvo com sucesso.' });
            router.reload({ only: ['usuarios'] });
        } finally {
            setSubmitting(false);
        }
    };

    const toggleStatus = async (usuario: Usuario) => {
        setFeedback(null);

        const response = await fetchWithCsrf(
            `/cadastros/usuario/${usuario.id}/status`,
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
        router.reload({ only: ['usuarios'] });
    };

    const resetPassword = async (usuario: Usuario) => {
        setFeedback(null);

        const response = await fetchWithCsrf(
            `/cadastros/usuario/${usuario.id}/reset-password`,
            { method: 'PATCH', headers: { Accept: 'application/json' } },
        );

        const payload = (await response.json().catch(() => ({}))) as { message?: string };

        if (!response.ok) {
            setFeedback({ type: 'error', message: payload.message ?? 'Falha ao redefinir senha.' });
            return;
        }

        setFeedback({ type: 'success', message: payload.message ?? 'Senha redefinida com sucesso.' });
    };

    return (
        <AppLayout>
            <Head title="Usuarios" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Usuarios"
                    description="Gerencie os usuarios do sistema."
                    actions={<Button onClick={handleCreate}>Novo usuario</Button>}
                />

                {feedback && (
                    <Alert variant={feedback.type === 'error' ? 'destructive' : 'default'}>
                        <AlertTitle>{feedback.type === 'error' ? 'Erro' : 'Sucesso'}</AlertTitle>
                        <AlertDescription>{feedback.message}</AlertDescription>
                    </Alert>
                )}

                <DataCard contentClassName="p-0">
                    <DataTablePaginated data={usuarios}>
                        {(rows) => (
                            <table className="w-full text-sm">
                                <thead className="bg-muted/45">
                                    <tr>
                                        <th className="px-4 py-2.5 text-left font-medium">ID</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Nome</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Username</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Email</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Tipo</th>
                                        <th className="px-4 py-2.5 text-left font-medium">Status</th>
                                        <th className="px-4 py-2.5 text-right font-medium">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.length === 0 && (
                                        <tr>
                                            <td className="px-4 py-6 text-center text-muted-foreground" colSpan={7}>
                                                Nenhum usuario cadastrado.
                                            </td>
                                        </tr>
                                    )}
                                    {rows.map((usuario) => (
                                        <tr key={usuario.id} className="border-t">
                                            <td className="px-4 py-2.5">{usuario.id}</td>
                                            <td className="px-4 py-2.5">{usuario.name}</td>
                                            <td className="px-4 py-2.5">{usuario.username || '-'}</td>
                                            <td className="px-4 py-2.5">{usuario.email}</td>
                                            <td className="px-4 py-2.5">
                                                <Badge variant="outline">
                                                    {usuario.USU_TIPO === 'A' ? 'Administrador' : 'Operador'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-2.5">
                                                <Badge
                                                    variant="secondary"
                                                    className={usuario.status === 'ATIVO' ? 'border-primary/30 bg-primary/10 text-primary' : ''}
                                                >
                                                    {usuario.status === 'ATIVO' ? 'Ativo' : 'Inativo'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-2.5 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Button size="sm" variant="outline" onClick={() => handleEdit(usuario)}>
                                                        Editar
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant={usuario.status === 'ATIVO' ? 'outline' : 'default'}
                                                        onClick={() => toggleStatus(usuario)}
                                                    >
                                                        {usuario.status === 'ATIVO' ? 'Inativar' : 'Ativar'}
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => resetPassword(usuario)}
                                                    >
                                                        Resetar senha
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
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle>{editingUsuario ? 'Editar Usuario' : 'Novo Usuario'}</DialogTitle>
                        <DialogDescription>
                            {editingUsuario
                                ? 'Atualize os dados do usuario.'
                                : 'Preencha os campos abaixo. A senha inicial sera 12345.'}
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Nome completo</Label>
                            <Input
                                id="name"
                                value={form.name}
                                onChange={(e) => handleChange('name', e.target.value)}
                                maxLength={100}
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="username">Username</Label>
                                <Input
                                    id="username"
                                    value={form.username}
                                    onChange={(e) => handleChange('username', e.target.value)}
                                    maxLength={40}
                                />
                                <InputError message={errors.username} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="cpf">CPF</Label>
                                <Input
                                    id="cpf"
                                    value={form.cpf}
                                    onChange={(e) => handleChange('cpf', e.target.value)}
                                    maxLength={14}
                                    placeholder="000.000.000-00"
                                />
                                <InputError message={errors.cpf} />
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                value={form.email}
                                onChange={(e) => handleChange('email', e.target.value)}
                                maxLength={100}
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="phone">Telefone</Label>
                                <Input
                                    id="phone"
                                    value={form.phone}
                                    onChange={(e) => handleChange('phone', e.target.value)}
                                    maxLength={20}
                                    placeholder="(00) 00000-0000"
                                />
                                <InputError message={errors.phone} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="USU_TIPO">Perfil</Label>
                                <select
                                    id="USU_TIPO"
                                    className={selectClass}
                                    value={form.USU_TIPO}
                                    onChange={(e) => handleChange('USU_TIPO', e.target.value)}
                                >
                                    <option value="N">Operador</option>
                                    <option value="A">Administrador</option>
                                </select>
                                <InputError message={errors.USU_TIPO} />
                            </div>
                        </div>

                        {editingUsuario && (
                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <select
                                    id="status"
                                    className={selectClass}
                                    value={form.status}
                                    onChange={(e) => handleChange('status', e.target.value)}
                                >
                                    <option value="ATIVO">Ativo</option>
                                    <option value="INATIVO">Inativo</option>
                                </select>
                                <InputError message={errors.status} />
                            </div>
                        )}
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
