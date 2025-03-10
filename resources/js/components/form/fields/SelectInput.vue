<template>
    <SCFormField :field="field" :form="form">
        <Select v-model="form[field.name]" :id="`${field.name}_${generateUniqueId()}`">
            <SelectTrigger :class="[
                'w-full',
                field.props?.disabled && 'bg-gray-100 cursor-not-allowed'
            ]">
                <SelectValue :placeholder="field.props?.placeholder" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem v-for="option in field.options" :key="option.value" :value="option.value">
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>
    </SCFormField>
</template>
<script setup lang="ts">
import SCFormField from '@/components/core/form/SCFormField.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import { FormField } from '../types';

defineProps<{
    field: FormField;
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