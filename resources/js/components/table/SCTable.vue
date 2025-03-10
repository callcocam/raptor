<template>
    <div class="space-y-4">
        <SCTableFilter v-model="search" :loading="loading" :initial-search="search" @update:model-value="handleSearch"
            :filters="filters">
            <template #filters="{ filters }">
                <SCTableFacetedFilter v-for="filter in filters" :column="filter.column" :title="filter.label"
                    :options="filter.options || []" />
            </template>
            <template #actions>
                <slot name="actions" />
            </template>
        </SCTableFilter>
        <!-- Table -->
        <Table>
            <TableCaption v-if="caption">{{ caption }}</TableCaption>
            <SCTableHead 
                :columns="columns" 
                :sort-column="sortColumn" 
                :sort-direction="sortDirection"
                :selectable="config?.selectable" 
                :selected-all="isAllSelected" 
                :indeterminate="isIndeterminate"
                :has-actions="hasActions" 
                @select-all="handleSelectAll" 
            />
            <TableBody>
                <template v-if="data.length">
                    <SCTableRow 
                        v-for="row in data" 
                        :key="row.id" 
                        :selectable="config?.selectable"
                        :selected="selectedItems.includes(row.id)"
                        @select-row="(checked) => handleSelectRow(row.id, checked)"
                    >
                        <TableCell v-for="column in columns" :key="column.key" :class="column.className">
                            {{ row[column.key] }}
                        </TableCell>
                        <template v-if="hasActions" #actions>
                            <TableCell>
                                <component
                                    :is="actionsType === 'inline' ? SCDataTableRowInlineActions : SCDataTableRowDropdownActions"
                                    :row="row" :actions="getRowActions(row)" @action="handleRowAction" />
                            </TableCell>
                        </template>
                    </SCTableRow>
                </template>
                <TableRow v-if="!data.length">
                    <TableCell :colspan="columns.length + (hasActions ? 1 : 0) + (config?.selectable ? 1 : 0)"
                        class="text-center">
                        No results found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <!-- Pagination -->
        <div v-if="pagination" class="flex items-center justify-end space-x-2">
            <SCTablePagination :pagination="pagination" @page-change="handlePageChange"
                @per-page-change="handlePerPageChange" />
        </div>

        <!-- BulkActions -->
        <SCTableBulkActions v-if="config?.selectable && bulkActions && selectedItems.length > 0"
            :selected-items="selectedItems" :actions="bulkActions" :total-records="pagination?.total || 0"
            :selecting-all="selectingAllRecords" @action="handleBulkAction" @clear-selection="clearSelection"
            @select-all-records="selectAllRecords" />
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted } from 'vue'
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableRow,
} from '@/components/ui/table'
import SCTableHead from './SCTableHead.vue'
import SCTableRow from './SCTableRow.vue'
import SCTableFilter from './SCTableFilter.vue'
import SCTableFacetedFilter from './SCTableFacetedFilter.vue'
import SCTablePagination from './SCTablePagination.vue'
import SCTableBulkActions from './SCTableBulkActions.vue'
import SCDataTableRowInlineActions from './SCDataTableRowInlineActions.vue'
import SCDataTableRowDropdownActions from './SCDataTableRowDropdownActions.vue'
import { Action, BulkAction, Column, Filter, TableConfig, TableRow as TableRowObject } from './types'



interface Props {
    data: TableRowObject[]
    columns: Column[]
    caption?: string
    routeName: string
    pagination?: {
        currentPage: number
        perPage: number
        total: number
    },
    config?: TableConfig
    initialFilters?: Record<string, any>
    filters?: Filter[]
    bulkActions?: BulkAction[]
    rowActions?: Action[]  // Agora é opcional, pois usaremos _actions da row 
}

const props = withDefaults(defineProps<Props>(), {
    initialFilters: () => ({}),
    rowActions: () => [], // Mantido para compatibilidade 
})
const emit = defineEmits<{
    'selection-change': [selectedIds: any[]]
    'bulk-action': [action: string, items: any[]]
    'select-all-records': []
    'row-action': [{ action: string, method: string, routeParams: any, row: any }]
}>()

const loading = ref(false)
const params = computed(() => new URLSearchParams(window.location.search))
const search = ref(params.value.get('search') || '')
const sortColumn = ref(params.value.get('sort') || '' as string | null)
const sortDirection = ref(params.value.get('direction') as 'asc' | 'desc' | null)

