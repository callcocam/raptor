import { ArrowDown, ArrowUp, ChevronsUpDown, EyeOff } from "lucide-react"

import { cn } from "@raptor/lib/utils"
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { type TableQueryParams } from "@raptor/lib/column-builder"

interface DataTableServerColumnHeaderProps extends React.HTMLAttributes<HTMLDivElement> {
  title: string
  sortable: boolean
  columnKey: string
  currentQuery: TableQueryParams
  onQueryChange: (params: TableQueryParams) => void
  onToggleVisibility?: () => void
}

export function DataTableServerColumnHeader({
  title,
  sortable,
  columnKey,
  currentQuery,
  onQueryChange,
  onToggleVisibility,
  className,
}: DataTableServerColumnHeaderProps) {
  
  const handleSort = (direction: 'asc' | 'desc') => {
    onQueryChange({
      ...currentQuery,
      sort_by: columnKey,
      sort_direction: direction,
      page: 1, // Reset para primeira página
    })
  }

  const getCurrentSortDirection = (): 'asc' | 'desc' | null => {
    if (currentQuery.sort_by === columnKey) {
      return currentQuery.sort_direction === 'desc' ? 'desc' : 'asc'
    }
    return null
  }

  const currentSort = getCurrentSortDirection()

  if (!sortable) {
    return <div className={cn(className)}>{title}</div>
  }

  return (
    <div className={cn("flex items-center space-x-2", className)}>
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button
            variant="ghost"
            size="sm"
            className="-ml-3 h-8 data-[state=open]:bg-accent"
          >
            <span>{title}</span>
            {currentSort === "desc" ? (
              <ArrowDown />
            ) : currentSort === "asc" ? (
              <ArrowUp />
            ) : (
              <ChevronsUpDown />
            )}
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="start">
          <DropdownMenuItem onClick={() => handleSort('asc')}>
            <ArrowUp className="h-3.5 w-3.5 text-muted-foreground/70" />
            Asc
          </DropdownMenuItem>
          <DropdownMenuItem onClick={() => handleSort('desc')}>
            <ArrowDown className="h-3.5 w-3.5 text-muted-foreground/70" />
            Desc
          </DropdownMenuItem>
          {onToggleVisibility && (
            <>
              <DropdownMenuSeparator />
              <DropdownMenuItem onClick={onToggleVisibility}>
                <EyeOff className="h-3.5 w-3.5 text-muted-foreground/70" />
                Hide
              </DropdownMenuItem>
            </>
          )}
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  )
} 