<template>
    <SCFormField :field="field" :form="form">
        <Input v-bind="field.props || {}" v-model="form[field.name]" :class="[
            field.props?.disabled && 'bg-gray-100 cursor-not-allowed'
        ]" :id="`${field.name}_${generateUniqueId()}`" />
    </SCFormField>
</template>
<script setup lang="ts">
import SCFormField from '@/components/core/form/SCFormField.vue';
import { Input } from '@/components/ui/input';
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