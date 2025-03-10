
interface HeaderAction {
  label: string;
  icon: string;
  route: string;
  color?: string;
  class?: string;
  target?: string;
  component?: string;
  iconSize?: number;
  method?: 'get' | 'post' | 'put' | 'delete' | 'patch';
}

interface FilterOption {
  label: string
  value: string
  icon?: any
}


interface DataTableToolbarProps {
  table: any
  searchColumn?: string
  searchPlaceholder?: string
  filters?: Filter[]
}

interface Column {
  key: string
  name?: string
  label: string
  sortable?: boolean
  searchable?: boolean
  className?: string
  format?: (value: any) => any
}
 
interface TableRow {
  id: string | number
  actions?: Action[]
  [key: string]: any
}

interface FilterOption {
  label: string
  value: string
  icon?: any
}

interface Filter {
  name: string
  label: string
  column: string
  component: string
  options?: FilterOption[]
  placeholder?: string
  value?: string | null
  routeName?: string
  routeParams?: Record<string, any>
}

interface Pagination {
  currentPage: number
  perPage: number
  total: number
}

interface BulkAction {
  label: string
  action: string
  icon?: string
  variant?: 'link' | 'default' | 'secondary' | 'destructive' | 'outline' | 'ghost'
  shortcut?: string
  route?: string
  routeParams?: Record<string, any>
  href?: string
}

interface TableConfig {
  indeterminate?: boolean
  selectable?: boolean
  actionsType?: 'dropdown' | 'inline'
  model?: string
  routeName: string,
  breadcrumbs?: string[]
  fullWidth?: boolean, 
}

interface Confirmation {
  title: string
  description?: string
  confirmText?: string
  cancelText?: string
  variant?: 'default' | 'destructive'
}

interface Action {
  label: string
  action: string
  icon?: string
  variant?: 'link' | 'default' | 'secondary' | 'destructive' | 'outline' | 'ghost'
  shortcut?: string
  route?: string
  routeParams?: Record<string, any>
  href?: string
  confirmation?: Confirmation,
  method?: 'post' | 'delete' | 'put' | 'patch' | 'get'
}

export type { FilterOption, DataTableToolbarProps, Pagination, Column, TableRow, Action, Filter, BulkAction, TableConfig, Confirmation , HeaderAction}
