export { DataTable } from './data-table'
export { DataTableColumnHeader } from './data-table-column-header'
export { DataTableFacetedFilter } from './data-table-faceted-filter'
export { DataTablePagination } from './data-table-pagination'
export { DataTableRowActions } from './data-table-row-actions'
export { DataTableToolbar } from './data-table-toolbar'
export { DataTableViewOptions } from './data-table-view-options'
export { columns } from './columns'

// 🆕 Server-side components
export { DataTableServer } from './data-table-server'
export { DataTableServerPagination } from './data-table-server-pagination'
export { DataTableServerToolbar } from './data-table-server-toolbar'
export { DataTableServerFacetedFilter } from './data-table-server-faceted-filter'
export { DataTableServerColumnHeader } from './data-table-server-column-header'
export { DataTableBulkActions } from './data-table-bulk-actions'
export { BulkActionConfirmDialog } from './bulk-action-confirm-dialog'

// Export data and schema
export { labels, priorities, statuses } from '@raptor/data/data'
export { taskSchema, type Task } from '@raptor/data/schema'

// Export column builder utilities
export { 
  buildColumnsFromBackend, 
  type BackendColumn, 
  type TableConfig, 
  type BackendFilter, 
  type FilterOption,
  // 🆕 Server-side types
  type LaravelPaginatedData,
  type LaravelPaginationMeta,
  type LaravelPaginationLinks,
  type TableQueryParams
} from '@raptor/lib/column-builder'

// Export hooks
export { useToast } from '@raptor/hooks/useToast' 