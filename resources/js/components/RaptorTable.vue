<template>
    <div>
        <slot />
        <div class="p-4 sm:p-6 lg:p-8">
            <SCTable
                :data="data.data"
                :columns="columns"
                :filters="filters"
                :bulk-actions="bulkActions"
                :config="config"
                :pagination="pagination"
                @bulk-action="handleBulkAction"
                @select-all-records="handleSelectAllRecords"
                @row-action="handleRowAction"
                :routeName="config.routeName"
            >
                <template #actions>
                    <HeaderActions :actions="actions" />
                    <!-- Header actios aqui -->
                </template>
            </SCTable>
        </div>
    </div>
</template>
<script setup lang="ts">
import { useTableActions } from './../composables/useTableActions';
import { BulkAction, Column, Filter, TableConfig } from './table/types';

interface Props {
    data: any;
    columns: Column[];
    filters: Filter[];
    bulkActions: BulkAction[];
    config: TableConfig;
    actions: any[];
}
const props = defineProps<Props>();
const { columns, pagination, bulkActions, filters, actions, handleBulkAction, handleSelectAllRecords, handleRowAction } = useTableActions(props);
</script>
