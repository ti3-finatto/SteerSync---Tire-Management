import { Head } from '@inertiajs/react';
import DataCard from '@/components/DataCard';
import PageContent from '@/components/PageContent';
import PageHeader from '@/components/PageHeader';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <PageContent>
                <PageHeader
                    title="Dashboard"
                    description="Visao geral de operacoes, performance e saude dos cadastros."
                    actions={(
                        <>
                            <Button variant="outline">Exportar</Button>
                            <Button>Novo Cadastro</Button>
                        </>
                    )}
                />

                <div className="grid gap-4 md:grid-cols-3">
                    <DataCard title="Pneus Em Estoque">
                        <div className="space-y-2">
                            <p className="text-3xl font-semibold tracking-tight">1.284</p>
                            <p className="text-sm text-muted-foreground">+6.2% em relacao ao ultimo mes</p>
                        </div>
                    </DataCard>

                    <DataCard title="Ordens Em Andamento">
                        <div className="space-y-2">
                            <p className="text-3xl font-semibold tracking-tight">42</p>
                            <p className="text-sm text-muted-foreground">14 aguardando aprovacao</p>
                        </div>
                    </DataCard>

                    <DataCard title="Indice De Inspecao">
                        <div className="space-y-2">
                            <p className="text-3xl font-semibold tracking-tight">97.8%</p>
                            <p className="text-sm text-muted-foreground">Meta mensal: 95%</p>
                        </div>
                    </DataCard>
                </div>

                <DataCard
                    title="Resumo Operacional"
                    description="Status rapido das principais filas de trabalho."
                >
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div className="rounded-lg border p-3">
                            <p className="text-xs uppercase text-muted-foreground">Descartes Pendentes</p>
                            <p className="mt-2 text-xl font-semibold">11</p>
                        </div>
                        <div className="rounded-lg border p-3">
                            <p className="text-xs uppercase text-muted-foreground">Transferencias</p>
                            <p className="mt-2 text-xl font-semibold">8</p>
                        </div>
                        <div className="rounded-lg border p-3">
                            <p className="text-xs uppercase text-muted-foreground">Alertas De Auditoria</p>
                            <p className="mt-2 text-xl font-semibold">3</p>
                        </div>
                        <div className="rounded-lg border p-3">
                            <p className="text-xs uppercase text-muted-foreground">SLA Medio</p>
                            <p className="mt-2 text-xl font-semibold">1h 24m</p>
                        </div>
                    </div>
                </DataCard>

                <DataCard title="Atividades Recentes">
                    <div className="space-y-3">
                        <div className="flex items-center justify-between rounded-lg border p-3">
                            <div>
                                <p className="text-sm font-medium">Cadastro de fornecedor atualizado</p>
                                <p className="text-xs text-muted-foreground">Hoje, 09:42</p>
                            </div>
                            <Badge variant="secondary">Fornecedor</Badge>
                        </div>
                        <div className="flex items-center justify-between rounded-lg border p-3">
                            <div>
                                <p className="text-sm font-medium">Unidade Norte inativada</p>
                                <p className="text-xs text-muted-foreground">Hoje, 08:31</p>
                            </div>
                            <Badge variant="secondary">Unidade</Badge>
                        </div>
                        <div className="flex items-center justify-between rounded-lg border p-3">
                            <div>
                                <p className="text-sm font-medium">Auditoria de estoque concluida</p>
                                <p className="text-xs text-muted-foreground">Ontem, 17:08</p>
                            </div>
                            <Badge>Auditoria</Badge>
                        </div>
                    </div>
                </DataCard>
            </PageContent>
        </AppLayout>
    );
}
