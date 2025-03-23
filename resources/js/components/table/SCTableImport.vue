<template>
    <Popover>
        <PopoverTrigger asChild>
            <Button variant="outline" size="sm" class="flex items-center">
                <FileUpIcon class="mr-2 h-4 w-4" />
                Import
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-80">
            <div class="grid gap-4">
                <div class="space-y-2">
                    <h4 class="font-medium leading-none">Import Data</h4>
                    <p class="text-sm text-muted-foreground">Upload data from a file or external source</p>
                </div>
                <div class="grid gap-2">
                    <div class="grid grid-cols-3 items-center gap-4">
                        <Label for="file-format">Format</Label>
                        <Select v-model="importFormat" class="col-span-2">
                            <SelectTrigger id="file-format">
                                <SelectValue placeholder="Select format" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="excel">Excel</SelectItem>
                                <SelectItem value="csv">CSV</SelectItem>
                                <SelectItem value="json">JSON</SelectItem>
                                <SelectItem value="api">External API</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div v-if="importFormat === 'api'" class="grid grid-cols-3 items-center gap-4">
                        <Label for="api-url">API URL</Label>
                        <Input id="api-url" v-model="apiUrl" placeholder="Enter API URL" class="col-span-2" />
                    </div>

                    <div v-else class="grid grid-cols-3 items-center gap-4">
                        <Label for="file">File</Label>
                        <Input id="file" type="file" @change="handleFileSelected" :accept="acceptFormats" class="col-span-2" />
                    </div>

                    <Button type="button" @click="importData" :disabled="isImporting">
                        <Loader v-if="isImporting" class="mr-2 h-4 w-4 animate-spin" />
                        {{ isImporting ? 'Importing...' : 'Import' }}
                    </Button>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>

<script setup lang="ts">
// @ts-nocheck
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import axios from 'axios';
import { FileUpIcon, Loader } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    import: any;
}

const props = withDefaults(defineProps<Props>(), {});
console.log('Import props:', props);
const emit = defineEmits<{
    'import-complete': [data: any];
    'import-error': [error: any];
}>();

// Import endpoint
const importEndpoint = computed(() => {
    return props.import.endpoint;
});
// Import state
const importFormat = ref('excel');
const apiUrl = ref('');
const selectedFile = ref<File | null>(null);
const isImporting = ref(false);

const acceptFormats = computed(() => {
    switch (importFormat.value) {
        case 'excel':
            return '.xlsx,.xls';
        case 'csv':
            return '.csv';
        case 'json':
            return '.json';
        default:
            return '';
    }
});

const handleFileSelected = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        selectedFile.value = input.files[0];
    }
};

const importData = async () => {
    if (importFormat.value !== 'api' && !selectedFile.value) {
        alert('Please select a file to import');
        return;
    }

    isImporting.value = true;
    const formData = new FormData();

    try {
        let response;

        if (importFormat.value === 'api') {
            // Import from external API
            if (!apiUrl.value) {
                alert('Please enter an API URL');
                isImporting.value = false;
                return;
            }

            response = await axios.post(importEndpoint.value, {
                source: 'api',
                apiUrl: apiUrl.value,
            });
        } else {
            // Import from file
            formData.append('file', selectedFile.value as File);
            formData.append('format', importFormat.value);

            console.log('FormData:', importEndpoint.value, formData);
            response = await axios.post(importEndpoint.value, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
        }

        emit('import-complete', response.data);
        // Reset state
        selectedFile.value = null;
        if (document.querySelector('input[type="file"]')) {
            (document.querySelector('input[type="file"]') as HTMLInputElement).value = '';
        }
    } catch (error) {
        emit('import-error', error);
        console.error('Import error:', error);
    } finally {
        isImporting.value = false;
    }
};
</script>
