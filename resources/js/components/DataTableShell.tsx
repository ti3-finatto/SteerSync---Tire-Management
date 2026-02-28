import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type DataTableShellProps = {
    filters?: ReactNode;
    actions?: ReactNode;
    children: ReactNode;
    className?: string;
};

export default function DataTableShell({
    filters,
    actions,
    children,
    className,
}: DataTableShellProps) {
    return (
        <div className={cn('space-y-4', className)}>
            {(filters || actions) && (
                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div className="flex flex-wrap items-center gap-2">{filters}</div>
                    <div className="flex flex-wrap items-center gap-2">{actions}</div>
                </div>
            )}
            <div className="overflow-hidden rounded-lg border">{children}</div>
        </div>
    );
}
