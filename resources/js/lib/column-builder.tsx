import * as React from "react"
import { ColumnDef } from "@tanstack/react-table"
import { Badge } from "@/components/ui/badge"
import { Checkbox } from "@/components/ui/checkbox"
import { Button } from "@/components/ui/button"
import { Link } from "@inertiajs/react"
import { router } from "@inertiajs/react"
import { DataTableColumnHeader } from "@raptor/components/table/data-table-column-header"
import { DataTableServerColumnHeader } from "@raptor/components/table/data-table-server-column-header"

// 🔥 Importar todos os ícones do Lucide que podem ser usados
import {
  Eye, EyeOff, PenSquare, Trash2, Plus, Minus, Check, X,
  Edit, Save, Settings, Info, AlertCircle,
  User, Users, Home, Search, Filter, Download, Upload,
  ChevronRight, ChevronLeft, ChevronUp, ChevronDown,
  ArrowRight, ArrowLeft, ArrowUp, ArrowDown,
  MoreHorizontal, MoreVertical, Menu, Copy, Share,
  Star, Heart, Bookmark, Flag, Tag, Calendar,
  Mail, Phone, MessageSquare, Bell, Shield,
  Lock, Unlock, Key, Globe, Wifi, Database,
  File, FileText, Image, Video, Music, Archive,
  Folder, FolderOpen, Cloud, CloudUpload, CloudDownload
} from "lucide-react"

export interface BackendColumn {
  accessorKey: string
  header: string
  id: string
  sortable: boolean
  enableHiding: boolean
  formatter?: string
  formatterOptions?: any
  // 🆕 Propriedades para ações dinâmicas
  component?: string // "Link", "Button", "CustomAction"
  routeNameBase?: string
  routeSuffix?: string
  icon?: string
  color?: string
  isHtml?: boolean
  variant?: string
  size?: string
  className?: string
  href?: string // Para links diretos
  target?: string // Para links externos
}

export interface FilterOption {
  value: string
  label: string
}

export interface BackendFilter {
  column: string
  label: string
  options: FilterOption[]
  type: string
}

// 🆕 Interfaces para paginação server-side do Laravel
export interface LaravelPaginationMeta {
  current_page: number
  from: number
  last_page: number
  path: string
  per_page: number
  to: number
  total: number
}

export interface LaravelPaginationLinks {
  first: string | null
  last: string | null
  prev: string | null
  next: string | null
}

export interface LaravelPaginatedData<T = any> {
  data: T[]
  meta: LaravelPaginationMeta
  links: LaravelPaginationLinks
}

// 🆕 Interface para query parameters
export interface TableQueryParams {
  page?: number
  per_page?: number
  search?: string
  sort_by?: string
  sort_direction?: 'asc' | 'desc'
  filters?: Record<string, string | string[]>
}

// 🆕 Interface para bulk actions (ações em massa)  
export interface BulkAction {
  id: string
  label: string
  icon?: string
  variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link'
  size?: 'default' | 'sm' | 'lg' | 'icon'
  color?: 'success' | 'primary' | 'danger' | 'warning' | 'secondary' | 'info' | 'light' | 'dark'
  className?: string
  permission?: string
  confirmMessage?: string // Mensagem de confirmação antes de executar
  action?: (selectedRows: any[]) => void | Promise<void> // 🔧 Agora opcional - implementamos dinamicamente
}

export interface TableConfig {
  searchColumns?: string[]
  searchPlaceholder?: string
  showSelectColumn?: boolean
  showActionsColumn?: boolean
  filters?: BackendFilter[]
  // 🆕 Configurações para server-side
  serverSide?: boolean
  onQueryChange?: (params: TableQueryParams) => void
  currentQuery?: TableQueryParams
  actions?: any[]
  // 🆕 Bulk actions (ações em massa)
  bulkActions?: BulkAction[]
}

