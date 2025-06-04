import { useMemo } from 'react';
import {
  useReactTable,
  getCoreRowModel,
  getSortedRowModel,
  getFilteredRowModel,
  ColumnDef,
  SortingState,
  ColumnFiltersState,
  VisibilityState,
} from '@tanstack/react-table';
import { TableColumn, PaginatedData } from '../types';

interface UseDataTableProps<T = any> {
  data: PaginatedData<T>;
  columns: TableColumn[];
  routeNameBase: string;
  sorting?: SortingState;
  setSorting?: (sorting: SortingState) => void;
  columnFilters?: ColumnFiltersState;
  setColumnFilters?: (filters: ColumnFiltersState) => void;
  columnVisibility?: VisibilityState;
  setColumnVisibility?: (visibility: VisibilityState) => void;
}

export function useDataTable<T = any>({
  data,
  columns,
  routeNameBase,
  sorting = [],
  setSorting,
  columnFilters = [],
  setColumnFilters,
  columnVisibility = {},
  setColumnVisibility,
}: UseDataTableProps<T>) {
  
  // Converter TableColumn[] para ColumnDef[] do TanStack Table
  const tableColumns = useMemo<ColumnDef<T>[]>(() => {
    return columns.map((column) => ({
      accessorKey: column.accessorKey,
      header: column.header,
      enableSorting: column.sortable ?? false,
      enableHiding: column.hideable ?? false,
      cell: ({ getValue }) => {
        const value = getValue();
        return formatValue(value, column.type);
      },
    }));
  }, [columns]);

  // Configurar a tabela
  const table = useReactTable({
    data: data.data,
    columns: tableColumns,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: setSorting,
    onColumnFiltersChange: setColumnFilters,
    onColumnVisibilityChange: setColumnVisibility,
    state: {
      sorting,
      columnFilters,
      columnVisibility,
    },
    manualPagination: true,
    manualSorting: true,
    manualFiltering: true,
  });

  return {
    table,
    pagination: data.meta,
    links: data.links,
  };
}

// Helper para formatar valores básicos
function formatValue(value: any, type?: string): string {
  if (value === null || value === undefined) return '-';
  
  switch (type) {
    case 'boolean':
      return value ? 'Sim' : 'Não';
    case 'date':
      try {
        return new Date(value).toLocaleDateString('pt-BR');
      } catch {
        return String(value);
      }
    case 'number':
      return typeof value === 'number' ? value.toLocaleString('pt-BR') : String(value);
    default:
      return String(value);
  }
} 