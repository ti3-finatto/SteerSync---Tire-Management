import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type PageHeaderMinimalProps = {
    title: string;
    description?: string;
    actions?: ReactNode;
    search?: ReactNode;
    className?: string;
};

export default function PageHeaderMinimal({
    title,
    description,
    actions,
    search,
    className,
}: PageHeaderMinimalProps) {
    return (
        <div
            className={cn(
                'flex flex-col gap-3 border-b pb-3 sm:flex-row sm:items-center sm:justify-between',
                className,
            )}
        >
            <div className="space-y-0.5">
                <h1 className="text-xl font-semibold tracking-tight">
                    {title}
                </h1>
                {description && (
                    <p className="text-sm text-muted-foreground">
                        {description}
                    </p>
                )}
            </div>
            {(search || actions) && (
                <div className="flex flex-wrap items-center gap-2">
                    {search}
                    {actions}
                </div>
            )}
        </div>
    );
}
