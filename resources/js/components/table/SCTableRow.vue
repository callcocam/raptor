<template>
    <TableRow :class="{ 'bg-muted/50': selected }">
        <TableCell v-if="selectable" class="w-[50px]">
            <Checkbox :checked="selected" @update:checked="$emit('select-row', $event)" />
        </TableCell>
        <slot></slot>
        <template v-if="$slots.actions">
            <TableCell class="text-right">
                <slot name="actions"></slot>
            </TableCell>
        </template>
    </TableRow>
</template>

<script setup lang="ts">
import { TableCell, TableRow } from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'

defineProps<{
    selectable?: boolean
    selected?: boolean
}>()

defineEmits<{
    'select-row': [checked: boolean]
}>()
</script>
