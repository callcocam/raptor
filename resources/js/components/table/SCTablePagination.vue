<script setup lang="ts">
import { Button } from '@/components/ui/button'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select' 
import { ChevronsLeftIcon, ChevronsRightIcon, ChevronLeftIcon, ChevronRightIcon } from 'lucide-vue-next'

interface Props {
    pagination: {
        currentPage: number
        perPage: number
        total: number
        from?: number
        to?: number
    }
}

const props = defineProps<Props>()

const pageCount = Math.ceil(props.pagination.total / props.pagination.perPage)

const emit = defineEmits<{
    (e: 'pageChange', page: number): void
    (e: 'perPageChange', perPage: number): void
}>()

const handlePerPageChange = (value: any) => {
    emit('perPageChange', parseInt(value))
}

const goToPage = (page: number) => {
    if (page < 1 || page > pageCount) return
    emit('pageChange', page)
}
</script>

<template>
    <div class="flex items-center justify-between px-2 w-full">
        <div class="flex-1 text-sm text-muted-foreground">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </div>
        <div class="flex items-center space-x-6 lg:space-x-8">
            <!-- Per Page Select -->
            <div class="flex items-center space-x-2">
                <p class="text-sm font-medium">Rows per page:</p>
                <Select :model-value="pagination.perPage.toString()" @update:model-value="handlePerPageChange">
                    <SelectTrigger class="h-8 w-[70px]">
                        <SelectValue :placeholder="pagination.perPage.toString()" />
                    </SelectTrigger>
                    <SelectContent side="top">
                        <SelectItem v-for="size in [10, 20, 30, 40, 50]" :key="size" :value="size.toString()">
                            {{ size }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Page Info -->
            <div class="flex w-[100px] items-center justify-center text-sm font-medium">
                Page {{ pagination.currentPage }} of {{ pageCount }}
            </div>

            <!-- Navigation Buttons -->
            <div class="flex items-center space-x-2">
                <Button variant="outline" class="hidden h-8 w-8 p-0 lg:flex" :disabled="pagination.currentPage <= 1"
                    @click="goToPage(1)">
                    <span class="sr-only">Go to first page</span>
                    <ChevronsLeftIcon class="h-4 w-4" />
                </Button>

                <Button variant="outline" class="h-8 w-8 p-0" :disabled="pagination.currentPage <= 1"
                    @click="goToPage(pagination.currentPage - 1)">
                    <span class="sr-only">Go to previous page</span>
                    <ChevronLeftIcon class="h-4 w-4" />
                </Button>

                <Button variant="outline" class="h-8 w-8 p-0" :disabled="pagination.currentPage >= pageCount"
                    @click="goToPage(pagination.currentPage + 1)">
                    <span class="sr-only">Go to next page</span>
                    <ChevronRightIcon class="h-4 w-4" />
                </Button>

                <Button variant="outline" class="hidden h-8 w-8 p-0 lg:flex"
                    :disabled="pagination.currentPage >= pageCount" @click="goToPage(pageCount)">
                    <span class="sr-only">Go to last page</span>
                    <ChevronsRightIcon class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
