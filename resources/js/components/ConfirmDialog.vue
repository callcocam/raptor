<script setup lang="ts">
import { 
  AlertDialogAction, 
  AlertDialogCancel, 
  AlertDialogContent, 
  AlertDialogDescription, 
  AlertDialogFooter, 
  AlertDialogHeader, 
  AlertDialogTitle 
} from "@/components/ui/alert-dialog"

const props = defineProps<{
  title: string
  description?: string
  confirmText?: string
  cancelText?: string
  variant?: 'default' | 'destructive'
}>()

const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
  <AlertDialogContent>
    <AlertDialogHeader>
      <AlertDialogTitle>{{ title }}</AlertDialogTitle>
      <AlertDialogDescription v-if="description">
        {{ description }}
      </AlertDialogDescription>
    </AlertDialogHeader>
    <AlertDialogFooter>
      <AlertDialogCancel @click="emit('cancel')">
        {{ cancelText || 'Cancelar' }}
      </AlertDialogCancel>
      <AlertDialogAction 
        :variant="variant || 'default'"
        @click="emit('confirm')"
      >
        {{ confirmText || 'Confirmar' }}
      </AlertDialogAction>
    </AlertDialogFooter>
  </AlertDialogContent>
</template>
