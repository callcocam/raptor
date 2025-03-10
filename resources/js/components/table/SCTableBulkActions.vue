<template>
    <div v-show="show" class="fixed bottom-0 left-0 right-0 z-50 flex items-center justify-between gap-2 bg-background p-4 shadow-top border-t">
        <div class="flex items-center gap-4">
            <span class="text-sm font-medium">{{ selectedCount }} item(s) selecionado(s)</span>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="$emit('clear-selection')">
                    <Icon name="XCircle" class="h-4 w-4 mr-2" />
                    Limpar Seleção
                </Button>
                <Button v-if="!selectingAll" variant="outline" size="sm" @click="handleSelectAll">
                    <Icon name="CheckSquare" class="h-4 w-4 mr-2" />
                    Selecionar Todos
                </Button>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <template v-for="action in actions" :key="action.label">
                <!-- Ações com confirmação -->
                <AlertDialog v-if="action.requireConfirmation">
                    <AlertDialogTrigger asChild>
                        <Button :variant="action.variant || 'default'" :size="action.size || 'sm'">
                            <Icon v-if="action.icon" :name="action.icon" class="h-4 w-4 mr-2" />
                            {{ action.label }}
                        </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>{{ action.confirmTitle || 'Confirmar Ação' }}</AlertDialogTitle>
                            <AlertDialogDescription>
                                {{ action.confirmDescription || `Você tem certeza que deseja
                                ${action.label.toLowerCase()}?` }}
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancelar</AlertDialogCancel>
                            <AlertDialogAction @click="handleAction(action)">
                                Confirmar
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
                <!-- Ações sem confirmação -->
                <Button v-else :variant="action.variant || 'default'" :size="action.size || 'sm'"
                    @click="handleAction(action)">
                    <Icon v-if="action.icon" :name="action.icon" class="h-4 w-4 mr-2" />
                    {{ action.label }}
                </Button>
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import { Button } from '@/components/ui/button'
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import { computed } from 'vue'

interface Action {
    label: string
    action: string
    icon?: string
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link'
    size?: 'default' | 'sm' | 'lg' | 'icon'
    requireConfirmation?: boolean
    confirmTitle?: string
    confirmDescription?: string
}

interface Props {
    selectedItems: any[]
    actions: Action[]
    totalRecords: number
    selectingAll?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    selectingAll: false
})

const emit = defineEmits<{
    'action': [action: string, items: any[]]
    'clear-selection': []
    'select-all-records': []
}>()

const show = computed(() => props.selectedItems.length > 0)
const selectedCount = computed(() => props.selectedItems.length)

const handleAction = (action: Action) => {
    emit('action', action.action, props.selectedItems)
}

const handleSelectAll = () => {
    emit('select-all-records')
}
</script>

<style scoped>
.shadow-top {
    box-shadow: 0 -4px 6px -1px rgb(0 0 0 / 0.1), 0 -2px 4px -2px rgb(0 0 0 / 0.1);
}
</style>
