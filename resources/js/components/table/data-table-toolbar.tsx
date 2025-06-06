"use client"

import * as React from "react"
import { Table } from "@tanstack/react-table"
import { X } from "lucide-react"

import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { DataTableViewOptions } from "./data-table-view-options"

import { DataTableFacetedFilter } from "./data-table-faceted-filter"
import { type BackendFilter } from "@raptor/lib/column-builder"

interface DataTableToolbarProps<TData> {
  table: Table<TData>
  searchPlaceholder?: string
  searchColumns?: string[]
  filters?: BackendFilter[]
}

export function DataTableToolbar<TData>({
  table,
  searchPlaceholder = "Filtrar...",
  searchColumns = ["title", "name", "email", "description"],
  filters = []
}: DataTableToolbarProps<TData>) {
  const isFiltered = table.getState().columnFilters.length > 0
  
  // Encontrar a primeira coluna disponível para busca
  const searchColumn = searchColumns.find(colId => table.getColumn(colId))
  const searchColumnObj = searchColumn ? table.getColumn(searchColumn) : null

  return (
    <div className="flex items-center justify-between">
      <div className="flex flex-1 items-center space-x-2">
        {searchColumnObj && (
          <Input
            placeholder={searchPlaceholder}
            value={(searchColumnObj.getFilterValue() as string) ?? ""}
            onChange={(event) =>
              searchColumnObj.setFilterValue(event.target.value)
            }
            className="h-8 w-[150px] lg:w-[250px]"
          />
        )}
        
        {/* Filtros dinâmicos do backend */}
        {filters.map((filter) => {
          const column = table.getColumn(filter.column)
          if (!column) return null
          
          return (
            <DataTableFacetedFilter
              key={filter.column}
              column={column}
              title={filter.label}
              options={filter.options}
            />
          )
        })}
        
        {isFiltered && (
          <Button
            variant="ghost"
            onClick={() => table.resetColumnFilters()}
            className="h-8 px-2 lg:px-3"
          >
            Reset
            <X />
          </Button>
        )}
      </div>
      <DataTableViewOptions table={table} />
    </div>
  )
}