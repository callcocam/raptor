import React from 'react';
import { DataTable } from '../data-table';
import { DataTableProps } from '../../types';
import { Button } from '../ui/button';

interface CrudIndexProps extends DataTableProps {
  pageTitle?: string;
  pageDescription?: string;
  showCreateButton?: boolean;
  createButtonText?: string;
  can?: {
    create_resource?: boolean;
    edit_resource?: boolean;
    show_resource?: boolean;
    destroy_resource?: boolean;
  };
}

export function CrudIndex({
  data,
  columns,
  actions,
  routeNameBase,
  pageTitle,
  pageDescription,
  showCreateButton = true,
  createButtonText = 'Criar Novo',
  can = {},
  ...props
}: CrudIndexProps) {
  
  // Função helper para gerar URL de criação
  const getCreateUrl = (): string => {
    const basePath = routeNameBase.replace('.', '/');
    return `/${basePath}/create`;
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
              <span className="mr-2">+</span>
              {createButtonText}
            </a>
          </Button>
        )}
      </div>

      {/* DataTable moderna - passa pageTitle e pageDescription como null para evitar duplicação */}
      <DataTable
        data={data}
        columns={columns}
        actions={actions}
        routeNameBase={routeNameBase}
        pageTitle={undefined} // Evita duplicação do header
        pageDescription={undefined}
        {...props}
      />
    </div>
  );
} 