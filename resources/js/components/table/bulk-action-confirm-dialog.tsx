import * as React from "react"
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog"
import { Button } from "@/components/ui/button"
import { BulkAction } from "@raptor/lib/column-builder"

// 🔥 Importar ícones para o dialog
import {
  AlertTriangle, Trash2, Archive, Download, Info
} from "lucide-react"

// 🔥 Mapeamento de ícones para o dialog
const dialogIconMap = {
  'BulkDelete': AlertTriangle,
  'BulkArchive': Archive, 
  'BulkExport': Download,
  'default': Info
} as const

interface BulkActionConfirmDialogProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  bulkAction: BulkAction | null
  selectedCount: number
  onConfirm: () => void
}

export function BulkActionConfirmDialog({
  open,
  onOpenChange,
  bulkAction,
  selectedCount,
  onConfirm
}: BulkActionConfirmDialogProps) {
  if (!bulkAction) return null

  // Determinar ícone baseado no ID da ação
  const getDialogIcon = () => {
    if (bulkAction.id in dialogIconMap) {
      return dialogIconMap[bulkAction.id as keyof typeof dialogIconMap]
    }
    return dialogIconMap.default
  }

  // Gerar título personalizado
  const getDialogTitle = () => {
    switch (bulkAction.id) {
      case 'BulkDelete':
        return 'Confirmar Exclusão'
      case 'BulkArchive':
        return 'Confirmar Arquivamento'
      case 'BulkExport':
        return 'Confirmar Exportação'
      default:
        return `Confirmar ${bulkAction.label}`
    }
  }

  // Gerar descrição personalizada
  const getDialogDescription = () => {
    if (bulkAction.confirmMessage) {
      return bulkAction.confirmMessage.replace('{count}', selectedCount.toString())
    }

    switch (bulkAction.id) {
      case 'BulkDelete':
        return `Tem certeza que deseja excluir ${selectedCount} ${selectedCount === 1 ? 'registro' : 'registros'}? Esta ação não pode ser desfeita.`
      case 'BulkArchive':
        return `Deseja arquivar ${selectedCount} ${selectedCount === 1 ? 'registro' : 'registros'}? ${selectedCount === 1 ? 'O registro ficará' : 'Os registros ficarão'} indisponíveis na listagem principal.`
      case 'BulkExport':
        return `Será gerado um arquivo com ${selectedCount} ${selectedCount === 1 ? 'registro' : 'registros'}. Deseja continuar?`
      default:
        return `A ação "${bulkAction.label}" será executada em ${selectedCount} ${selectedCount === 1 ? 'registro' : 'registros'}.`
    }
  }

  // Determinar cor do botão de confirmação
  const getConfirmButtonVariant = () => {
    switch (bulkAction.id) {
      case 'BulkDelete':
        return 'destructive'
      case 'BulkArchive':
        return 'secondary'
      default:
        return 'default'
    }
  }

  // Determinar texto do botão de confirmação
  const getConfirmButtonText = () => {
    switch (bulkAction.id) {
      case 'BulkDelete':
        return 'Sim, Excluir'
      case 'BulkArchive':
        return 'Sim, Arquivar'
      case 'BulkExport':
        return 'Sim, Exportar'
      default:
        return 'Confirmar'
    }
  }

  const IconComponent = getDialogIcon()

  return (
    <AlertDialog open={open} onOpenChange={onOpenChange}>
      <AlertDialogContent className="sm:max-w-lg max-w-[90vw] w-full">
        <AlertDialogHeader>
          <div className="flex items-center gap-3">
            {IconComponent && (
              <div className={`p-2 rounded-full flex-shrink-0 ${
                bulkAction.id === 'BulkDelete' 
                  ? 'bg-red-100 text-red-600' 
                  : bulkAction.id === 'BulkArchive'
                  ? 'bg-gray-100 text-gray-600'
                  : 'bg-blue-100 text-blue-600'
              }`}>
                <IconComponent className="h-5 w-5" />
              </div>
            )}
            <div className="flex-1 min-w-0">
              <AlertDialogTitle className="text-left text-lg font-semibold">
                {getDialogTitle()}
              </AlertDialogTitle>
            </div>
          </div>
          <AlertDialogDescription className="text-left text-sm text-muted-foreground mt-2">
            {getDialogDescription()}
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter className="flex-col sm:flex-row gap-2 sm:gap-2">
          <AlertDialogCancel className="mt-2 sm:mt-0">Cancelar</AlertDialogCancel>
          <AlertDialogAction asChild>
            <Button 
              variant={getConfirmButtonVariant()}
              onClick={onConfirm}
              className="w-full sm:w-auto"
            >
              {getConfirmButtonText()}
            </Button>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  )
} 