// 🔥 Mapeamento completo de ícones do Lucide
const iconMap = {
  // Actions básicas
  'Eye': Eye, 'EyeOff': EyeOff, 'PenSquare': PenSquare, 'Trash2': Trash2,
  'Edit': Edit, 'Save': Save,
  'Plus': Plus, 'Minus': Minus, 'Check': Check, 'X': X,

  // Interface
  'Settings': Settings, 'Info': Info, 'AlertCircle': AlertCircle,
  'MoreHorizontal': MoreHorizontal, 'MoreVertical': MoreVertical,
  'Menu': Menu, 'Copy': Copy, 'Share': Share,

  // Navegação
  'Home': Home, 'ChevronRight': ChevronRight, 'ChevronLeft': ChevronLeft,
  'ChevronUp': ChevronUp, 'ChevronDown': ChevronDown,
  'ArrowRight': ArrowRight, 'ArrowLeft': ArrowLeft,
  'ArrowUp': ArrowUp, 'ArrowDown': ArrowDown,

  // Usuário e social
  'User': User, 'Users': Users, 'Star': Star, 'Heart': Heart,
  'Bookmark': Bookmark, 'Flag': Flag, 'Tag': Tag,

  // Comunicação
  'Mail': Mail, 'Phone': Phone, 'MessageSquare': MessageSquare,
  'Bell': Bell, 'Calendar': Calendar,

  // Dados e arquivos
  'Search': Search, 'Filter': Filter, 'Download': Download, 'Upload': Upload,
  'File': File, 'FileText': FileText, 'Image': Image, 'Video': Video,
  'Music': Music, 'Archive': Archive, 'Folder': Folder, 'FolderOpen': FolderOpen,

  // Sistema
  'Shield': Shield, 'Lock': Lock, 'Unlock': Unlock, 'Key': Key,
  'Globe': Globe, 'Wifi': Wifi, 'Database': Database,
  'Cloud': Cloud, 'CloudUpload': CloudUpload, 'CloudDownload': CloudDownload,
} as const

// 🔥 Mapeamento de cores para classes CSS
const colorMap = {
  'success': 'text-green-600 hover:text-green-700',
  'primary': 'text-blue-600 hover:text-blue-700',
  'danger': 'text-red-600 hover:text-red-700',
  'warning': 'text-yellow-600 hover:text-yellow-700',
  'secondary': 'text-gray-600 hover:text-gray-700',
  'info': 'text-cyan-600 hover:text-cyan-700',
  'light': 'text-gray-400 hover:text-gray-500',
  'dark': 'text-gray-800 hover:text-gray-900',
} as const

// 🔥 Mapeamento de variantes para Button
const variantMap = {
  'default': 'default',
  'destructive': 'destructive',
  'outline': 'outline',
  'secondary': 'secondary',
  'ghost': 'ghost',
  'link': 'link',
} as const

// 🔥 Mapeamento de tamanhos
const sizeMap = {
  'default': 'default',
  'sm': 'sm',
  'lg': 'lg',
  'icon': 'icon',
} as const

