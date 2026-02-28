import { Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import BrandLogo from '@/components/BrandLogo';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

const SLIDES = [
    {
        image: '/images/caminhao.jpg',
        label: 'Controle de frota',
        title: 'Visibilidade total da operação, de qualquer lugar.',
        description: 'Acompanhe disponibilidade, custos e indicadores de cada veículo em tempo real.',
    },
    {
        image: '/images/tire.jpg',
        label: 'Gestão de pneus',
        title: 'Maximize a vida útil e reduza o custo por km.',
        description: 'Histórico completo por pneu, eixo e veículo com alertas de desgaste e recapagem.',
    },
    {
        image: '/images/tire-2.jpg',
        label: 'Manutenção preventiva',
        title: 'Antecipe falhas antes que elas parem sua operação.',
        description: 'Alertas automáticos por hodômetro, tempo e histórico de cada veículo.',
    },
];

const INTERVAL = 6000;
const FADE_MS = 700;

export default function AuthSplitLayout({ children, title, description }: AuthLayoutProps) {
    const { name } = usePage().props;
    const [current, setCurrent] = useState(0);
    const [visible, setVisible] = useState(true);

    useEffect(() => {
        const timer = setInterval(() => {
            setVisible(false);
            setTimeout(() => {
                setCurrent(i => (i + 1) % SLIDES.length);
                setVisible(true);
            }, FADE_MS);
        }, INTERVAL);
        return () => clearInterval(timer);
    }, []);

    const goTo = (i: number) => {
        if (i === current) return;
        setVisible(false);
        setTimeout(() => { setCurrent(i); setVisible(true); }, FADE_MS);
    };

    return (
        <div className="relative grid h-dvh lg:max-w-none lg:grid-cols-[480px_1fr] xl:grid-cols-[520px_1fr]">

            {/* ─── Painel esquerdo (formulário) ────────────────────── */}
            <div className="flex h-full w-full flex-col bg-background">
                <div className="flex items-center justify-center border-b border-border/50 p-5 lg:hidden">
                    <Link href={home()}><BrandLogo /></Link>
                </div>

                <div className="flex flex-1 items-center justify-center overflow-y-auto px-8 py-10">
                    <div className="w-full max-w-sm">
                        <div className="mb-8">
                            <h1 className="text-2xl font-semibold tracking-tight text-foreground">{title}</h1>
                            <p className="mt-1.5 text-sm text-muted-foreground">{description}</p>
                        </div>
                        {children}
                    </div>
                </div>
            </div>

            {/* ─── Painel direito (carrossel) ──────────────────────── */}
            <div className="relative hidden h-full overflow-hidden lg:block">

                {/* Slides — pré-carrega todos, só muda opacidade do ativo */}
                {SLIDES.map((slide, i) => (
                    <div
                        key={i}
                        className="absolute inset-0 bg-cover bg-center"
                        style={{
                            backgroundImage: `url(${slide.image})`,
                            opacity: i === current ? (visible ? 1 : 0) : 0,
                            transition: `opacity ${FADE_MS}ms ease-in-out`,
                        }}
                    />
                ))}

                {/* Overlay gradiente */}
                <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/30" />

                {/* Logo — canto superior direito */}
                <div className="absolute right-0 top-0 z-20 p-10">
                    <Link href={home()}>
                        <BrandLogo alt={name} />
                    </Link>
                </div>

                {/* Texto + dots — rodapé */}
                <div className="absolute bottom-0 left-0 right-0 z-20 p-10">
                    <div
                        style={{
                            opacity: visible ? 1 : 0,
                            transform: visible ? 'translateY(0)' : 'translateY(8px)',
                            transition: `opacity ${FADE_MS}ms ease-in-out, transform ${FADE_MS}ms ease-in-out`,
                        }}
                    >
                        <p className="mb-2 text-xs font-semibold uppercase tracking-widest text-lime-400">
                            {SLIDES[current].label}
                        </p>
                        <h2 className="max-w-sm text-2xl font-semibold leading-snug tracking-tight text-white">
                            {SLIDES[current].title}
                        </h2>
                        <p className="mt-2 max-w-sm text-sm leading-relaxed text-white/60">
                            {SLIDES[current].description}
                        </p>
                    </div>

                    {/* Dots */}
                    <div className="mt-6 flex items-center gap-2">
                        {SLIDES.map((_, i) => (
                            <button
                                key={i}
                                onClick={() => goTo(i)}
                                aria-label={`Slide ${i + 1}`}
                                style={{
                                    width: i === current ? '24px' : '6px',
                                    height: '6px',
                                    borderRadius: '9999px',
                                    backgroundColor: i === current ? 'rgb(163 230 53)' : 'rgba(255,255,255,0.3)',
                                    transition: 'all 400ms ease',
                                    border: 'none',
                                    cursor: 'pointer',
                                    padding: 0,
                                }}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}