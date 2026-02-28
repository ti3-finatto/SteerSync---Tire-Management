import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type DataTableProps = {
    children: ReactNode;
    className?: string;
};

export default function DataTable({ children, className }: DataTableProps) {
    return (
        <div className={cn('overflow-x-auto', className)}>
            {children}
        </div>
    );
}