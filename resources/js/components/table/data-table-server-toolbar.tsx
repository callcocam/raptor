"use client"

import * as React from "react"
import { X } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { DataTableServerFacetedFilter } from "./data-table-server-faceted-filter"
import { 
  type BackendFilter, 
  type TableQueryParams 
} from "@raptor/lib/column-builder"

interface DataTableServerToolbarProps {
  searchPlaceholder?: string
  filters?: BackendFilter[]
  onQueryChange: (params: TableQueryParams) => void
  currentQuery: TableQueryParams
}

export function DataTableServerToolbar({
  searchPlaceholder = "Filter tasks...",
  filters = [],
  onQueryChange,
  currentQuery
}: DataTableServerToolbarProps) {
  const [searchValue, setSearchValue] = React.useState(currentQuery.search || "")
  
  // 🔧 UseRef para manter referência estável da função
  const onQueryChangeRef = React.useRef(onQueryChange)
  onQueryChangeRef.current = onQueryChange
  
  // 🔧 Atualizar searchValue quando currentQuery.search mudar (navegação/reload)
  React.useEffect(() => {
    setSearchValue(currentQuery.search || "")
  }, [currentQuery.search])
  
  // 🔧 Debounce melhorado - só executa se realmente mudou
  React.useEffect(() => {
    // Se o valor local é igual ao do query atual, não fazer nada
    if (searchValue === (currentQuery.search || "")) {
      return
    }
    
    const timer = setTimeout(() => {
      onQueryChangeRef.current({
        ...currentQuery,
        search: searchValue || undefined,
        page: 1 // Reset para primeira página na busca
      })
    }, 500)
    
    return () => clearTimeout(timer)
  }, [searchValue, currentQuery.search, currentQuery.page, currentQuery.per_page, currentQuery.sort_by, currentQuery.sort_direction])
  
  // 🎯 Handler para mudanças nos filtros facetados
  const handleFilterChange = (filterColumn: string, selectedValues: string[]) => {
    const newFilters = { ...currentQuery.filters }
    
    if (selectedValues.length > 0) {
      newFilters[filterColumn] = selectedValues.join(',')
    } else {
      delete newFilters[filterColumn]
    }
    
    onQueryChange({
      ...currentQuery,
      filters: Object.keys(newFilters).length > 0 ? newFilters : undefined,
      page: 1 // Reset para primeira página ao filtrar
    })
  }
  
  // 🎯 Obter valores selecionados para um filtro
  const getSelectedValues = (filterColumn: string): string[] => {
    const filterValue = currentQuery.filters?.[filterColumn]
    if (!filterValue) return []
    
    // Se for string com vírgulas, dividir em array
    if (typeof filterValue === 'string') {
      return filterValue.split(',').filter(v => v.trim())
    }
    
    // Se for array, retornar como está
    if (Array.isArray(filterValue)) {
      return filterValue
    }
    
    return []
  }
  
  const clearAllFilters = () => {
    setSearchValue("")
    onQueryChange({
      ...currentQuery,
      search: undefined,
      filters: undefined,
      page: 1
    })
  }
  
  const hasActiveFilters = searchValue || (currentQuery.filters && Object.keys(currentQuery.filters).length > 0)

  return (
    <div className="flex items-center justify-between">
      <div className="flex flex-1 items-center space-x-2">
        <Input
          placeholder={searchPlaceholder}
          value={searchValue}
          onChange={(event) => setSearchValue(event.target.value)}
          className="h-8 w-[150px] lg:w-[250px]"
        />
        
        {/* 🎯 Filtros dinâmicos com DataTableServerFacetedFilter */}
        {filters.map((filter) => (
          <DataTableServerFacetedFilter
            key={filter.column}
            title={filter.label}
            options={filter.options}
            selectedValues={getSelectedValues(filter.column)}
            onSelectionChange={(values) => handleFilterChange(filter.column, values)}
          />
        ))}
        
        {hasActiveFilters && (
          <Button
            variant="ghost"
            onClick={clearAllFilters}
            className="h-8 px-2 lg:px-3"
          >
            Reset
            <X />
          </Button>
        )}
      </div>
    </div>
  )
} 