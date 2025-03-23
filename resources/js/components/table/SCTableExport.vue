<template>
    <Popover>
        <PopoverTrigger asChild>
            <Button variant="outline" size="sm" class="flex items-center">
                <FileDownIcon class="mr-2 h-4 w-4" />
                Export
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-80">
            <div class="grid gap-4">
                <div class="space-y-2">
                    <h4 class="font-medium leading-none">Export Data</h4>
                    <p class="text-sm text-muted-foreground">Download your data in various formats</p>
                </div>
                <div class="grid gap-2">
                    <div class="grid grid-cols-3 items-center gap-4">
                        <Label for="export-format">Format</Label>
                        <Select v-model="exportFormat" class="col-span-2">
                            <SelectTrigger id="export-format">
                                <SelectValue placeholder="Select format" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="excel">Excel</SelectItem>
                                <SelectItem value="csv">CSV</SelectItem>
                                <SelectItem value="json">JSON</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid grid-cols-3 items-center gap-4">
                        <Label for="export-selection">Export</Label>
                        <Select v-model="exportSelection" class="col-span-2">
                            <SelectTrigger id="export-selection">
                                <SelectValue placeholder="Select data" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Records</SelectItem>
                                <SelectItem value="filtered" :disabled="!hasFilters">Filtered Records</SelectItem>
                                <SelectItem value="selected" :disabled="!hasSelectedItems">Selected Records</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <Button type="button" @click="exportData" :disabled="isExporting">
                        <Loader v-if="isExporting" class="mr-2 h-4 w-4 animate-spin" />
                        {{ isExporting ? 'Exporting...' : 'Export' }}
                    </Button>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>

<script setup lang="ts">
// @ts-nocheck
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import axios from 'axios';
import { FileDownIcon, Loader } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    selectedItems: Array<string | number>;
    export: any;
    hasFilters?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    hasFilters: false,
});

const emit = defineEmits<{
    'export-complete': [data: any];
    'export-error': [error: any];
}>();

// Export endpoint
const exportEndpoint = ref(props.export.endpoint);

// Export state
const exportFormat = ref('excel');
const exportSelection = ref('all');
const isExporting = ref(false);

const hasSelectedItems = computed(() => props.selectedItems.length > 0);

const exportData = async () => {
    isExporting.value = true;

    try {
        const params: Record<string, any> = {
            format: exportFormat.value,
        };

        // If exporting selected items, include them in the request
        if (exportSelection.value === 'selected') {
            params.selectedItems = props.selectedItems;
        }

        // For filtered data, we'll use current URL parameters
        if (exportSelection.value === 'filtered') {
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.forEach((value, key) => {
                if (key !== 'page' && key !== 'perPage') {
                    params[key] = value;
                }
            });
        }

        const response = await axios.post(
            route(exportEndpoint.value, {
                exportType: exportSelection.value,
                format: exportFormat.value,
            }),
            {
                params,
                responseType: 'blob',
            },
        );

        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;

        // Get filename from response headers or use default
        const contentDisposition = response.headers['content-disposition'];
        let filename = 'export';
        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename=(.+)/);
            if (filenameMatch && filenameMatch.length > 1) {
                filename = filenameMatch[1];
            }
        }

        if (!filename.includes('.')) {
            // Add extension if not present
            switch (exportFormat.value) {
                case 'excel':
                    filename += '.xlsx';
                    break;
                case 'csv':
                    filename += '.csv';
                    break;
                case 'json':
                    filename += '.json';
                    break;
            }
        }

        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        emit('export-complete', { success: true });
    } catch (error) {
        emit('export-error', error);
        console.error('Export error:', error);
    } finally {
        isExporting.value = false;
    }
};
</script>
