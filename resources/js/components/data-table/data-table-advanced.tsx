import React from 'react';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '../ui/table';
import { Input } from '../ui/input';
import { Button } from '../ui/button';
import { Badge } from '../ui/badge';
import { Checkbox } from '../ui/checkbox';
import { DropdownFilter } from '../ui/select';
import { DataTableProps, TableColumn } from '../../types';
import { useDataTableAdvanced } from '../../hooks/use-data-table-advanced';
import { 
  Eye, 
  Edit, 
  Trash2, 
  ArrowUpDown, 
  ChevronUp, 
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  RotateCcw
} from 'lucide-react';

interface DataTableAdvancedProps<T = any> extends DataTableProps<T> {
  pageTitle?: string;
  pageDescription?: string;
  selectable?: boolean;
  onBulkAction?: (action: string, selectedIds: (string | number)[]) => void;
  bulkActions?: Array<{
    id: string;
    label: string;
    icon?: string | React.ReactNode;
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost';
  }>;
}

export function DataTableAdvanced<T = any>({
  data,
  columns,
  actions = [],
  routeNameBase,
  pageTitle,
  pageDescription,
  filterOptions = [],
  selectable = false,
  onBulkAction,
  bulkActions = [],
  ...props
}: DataTableAdvancedProps<T>) {
  
  const {
    searchValue,
    activeFilters,
    sortConfig,
    filteredData,
    originalData,
    handleSearchChange,
    applyFilter,
    applySort,
    toggleRowSelection,
    toggleAllSelection,
    clearSelection,
    selectionInfo,
    searchable,
    sortable,
  } = useDataTableAdvanced({
    data,
    columns,
    routeNameBase,
    filterOptions,
    searchable: props.searchable,
    sortable: props.sortable,
    selectable,
  });

  // Função para formatar valor da célula com badges modernos
  const formatCellValue = (value: any, column: TableColumn): React.ReactNode => {
    if (value === null || value === undefined) return '-';
    
    // Se tem função cell customizada
    if (column.cell) {
      return column.cell(value);
    }
    
    // Formatação por tipo
    switch (column.type) {
      case 'boolean':
        return (
          <Badge variant={value ? 'success' : 'gray'} className="text-xs">
            {value ? 'Sim' : 'Não'}
          </Badge>
        );
      case 'date':
        try {
          return new Date(value).toLocaleDateString('pt-BR');
        } catch {
          return String(value);
        }
      case 'number':
        return typeof value === 'number' ? value.toLocaleString('pt-BR') : String(value);
      case 'html':
        return <div dangerouslySetInnerHTML={{ __html: String(value) }} />;
      default:
        // Se tem formatter de badge, aplicar
        if (column.formatter === 'renderBadge' && column.options) {
          const variant = column.options[value] || 'secondary';
          const variantMap: Record<string, any> = {
            'primary': 'default',
            'secondary': 'secondary',
            'success': 'success',
            'warning': 'warning',
            'danger': 'destructive',
            'info': 'info',
            'purple': 'purple',
            'pink': 'pink',
            'gray': 'gray',
          };
          return (
            <Badge variant={variantMap[variant] || 'secondary'} className="text-xs">
              {value}
            </Badge>
          );
        }
        return String(value);
    }
  };

  // Função para renderizar ações de linha com ícones corretos
  const renderActions = (row: T) => {
    if (!actions || actions.length === 0) return null;
    
    return (
      <div className="flex items-center gap-1">
        {actions.map((action) => (
          <ActionButton 
            key={action.id}
            action={action}
            row={row}
            routeNameBase={routeNameBase}
          />
        ))}
      </div>
    );
  };

  // Função para renderizar filtros avançados com contadores
  const renderAdvancedFilters = () => {
    if (!filterOptions || filterOptions.length === 0) return null;

    return (
      <div className="flex items-center gap-3 flex-wrap">
        {filterOptions.map((filter) => (
          <div key={filter.column}>
            {filter.type === 'select' && filter.options ? (
              <DropdownFilter
                label={filter.label}
                value={activeFilters[filter.column] || ''}
                options={filter.options.map(option => ({
                  label: option.label,
                  value: String(option.value),
                  count: (option as any).count || undefined
                }))}
                onChange={(value) => applyFilter(filter.column, value)}
                showCounts={true}
              />
            ) : (
              <div className="flex items-center gap-2">
                <span className="text-sm font-medium text-muted-foreground">
                  {filter.label}:
                </span>
                <Input
                  placeholder={`Filtrar por ${filter.label.toLowerCase()}...`}
                  value={activeFilters[filter.column] || ''}
                  onChange={(e) => applyFilter(filter.column, e.target.value)}
                  className="w-48 h-9"
                />
              </div>
            )}
          </div>
        ))}
        
        {/* Botão Reset */}
        {Object.keys(activeFilters).length > 0 && (
          <Button
            variant="outline"
            size="sm"
            onClick={() => {
              Object.keys(activeFilters).forEach(key => {
                applyFilter(key, '');
              });
            }}
            className="h-9"
          >
            <RotateCcw className="w-4 h-4 mr-2" />
            Reset
          </Button>
        )}
      </div>
    );
  };

  // Função para renderizar header de ordenação
  const renderSortableHeader = (column: TableColumn) => {
    const isSorted = sortConfig?.column === column.accessorKey;
    const direction = sortConfig?.direction;

    return (
      <div 
        className={`flex items-center gap-2 ${sortable && column.sortable ? 'cursor-pointer hover:text-foreground' : ''}`}
        onClick={() => {
          if (sortable && column.sortable) {
            applySort(column.accessorKey);
          }
        }}
      >
        {column.header}
        {sortable && column.sortable && (
          <span className="text-muted-foreground">
            {isSorted ? (
              direction === 'asc' ? (
                <ChevronUp className="w-4 h-4" />
              ) : (
                <ChevronDown className="w-4 h-4" />
              )
            ) : (
              <ArrowUpDown className="w-4 h-4" />
            )}
          </span>
        )}
      </div>
    );
  };

  // Função para executar ação em lote
  const handleBulkAction = (actionId: string) => {
    if (onBulkAction && selectionInfo.selectedIds.length > 0) {
      onBulkAction(actionId, selectionInfo.selectedIds);
      clearSelection();
    }
  };

  return (
    <div className="space-y-4">
      {/* Header moderno */}
      {(pageTitle || pageDescription) && (
        <div className="space-y-1">
          {pageTitle && (
            <h2 className="text-2xl font-bold tracking-tight">{pageTitle}</h2>
          )}
          {pageDescription && (
            <p className="text-muted-foreground">{pageDescription}</p>
          )}
        </div>
      )}

      {/* Barra de ações em lote */}
      {selectable && selectionInfo.hasSelection && (
        <div className="flex items-center justify-between bg-muted/50 px-4 py-2 rounded-lg">
          <div className="flex items-center gap-2">
            <span className="text-sm font-medium">
              {selectionInfo.selectedCount} item(s) selecionado(s)
            </span>
            <Button variant="outline" size="sm" onClick={clearSelection}>
              Limpar seleção
            </Button>
          </div>
          {bulkActions.length > 0 && (
            <div className="flex items-center gap-2">
              {bulkActions.map((action) => (
                <Button
                  key={action.id}
                  variant={action.variant || 'outline'}
                  size="sm"
                  onClick={() => handleBulkAction(action.id)}
                >
                  {action.icon && <span className="mr-2">{action.icon}</span>}
                  {action.label}
                </Button>
              ))}
            </div>
          )}
        </div>
      )}

      {/* Barra de filtros e busca modernos */}
      <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        {/* Filtros avançados */}
        {renderAdvancedFilters()}
        
        {/* Busca */}
        {searchable && (
          <div className="flex items-center gap-3">
            <Input
              placeholder="Buscar registros..."
              value={searchValue}
              onChange={(e) => handleSearchChange(e.target.value)}
              className="max-w-sm h-9"
            />
          </div>
        )}
      </div>

      {/* Tabela moderna com espaçamento reduzido */}
      <div className="rounded-md border">
        <Table>
          <TableHeader>
            <TableRow>
              {selectable && (
                <TableHead className="w-[50px] py-2">
                  <Checkbox
                    checked={selectionInfo.isAllSelected}
                    indeterminate={selectionInfo.hasSelection && !selectionInfo.isAllSelected}
                    onChange={toggleAllSelection}
                  />
                </TableHead>
              )}
              {columns.map((column) => (
                <TableHead key={column.accessorKey} className="font-medium py-2">
                  {renderSortableHeader(column)}
                </TableHead>
              ))}
              {actions && actions.length > 0 && (
                <TableHead className="w-[100px] py-2">Ações</TableHead>
              )}
            </TableRow>
          </TableHeader>
          <TableBody>
            {originalData.data.length > 0 ? (
              originalData.data.map((row, index) => (
                <TableRow key={index} className="hover:bg-muted/50">
                  {selectable && (
                    <TableCell className="py-2">
                      <Checkbox
                        checked={selectionInfo.selectedIds.includes((row as any).id)}
                        onChange={() => toggleRowSelection((row as any).id)}
                      />
                    </TableCell>
                  )}
                  {columns.map((column) => (
                    <TableCell key={column.accessorKey} className="py-2">
                      {formatCellValue((row as any)[column.accessorKey], column)}
                    </TableCell>
                  ))}
                  {actions && actions.length > 0 && (
                    <TableCell className="py-2">
                      {renderActions(row)}
                    </TableCell>
                  )}
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell 
                  colSpan={columns.length + (actions && actions.length > 0 ? 1 : 0) + (selectable ? 1 : 0)} 
                  className="h-16 text-center text-muted-foreground"
                >
                  Nenhum resultado encontrado.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>

      {/* Paginação moderna */}
      <DataTablePagination 
        pagination={originalData.meta}
        links={originalData.links}
      />
    </div>
  );
}

// Componente para botões de ação modernos com ícones corretos
interface ActionButtonProps {
  action: any;
  row: any;
  routeNameBase: string;
}

function ActionButton({ action, row, routeNameBase }: ActionButtonProps) {
  const getActionUrl = (action: any, row: any): string => {
    // Usar as informações reais do backend para construir a rota
    const routeName = `${action.routeNameBase}.${action.routeSuffix}`;
    
    try {
      // Usar o helper route() do Ziggy com as informações do backend
      return route(routeName, row.id);
    } catch (error) {
      console.warn(`Rota não encontrada: ${routeName}`, error);
      // Fallback para URL manual se a rota não existir
      const basePath = action.routeNameBase.replace('.', '/');
      switch (action.routeSuffix) {
        case 'show':
          return `/${basePath}/${row.id}`;
        case 'edit':
          return `/${basePath}/${row.id}/edit`;
        case 'destroy':
          return `/${basePath}/${row.id}`;
        default:
          return `/${basePath}`;
      }
    }
  };

  const url = getActionUrl(action, row);

  const getButtonVariant = (color: string): 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link' => {
    switch (color) {
      case 'primary': return 'ghost';
      case 'danger': return 'ghost';
      case 'warning': return 'ghost';
      case 'success': return 'ghost';
      default: return 'ghost';
    }
  };

  // Ícones corretos para ações
  const getActionIcon = (routeSuffix: string) => {
    switch (routeSuffix) {
      case 'show':
        return <Eye className="w-4 h-4" />;
      case 'edit':
        return <Edit className="w-4 h-4" />;
      case 'destroy':
        return <Trash2 className="w-4 h-4" />;
      default:
        return action.icon ? (
          <img src={action.icon} alt={action.header} className="w-4 h-4" />
        ) : (
          <span className="text-sm">{routeSuffix.toUpperCase()}</span>
        );
    }
  };

  return (
    <Button
      asChild
      variant={getButtonVariant(action.color)}
      size="sm"
      className="h-7 w-7 p-0 hover:bg-muted"
      title={action.tooltip || action.header}
    >
      <a href={url}>
        <span className="sr-only">{action.header}</span>
        {getActionIcon(action.routeSuffix)}
      </a>
    </Button>
  );
}

// Componente de paginação moderna
interface DataTablePaginationProps {
  pagination: any;
  links: any;
}

function DataTablePagination({ pagination, links }: DataTablePaginationProps) {
  return (
    <div className="flex items-center justify-between space-x-6 lg:space-x-8">
      <div className="flex items-center space-x-2">
        <p className="text-sm font-medium">Linhas por página</p>
        <select className="h-8 w-[70px] rounded border border-input bg-background px-2 py-1 text-sm">
          <option value="10">10</option>
          <option value="20">20</option>
          <option value="30">30</option>
          <option value="40">40</option>
          <option value="50">50</option>
        </select>
      </div>
      
      <div className="flex w-[100px] items-center justify-center text-sm font-medium">
        Página {pagination.current_page} de {pagination.last_page}
      </div>
      
      <div className="flex items-center space-x-2">
        <p className="text-sm font-medium">
          {pagination.from}-{pagination.to} de {pagination.total}
        </p>
        <div className="flex items-center space-x-2">
          {links.prev ? (
            <Button variant="outline" className="h-8 w-8 p-0" asChild>
              <a href={links.prev}>
                <span className="sr-only">Página anterior</span>
                <ChevronLeft className="w-4 h-4" />
              </a>
            </Button>
          ) : (
            <Button variant="outline" className="h-8 w-8 p-0" disabled>
              <ChevronLeft className="w-4 h-4" />
            </Button>
          )}
          {links.next ? (
            <Button variant="outline" className="h-8 w-8 p-0" asChild>
              <a href={links.next}>
                <span className="sr-only">Próxima página</span>
                <ChevronRight className="w-4 h-4" />
              </a>
            </Button>
          ) : (
            <Button variant="outline" className="h-8 w-8 p-0" disabled>
              <ChevronRight className="w-4 h-4" />
            </Button>
          )}
        </div>
      </div>
    </div>
  );
} 