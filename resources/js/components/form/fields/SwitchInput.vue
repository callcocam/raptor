<template>
    <SCFormField :field="field" :form="form">
        <Switch v-model="form[field.name]" :id="`${field.name}_${generateUniqueId()}`" />
    </SCFormField>
</template>
<script setup lang="ts">
import SCFormField from '@/components/core/form/SCFormField.vue';
import { FormField } from '../types';
import { Switch } from '@/components/ui/switch';
import { watch } from 'vue';
const props = defineProps<{
    field: FormField;
    form: any;
}>();
const emit = defineEmits(['update:modelValue']);

watch(() => props.form[props.field.name], (newValue) => {
    emit('update:modelValue', newValue)
}, { deep: true })

/**
 * Gera um ID único para cada item
 */
function generateUniqueId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
}
</script>