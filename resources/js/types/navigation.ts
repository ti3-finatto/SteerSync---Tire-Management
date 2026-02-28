import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
};

export type SidebarNavChild = {
    title: string;
    href?: NonNullable<InertiaLinkProps['href']>;
    disabled?: boolean;
};

export type SidebarNavItem = {
    title: string;
    href?: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
    startsWith?: boolean;
    disabled?: boolean;
    items?: SidebarNavChild[];
};

export type SidebarNavSection = {
    label: string;
    items: SidebarNavItem[];
};
