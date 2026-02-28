import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type PageContentProps = {
    children: ReactNode;
    className?: string;
};

export default function PageContent({ children, className }: PageContentProps) {
    return <div className={cn('space-y-6 p-4 md:p-6', className)}>{children}</div>;
}
