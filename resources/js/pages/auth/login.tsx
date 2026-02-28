import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
};

export default function Login({ status, canResetPassword, canRegister }: Props) {
    return (
        <AuthLayout
            title="Bem-vindo de volta"
            description="Acesse o painel de controle da sua frota"
        >
            <Head title="Entrar" />

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-5"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-5">
                            {status && (
                                <div className="flex items-center gap-2.5 rounded-lg border border-emerald-500/20 bg-emerald-500/8 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                                    <svg className="h-4 w-4 shrink-0" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13ZM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8Zm11.28-1.72a.75.75 0 0 1 0 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-1.5-1.5a.75.75 0 0 1 1.06-1.06l.97.97 2.97-2.97a.75.75 0 0 1 1.06 0Z" fill="currentColor"/>
                                    </svg>
                                    {status}
                                </div>
                            )}

                            <div className="grid gap-1.5">
                                <Label htmlFor="email" className="text-sm font-medium text-foreground">
                                    E-mail corporativo
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder="seuemail@empresa.com"
                                    className="h-10 border-border/60 bg-background/50 transition-colors focus:border-lime-500 focus:ring-lime-500/20"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-1.5">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password" className="text-sm font-medium text-foreground">
                                        Senha
                                    </Label>
                                    {canResetPassword && (
                                        <TextLink
                                            href={request()}
                                            className="text-xs text-muted-foreground underline-offset-4 hover:text-foreground hover:underline"
                                            tabIndex={5}
                                        >
                                            Esqueci minha senha
                                        </TextLink>
                                    )}
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    placeholder="••••••••"
                                    className="h-10 border-border/60 bg-background/50 tracking-widest transition-colors focus:border-lime-500 focus:ring-lime-500/20"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center gap-2.5">
                                <Checkbox id="remember" name="remember" tabIndex={3} className="border-border/60 data-[state=checked]:border-lime-500 data-[state=checked]:bg-lime-500" />
                                <Label htmlFor="remember" className="cursor-pointer text-sm text-muted-foreground">
                                    Manter sessão ativa por 30 dias
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                className="mt-1 h-10 w-full gap-2 bg-lime-500 font-semibold text-zinc-950 hover:bg-lime-400 disabled:opacity-60"
                                tabIndex={4}
                                disabled={processing}
                                data-test="login-button"
                            >
                                {processing ? (
                                    <>
                                        <Spinner className="h-4 w-4" />
                                        Entrando…
                                    </>
                                ) : (
                                    <>
                                        <svg className="h-4 w-4" viewBox="0 0 16 16" fill="none">
                                            <path d="M3 8a.75.75 0 0 1 .75-.75h6.94L8.22 4.78a.75.75 0 0 1 1.06-1.06l3.5 3.5a.75.75 0 0 1 0 1.06l-3.5 3.5a.75.75 0 1 1-1.06-1.06l2.47-2.47H3.75A.75.75 0 0 1 3 8Z" fill="currentColor"/>
                                        </svg>
                                        Acessar painel
                                    </>
                                )}
                            </Button>
                        </div>

                        {canRegister && (
                            <p className="text-center text-sm text-muted-foreground">
                                Novo na plataforma?{' '}
                                <TextLink
                                    href={register()}
                                    tabIndex={6}
                                    className="font-medium text-foreground underline-offset-4 hover:underline"
                                >
                                    Criar conta
                                </TextLink>
                            </p>
                        )}
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}