<template>
    <SCFormField :field="field" :form="form">
        <Textarea v-bind="field.props || {}" v-model="form[field.name]" :class="[
            'w-full',
            field.props?.disabled && 'bg-gray-100 cursor-not-allowed'
            ]" :id="`${field.name}_${generateUniqueId()}`" /> 
    </SCFormField>
</template>
<script setup lang="ts">
import SCFormField from '@/components/core/form/SCFormField.vue';
import { FormField } from '../types';
import { Textarea } from '@/components/ui/textarea';
 
defineProps<{
    field: FormField;
    form: any;
}>(); 
/**
 * Gera um ID único para cada item
 */
function generateUniqueId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
}

const emit = defineEmits(['update:modelValue']);


</script>