// Formatadores comuns
const formatters = {
  formatDate: (value: string, options?: string | any) => {
    if (!value) return ""
    const date = new Date(value)
    
    // Verificar se options é string (formato simples)
    if (typeof options === 'string') {
      // Formato simples como "dd/MM/yyyy HH:mm"
      return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date)
    }
    
    // Verificar se options é objeto (formato avançado)
    if (typeof options === 'object' && options !== null) {
      const { format, locale = 'pt-BR', ...customOptions } = options
      
      // Se tem formato personalizado, tentar usar
      if (format) {
        // Mapear formatos comuns para Intl.DateTimeFormat
        const formatMap: Record<string, Intl.DateTimeFormatOptions> = {
          'dd/MM/yyyy': { day: '2-digit', month: '2-digit', year: 'numeric' },
          'dd/MM/yyyy HH:mm': { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' },
          'yyyy-MM-dd': { year: 'numeric', month: '2-digit', day: '2-digit' },
          'yyyy-MM-dd HH:mm:ss': { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' },
          'HH:mm': { hour: '2-digit', minute: '2-digit' },
          'dd/MM': { day: '2-digit', month: '2-digit' }
        }
        
        const formatOptions = formatMap[format] || customOptions
        return new Intl.DateTimeFormat(locale, formatOptions).format(date)
      }
      
      // Usar opções customizadas diretamente
      return new Intl.DateTimeFormat(locale, customOptions).format(date)
    }
    
    // Formato padrão brasileiro
    return new Intl.DateTimeFormat('pt-BR').format(date)
  },

  formatCurrency: (value: number) => {
    if (!value) return "R$ 0,00"
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value)
  },

  formatBadge: (value: string, options?: any) => {
    if (!value) return null
    
    // Configurações padrão
    let badgeProps = {
      variant: 'default' as any,
      className: '',
      children: value
    }
    
    // Se não há opções, usar configuração padrão
    if (!options) {
      return <Badge {...badgeProps}>{value}</Badge>
    }
    
    // Se há mapping, verificar se o valor atual tem configuração específica
    if (options.mapping && typeof options.mapping === 'object') {
      const mappedConfig = options.mapping[value]
      if (mappedConfig && typeof mappedConfig === 'object') {
        // Aplicar configurações do mapping
        if (mappedConfig.variant) badgeProps.variant = mappedConfig.variant
        if (mappedConfig.color) {
          // Mapear cores para classes CSS
          const colorClasses = {
            success: 'bg-green-100 text-green-800 hover:bg-green-200',
            error: 'bg-red-100 text-red-800 hover:bg-red-200',  
            warning: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
            info: 'bg-blue-100 text-blue-800 hover:bg-blue-200',
            primary: 'bg-blue-100 text-blue-800 hover:bg-blue-200',
            secondary: 'bg-gray-100 text-gray-800 hover:bg-gray-200'
          }
          badgeProps.className += ` ${colorClasses[mappedConfig.color as keyof typeof colorClasses] || ''}`
        }
        if (mappedConfig.size) {
          // Adicionar classes de tamanho se necessário
          const sizeClasses = {
            sm: 'text-xs px-2 py-0.5',
            default: 'text-sm px-2.5 py-0.5', 
            lg: 'text-base px-3 py-1'
          }
          badgeProps.className += ` ${sizeClasses[mappedConfig.size as keyof typeof sizeClasses] || ''}`
        }
        
        return <Badge {...badgeProps}>{mappedConfig.label || value}</Badge>
      }
    }
    
    // Aplicar configurações diretas (sem mapping)
    if (options.variant) badgeProps.variant = options.variant
    if (options.color) {
      const colorClasses = {
        success: 'bg-green-100 text-green-800 hover:bg-green-200',
        error: 'bg-red-100 text-red-800 hover:bg-red-200',
        warning: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200', 
        info: 'bg-blue-100 text-blue-800 hover:bg-blue-200',
        primary: 'bg-blue-100 text-blue-800 hover:bg-blue-200',
        secondary: 'bg-gray-100 text-gray-800 hover:bg-gray-200'
      }
      badgeProps.className += ` ${colorClasses[options.color as keyof typeof colorClasses] || ''}`
    }
    if (options.size) {
      const sizeClasses = {
        sm: 'text-xs px-2 py-0.5',
        default: 'text-sm px-2.5 py-0.5',
        lg: 'text-base px-3 py-1'
      }
      badgeProps.className += ` ${sizeClasses[options.size as keyof typeof sizeClasses] || ''}`
    }
    
    return <Badge {...badgeProps}>{value}</Badge>
  },

  formatBoolean: (value: boolean) => {
    return value ? "Sim" : "Não"
  }
}
 

