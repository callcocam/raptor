import { useState, useMemo, useCallback, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { PaginatedData, TableColumn, FilterOption } from '../types';

interface UseDataTableAdvancedProps<T = any> {
  data: PaginatedData<T>;
  columns: TableColumn[];
  routeNameBase: string;
  filterOptions?: FilterOption[];
  searchable?: boolean;
  sortable?: boolean;
  selectable?: boolean;
}

interface SortConfig {
  column: string;
  direction: 'asc' | 'desc';
}

export function useDataTableAdvanced<T = any>({
  data,
  columns,
  routeNameBase,
  filterOptions = [],
  searchable = true,
  sortable = true,
  selectable = false,
}: UseDataTableAdvancedProps<T>) {
  
  // Função para ler parâmetros da URL atual (vindos do backend)
  const getUrlParams = useCallback(() => {
    const url = new URL(window.location.href);
    const params: Record<string, string> = {};
    
    // Ler todos os parâmetros da URL
    url.searchParams.forEach((value, key) => {
      params[key] = value;
    });
    
    return params;
  }, []);

  // Inicializar estados com valores da URL (backend)
  const urlParams = getUrlParams();
  
  const [searchValue, setSearchValue] = useState(urlParams.search || '');
  const [activeFilters, setActiveFilters] = useState<Record<string, string>>(() => {
    const filters: Record<string, string> = {};
    filterOptions.forEach(filter => {
      if (urlParams[filter.column]) {
        filters[filter.column] = urlParams[filter.column];
      }
    });
    return filters;
  });
  
  const [sortConfig, setSortConfig] = useState<SortConfig | null>(() => {
    if (urlParams.sort && urlParams.direction) {
      return {
        column: urlParams.sort,
        direction: urlParams.direction as 'asc' | 'desc'
      };
    }
    return null;
  });
  
  const [selectedRows, setSelectedRows] = useState<Set<string | number>>(new Set());
  const [isAllSelected, setIsAllSelected] = useState(false);

  // Sincronizar estados quando URL mudar (navegação do browser)
  useEffect(() => {
    const params = getUrlParams();
    
    setSearchValue(params.search || '');
    
    // Atualizar filtros ativos
    const newFilters: Record<string, string> = {};
    filterOptions.forEach(filter => {
      if (params[filter.column]) {
        newFilters[filter.column] = params[filter.column];
      }
    });
    setActiveFilters(newFilters);
    
    // Atualizar ordenação
    if (params.sort && params.direction) {
      setSortConfig({
        column: params.sort,
        direction: params.direction as 'asc' | 'desc'
      });
    } else {
      setSortConfig(null);
    }
  }, [filterOptions, getUrlParams]);

  // Função para construir URL com parâmetros
  const buildUrl = useCallback((params: Record<string, any>) => {
    const url = new URL(window.location.href);
    
    // Limpar parâmetros existentes de filtros/busca/ordenação
    url.searchParams.delete('search');
    url.searchParams.delete('sort');
    url.searchParams.delete('direction');
    filterOptions.forEach(filter => {
      url.searchParams.delete(filter.column);
    });

    // Adicionar novos parâmetros
    Object.entries(params).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        url.searchParams.set(key, String(value));
      }
    });

    return url.toString();
  }, [filterOptions]);

  // Aplicar busca com debounce
  const applySearch = useCallback((value: string) => {
    const params: Record<string, any> = {
      search: value,
      ...activeFilters,
    };

    if (sortConfig) {
      params.sort = sortConfig.column;
      params.direction = sortConfig.direction;
    }

    const url = buildUrl(params);
    router.get(url, {}, { 
      preserveState: true,
      preserveScroll: true,
      replace: true 
    });
  }, [activeFilters, sortConfig, buildUrl]);

  // Aplicar filtro
  const applyFilter = useCallback((column: string, value: string) => {
    const newFilters = { ...activeFilters };
    
    if (value === '' || value === null) {
      delete newFilters[column];
    } else {
      newFilters[column] = value;
    }

    setActiveFilters(newFilters);

    const params: Record<string, any> = {
      search: searchValue,
      ...newFilters,
    };

    if (sortConfig) {
      params.sort = sortConfig.column;
      params.direction = sortConfig.direction;
    }

    const url = buildUrl(params);
    router.get(url, {}, { 
      preserveState: true,
      preserveScroll: true,
      replace: true 
    });
  }, [activeFilters, searchValue, sortConfig, buildUrl]);

  // Aplicar ordenação
  const applySort = useCallback((column: string) => {
    let direction: 'asc' | 'desc' = 'asc';
    
    if (sortConfig?.column === column && sortConfig.direction === 'asc') {
      direction = 'desc';
    }

    const newSortConfig = { column, direction };
    setSortConfig(newSortConfig);

    const params: Record<string, any> = {
      search: searchValue,
      sort: column,
      direction,
      ...activeFilters,
    };

    const url = buildUrl(params);
    router.get(url, {}, { 
      preserveState: true,
      preserveScroll: true,
      replace: true 
    });
  }, [searchValue, activeFilters, sortConfig, buildUrl]);

  // Gerenciar seleção de linhas
  const toggleRowSelection = useCallback((id: string | number) => {
    setSelectedRows(prev => {
      const newSet = new Set(prev);
      if (newSet.has(id)) {
        newSet.delete(id);
      } else {
        newSet.add(id);
      }
      
      // Atualizar estado de "selecionar todos"
      setIsAllSelected(newSet.size === data.data.length && data.data.length > 0);
      
      return newSet;
    });
  }, [data.data.length]);

  // Selecionar/desselecionar todos
  const toggleAllSelection = useCallback(() => {
    if (isAllSelected) {
      setSelectedRows(new Set());
      setIsAllSelected(false);
    } else {
      const allIds = data.data.map((row: any) => row.id).filter(Boolean);
      setSelectedRows(new Set(allIds));
      setIsAllSelected(true);
    }
  }, [isAllSelected, data.data]);

  // Limpar seleção
  const clearSelection = useCallback(() => {
    setSelectedRows(new Set());
    setIsAllSelected(false);
  }, []);

  // Busca com debounce
  const debouncedSearch = useMemo(() => {
    let timeoutId: NodeJS.Timeout;
    
    return (value: string) => {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => {
        applySearch(value);
      }, 300);
    };
  }, [applySearch]);

  // Handler para mudança de busca
  const handleSearchChange = useCallback((value: string) => {
    setSearchValue(value);
    if (searchable) {
      debouncedSearch(value);
    }
  }, [searchable, debouncedSearch]);

  // Dados filtrados localmente (para preview antes da requisição)
  const filteredData = useMemo(() => {
    let filtered = [...data.data];

    // Aplicar busca local (para feedback imediato)
    if (searchValue && searchable) {
      const searchLower = searchValue.toLowerCase();
      filtered = filtered.filter((row: any) => {
        return columns.some(column => {
          const value = row[column.accessorKey];
          return value && String(value).toLowerCase().includes(searchLower);
        });
      });
    }

    return filtered;
  }, [data.data, searchValue, searchable, columns]);

  // Informações de seleção
  const selectionInfo = useMemo(() => ({
    selectedCount: selectedRows.size,
    isAllSelected,
    selectedIds: Array.from(selectedRows),
    hasSelection: selectedRows.size > 0,
  }), [selectedRows, isAllSelected]);

  return {
    // Estados
    searchValue,
    activeFilters,
    sortConfig,
    selectedRows,
    isAllSelected,
    
    // Dados
    filteredData,
    originalData: data,
    
    // Handlers
    handleSearchChange,
    applyFilter,
    applySort,
    toggleRowSelection,
    toggleAllSelection,
    clearSelection,
    
    // Info de seleção
    selectionInfo,
    
    // Configurações
    searchable,
    sortable,
    selectable,
  };
} 