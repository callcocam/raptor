<template>
    <SCFormField :field="field" :form="form">
        <RadioGroup v-model="form[field.name]" class="grid gap-2">
            <FormItem v-for="item in field.options" :key="item.value" class="flex items-center space-x-3 space-y-0">
                <FormControl>
                    <RadioGroupItem :value="item.value" :id="`${field.name}_${generateUniqueId()}`" />
                </FormControl>
                <FormLabel class="font-normal">
                    {{ item.label }}
                </FormLabel>
            </FormItem>
        </RadioGroup>
    </SCFormField>
</template>

<script setup lang="ts">
import SCFormField from '@/components/core/form/SCFormField.vue';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import {
    FormItem,
    FormLabel,
    FormControl,
} from '@/components/ui/form'
import type { FormField as IFormField } from '../types'
defineProps<{
    field: IFormField;
    form: any;
}>();
const emit = defineEmits(['update:modelValue']);

/**
 * Gera um ID único para cada item
 */
function generateUniqueId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
}
</script>
