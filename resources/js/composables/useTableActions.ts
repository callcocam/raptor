import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import type { BulkActionPayload } from '../types'
import { Column } from '@/components/core/table/types'

export function useTableActions(props: any) {
    // Computed Properties
    const columns = ref<Column[]>(props.columns)

    const routeName = ref(props.config?.bulkActionName || 'bulk-action')

    const pagination = computed(() => ({
        total: props.data.meta.total || 0,
        perPage: props.data.meta.per_page || 0,
        currentPage: props.data.meta.current_page,
        from: props.data.meta.from,
        to: props.data.meta.to
    }))

    // Reactive References
    const bulkActions = ref(props.bulkActions.map((action: any) => ({
        ...action,
        variant: action.variant as 'default' | 'link' | 'secondary' | 'destructive' | 'outline' | 'ghost' | undefined
    })))

    const filters = ref(props.filters.map((filter: any) => ({
        ...filter,
        value: filter.value || null
    })))


    const actions = computed(() => props.actions.map((action: any) => ({
        ...action,
        variant: action.variant as 'default' | 'link' | 'secondary' | 'destructive' | 'outline' | 'ghost' | undefined
    })))

    // Action Handlers
    const executeBulkAction = (payload: BulkActionPayload) => {
        router.post(route(routeName.value), { ...payload }, {
            onSuccess: ({ props: responseProps }: any) => {
                if (payload.action === 'select-all') {
                    console.log(responseProps)
                    return
                }
            },
            onError: (error) => {
                console.log(error)
            }
        })
    }

    const handleBulkAction = (action: string, items: any[]) => {
        executeBulkAction({
            action,
            model: props.config.model || '',
            items,
            // @ts-expect-error
            filters: route().params
        })
    }

    const handleSelectAllRecords = () => {
        executeBulkAction({
            action: 'select-all',
            model: props.config.model || '',
            // @ts-expect-error
            filters: route().params
        })
    }

    const handleRowAction = (actionData: {
        action: string,
        method: string,
        routeParams: any,
        row: any
    }) => {
        switch (actionData.method) {
            case 'post':
                router.post(actionData.action, { ...actionData.routeParams })
                break
            case 'delete':
                router.delete(actionData.action, { ...actionData.routeParams })
                break
            default:
                router.get(actionData.action, { ...actionData.routeParams })
        }
    }

    return {
        // State
        columns,
        pagination,
        bulkActions,
        filters,
        actions,

        // Methods
        executeBulkAction,
        handleBulkAction,
        handleSelectAllRecords,
        handleRowAction
    }
}
