import type { ReactNode } from 'react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';

type CardSectionProps = {
    title?: string;
    description?: string;
    actions?: ReactNode;
    children: ReactNode;
    className?: string;
    contentClassName?: string;
};

export default function CardSection({
    title,
    description,
    actions,
    children,
    className,
    contentClassName,
}: CardSectionProps) {
    return (
        <Card className={cn('shadow-sm', className)}>
            {(title || description || actions) && (
                <CardHeader className="flex flex-row items-start justify-between gap-4 space-y-0">
                    <div className="space-y-1">
                        {title && <CardTitle className="text-base">{title}</CardTitle>}
                        {description && (
                            <CardDescription>{description}</CardDescription>
                        )}
                    </div>
                    {actions && <div>{actions}</div>}
                </CardHeader>
            )}
            <CardContent className={cn(contentClassName)}>{children}</CardContent>
        </Card>
    );
}