// Sincronizar search com URL ao montar o componente
onMounted(() => {
    const searchParam = params.value.get('search')
    if (searchParam) {
        search.value = searchParam
    }
})

// Observar mudanças na URL para atualizar o estado local
watch(() => window.location.search, (newSearch) => {
    const newParams = new URLSearchParams(newSearch)
    search.value = newParams.get('search') || ''
    sortColumn.value = newParams.get('sort')
    sortDirection.value = newParams.get('direction') as 'asc' | 'desc' | null
}, { immediate: true })

const selectable = computed(() => props.config?.selectable)

const actionsType = computed(() => props.config?.actionsType)

const handleSearch = window._.debounce((value: string) => {
    const currentParams = new URLSearchParams(window.location.search)

    if (value && value.trim()) {
        currentParams.set('search', value.trim())
        search.value = value.trim()
    } else {
        currentParams.delete('search')
        search.value = ''
    }
    currentParams.delete('page')

    updateTableState(Object.fromEntries(currentParams))
}, 300)

const handlePageChange = (page: number) => {
    const currentParams = new URLSearchParams(window.location.search)
    currentParams.set('page', page.toString())
    updateTableState(Object.fromEntries(currentParams))
}

const handlePerPageChange = (perPage: number) => {
    const currentParams = new URLSearchParams(window.location.search)
    currentParams.set('perPage', perPage.toString())
    currentParams.delete('page')
    updateTableState(Object.fromEntries(currentParams))
}

const updateTableState = (newParams: Record<string, any>) => {
    const query = Object.fromEntries(
        Object.entries(newParams).filter(([_, value]) =>
            value !== null && value !== undefined && value !== ''
        )
    )

    router.get(route(props.routeName), query, {
        preserveState: false,
        preserveScroll: true,
        onStart: () => loading.value = true,
        onFinish: () => loading.value = false
    })
}

const selectedItems = ref<(string | number)[]>([])
const selectingAllRecords = ref(false)

// Computed properties para controle de seleção
const isAllSelected = computed(() => {
    return props.data.length > 0 && selectedItems.value.length === props.data.length
})

const isIndeterminate = computed(() => {
    return selectedItems.value.length > 0 && selectedItems.value.length < props.data.length
})

const handleSelectAll = (checked: boolean) => {
    if (checked) {
        // Ensure we're using the correct ID property from each row
        selectedItems.value = props.data.map(item => item.id);
    } else {
        selectedItems.value = [];
    }
    emit('selection-change', selectedItems.value);
}

const handleSelectRow = (id: string | number, checked: boolean) => {
    if (checked) {
        if (!selectedItems.value.includes(id)) {
            selectedItems.value.push(id);
        }
    } else {
        selectedItems.value = selectedItems.value.filter(item => item !== id);
    }
    emit('selection-change', selectedItems.value);
}

const handleBulkAction = (action: string, items: any[]) => { 
    emit('bulk-action', action, items)
}

const clearSelection = () => {
    selectedItems.value = []
    selectingAllRecords.value = false
    emit('selection-change', selectedItems.value)
}

const selectAllRecords = () => {
    selectingAllRecords.value = true
    // Emitir evento especial para indicar que todos os registros foram selecionados
    emit('select-all-records')
}

const handleRowAction = (actionData: { action: string, method: string, routeParams: any, row: any }) => {
    emit('row-action', actionData)
}

// Reset selection when data changes, unless selecting all records is active
watch(() => props.data, () => {
    if (!selectingAllRecords.value) {
        selectedItems.value = [];
    } else if (props.data.length && isAllSelected.value) {
        // If "select all" is active, ensure all new items are selected
        const currentIds = selectedItems.value;
        const newIds = props.data.map(item => item.id);
        
        // Add any new IDs that aren't already in the selection
        newIds.forEach(id => {
            if (!currentIds.includes(id)) {
                selectedItems.value.push(id);
            }
        });
    }
}, { deep: true });

// Computed para verificar se tem ações na linha ou globais
const hasActions = computed(() => {
    return props.data.some(row => row.actions && row.actions.length > 0) || props.rowActions.length > 0
})

// Helper para pegar as ações da linha
const getRowActions = (row: TableRowObject): Action[] => {
    return row.actions || props.rowActions
}
</script>