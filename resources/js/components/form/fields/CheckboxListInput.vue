<template>
    <FormField :name="field.name" v-slot="{ errorMessage }">
        <FormItem>
            <div class="mb-4">
                <FormLabel class="text-base">
                    {{ field.label }}
                </FormLabel>
                <FormDescription v-if="field.description">
                    {{ field.description }}
                </FormDescription>
            </div>
            <div class="grid gap-4">
                <FormItem v-for="item in items" :key="item.value" class="flex flex-row items-start space-x-3 space-y-0">
                    <FormControl>
                        <Checkbox :checked="selectedItems.includes(item.value)"
                            @update:checked="(checked) => toggleItem(item.value, checked)" :id="uniqueId(item.value)"/>
                    </FormControl>
                    <FormLabel class="font-normal">
                        {{ item.label }}
                    </FormLabel>
                </FormItem>
            </div>
            <FormMessage :errors="form.errors" :name="field.name" />
        </FormItem>
    </FormField>
</template>

<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox'
import {
    FormField,
    FormControl,
    FormDescription,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form'
import type { FormField as IFormField } from '../types'
import { ref, watch } from 'vue'

const props = defineProps<{
    field: IFormField
    form: any
}>()

const emit = defineEmits(['update:modelValue'])
const items = ref(props.field.options || [])
const selectedItems = ref<string[]>(props.form[props.field.name] || [])

const uniqueId = (value: string) => {
    return `${props.field.name}-${value}`
}

const toggleItem = (value: string, checked: boolean) => {
    if (checked) {
        selectedItems.value.push(value)
    } else {
        const index = selectedItems.value.indexOf(value)
        if (index >= 0) {
            selectedItems.value.splice(index, 1)
        }
    }
    updateForm()
}

const updateForm = () => {
    props.form[props.field.name] = selectedItems.value
    emit('update:modelValue', selectedItems.value)
}

// Sincroniza mudanças externas do form
watch(() => props.form[props.field.name], (newValue) => {
    if (newValue !== selectedItems.value) {
        selectedItems.value = Array.isArray(newValue) ? newValue : []
    }
}, { deep: true })
</script>