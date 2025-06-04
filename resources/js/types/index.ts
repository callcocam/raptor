// Tipos para paginação
export interface PaginationMeta {
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    per_page: number;
}

export interface PaginationLinks {
    prev: string | null;
    next: string | null;
}

export interface PaginatedData<T = any> {
    data: T[];
    meta: PaginationMeta;
    links: PaginationLinks;
}

// Tipos para colunas da tabela
export interface TableColumn {
    id?: string;
    accessorKey: string;
    header: string;
    sortable?: boolean;
    searchable?: boolean;
    hideable?: boolean;
    enableHiding?: boolean;
    type?: 'text' | 'number' | 'date' | 'boolean' | 'select' | 'image' | 'html';
    formatter?: 'formatDate' | 'renderBadge' | 'currency' | 'percentage';
    formatterOptions?: string | Record<string, any>;
    options?: Record<string, any>;
    cell?: (row: any) => string | React.ReactNode;
    [key: string]: unknown;
}

// Tipos para ações da tabela
export interface Action {
    id: string;
    icon: string;
    color: 'primary' | 'secondary' | 'success' | 'warning' | 'danger';
    routeNameBase: string;
    routeSuffix: string;
    isHtml: boolean;
    header: string;
    accessorKey: string;
    tooltip?: string;
    [key: string]: unknown;
}

// Tipos para filtros
export interface FilterOption {
    column: string;
    type: 'select' | 'text' | 'date' | 'boolean';
    label: string;
    options?: Array<{
        label: string;
        value: string | number;
        count?: number;
    }>;
    [key: string]: unknown;
}

// Tipos para props do DataTable
export interface DataTableProps<T = any> {
    data: PaginatedData<T>;
    columns: TableColumn[];
    actions?: Action[];
    filters?: Record<string, unknown>;
    filterOptions?: FilterOption[];
    searchable?: boolean;
    sortable?: boolean;
    routeNameBase: string;
    onSearch?: (search: string) => void;
    onSort?: (column: string, direction: 'asc' | 'desc') => void;
    onFilter?: (filters: Record<string, unknown>) => void;
    onPageChange?: (page: number) => void;
    onPerPageChange?: (perPage: number) => void;
}

// Tipos para configuração de colunas do TanStack Table
export interface DataTableColumn<T = any> {
    accessorKey: string;
    header: string | React.ReactNode;
    cell?: (info: any) => React.ReactNode;
    enableSorting?: boolean;
    enableHiding?: boolean;
    meta?: {
        type?: TableColumn['type'];
        formatter?: TableColumn['formatter'];
        options?: TableColumn['options'];
    };
} 