import { usePage } from '@inertiajs/react';
import { Moon, Search, Sun } from 'lucide-react';
import BrandLogo from '@/components/BrandLogo';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { UserMenuContent } from '@/components/user-menu-content';
import { useAppearance } from '@/hooks/use-appearance';
import { useInitials } from '@/hooks/use-initials';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';
import type { User } from '@/types';

type SharedProps = {
    auth: {
        user: User;
    };
};

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { auth } = usePage<SharedProps>().props;
    const getInitials = useInitials();
    const { resolvedAppearance, updateAppearance } = useAppearance();

    const toggleTheme = () => {
        updateAppearance(resolvedAppearance === 'dark' ? 'light' : 'dark');
    };

    return (
        <header className="bg-background/95 sticky top-0 z-20 flex h-16 shrink-0 items-center border-b border-sidebar-border/60 px-4 backdrop-blur transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-5">

            {/* Mobile layout */}
            <div className="flex w-full items-center justify-between md:hidden">
                <SidebarTrigger />
                <BrandLogo />
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" className="h-9 px-2">
                            <Avatar className="h-7 w-7">
                                <AvatarImage src={auth.user.avatar} alt={auth.user.name} />
                                <AvatarFallback>{getInitials(auth.user.name)}</AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-64">
                        <UserMenuContent user={auth.user} />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            {/* Desktop layout */}
            <div className="hidden w-full items-center gap-3 md:flex">
                <SidebarTrigger className="-ml-1" />

                <div className="min-w-0 flex-1">
                    {breadcrumbs.length > 0 ? (
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    ) : (
                        <span className="text-muted-foreground text-sm font-medium">SteerSync</span>
                    )}
                </div>

                <div className="hidden lg:block">
                    <div className="relative">
                        <Search className="text-muted-foreground absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2" />
                        <Input className="h-9 w-64 pl-8" placeholder="Buscar..." aria-label="Buscar" />
                    </div>
                </div>

                <Button variant="outline" size="icon" onClick={toggleTheme}>
                    {resolvedAppearance === 'dark' ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
                    <span className="sr-only">Alternar tema</span>
                </Button>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" className="h-9 px-2">
                            <Avatar className="h-7 w-7">
                                <AvatarImage src={auth.user.avatar} alt={auth.user.name} />
                                <AvatarFallback>{getInitials(auth.user.name)}</AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-64">
                        <UserMenuContent user={auth.user} />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

        </header>
    );
}