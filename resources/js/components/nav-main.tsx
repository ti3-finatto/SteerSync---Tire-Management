import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuAction,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import type { SidebarNavSection } from '@/types';

export function NavMain({ sections = [] }: { sections: SidebarNavSection[] }) {
    const { isCurrentOrParentUrl, isCurrentUrl } = useCurrentUrl();
    const { state } = useSidebar();
    const isCollapsed = state === 'collapsed';

    const itemButtonClass = cn(
        'text-xs w-full min-w-0 overflow-visible whitespace-nowrap data-[active=true]:bg-primary/10 data-[active=true]:text-primary data-[active=true]:outline data-[active=true]:outline-1 data-[active=true]:outline-primary/40 [&>svg]:size-4 [&>svg]:shrink-0',
        isCollapsed && 'justify-center gap-0 px-0 [&>svg]:mx-auto',
    );

    return (
        <div className="min-w-0 overflow-x-hidden">
            {sections.map((section) => (
                <Collapsible
                    key={section.label}
                    defaultOpen
                    className="group/section"
                >
                    <SidebarGroup className={cn('min-w-0 overflow-x-hidden py-0.5', !isCollapsed && 'px-1')}>
                        {!isCollapsed ? (
                            <CollapsibleTrigger asChild>
                                <SidebarGroupLabel className="cursor-pointer justify-between text-[0.62rem] font-semibold tracking-[0.08em] text-sidebar-foreground/65 uppercase hover:text-sidebar-foreground/90">
                                    <span className="truncate whitespace-nowrap">
                                        {section.label}
                                    </span>
                                    <ChevronRight className="size-3.5 transition-transform group-data-[state=open]/section:rotate-90" />
                                </SidebarGroupLabel>
                            </CollapsibleTrigger>
                        ) : (
                            <SidebarGroupLabel className="sr-only">
                                {section.label}
                            </SidebarGroupLabel>
                        )}

                        <CollapsibleContent>
                            <SidebarGroupContent className="min-w-0 overflow-x-hidden">
                                <SidebarMenu className={cn('min-w-0 overflow-x-hidden', isCollapsed && 'w-full items-center')}>
                                    {section.items.map((item) => {
                                        const childItems = item.items ?? [];
                                        const hasChildren =
                                            childItems.length > 0;
                                        const isItemActive = item.href
                                            ? item.startsWith
                                                ? isCurrentOrParentUrl(
                                                      item.href,
                                                  )
                                                : isCurrentUrl(item.href)
                                            : false;
                                        const hasActiveChild = childItems.some(
                                            (child) =>
                                                child.href &&
                                                isCurrentOrParentUrl(
                                                    child.href,
                                                ),
                                        );

                                        if (!hasChildren) {
                                            return (
                                                <SidebarMenuItem
                                                    key={item.title}
                                                    className={cn(isCollapsed && 'flex w-full justify-center')}
                                                >
                                                    {item.href &&
                                                    !item.disabled ? (
                                                        <SidebarMenuButton
                                                            asChild
                                                            isActive={
                                                                isItemActive
                                                            }
                                                            className={
                                                                itemButtonClass
                                                            }
                                                            tooltip={{
                                                                children:
                                                                    item.title,
                                                            }}
                                                        >
                                                            <Link
                                                                href={item.href}
                                                                prefetch
                                                            >
                                                                {item.icon && (
                                                                    <item.icon />
                                                                )}
                                                                {!isCollapsed && (
                                                                    <span>
                                                                        {
                                                                            item.title
                                                                        }
                                                                    </span>
                                                                )}
                                                            </Link>
                                                        </SidebarMenuButton>
                                                    ) : (
                                                        <SidebarMenuButton
                                                            disabled
                                                            className={cn(
                                                                itemButtonClass,
                                                                'cursor-not-allowed opacity-60',
                                                            )}
                                                            tooltip={{
                                                                children:
                                                                    item.title,
                                                            }}
                                                        >
                                                            {item.icon && (
                                                                <item.icon />
                                                            )}
                                                            {!isCollapsed && (
                                                                <span>
                                                                    {item.title}
                                                                </span>
                                                            )}
                                                        </SidebarMenuButton>
                                                    )}
                                                </SidebarMenuItem>
                                            );
                                        }

                                        return (
                                            <Collapsible
                                                key={item.title}
                                                defaultOpen={
                                                    !isCollapsed &&
                                                    (hasActiveChild ||
                                                        isItemActive)
                                                }
                                                className="group/item"
                                            >
                                                <SidebarMenuItem className={cn(isCollapsed && 'flex w-full justify-center')}>
                                                    {item.href &&
                                                    !item.disabled ? (
                                                        <SidebarMenuButton
                                                            asChild
                                                            isActive={
                                                                hasActiveChild ||
                                                                isItemActive
                                                            }
                                                            className={
                                                                itemButtonClass
                                                            }
                                                            tooltip={{
                                                                children:
                                                                    item.title,
                                                            }}
                                                        >
                                                            <Link
                                                                href={item.href}
                                                                prefetch
                                                            >
                                                                {item.icon && (
                                                                    <item.icon />
                                                                )}
                                                                {!isCollapsed && (
                                                                    <span>
                                                                        {
                                                                            item.title
                                                                        }
                                                                    </span>
                                                                )}
                                                            </Link>
                                                        </SidebarMenuButton>
                                                    ) : (
                                                        <CollapsibleTrigger
                                                            asChild
                                                            disabled={
                                                                isCollapsed
                                                            }
                                                        >
                                                            <SidebarMenuButton
                                                                disabled={
                                                                    item.disabled
                                                                }
                                                                isActive={
                                                                    hasActiveChild ||
                                                                    isItemActive
                                                                }
                                                                className={
                                                                    itemButtonClass
                                                                }
                                                                tooltip={{
                                                                    children:
                                                                        item.title,
                                                                }}
                                                            >
                                                                {item.icon && (
                                                                    <item.icon />
                                                                )}
                                                                {!isCollapsed && (
                                                                    <span>
                                                                        {
                                                                            item.title
                                                                        }
                                                                    </span>
                                                                )}
                                                                {!isCollapsed && (
                                                                    <ChevronRight className="ml-auto transition-transform group-data-[state=open]/item:rotate-90" />
                                                                )}
                                                            </SidebarMenuButton>
                                                        </CollapsibleTrigger>
                                                    )}

                                                    {!isCollapsed &&
                                                        item.href &&
                                                        !item.disabled && (
                                                            <CollapsibleTrigger
                                                                asChild
                                                            >
                                                                <SidebarMenuAction
                                                                    className="transition-transform group-data-[state=open]/item:rotate-90"
                                                                    showOnHover
                                                                >
                                                                    <ChevronRight />
                                                                    <span className="sr-only">
                                                                        Expandir
                                                                        ou
                                                                        recolher{' '}
                                                                        {
                                                                            item.title
                                                                        }
                                                                    </span>
                                                                </SidebarMenuAction>
                                                            </CollapsibleTrigger>
                                                        )}

                                                    {!isCollapsed && (
                                                        <CollapsibleContent>
                                                            <SidebarMenuSub className="mx-2.5 my-0.5">
                                                                {childItems.map(
                                                                    (child) => {
                                                                        const isChildActive =
                                                                            child.href
                                                                                ? isCurrentOrParentUrl(
                                                                                      child.href,
                                                                                  )
                                                                                : false;

                                                                        return (
                                                                            <SidebarMenuSubItem
                                                                                key={
                                                                                    child.title
                                                                                }
                                                                            >
                                                                                {child.href &&
                                                                                !child.disabled ? (
                                                                                    <SidebarMenuSubButton
                                                                                        asChild
                                                                                        isActive={
                                                                                            isChildActive
                                                                                        }
                                                                                        className="text-xs data-[active=true]:bg-primary/10 data-[active=true]:text-primary data-[active=true]:outline data-[active=true]:outline-1 data-[active=true]:outline-primary/40"
                                                                                    >
                                                                                        <Link
                                                                                            href={
                                                                                                child.href
                                                                                            }
                                                                                            prefetch
                                                                                        >
                                                                                            <span>
                                                                                                {
                                                                                                    child.title
                                                                                                }
                                                                                            </span>
                                                                                        </Link>
                                                                                    </SidebarMenuSubButton>
                                                                                ) : (
                                                                                    <SidebarMenuSubButton
                                                                                        className="text-xs cursor-not-allowed opacity-60"
                                                                                        aria-disabled="true"
                                                                                    >
                                                                                        <span>
                                                                                            {
                                                                                                child.title
                                                                                            }
                                                                                        </span>
                                                                                    </SidebarMenuSubButton>
                                                                                )}
                                                                            </SidebarMenuSubItem>
                                                                        );
                                                                    },
                                                                )}
                                                            </SidebarMenuSub>
                                                        </CollapsibleContent>
                                                    )}
                                                </SidebarMenuItem>
                                            </Collapsible>
                                        );
                                    })}
                                </SidebarMenu>
                            </SidebarGroupContent>
                        </CollapsibleContent>
                    </SidebarGroup>
                </Collapsible>
            ))}
        </div>
    );
}