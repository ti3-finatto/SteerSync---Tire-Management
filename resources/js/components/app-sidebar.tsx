import { Link, usePage } from '@inertiajs/react';
import {
    CarFront,
    CircleDot,
    ClipboardList,
    Gauge,
    FileBarChart2,
    House,
    LifeBuoy,
    Truck,
    UserRoundCog,
    Users,
    UserRound,
    Wrench,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarRail,
    useSidebar,
} from '@/components/ui/sidebar';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import type { SidebarNavSection } from '@/types';

const getMainNavSections = (isAdmin: boolean): SidebarNavSection[] => [
    {
        label: 'Principal',
        items: [
            {
                title: 'Inicio',
                href: dashboard(),
                icon: House,
            },
            {
                title: 'Operacao',
                icon: Truck,
                items: [
                    { title: 'Inspecao', disabled: true },
                    { title: 'Movimentacoes', disabled: true },
                    { title: 'Transferencias', disabled: true },
                ],
            },
        ],
    },
    {
        label: 'Cadastros',
        items: [
            {
                title: 'Pneus',
                icon: CircleDot,
                items: [
                    { title: 'Marcas', disabled: true },
                    { title: 'Modelos', disabled: true },
                    { title: 'Medidas', disabled: true },
                    { title: 'Configuracoes', disabled: true },
                ],
            },
            {
                title: 'Veiculos',
                icon: CarFront,
                items: [
                    { title: 'Cadastrar', disabled: true },
                    { title: 'Marcas', disabled: true },
                    { title: 'Modelos', disabled: true },
                    { title: 'Alterar', disabled: true },
                ],
            },
            {
                title: 'Fornecedores',
                href: isAdmin ? '/cadastros/fornecedor' : undefined,
                icon: Users,
                startsWith: true,
                disabled: !isAdmin,
            },
            {
                title: 'Unidades',
                href: isAdmin ? '/cadastros/unidade' : undefined,
                icon: CircleDot,
                startsWith: true,
                disabled: !isAdmin,
            },
        ],
    },
    {
        label: 'Relatorios',
        items: [
            {
                title: 'Relatorios',
                icon: FileBarChart2,
                items: [
                    { title: 'Pneus', disabled: true },
                    { title: 'Veiculos', disabled: true },
                    { title: 'Movimentacoes', disabled: true },
                    { title: 'Transferencias', disabled: true },
                    { title: 'CPK', disabled: true },
                ],
            },
        ],
    },
    {
        label: 'Rotinas',
        items: [
            {
                title: 'Calibragem',
                icon: Gauge,
                disabled: true,
            },
            {
                title: 'Checklist',
                icon: ClipboardList,
                disabled: true,
            },
            {
                title: 'Motoristas',
                icon: UserRound,
                disabled: true,
            },
            {
                title: 'Manutencao',
                icon: Wrench,
                disabled: true,
            },
        ],
    },
    {
        label: 'Sistema',
        items: [
            {
                title: 'Usuarios',
                icon: UserRoundCog,
                disabled: true,
            },
            {
                title: 'Historico de Logins',
                icon: FileBarChart2,
                disabled: true,
            },
            {
                title: 'Suporte',
                icon: LifeBuoy,
                items: [
                    {
                        title: 'Enviar Email ao Suporte',
                        href: 'mailto:ti@grupofinatto.com.br',
                    },
                ],
            },
        ],
    },
];

export function AppSidebar() {
    const userType = usePage().props.auth?.user?.USU_TIPO;
    const { state } = useSidebar();
    const isAdmin =
        typeof userType === 'string' && userType.trim().toUpperCase() === 'A';
    const isCollapsed = state === 'collapsed';
    const mainNavSections = getMainNavSections(isAdmin);

    return (
        <Sidebar
            collapsible="icon"
            variant="sidebar"
            className="min-w-0 overflow-x-hidden"
        >
            <SidebarHeader className={cn('min-w-0 overflow-x-hidden border-b border-sidebar-border/70 py-2', isCollapsed ? 'px-0' : 'px-2')}>
                <SidebarMenu className={cn(isCollapsed && 'items-center')}>
                    <SidebarMenuItem className={cn(isCollapsed && 'flex w-full justify-center')}>
                        <SidebarMenuButton
                            size="lg"
                            asChild
                            tooltip={
                                isCollapsed
                                    ? {
                                          children: 'SteerSync',
                                      }
                                    : undefined
                            }
                            className={cn(
                                'flex items-center',
                                isCollapsed &&
                                    'w-full justify-center gap-0 px-0',
                            )}
                        >
                            <Link href={dashboard()} prefetch>
                                {isCollapsed ? (
                                    <img
                                        src="/brand/icone-light.png"
                                        alt="Logo"
                                        className="size-6 dark:hidden"
                                    />
                                ) : (
                                    <AppLogo />
                                )}
                                {isCollapsed && (
                                    <img
                                        src="/brand/icone-dark.png"
                                        alt="Logo"
                                        className="hidden size-6 dark:block"
                                    />
                                )}
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>

            </SidebarHeader>

            <SidebarContent className="min-h-0 min-w-0 overflow-x-hidden px-1 py-1">
                <div className="min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto pr-1">
                    <NavMain sections={mainNavSections} />
                </div>
            </SidebarContent>

            <SidebarFooter className={cn('min-w-0 overflow-x-hidden border-t border-sidebar-border/70 py-2', isCollapsed ? 'px-0' : 'px-2')}>
                <NavUser />
            </SidebarFooter>
            <SidebarRail />
        </Sidebar>
    );
}