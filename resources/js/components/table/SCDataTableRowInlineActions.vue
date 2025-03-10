<script setup lang="ts">
import { Button } from '@/components/ui/button'
import Icon from '@/components/Icon.vue'
import { Link } from '@inertiajs/vue3'
import ConfirmDialog from '../ConfirmDialog.vue'
import { ref } from 'vue'
import { AlertDialog } from "@/components/ui/alert-dialog"
import { Action } from './types'



interface Props {
  row: any
  actions: Action[]
}

const props = defineProps<Props>()
const emit = defineEmits(['action'])

const currentAction = ref<Action | null>(null)
const showConfirmation = ref(false)
const dialogId = `dialog-${Math.random().toString(36).slice(2)}` 

const handleAction = (action: Action) => {
  if (action.route || action.href) return // Ignora se for link
  
  if (action.confirmation) {
    currentAction.value = action
    showConfirmation.value = true
    return
  }
  
  emit('action', { action: action.action, row: props.row })
}

const handleConfirm = () => {
  if (currentAction.value) {
    emit('action', { action: currentAction.value.action, method: currentAction.value.method, row: props.row })
    handleCancel()
  }
}

const handleCancel = () => {
  currentAction.value = null
  showConfirmation.value = false
}

const getRouteParams = (action: Action) => {  
  // Se não houver routeParams definido, usa o ID do registro
  if (!action.routeParams) {
    return { id: props.row.id }
  }
  
  // Se routeParams for uma função, executa ela passando a row
  if (typeof action.routeParams === 'function') {
    return action.routeParams(props.row)
  }
  
  // Se for um objeto, processa cada valor
  return Object.entries(action.routeParams).reduce((acc, [key, value]) => {
    acc[key] = typeof value === 'function' ? value(props.row) : props.row[value] || value
    return acc
  }, {} as Record<string, any>)
}
</script>

<template>
  <div class="flex items-center gap-2">
    <template v-for="item in actions" :key="item.action">
      <!-- Para rotas internas usando Inertia Link -->
      <Link
        v-if="item.route"
        :href="route(item.route, getRouteParams(item))"
        class="inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-muted"
        :title="item.label"
        method="get"
      >       
        <Icon 
          v-if="item.icon" 
          :name="item.icon" 
          class="h-4 w-4 text-muted-foreground"
        />
        <span class="sr-only">{{ item.label }}</span> 
      </Link>

      <!-- Para links externos -->
      <a
        v-else-if="item.href"
        :href="item.href"
        class="inline-flex"
        target="_blank"
        rel="noopener noreferrer"
      >
        <Button
          :variant="item.variant || 'ghost'"
          size="sm"
          class="h-8 w-8"
          :title="item.label"
        >
          <Icon v-if="item.icon" :name="item.icon" class="h-4 w-4" />
          <span class="sr-only">{{ item.label }}</span>
        </Button>
      </a>

      <!-- Para ações regulares -->
      <Button
        v-else
        :variant="item.variant || 'ghost'"
        size="sm"
        class="h-8 w-8"
        :title="item.label"
        @click="handleAction(item)"
      >
        <Icon v-if="item.icon" :name="item.icon" class="h-4 w-4" />
        <span class="sr-only">{{ item.label }}</span>
      </Button>
    </template>
    
    <AlertDialog 
      :open="showConfirmation" 
      @close="handleCancel"
      :aria-labelledby="`${dialogId}-title`"
      :aria-describedby="`${dialogId}-description`"
    >
      <ConfirmDialog
        v-if="currentAction?.confirmation"
        v-bind="currentAction.confirmation" 
        @confirm="handleConfirm"
        @cancel="handleCancel"
      />
    </AlertDialog>
  </div>
</template>
