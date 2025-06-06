"use client"

import * as React from "react"
import {
  ColumnDef,
  VisibilityState,
  flexRender,
  getCoreRowModel,
  useReactTable,
} from "@tanstack/react-table"

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

import { DataTableServerPagination } from "./data-table-server-pagination"
import { DataTableServerToolbar } from "./data-table-server-toolbar"
import { DataTableViewOptions } from "./data-table-view-options"
import { DataTableBulkActions } from "./data-table-bulk-actions"
import { 
  type BackendFilter, 
  type LaravelPaginationMeta,
  type TableQueryParams,
  type BulkAction
} from "@raptor/lib/column-builder"

interface DataTableServerProps<TData, TValue> {
  columns: ColumnDef<TData, TValue>[]
  data: TData[]
  meta: LaravelPaginationMeta
  searchPlaceholder?: string
  filters?: BackendFilter[]
  bulkActions?: BulkAction[]
  routeNameBase?: string
  onQueryChange: (params: TableQueryParams) => void
  currentQuery: TableQueryParams
}

export function DataTableServer<TData, TValue>({
  columns,
  data,
  meta,
  searchPlaceholder,
  filters,
  bulkActions,
  routeNameBase,
  onQueryChange,
  currentQuery
}: DataTableServerProps<TData, TValue>) {
  const [rowSelection, setRowSelection] = React.useState({})
  const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>({})

  // Usar TanStack Table apenas para renderização e visibilidade de colunas
  // Sem paginação, filtros ou ordenação client-side
  const table = useReactTable({
    data,
    columns,
    state: {
      columnVisibility,
      rowSelection,
    },
    enableRowSelection: true,
    onRowSelectionChange: setRowSelection,
    onColumnVisibilityChange: setColumnVisibility,
    getCoreRowModel: getCoreRowModel(),
    // Remover funcionalidades client-side
    manualPagination: true,
    manualSorting: true,
    manualFiltering: true,
  })

  const selectedRowsCount = Object.keys(rowSelection).length

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <DataTableServerToolbar 
          searchPlaceholder={searchPlaceholder}
          filters={filters}
          onQueryChange={onQueryChange}
          currentQuery={currentQuery}
        />
        <DataTableViewOptions table={table} />
      </div>
      
      {/* 🆕 Bulk Actions Toolbar - aparece quando há seleção */}
      <DataTableBulkActions table={table} bulkActions={bulkActions} routeNameBase={routeNameBase} />
      
      <div className="rounded-md border">
        <Table>
          <TableHeader>
            {table.getHeaderGroups().map((headerGroup) => (
              <TableRow key={headerGroup.id}>
                {headerGroup.headers.map((header) => {
                  return (
                    <TableHead key={header.id} colSpan={header.colSpan}>
                      {header.isPlaceholder
                        ? null
                        : flexRender(
                            header.column.columnDef.header,
                            header.getContext()
                          )}
                    </TableHead>
                  )
                })}
              </TableRow>
            ))}
          </TableHeader>
          <TableBody>
            {data?.length ? (
              table.getRowModel().rows.map((row) => (
                <TableRow
                  key={row.id}
                  data-state={row.getIsSelected() && "selected"}
                >
                  {row.getVisibleCells().map((cell) => (
                    <TableCell key={cell.id}>
                      {flexRender(
                        cell.column.columnDef.cell,
                        cell.getContext()
                      )}
                    </TableCell>
                  ))}
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell
                  colSpan={columns.length}
                  className="h-24 text-center"
                >
                  Nenhum resultado encontrado.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>
      
      <DataTableServerPagination 
        meta={meta}
        selectedRowsCount={selectedRowsCount}
        onQueryChange={onQueryChange}
        currentQuery={currentQuery}
      />
    </div>
  )
} 