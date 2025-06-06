import { toast as sonnerToast } from "sonner"
import { usePage } from "@inertiajs/react"
import { useEffect } from "react"

// 🔥 Hook personalizado para toast
export function useToast() {
  const page = usePage()
  const flash = (page.props as any).flash || {}

  // 🔥 Detectar mensagens flash do Laravel automaticamente
  useEffect(() => {
    if (flash.success) {
      sonnerToast.success(flash.success, {
        description: "Operação realizada com sucesso",
        duration: 4000,
      })
    }

    if (flash.error) {
      sonnerToast.error(flash.error, {
        description: "Ocorreu um erro na operação",
        duration: 5000,
      })
    }

    if (flash.info) {
      sonnerToast.info(flash.info, {
        description: "Informação importante",
        duration: 4000,
      })
    }

    if (flash.warning) {
      sonnerToast.warning(flash.warning, {
        description: "Atenção necessária",
        duration: 4000,
      })
    }
  }, [flash])

  // 🔥 Métodos para toast manual
  const toast = {
    success: (message: string, description?: string) => {
      sonnerToast.success(message, {
        description,
        duration: 4000,
      })
    },

    error: (message: string, description?: string) => {
      sonnerToast.error(message, {
        description,
        duration: 5000,
      })
    },

    info: (message: string, description?: string) => {
      sonnerToast.info(message, {
        description,
        duration: 4000,
      })
    },

    warning: (message: string, description?: string) => {
      sonnerToast.warning(message, {
        description,
        duration: 4000,
      })
    },

    // Toast com ação customizada
    action: (message: string, action: { label: string; onClick: () => void }, description?: string) => {
      sonnerToast(message, {
        description,
        action: {
          label: action.label,
          onClick: action.onClick,
        },
        duration: 6000,
      })
    },

    // Toast de loading
    loading: (message: string, description?: string) => {
      return sonnerToast.loading(message, {
        description,
      })
    },

    // Dismiss toast específico
    dismiss: (id: string | number) => {
      sonnerToast.dismiss(id)
    },

    // Dismiss todos os toasts
    dismissAll: () => {
      sonnerToast.dismiss()
    }
  }

  return { toast }
} 