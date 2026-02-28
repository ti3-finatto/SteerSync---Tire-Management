import { useState } from 'react';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';

type DataTablePaginatedProps<T> = {
    data: T[];
    rowsPerPageOptions?: number[];
    defaultRowsPerPage?: number;
    className?: string;
    children: (paginatedData: T[]) => React.ReactNode;
};

export default function DataTablePaginated<T>({
    data,
    rowsPerPageOptions = [10, 25, 50, 100],
    defaultRowsPerPage = 10,
    className,
    children,
}: DataTablePaginatedProps<T>) {
    const [currentPage, setCurrentPage] = useState(1);
    const [rowsPerPage, setRowsPerPage] = useState(defaultRowsPerPage);

    const totalRows = data.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));

    const safePage = Math.min(currentPage, totalPages);
    const startIndex = (safePage - 1) * rowsPerPage;
    const paginatedData = data.slice(startIndex, startIndex + rowsPerPage);

    const handleRowsPerPageChange = (value: string) => {
        setRowsPerPage(Number(value));
        setCurrentPage(1);
    };

    const firstRow = totalRows === 0 ? 0 : startIndex + 1;
    const lastRow = Math.min(startIndex + rowsPerPage, totalRows);

    return (
        <div className={cn('flex flex-col', className)}>
            {/* Table */}
            <div className="overflow-x-auto">
                {children(paginatedData)}
            </div>

            {/* Footer */}
            <div className="flex flex-col items-center justify-between gap-3 border-t px-4 py-3 sm:flex-row">
                {/* Rows per page */}
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <span className="whitespace-nowrap">Linhas por página</span>
                    <Select
                        value={String(rowsPerPage)}
                        onValueChange={handleRowsPerPageChange}
                    >
                        <SelectTrigger className="h-8 w-16 text-xs">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {rowsPerPageOptions.map((option) => (
                                <SelectItem
                                    key={option}
                                    value={String(option)}
                                    className="text-xs"
                                >
                                    {option}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                {/* Info + navigation */}
                <div className="flex items-center gap-3">
                    <span className="text-xs text-muted-foreground whitespace-nowrap">
                        {totalRows === 0
                            ? 'Nenhum registro'
                            : `${firstRow}–${lastRow} de ${totalRows}`}
                    </span>

                    <div className="flex items-center gap-1">
                        <Button
                            variant="outline"
                            size="icon"
                            className="h-7 w-7"
                            onClick={() => setCurrentPage(1)}
                            disabled={safePage === 1}
                        >
                            <ChevronsLeft className="h-3.5 w-3.5" />
                            <span className="sr-only">Primeira página</span>
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            className="h-7 w-7"
                            onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                            disabled={safePage === 1}
                        >
                            <ChevronLeft className="h-3.5 w-3.5" />
                            <span className="sr-only">Página anterior</span>
                        </Button>
                        <span className="px-1 text-xs text-muted-foreground whitespace-nowrap">
                            {safePage} / {totalPages}
                        </span>
                        <Button
                            variant="outline"
                            size="icon"
                            className="h-7 w-7"
                            onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
                            disabled={safePage === totalPages}
                        >
                            <ChevronRight className="h-3.5 w-3.5" />
                            <span className="sr-only">Próxima página</span>
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            className="h-7 w-7"
                            onClick={() => setCurrentPage(totalPages)}
                            disabled={safePage === totalPages}
                        >
                            <ChevronsRight className="h-3.5 w-3.5" />
                            <span className="sr-only">Última página</span>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    );
}