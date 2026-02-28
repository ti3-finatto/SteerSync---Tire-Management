import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type PageContainerProps = {
    children: ReactNode;
    className?: string;
};

export default function PageContainer({
    children,
    className,
}: PageContainerProps) {
    return (
        <div className={cn('space-y-4 p-4 md:p-5', className)}>{children}</div>
    );
}