// 🔥 Função para renderizar ação dinâmica
const renderDynamicAction = (col: BackendColumn, row: any) => {
  const IconComponent = col.icon ? iconMap[col.icon as keyof typeof iconMap] : null
  const colorClass = col.color ? colorMap[col.color as keyof typeof colorMap] : 'text-gray-600 hover:text-gray-700'
  const variant = col.variant ? variantMap[col.variant as keyof typeof variantMap] : 'ghost'
  const size = col.size ? sizeMap[col.size as keyof typeof sizeMap] : 'sm'

  // Construir classes CSS
  const baseClasses = "h-8 w-8 p-0"
  const finalClasses = col.className
    ? `${baseClasses} ${col.className}`
    : `${baseClasses} ${colorClass}`

  // 🔥 Renderizar baseado no component
  switch (col.component) {
    case 'Link':
      const linkHref = col.href || (col.routeNameBase && col.routeSuffix
        ? route(`${col.routeNameBase}.${col.routeSuffix}`, { id: row.original.id })
        : '#')

      return (
        <Link
          href={linkHref}
          className={finalClasses}
          title={col.header}
          target={col.target}
        >
          {IconComponent && <IconComponent className="h-4 w-4" />}
        </Link>
      )

    case 'Button':
    default:
      const handleAction = () => {
        if (col.href) {
          if (col.target === '_blank') {
            window.open(col.href, '_blank')
          } else {
            router.visit(col.href)
          }
        } else if (col.routeNameBase && col.routeSuffix) {
          const routeName = `${col.routeNameBase}.${col.routeSuffix}`
          const routeParams = { id: row.original.id }
          router.visit(route(routeName, routeParams))
        }
      }

      return (
        <Button
          variant={variant}
          size={size}
          onClick={handleAction}
          className={finalClasses}
          title={col.header}
        >
          {IconComponent && <IconComponent className="h-4 w-4" />}
        </Button>
      )
  }
}

export function buildColumnsFromBackend<TData = any>(
  backendColumns: BackendColumn[],
  config: TableConfig = {},
  actions: any[] = []
): ColumnDef<TData>[] {
  const columns: ColumnDef<TData>[] = []

  const {
    showSelectColumn = true,
    showActionsColumn = true,
    serverSide = false,
    onQueryChange,
    currentQuery,
  } = config

  // Verificar se já existe coluna com determinado ID
  const hasColumnWithId = (id: string) => {
    return backendColumns.some(col => col.id === id)
  }

  // Adicionar coluna de seleção (só se não existir)
  if (showSelectColumn && !hasColumnWithId("select")) {
    columns.push({
      id: "select",
      header: ({ table }) => (
        <Checkbox
          checked={
            table.getIsAllPageRowsSelected() ||
            (table.getIsSomePageRowsSelected() && "indeterminate")
          }
          onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
          aria-label="Select all"
          className="translate-y-[2px]"
        />
      ),
      cell: ({ row }) => (
        <Checkbox
          checked={row.getIsSelected()}
          onCheckedChange={(value) => row.toggleSelected(!!value)}
          aria-label="Select row"
          className="translate-y-[2px]"
        />
      ),
      enableSorting: false,
      enableHiding: false,
    })
  }

  // Converter colunas do backend
  backendColumns.forEach((col) => {
    const column: ColumnDef<TData> = {
      id: col.id,
      accessorKey: col.accessorKey,
      enableSorting: col.sortable,
      enableHiding: col.enableHiding,
      header: ({ column }) => {
        // 🔥 Usar header server-side ou client-side baseado na configuração
        if (serverSide && onQueryChange && currentQuery) {
          return (
            <DataTableServerColumnHeader
              title={col.header}
              sortable={col.sortable}
              columnKey={col.accessorKey}
              currentQuery={currentQuery}
              onQueryChange={onQueryChange}
            />
          )
        } else {
          return (
            <DataTableColumnHeader column={column} title={col.header} />
          )
        }
      },
      cell: ({ row }) => {        

        const value = row.getValue(col.accessorKey)

        // Aplicar formatador se especificado
        if (col.formatter && formatters[col.formatter as keyof typeof formatters]) {
          const formatter = formatters[col.formatter as keyof typeof formatters] as any 
          return formatter(value, col.formatterOptions)
        }

        // Formatação padrão
        if (typeof value === 'string' && value.length > 50) {
          return (
            <div className="max-w-[500px] truncate" title={value}>
              {value}
            </div>
          )
        }

        return <div>{String(value || '')}</div>
      }
    }
    columns.push(column)
  })

  // Adicionar coluna de ações automaticamente (só se não existir uma do backend)
  if (actions.length > 0) {
    columns.push({
      id: "actions",
      enableHiding: false,
      header: "Ações",
      cell: ({ row }) => (
        <div className="flex items-center gap-2">
          {actions.map((action, index) => (
            <React.Fragment key={action.id || action.accessorKey || index}>
              {renderDynamicAction(action, row)}
            </React.Fragment>
          ))}
        </div>
      ),
    })
  }

  return columns
} 