import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import DataCard from '@/components/DataCard';
import PageContainer from '@/components/PageContainer';
import PageHeaderMinimal from '@/components/PageHeaderMinimal';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import type { PneuConsultaPageProps } from '@/types';

type TabKey = 'detalhes' | 'movimentacoes' | 'alertas' | 'vidas';

const tabs: { key: TabKey; label: string }[] = [
    { key: 'detalhes', label: 'Detalhes do Pneu' },
    { key: 'movimentacoes', label: 'Movimentacoes' },
    { key: 'alertas', label: 'Alertas' },
    { key: 'vidas', label: 'Relatorio de Vidas' },
];

export default function PneuConsultaIndex({ filtroFogo, pneu, movimentacoes, alertas, vidas }: PneuConsultaPageProps) {
    const [fogo, setFogo] = useState(filtroFogo ?? '');
    const [activeTab, setActiveTab] = useState<TabKey>('detalhes');

    const buscar = () => {
        router.get(
            '/cadastros/pneu-consulta',
            { fogo: fogo.trim().toUpperCase() },
            { preserveState: true, preserveScroll: true },
        );
    };

    return (
        <AppLayout>
            <Head title="Consulta Individual de Pneu" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Consulta Individual de Pneu"
                    description="Digite o numero de fogo para consultar detalhes, movimentacoes, alertas e relatorio de vidas."
                />

                <DataCard contentClassName="space-y-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-end">
                        <div className="grid flex-1 gap-2">
                            <Label htmlFor="fogo">Numero de Fogo</Label>
                            <Input
                                id="fogo"
                                maxLength={20}
                                value={fogo}
                                onChange={(e) => setFogo(e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase())}
                            />
                        </div>
                        <Button type="button" onClick={buscar}>
                            Buscar
                        </Button>
                    </div>
                </DataCard>

                {!pneu && filtroFogo !== '' && (
                    <DataCard>
                        <p className="text-sm text-muted-foreground">Nenhum pneu encontrado para o fogo informado.</p>
                    </DataCard>
                )}

                {pneu && (
                    <DataCard contentClassName="space-y-4">
                        <div className="flex flex-wrap gap-2">
                            {tabs.map((tab) => (
                                <Button
                                    key={tab.key}
                                    size="sm"
                                    variant={activeTab === tab.key ? 'default' : 'outline'}
                                    onClick={() => setActiveTab(tab.key)}
                                >
                                    {tab.label}
                                </Button>
                            ))}
                        </div>

                        {activeTab === 'detalhes' && (
                            <div className="grid gap-3 text-sm md:grid-cols-2">
                                <div><strong>Codigo:</strong> {pneu.PNE_CODIGO}</div>
                                <div><strong>Fogo:</strong> {pneu.PNE_FOGO}</div>
                                <div><strong>Unidade:</strong> {pneu.UNI_DESCRICAO}</div>
                                <div><strong>Status:</strong> {pneu.PNE_STATUS}</div>
                                <div><strong>Status Compra:</strong> {pneu.PNE_STATUSCOMPRA}</div>
                                <div><strong>Vida Compra/Atual:</strong> {pneu.PNE_VIDACOMPRA} / {pneu.PNE_VIDAATUAL}</div>
                                <div><strong>SKU Carcaca:</strong> {pneu.SKU_CARCACA}</div>
                                <div><strong>SKU Recapagem:</strong> {pneu.SKU_RECAPE ?? '-'}</div>
                                <div><strong>DOT:</strong> {pneu.PNE_DOT ?? '-'}</div>
                                <div><strong>MM Atual:</strong> {pneu.PNE_MM}</div>
                                <div><strong>KM Atual:</strong> {pneu.PNE_KM}</div>
                                <div><strong>Valor Compra:</strong> {pneu.PNE_VALORCOMPRA}</div>
                                <div><strong>Custo Atual:</strong> {pneu.PNE_CUSTOATUAL}</div>
                            </div>
                        )}

                        {activeTab === 'movimentacoes' && (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead className="bg-muted/45">
                                        <tr>
                                            <th className="px-3 py-2 text-left">Cod</th>
                                            <th className="px-3 py-2 text-left">Data</th>
                                            <th className="px-3 py-2 text-left">Operacao</th>
                                            <th className="px-3 py-2 text-left">KM Pneu</th>
                                            <th className="px-3 py-2 text-left">KM Veiculo</th>
                                            <th className="px-3 py-2 text-left">Comentario</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {movimentacoes.length === 0 && (
                                            <tr>
                                                <td className="px-3 py-4 text-muted-foreground" colSpan={6}>
                                                    Nenhuma movimentacao encontrada.
                                                </td>
                                            </tr>
                                        )}
                                        {movimentacoes.map((mov) => (
                                            <tr key={mov.MOV_CODIGO} className="border-t">
                                                <td className="px-3 py-2">{mov.MOV_CODIGO}</td>
                                                <td className="px-3 py-2">{mov.MOV_DATA ?? '-'}</td>
                                                <td className="px-3 py-2">{mov.MOV_OPERACAO}</td>
                                                <td className="px-3 py-2">{mov.MOV_KMPNEU ?? '-'}</td>
                                                <td className="px-3 py-2">{mov.MOV_KMVEICULO ?? '-'}</td>
                                                <td className="px-3 py-2">{mov.MOV_COMENTARIO ?? '-'}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}

                        {activeTab === 'alertas' && (
                            <div className="space-y-2">
                                {alertas.map((alerta, idx) => (
                                    <div key={`${alerta.titulo}-${idx}`} className="rounded-md border p-3">
                                        <div className="mb-1 flex items-center gap-2">
                                            <Badge variant={alerta.tipo === 'critical' ? 'destructive' : 'secondary'}>
                                                {alerta.tipo.toUpperCase()}
                                            </Badge>
                                            <strong className="text-sm">{alerta.titulo}</strong>
                                        </div>
                                        <p className="text-sm text-muted-foreground">{alerta.mensagem}</p>
                                    </div>
                                ))}
                            </div>
                        )}

                        {activeTab === 'vidas' && (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead className="bg-muted/45">
                                        <tr>
                                            <th className="px-3 py-2 text-left">Vida</th>
                                            <th className="px-3 py-2 text-left">SKU</th>
                                            <th className="px-3 py-2 text-left">KM</th>
                                            <th className="px-3 py-2 text-left">MM</th>
                                            <th className="px-3 py-2 text-left">Custo</th>
                                            <th className="px-3 py-2 text-left">Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {vidas.length === 0 && (
                                            <tr>
                                                <td className="px-3 py-4 text-muted-foreground" colSpan={6}>
                                                    Nenhum registro de vida encontrado.
                                                </td>
                                            </tr>
                                        )}
                                        {vidas.map((vida, idx) => (
                                            <tr key={`${vida.VIPN_CODIGO ?? 'atual'}-${idx}`} className="border-t">
                                                <td className="px-3 py-2">{vida.VIDA}</td>
                                                <td className="px-3 py-2">{vida.SKU ?? '-'}</td>
                                                <td className="px-3 py-2">{vida.KM ?? '-'}</td>
                                                <td className="px-3 py-2">{vida.MM ?? '-'}</td>
                                                <td className="px-3 py-2">{vida.CUSTO ?? '-'}</td>
                                                <td className="px-3 py-2">{vida.DATA_EVENTO ?? '-'}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </DataCard>
                )}
            </PageContainer>
        </AppLayout>
    );
}
