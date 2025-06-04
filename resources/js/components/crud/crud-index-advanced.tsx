import React from 'react';
import { DataTableAdvanced } from '../data-table/data-table-advanced';
import { DataTableProps } from '../../types';
import { Button } from '../ui/button';
import { Trash2, Download, Plus } from 'lucide-react';

interface CrudIndexAdvancedProps extends DataTableProps {
  pageTitle?: string;
  pageDescription?: string;
  showCreateButton?: boolean;
  createButtonText?: string;
  selectable?: boolean;
  bulkActions?: Array<{
    id: string;
    label: string;
    icon?: string | React.ReactNode;
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost';
  }>;
  onBulkAction?: (action: string, selectedIds: (string | number)[]) => void;
  can?: {
    create_resource?: boolean;
    edit_resource?: boolean;
    show_resource?: boolean;
    destroy_resource?: boolean;
  };
}

export function CrudIndexAdvanced({
  data,
  columns,
  actions,
  routeNameBase,
  pageTitle,
  pageDescription,
  showCreateButton = true,
  createButtonText = 'Criar Novo',
  selectable = false,
  bulkActions = [],
  onBulkAction,
  can = {},
  ...props
}: CrudIndexAdvancedProps) {
  
  // Função helper para gerar URL de criação
  const getCreateUrl = (): string => {
    const basePath = routeNameBase.replace('.', '/');
    return `/${basePath}/create`;
  };

  // Ações em lote padrão se não fornecidas
  const defaultBulkActions = bulkActions.length > 0 ? bulkActions : [
    {
      id: 'delete',
      label: 'Excluir Selecionados',
      icon: <Trash2 className="h-4 w-4" />,
      variant: 'destructive' as const,
    },
    {
      id: 'export',
      label: 'Exportar',
      icon: <Download className="h-4 w-4" />,
      variant: 'outline' as const,
    },
  ];

  // Handler padrão para ações em lote
  const handleBulkAction = (action: string, selectedIds: (string | number)[]) => {
    if (onBulkAction) {
      onBulkAction(action, selectedIds);
    } else {
      // Implementação padrão
      switch (action) {
        case 'delete':
          if (confirm(`Tem certeza que deseja excluir ${selectedIds.length} item(s)?`)) {
            console.log('Excluindo itens:', selectedIds);
            // Aqui seria feita a requisição para excluir
          }
          break;
        case 'export':
          console.log('Exportando itens:', selectedIds);
          // Aqui seria feita a exportação
          break;
        default:
          console.log(`Ação ${action} executada para:`, selectedIds);
      }
    }
  };

  return (
    <div className="space-y-6">
      {/* Header com botão de criar */}
      <div className="flex items-center justify-between">
        <div className="space-y-1">
          {pageTitle && (
            <h1 className="text-3xl font-bold tracking-tight">{pageTitle}</h1>
          )}
          {pageDescription && (
            <p className="text-muted-foreground">{pageDescription}</p>
          )}
        </div>
        
        {showCreateButton && can.create_resource && (
          <Button asChild>
            <a href={getCreateUrl()}>
              <Plus className="h-4 w-4 mr-2" />
              {createButtonText}
            </a>
          </Button>
        )}
      </div>

      {/* DataTable Avançada */}
      <DataTableAdvanced
        data={data}
        columns={columns}
        actions={actions}
        routeNameBase={routeNameBase}
        pageTitle={undefined} // Evita duplicação do header
        pageDescription={undefined}
        selectable={selectable}
        bulkActions={selectable ? defaultBulkActions : []}
        onBulkAction={handleBulkAction}
        {...props}
      />
    </div>
  );
} 