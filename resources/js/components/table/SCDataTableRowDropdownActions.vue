<script setup lang="ts">
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem, 
  DropdownMenuShortcut, 
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { EllipsisIcon } from 'lucide-vue-next'
import { Action } from './types'
 

interface RowActionsProps {
  row: any
  actions: Action[]
}

const props = defineProps<RowActionsProps>()

const emit = defineEmits(['action'])

const handleAction = (action: string) => {
  emit('action', { action, row: props.row })
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" class="flex h-8 w-8 p-0 data-[state=open]:bg-muted">
        <EllipsisIcon class="h-4 w-4" />
        <span class="sr-only">Open menu</span>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-[160px]">
      <DropdownMenuItem 
        v-for="item in actions" 
        :key="item.action"
        @click="handleAction(item.action)"
      >
        <component 
          v-if="item.icon" 
          :is="item.icon" 
          class="mr-2 h-4 w-4"
        />
        {{ item.label }}
        <DropdownMenuShortcut v-if="item.shortcut">
          {{ item.shortcut }}
        </DropdownMenuShortcut>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>