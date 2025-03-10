<template>
    <FormField :name="field.name" v-slot="{ errorMessage }">
        <FormItem class="flex flex-row items-start gap-x-3 space-y-0 rounded-md border p-4 shadow">
            <FormControl>
                <Checkbox v-model:checked="form[field.name]"   @update:checked="handleChange"
                    :id="`${field.name}_${generateUniqueId()}`" />
            </FormControl>
            <div class="space-y-1 leading-none">
                <FormLabel>{{ field.label }}</FormLabel>
                <FormDescription v-if="field.description">
                    {{ field.description }}
                </FormDescription>
                <FormMessage :errors="form.errors" :name="field.name" />
            </div>
        </FormItem>
    </FormField>
</template>

<script setup lang="ts">
import { FormField, FormItem, FormLabel, FormControl, FormMessage, FormDescription } from '@/components/ui/form'
import { Checkbox } from '@/components/ui/checkbox'
import type { FormField as IFormField } from '../types'
import { ref, watch } from 'vue'

const props = defineProps<{
    field: IFormField
    form: any
}>()

const emit = defineEmits(['update:modelValue'])
const checked = ref(props.form[props.field.name] || false)

const handleChange = (value: boolean) => {
    checked.value = value
    props.form[props.field.name] = value // Atualiza o valor no formulário
    emit('update:modelValue', value)
}

watch(() => props.form[props.field.name], (newValue) => {
    if (newValue !== checked.value) {
        checked.value = !!newValue
    }
}, { deep: true })

/**
 * Gera um ID único para cada item
 */
function generateUniqueId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2, 5)
}
</script>