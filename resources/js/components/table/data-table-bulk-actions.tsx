import * as React from "react"
import { Table } from "@tanstack/react-table"
import { Button } from "@/components/ui/button"
import { Separator } from "@/components/ui/separator"
import { Badge } from "@/components/ui/badge"
import { X } from "lucide-react"
import { BulkAction } from "@raptor/lib/column-builder"
import { BulkActionConfirmDialog } from "./bulk-action-confirm-dialog"
import { router } from "@inertiajs/react"
import { useToast } from "@raptor/hooks/useToast"

// 🔥 Importar ícones necessários para bulk actions
import {
  Trash2, Archive, Download, FileText, Send, Copy,
  Edit, Settings, Eye, Check, AlertTriangle
} from "lucide-react"

// 🔥 Mapeamento de ícones para bulk actions
const bulkIconMap = {
  'Trash2': Trash2, 'Archive': Archive, 'Download': Download, 
  'FileText': FileText, 'Send': Send, 'Copy': Copy,
  'Edit': Edit, 'Settings': Settings, 'Eye': Eye, 
  'Check': Check, 'AlertTriangle': AlertTriangle,
} as const

// 🔥 Mapeamento de cores para bulk actions
const bulkColorMap = {
  'success': 'text-green-600 hover:text-green-700 hover:bg-green-50',
  'primary': 'text-blue-600 hover:text-blue-700 hover:bg-blue-50',
  'danger': 'text-red-600 hover:text-red-700 hover:bg-red-50',
  'warning': 'text-yellow-600 hover:text-yellow-700 hover:bg-yellow-50',
  'secondary': 'text-gray-600 hover:text-gray-700 hover:bg-gray-50',
  'info': 'text-cyan-600 hover:text-cyan-700 hover:bg-cyan-50',
} as const

interface DataTableBulkActionsProps<TData> {
  table: Table<TData>
  bulkActions?: BulkAction[]
  routeNameBase?: string // 🆕 Para construir a rota de bulk action
}

export function DataTableBulkActions<TData>({
  table,
  bulkActions = [],
  routeNameBase
}: DataTableBulkActionsProps<TData>) {
  const selectedRows = table.getFilteredSelectedRowModel().rows
  const selectedCount = selectedRows.length
  const { toast } = useToast()

  // 🆕 Estado para controle do dialog de confirmação
  const [dialogOpen, setDialogOpen] = React.useState(false)
  const [pendingAction, setPendingAction] = React.useState<BulkAction | null>(null)
  const [isLoading, setIsLoading] = React.useState(false)

  // 🔧 Debug: log das bulk actions recebidas
  React.useEffect(() => {
    if (bulkActions.length > 0) {
      console.log('🔧 Bulk Actions recebidas:', bulkActions)
      console.log('🔧 Route Name Base:', routeNameBase)
    }
  }, [bulkActions, routeNameBase])

  // Não renderizar se não há seleção ou bulk actions
  if (selectedCount === 0 || bulkActions.length === 0) {
    return null
  }

  // 🆕 Função para abrir dialog de confirmação
  const openConfirmDialog = (bulkAction: BulkAction) => {
    setPendingAction(bulkAction)
    setDialogOpen(true)
  }

  // 🆕 Função para executar bulk action via POST
  const executeBulkAction = async () => {
    if (!pendingAction || !routeNameBase) {
      console.error('❌ Ação pendente ou rota não definida')
      return
    }

    setIsLoading(true)
    
    try {
      // Coletar IDs dos registros selecionados
      const selectedIds = selectedRows
        .map(row => {
          const data = row.original as any
          return data?.id || data?.uuid || null
        })
        .filter(Boolean)
      
      if (selectedIds.length === 0) {
        console.error('❌ Nenhum ID válido encontrado nos registros selecionados')
        return
      }

      console.log('🚀 Executando bulk action:', {
        action: pendingAction.id,
        selectedIds,
        count: selectedIds.length
      })

      // Construir rota para bulk action
      const bulkActionRoute = `${routeNameBase}.bulk-action`
      
      // Enviar POST para o backend
      router.post(route(bulkActionRoute), {
        action: pendingAction.id,
        selectedIds: selectedIds,
        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          console.log('✅ Bulk action executada com sucesso')
          // Limpar seleção após sucesso
          table.toggleAllRowsSelected(false)
          setDialogOpen(false)
          setPendingAction(null)
          toast.success("Ação realizada com sucesso", "A operação foi executada com êxito")
        },
        onError: (errors) => {
          console.error('❌ Erro ao executar bulk action:', errors)
          toast.error("Erro ao executar ação", "Ocorreu um problema durante a execução")
        },
        onFinish: () => {
          setIsLoading(false)
        }
      })

    } catch (error) {
      console.error('❌ Erro inesperado ao executar bulk action:', error)
      setIsLoading(false)
      toast.error("Erro ao executar ação", error instanceof Error ? error.message : "Ocorreu um erro inesperado")
    }
  }

  // 🔧 Função para lidar com clique na bulk action
  const handleBulkActionClick = (bulkAction: BulkAction) => {
    // Se tem função action personalizada, executar diretamente
    if (typeof bulkAction.action === 'function') {
      const selectedData = selectedRows.map(row => row.original)
      bulkAction.action(selectedData)
      return
    }

    // Caso contrário, abrir dialog de confirmação
    openConfirmDialog(bulkAction)
  }

  return (
    <div className="flex items-center justify-between px-4 py-3 bg-muted/50 border-b border-border">
      <div className="flex items-center gap-3">
        <Badge variant="secondary" className="text-sm font-medium">
          {selectedCount} {selectedCount === 1 ? 'item selecionado' : 'itens selecionados'}
        </Badge>
        
        <Button
          variant="ghost"
          size="sm"
          onClick={() => table.toggleAllRowsSelected(false)}
          className="h-8 px-2 text-muted-foreground hover:text-foreground"
          title="Limpar seleção"
        >
          <X className="h-4 w-4" />
        </Button>
      </div>

      <div className="flex items-center gap-2">
        {bulkActions.map((bulkAction) => {
          const IconComponent = bulkAction.icon ? bulkIconMap[bulkAction.icon as keyof typeof bulkIconMap] : null
          const colorClass = bulkAction.color ? bulkColorMap[bulkAction.color as keyof typeof bulkColorMap] : 'text-gray-600 hover:text-gray-700 hover:bg-gray-50'

          return (
            <Button
              key={bulkAction.id}
              variant={(bulkAction.variant as any) || 'ghost'}
              size={(bulkAction.size as any) || 'sm'}
              onClick={() => handleBulkActionClick(bulkAction)}
              className={bulkAction.className || `${colorClass} min-h-8 px-3`}
              title={bulkAction.label || 'Ação'}
              disabled={isLoading}
            >
              <div className="flex items-center gap-2">
                {IconComponent && <IconComponent className="h-4 w-4 flex-shrink-0" />}
                <span className="whitespace-nowrap text-sm">
                  {bulkAction.label || 'Ação'}
                </span>
              </div>
            </Button>
          )
        })}
      </div>

      {/* 🆕 Dialog de confirmação personalizado */}
      <BulkActionConfirmDialog
        open={dialogOpen}
        onOpenChange={setDialogOpen}
        bulkAction={pendingAction}
        selectedCount={selectedCount}
        onConfirm={executeBulkAction}
      />
    </div>
  )
} 