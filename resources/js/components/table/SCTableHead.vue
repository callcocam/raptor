<template>
    <TableHeader>
        <TableRow>
            <TableHead v-if="selectable" class="w-[50px]">
                <Checkbox
                    :checked="selectedAll"
                    :indeterminate="indeterminate"
                    @update:checked="$emit('select-all', $event)"
                />
            </TableHead>
            <TableHead v-for="column in columns" :key="column.key" :class="[
                column.className,
                { 'cursor-pointer hover:bg-muted': column.sortable }
            ]">
                <template v-if="column.sortable">
                    <Link :href="getLink(column)" class="flex items-center gap-2 justify-between w-full" preserve-scroll >
                    <span>{{ column.label }}</span>
                    <component :is="getSortIcon(column)" class="h-4 w-4 transition-colors" />
                    </Link>
                </template>
                <template v-else>
                    {{ column.label }}
                </template>
            </TableHead>
            <TableHead v-if="hasActions" class="text-right">Actions</TableHead>
        </TableRow>
    </TableHeader>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'
import { Link } from '@inertiajs/vue3'
import {
    ChevronUp,
    ChevronDown,
    ChevronsUpDown,
} from 'lucide-vue-next'
import { Column } from './types'
 

interface Props {
    columns: Column[]
    selectable?: boolean
    selectedAll?: boolean
    indeterminate?: boolean
    sortColumn?: string | null
    sortDirection?: 'asc' | 'desc' | null
    hasActions?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    selectable: false,
    selectedAll: false,
    indeterminate: false,
    sortColumn: null,
    sortDirection: null,
    hasActions: false
})

defineEmits<{
    'select-all': [checked: boolean]
}>()

const getSortIcon = (column: Column) => {
    if (!column.sortable) return null
    
    if (props.sortColumn !== column.key) return ChevronsUpDown
    return props.sortDirection === 'asc' ? ChevronUp : ChevronDown
}

const getLink = (column: Column) => {
    const params = new URLSearchParams(window.location.search)
    const currentSort = params.get('sort')
    const currentDirection = params.get('direction') 
    
    if (currentSort !== column.key) {
        params.set('sort', column.key)
        params.set('direction', 'asc')
    } else {
        if (currentDirection === 'asc') {
            params.set('direction', 'desc')
        } else {
            params.delete('sort')
            params.delete('direction')
        }
    }
    
    // Use the route helper if available, otherwise build URL manually
    try {
        // @ts-ignore
        return route(route().current() as string, Object.fromEntries(params))
    } catch (e) {
        // Fallback if route helper is not available
        const url = new URL(window.location.href)
        url.search = params.toString()
        return url.toString()
    }
}
</script>
