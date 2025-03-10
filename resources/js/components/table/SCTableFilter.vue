<script setup lang="ts">
import { cn } from '@/lib/utils'
import { Input } from '@/components/ui/input'
import { Filter } from './types'
import { onMounted } from 'vue'

interface Props {
    loading?: boolean
    class?: string
    filters?: Filter[]
    initialSearch?: string
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    filters: () => [],
    initialSearch: ''
})

const modelValue = defineModel<string>()

onMounted(() => {
    const params = new URLSearchParams(window.location.search)
    const searchValue = params.get('search')
    if (searchValue) {
        modelValue.value = searchValue
    }
})

// Emit para informar mudanças no valor
const emit = defineEmits(['update:modelValue'])

const handleInput = (event: Event) => {
    const value = (event.target as HTMLInputElement).value
    emit('update:modelValue', value)
}
</script>

<template>
    <div :class="cn('flex items-center justify-between gap-4 py-4', props.class)">
        <div class="flex flex-1 items-center space-x-2">
            <Input 
                v-model="modelValue" 
                class="max-w-sm"
                placeholder="Search..." 
                :disabled="loading"
                type="search"
            />
            <slot name="filters" :filters="filters" />
        </div>
        <div class="flex items-center space-x-2">
            <slot name="actions" />
        </div>
        <div v-if="loading" class="animate-spin">
            <slot name="loading">
                <svg class="h-4 w-4" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                        fill="none" />
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
            </slot>
        </div>
    </div>
</template>
