import type { ReactNode } from 'react';

type PageHeaderProps = {
    title: string;
    description?: string;
    search?: ReactNode;
    actions?: ReactNode;
};

export default function PageHeader({ title, description, search, actions }: PageHeaderProps) {
    return (
        <div className="flex flex-col gap-4 border-b pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div className="space-y-1">
                <h1 className="text-2xl font-semibold tracking-tight">{title}</h1>
                {description && (
                    <p className="text-sm text-muted-foreground">{description}</p>
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
