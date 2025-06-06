import {
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
} from "lucide-react"

import { Button } from "@/components/ui/button"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { type LaravelPaginationMeta, type TableQueryParams } from "@raptor/lib/column-builder"

interface DataTableServerPaginationProps {
  meta: LaravelPaginationMeta
  selectedRowsCount?: number
  onQueryChange: (params: TableQueryParams) => void
  currentQuery: TableQueryParams
}

export function DataTableServerPagination({
  meta,
  selectedRowsCount = 0,
  onQueryChange,
  currentQuery
}: DataTableServerPaginationProps) {
  
  const handlePageChange = (page: number) => {
    onQueryChange({
      ...currentQuery,
      page
    })
  }
  
  const handlePageSizeChange = (pageSize: string) => {
    onQueryChange({
      ...currentQuery,
      per_page: Number(pageSize),
      page: 1 // Reset para primeira página ao mudar tamanho
    })
  }

  return (
    <div className="flex items-center justify-between px-2">
      <div className="flex-1 text-sm text-muted-foreground">
        {selectedRowsCount > 0 && `${selectedRowsCount} selecionada(s). `}
        Mostrando {meta.from || 0} a {meta.to || 0} de {meta.total} registro(s).
      </div>
      <div className="flex items-center space-x-6 lg:space-x-8">
        <div className="flex items-center space-x-2">
          <p className="text-sm font-medium">Registros por página</p>
          <Select
            value={`${meta.per_page}`}
            onValueChange={handlePageSizeChange}
          >
            <SelectTrigger className="h-8 w-[70px]">
              <SelectValue placeholder={meta.per_page} />
            </SelectTrigger>
            <SelectContent side="top">
              {[10, 20, 30, 40, 50].map((pageSize) => (
                <SelectItem key={pageSize} value={`${pageSize}`}>
                  {pageSize}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
        <div className="flex w-[100px] items-center justify-center text-sm font-medium">
          Página {meta.current_page} de {meta.last_page}
        </div>
        <div className="flex items-center space-x-2">
          <Button
            variant="outline"
            className="hidden h-8 w-8 p-0 lg:flex"
            onClick={() => handlePageChange(1)}
            disabled={meta.current_page <= 1}
          >
            <span className="sr-only">Ir para primeira página</span>
            <ChevronsLeft />
          </Button>
          <Button
            variant="outline"
            className="h-8 w-8 p-0"
            onClick={() => handlePageChange(meta.current_page - 1)}
            disabled={meta.current_page <= 1}
          >
            <span className="sr-only">Página anterior</span>
            <ChevronLeft />
          </Button>
          <Button
            variant="outline"
            className="h-8 w-8 p-0"
            onClick={() => handlePageChange(meta.current_page + 1)}
            disabled={meta.current_page >= meta.last_page}
          >
            <span className="sr-only">Próxima página</span>
            <ChevronRight />
          </Button>
          <Button
            variant="outline"
            className="hidden h-8 w-8 p-0 lg:flex"
            onClick={() => handlePageChange(meta.last_page)}
            disabled={meta.current_page >= meta.last_page}
          >
            <span className="sr-only">Ir para última página</span>
            <ChevronsRight />
          </Button>
        </div>
      </div>
    </div>
  )
} 