import React, { useState } from 'react';
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
import { DataTableProps, TableColumn } from '../../types';
import { Settings, ArrowUpDown, ChevronLeft, ChevronRight } from 'lucide-react';

export function DataTable<T = any>({
  data,
  columns,
  actions = [],
  routeNameBase,
  searchable = true,
  pageTitle,
  pageDescription,
  filters,
  filterOptions = [],
}: DataTableProps<T> & {
  pageTitle?: string;
  pageDescription?: string;
}) {
  const [searchValue, setSearchValue] = useState('');
  const [activeFilters, setActiveFilters] = useState<Record<string, string>>({});

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
          <Badge variant={value ? 'success' : 'gray'}>
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
            <Badge variant={variantMap[variant] || 'secondary'}>
              {value}
            </Badge>
          );
        }
        return String(value);
    }
  };

  // Função para renderizar ações modernas
  const renderActions = (row: T) => {
    if (!actions || actions.length === 0) return null;
    
    return (
      <div className="flex items-center gap-2">
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

  // Função para renderizar filtros
  const renderFilters = () => {
    if (!filterOptions || filterOptions.length === 0) return null;

    return (
      <div className="flex items-center gap-4">
        {filterOptions.map((filter) => (
          <div key={filter.column} className="flex items-center gap-2">
            <span className="text-sm font-medium text-muted-foreground">
              {filter.label}:
            </span>
            {filter.type === 'select' && filter.options ? (
              <div className="flex gap-1">
                <Button
                  variant={!activeFilters[filter.column] ? 'secondary' : 'outline'}
                  size="sm"
                  onClick={() => {
                    const newFilters = { ...activeFilters };
                    delete newFilters[filter.column];
                    setActiveFilters(newFilters);
                  }}
                >
                  Todos
                </Button>
                {filter.options.map((option) => (
                  <Button
                    key={option.value}
                    variant={activeFilters[filter.column] === option.value ? 'secondary' : 'outline'}
                    size="sm"
                    onClick={() => {
                      setActiveFilters(prev => ({
                        ...prev,
                        [filter.column]: String(option.value)
                      }));
                    }}
                  >
                    {option.label}
                  </Button>
                ))}
              </div>
            ) : (
              <Input
                placeholder={`Filtrar por ${filter.label.toLowerCase()}...`}
                value={activeFilters[filter.column] || ''}
                onChange={(e) => {
                  setActiveFilters(prev => ({
                    ...prev,
                    [filter.column]: e.target.value
                  }));
                }}
                className="w-48"
              />
            )}
          </div>
        ))}
      </div>
    );
  };

  return (
    <div className="space-y-6">
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

      {/* Barra de filtros e busca */}
      <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        {/* Filtros */}
        {renderFilters()}
        
        {/* Busca */}
        {searchable && (
          <div className="flex items-center gap-4">
            <Input
              placeholder="Filtrar registros..."
              value={searchValue}
              onChange={(e) => setSearchValue(e.target.value)}
              className="max-w-sm"
            />
            <Button variant="outline" size="sm">
              <Settings className="w-4 h-4 mr-2" />
              Opções
            </Button>
          </div>
        )}
      </div>

      {/* Tabela moderna */}
      <div className="rounded-md border">
        <Table>
          <TableHeader>
            <TableRow>
              {columns.map((column) => (
                <TableHead key={column.accessorKey} className="font-medium">
                  <div className="flex items-center gap-2">
                    {column.header}
                    {column.sortable && (
                      <ArrowUpDown className="w-4 h-4 text-muted-foreground" />
                    )}
                  </div>
                </TableHead>
              ))}
              {actions && actions.length > 0 && (
                <TableHead className="w-[100px]">Ações</TableHead>
              )}
            </TableRow>
          </TableHeader>
          <TableBody>
            {data.data.length > 0 ? (
              data.data.map((row, index) => (
                <TableRow key={index} className="hover:bg-muted/50">
                  {columns.map((column) => (
                    <TableCell key={column.accessorKey}>
                      {formatCellValue((row as any)[column.accessorKey], column)}
                    </TableCell>
                  ))}
                  {actions && actions.length > 0 && (
                    <TableCell>
                      {renderActions(row)}
                    </TableCell>
                  )}
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell 
                  colSpan={columns.length + (actions && actions.length > 0 ? 1 : 0)} 
                  className="h-24 text-center text-muted-foreground"
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
        pagination={data.meta}
        links={data.links}
      />
    </div>
  );
}

// Componente para botões de ação modernos
interface ActionButtonProps {
  action: any;
  row: any;
  routeNameBase: string;
}

function ActionButton({ action, row, routeNameBase }: ActionButtonProps) {
  const getActionUrl = (action: any, row: any): string => {
    const basePath = routeNameBase.replace('.', '/');
    
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
  };

  const url = getActionUrl(action, row);

  const getButtonVariant = (color: string) => {
    switch (color) {
      case 'primary': return 'default';
      case 'danger': return 'destructive';
      case 'warning': return 'outline';
      case 'success': return 'secondary';
      default: return 'ghost';
    }
  };

  return (
    <Button
      asChild
      variant={getButtonVariant(action.color)}
      size="sm"
      className="h-8 w-8 p-0"
    >
      <a href={url} title={action.tooltip || action.header}>
        <span className="sr-only">{action.header}</span>
        {action.icon}
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