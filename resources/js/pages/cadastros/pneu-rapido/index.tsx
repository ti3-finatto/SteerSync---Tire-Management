import { Head } from '@inertiajs/react';
import { AlertCircle, CheckCircle2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import DataCard from '@/components/DataCard';
import InputError from '@/components/input-error';
import PageContainer from '@/components/PageContainer';
import PageHeaderMinimal from '@/components/PageHeaderMinimal';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { fetchWithCsrf } from '@/lib/http';
import type { PneuRapidoPageProps } from '@/types';

type Feedback = { type: 'success' | 'error'; message: string };

type PneuRapidoForm = {
    UNI_CODIGO: number | '';
    PNE_FOGO: string;
    MARP_CODIGO: number | '';
    TIPO_CODIGO: number | '';
    PNE_STATUSCOMPRA: 'N' | 'U';
    PNE_VIDA: 'N' | 'R1' | 'R2' | 'R3' | 'R4' | 'R5';
    MARP_CODIGO_RECAPE: number | '';
    TIPO_CODIGORECAPE: number | '';
    PNE_VALORRECAPAGEM: number | '';
    PNE_DOT: string;
    PNE_VALORCOMPRA: number | '';
    PNE_MM: number | '';
    PNE_KM: number | '';
};

type FormErrors = Partial<Record<keyof PneuRapidoForm, string>>;

const initialForm: PneuRapidoForm = {
    UNI_CODIGO: '',
    PNE_FOGO: '',
    MARP_CODIGO: '',
    TIPO_CODIGO: '',
    PNE_STATUSCOMPRA: 'N',
    PNE_VIDA: 'N',
    MARP_CODIGO_RECAPE: '',
    TIPO_CODIGORECAPE: '',
    PNE_VALORRECAPAGEM: '',
    PNE_DOT: '',
    PNE_VALORCOMPRA: '',
    PNE_MM: '',
    PNE_KM: '',
};

const selectClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50';

const vidaOpcoes: { value: PneuRapidoForm['PNE_VIDA']; label: string }[] = [
    { value: 'N', label: 'Nova (N)' },
    { value: 'R1', label: '1ª Recapagem (R1)' },
    { value: 'R2', label: '2ª Recapagem (R2)' },
    { value: 'R3', label: '3ª Recapagem (R3)' },
    { value: 'R4', label: '4ª Recapagem (R4)' },
    { value: 'R5', label: '5ª Recapagem (R5)' },
];

function SectionHeader({ title }: { title: string }) {
    return (
        <p className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
            {title}
        </p>
    );
}

export default function PneuRapidoIndex({
    unidades,
    marcasCarcaca,
    marcasRecapagem,
    tiposCarcaca,
    tiposRecapagem,
    flash,
}: PneuRapidoPageProps) {
    const [form, setForm] = useState<PneuRapidoForm>(initialForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [submitting, setSubmitting] = useState(false);
    const [feedback, setFeedback] = useState<Feedback | null>(() => {
        if (flash?.success) return { type: 'success', message: flash.success };
        if (flash?.error) return { type: 'error', message: flash.error };
        return null;
    });

    const isRecapado = form.PNE_VIDA !== 'N';

    const tiposCarcacaFiltrados = useMemo(() => {
        if (!form.MARP_CODIGO) return tiposCarcaca;
        return tiposCarcaca.filter((tipo) => tipo.MARP_CODIGO === form.MARP_CODIGO);
    }, [form.MARP_CODIGO, tiposCarcaca]);

    const tiposRecapagemFiltrados = useMemo(() => {
        if (!form.MARP_CODIGO_RECAPE) return tiposRecapagem;
        return tiposRecapagem.filter((tipo) => tipo.MARP_CODIGO === form.MARP_CODIGO_RECAPE);
    }, [form.MARP_CODIGO_RECAPE, tiposRecapagem]);

    const handleChange = (field: keyof PneuRapidoForm, value: string | number) => {
        setForm((current) => ({ ...current, [field]: value }));
    };

    const handleMarcaCarcacaChange = (marca: number | '') => {
        setForm((current) => {
            const next = { ...current, MARP_CODIGO: marca };
            const tipoValido =
                typeof next.TIPO_CODIGO === 'number' &&
                tiposCarcaca.some(
                    (tipo) => tipo.TIPO_CODIGO === next.TIPO_CODIGO && tipo.MARP_CODIGO === marca,
                );
            if (!tipoValido) next.TIPO_CODIGO = '';
            return next;
        });
    };

    const handleMarcaRecapeChange = (marca: number | '') => {
        setForm((current) => {
            const next = { ...current, MARP_CODIGO_RECAPE: marca };
            const tipoValido =
                typeof next.TIPO_CODIGORECAPE === 'number' &&
                tiposRecapagem.some(
                    (tipo) =>
                        tipo.TIPO_CODIGO === next.TIPO_CODIGORECAPE && tipo.MARP_CODIGO === marca,
                );
            if (!tipoValido) next.TIPO_CODIGORECAPE = '';
            return next;
        });
    };

    const parseErrors = (payload: unknown): FormErrors => {
        if (!payload || typeof payload !== 'object' || !('errors' in payload)) return {};
        const payloadErrors = payload.errors as Record<string, string[]>;
        return {
            UNI_CODIGO: payloadErrors.UNI_CODIGO?.[0],
            PNE_FOGO: payloadErrors.PNE_FOGO?.[0],
            MARP_CODIGO: payloadErrors.MARP_CODIGO?.[0],
            TIPO_CODIGO: payloadErrors.TIPO_CODIGO?.[0],
            PNE_STATUSCOMPRA: payloadErrors.PNE_STATUSCOMPRA?.[0],
            PNE_VIDA: payloadErrors.PNE_VIDA?.[0],
            MARP_CODIGO_RECAPE: payloadErrors.MARP_CODIGO_RECAPE?.[0],
            TIPO_CODIGORECAPE: payloadErrors.TIPO_CODIGORECAPE?.[0],
            PNE_VALORRECAPAGEM: payloadErrors.PNE_VALORRECAPAGEM?.[0],
            PNE_DOT: payloadErrors.PNE_DOT?.[0],
            PNE_VALORCOMPRA: payloadErrors.PNE_VALORCOMPRA?.[0],
            PNE_MM: payloadErrors.PNE_MM?.[0],
            PNE_KM: payloadErrors.PNE_KM?.[0],
        };
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        setFeedback(null);

        try {
            const response = await fetchWithCsrf('/cadastros/pneu-rapido', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(form),
            });

            const payload = (await response.json().catch(() => ({}))) as {
                message?: string;
                errors?: Record<string, string[]>;
            };

            if (response.status === 422) {
                setErrors(parseErrors(payload));
                setFeedback({
                    type: 'error',
                    message: payload.message ?? 'Existem campos invalidos no formulario.',
                });
                return;
            }

            if (response.status === 409) {
                setFeedback({
                    type: 'error',
                    message: payload.message ?? 'Conflito de dados detectado.',
                });
                return;
            }

            if (!response.ok) {
                setFeedback({
                    type: 'error',
                    message: payload.message ?? 'Falha ao cadastrar pneu.',
                });
                return;
            }

            setForm(initialForm);
            setFeedback({ type: 'success', message: payload.message ?? 'Pneu cadastrado com sucesso.' });
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <AppLayout>
            <Head title="Cadastro Rapido de Pneus" />

            <PageContainer>
                <PageHeaderMinimal
                    title="Cadastro Rapido de Pneus"
                    description="Cadastre pneus novos ou usados com configuracao, vida e dados de recapagem."
                />

                {feedback && (
                    <Alert variant={feedback.type === 'error' ? 'destructive' : 'default'}>
                        {feedback.type === 'error' ? (
                            <AlertCircle className="h-4 w-4" />
                        ) : (
                            <CheckCircle2 className="h-4 w-4" />
                        )}
                        <AlertTitle>{feedback.type === 'error' ? 'Erro' : 'Sucesso'}</AlertTitle>
                        <AlertDescription>{feedback.message}</AlertDescription>
                    </Alert>
                )}

                <DataCard contentClassName="space-y-6">
                    {/* Identificação */}
                    <div className="space-y-3">
                        <SectionHeader title="Identificação" />
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div className="grid gap-2">
                                <Label htmlFor="UNI_CODIGO">Unidade</Label>
                                <select
                                    id="UNI_CODIGO"
                                    className={selectClass}
                                    value={form.UNI_CODIGO}
                                    onChange={(e) =>
                                        handleChange(
                                            'UNI_CODIGO',
                                            e.target.value ? Number(e.target.value) : '',
                                        )
                                    }
                                >
                                    <option value="">Selecione...</option>
                                    {unidades.map((unidade) => (
                                        <option key={unidade.UNI_CODIGO} value={unidade.UNI_CODIGO}>
                                            {unidade.UNI_DESCRICAO}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.UNI_CODIGO} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="PNE_FOGO">Numero de Fogo</Label>
                                <Input
                                    id="PNE_FOGO"
                                    maxLength={20}
                                    value={form.PNE_FOGO}
                                    onChange={(e) =>
                                        handleChange(
                                            'PNE_FOGO',
                                            e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase(),
                                        )
                                    }
                                />
                                <InputError message={errors.PNE_FOGO} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="PNE_STATUSCOMPRA">Status de Compra</Label>
                                <select
                                    id="PNE_STATUSCOMPRA"
                                    className={selectClass}
                                    value={form.PNE_STATUSCOMPRA}
                                    onChange={(e) =>
                                        handleChange(
                                            'PNE_STATUSCOMPRA',
                                            e.target.value as 'N' | 'U',
                                        )
                                    }
                                >
                                    <option value="N">Novo (N)</option>
                                    <option value="U">Usado (U)</option>
                                </select>
                                <InputError message={errors.PNE_STATUSCOMPRA} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="PNE_DOT">DOT (4 digitos)</Label>
                                <Input
                                    id="PNE_DOT"
                                    inputMode="numeric"
                                    maxLength={4}
                                    placeholder="ex: 2524"
                                    value={form.PNE_DOT}
                                    onChange={(e) =>
                                        handleChange(
                                            'PNE_DOT',
                                            e.target.value.replace(/\D/g, '').slice(0, 4),
                                        )
                                    }
                                />
                                <InputError message={errors.PNE_DOT} />
                            </div>
                        </div>
                    </div>

                    <Separator />

                    {/* Configuração da Carcaça */}
                    <div className="space-y-3">
                        <SectionHeader title="Configuracao da Carcaca" />
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div className="grid gap-2">
                                <Label htmlFor="PNE_VIDA">Vida do Pneu</Label>
                                <select
                                    id="PNE_VIDA"
                                    className={selectClass}
                                    value={form.PNE_VIDA}
                                    onChange={(e) => handleChange('PNE_VIDA', e.target.value)}
                                >
                                    {vidaOpcoes.map((opcao) => (
                                        <option key={opcao.value} value={opcao.value}>
                                            {opcao.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.PNE_VIDA} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="MARP_CODIGO">Marca da Carcaca</Label>
                                <select
                                    id="MARP_CODIGO"
                                    className={selectClass}
                                    value={form.MARP_CODIGO}
                                    onChange={(e) =>
                                        handleMarcaCarcacaChange(
                                            e.target.value ? Number(e.target.value) : '',
                                        )
                                    }
                                >
                                    <option value="">Todas</option>
                                    {marcasCarcaca.map((marca) => (
                                        <option key={marca.MARP_CODIGO} value={marca.MARP_CODIGO}>
                                            {marca.MARP_DESCRICAO}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.MARP_CODIGO} />
                            </div>

                            <div className="grid gap-2 sm:col-span-2 lg:col-span-1">
                                <Label htmlFor="PNE_VALORCOMPRA">Valor de Compra (R$)</Label>
                                <Input
                                    id="PNE_VALORCOMPRA"
                                    type="number"
                                    min={0}
                                    step="0.01"
                                    placeholder="0,00"
                                    value={form.PNE_VALORCOMPRA}
                                    onChange={(e) =>
                                        handleChange(
                                            'PNE_VALORCOMPRA',
                                            e.target.value ? Number(e.target.value) : '',
                                        )
                                    }
                                />
                                <InputError message={errors.PNE_VALORCOMPRA} />
                            </div>

                            <div className="grid gap-2 sm:col-span-2 lg:col-span-3">
                                <Label htmlFor="TIPO_CODIGO">Configuracao / SKU (Carcaca)</Label>
                                <select
                                    id="TIPO_CODIGO"
                                    className={selectClass}
                                    value={form.TIPO_CODIGO}
                                    onChange={(e) =>
                                        handleChange(
                                            'TIPO_CODIGO',
                                            e.target.value ? Number(e.target.value) : '',
                                        )
                                    }
                                >
                                    <option value="">Selecione...</option>
                                    {tiposCarcacaFiltrados.map((tipo) => (
                                        <option key={tipo.TIPO_CODIGO} value={tipo.TIPO_CODIGO}>
                                            {tipo.TIPO_DESCRICAO}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.TIPO_CODIGO} />
                            </div>
                        </div>
                    </div>

                    {/* Recapagem (condicional) */}
                    {isRecapado && (
                        <>
                            <Separator />
                            <div className="space-y-3">
                                <SectionHeader title="Recapagem" />
                                <div className="grid gap-4 sm:grid-cols-3">
                                    <div className="grid gap-2">
                                        <Label htmlFor="MARP_CODIGO_RECAPE">Marca da Recapagem</Label>
                                        <select
                                            id="MARP_CODIGO_RECAPE"
                                            className={selectClass}
                                            value={form.MARP_CODIGO_RECAPE}
                                            onChange={(e) =>
                                                handleMarcaRecapeChange(
                                                    e.target.value ? Number(e.target.value) : '',
                                                )
                                            }
                                        >
                                            <option value="">Selecione...</option>
                                            {marcasRecapagem.map((marca) => (
                                                <option
                                                    key={marca.MARP_CODIGO}
                                                    value={marca.MARP_CODIGO}
                                                >
                                                    {marca.MARP_DESCRICAO}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={errors.MARP_CODIGO_RECAPE} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="TIPO_CODIGORECAPE">SKU da Recapagem</Label>
                                        <select
                                            id="TIPO_CODIGORECAPE"
                                            className={selectClass}
                                            value={form.TIPO_CODIGORECAPE}
                                            onChange={(e) =>
                                                handleChange(
                                                    'TIPO_CODIGORECAPE',
                                                    e.target.value ? Number(e.target.value) : '',
                                                )
                                            }
                                        >
                                            <option value="">Selecione...</option>
                                            {tiposRecapagemFiltrados.map((tipo) => (
                                                <option
                                                    key={tipo.TIPO_CODIGO}
                                                    value={tipo.TIPO_CODIGO}
                                                >
                                                    {tipo.TIPO_DESCRICAO}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={errors.TIPO_CODIGORECAPE} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="PNE_VALORRECAPAGEM">
                                            Valor da Recapagem (R$)
                                        </Label>
                                        <Input
                                            id="PNE_VALORRECAPAGEM"
                                            type="number"
                                            min={0}
                                            step="0.01"
                                            placeholder="0,00"
                                            value={form.PNE_VALORRECAPAGEM}
                                            onChange={(e) =>
                                                handleChange(
                                                    'PNE_VALORRECAPAGEM',
                                                    e.target.value ? Number(e.target.value) : '',
                                                )
                                            }
                                        />
                                        <InputError message={errors.PNE_VALORRECAPAGEM} />
                                    </div>
                                </div>
                            </div>
                        </>
                    )}

                    <Separator />

                    {/* Dados Operacionais */}
                    <div className="space-y-3">
                        <SectionHeader title="Dados Operacionais" />
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="PNE_MM">Milimetragem Atual (mm)</Label>
                                <Input
                                    id="PNE_MM"
                                    type="number"
                                    min={0}
                                    step="0.1"
                                    placeholder="0.0"
                                    value={form.PNE_MM}
                                    onChange={(e) =>
                                        handleChange(
                                            'PNE_MM',
                                            e.target.value ? Number(e.target.value) : '',
                                        )
                                    }
                                />
                                <InputError message={errors.PNE_MM} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="PNE_KM">KM Atual</Label>
                                <Input
                                    id="PNE_KM"
                                    type="number"
                                    min={0}
                                    step="1"
                                    placeholder="0"
                                    value={form.PNE_KM}
                                    onChange={(e) =>
                                        handleChange(
                                            'PNE_KM',
                                            e.target.value ? Number(e.target.value) : '',
                                        )
                                    }
                                />
                                <InputError message={errors.PNE_KM} />
                            </div>
                        </div>
                    </div>

                    <Separator />

                    {/* Ações */}
                    <div className="flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => {
                                setForm(initialForm);
                                setErrors({});
                                setFeedback(null);
                            }}
                            disabled={submitting}
                        >
                            Limpar
                        </Button>
                        <Button type="button" onClick={submit} disabled={submitting}>
                            {submitting ? 'Salvando...' : 'Cadastrar Pneu'}
                        </Button>
                    </div>
                </DataCard>
            </PageContainer>
        </AppLayout>
    );
}
