<template>
    <ShadcnFormField :name="field.name">
        <FormItem>
            <FormLabel v-if="field.label && !appendLabel && !prependLabel">
                {{ field.label }}
            </FormLabel>
            <FormControl>
                <slot />
            </FormControl>
            <FormLabel v-if="field.label && appendLabel">
                {{ field.label }}
                <slot name="appendLabel" />
            </FormLabel>
            <FormDescription v-if="field.description">{{ field.description }}</FormDescription>
            <FormMessage :errors="form.errors" :name="field.name" />
        </FormItem>
    </ShadcnFormField>
</template>
<script setup lang="ts">
import { FormField as ShadcnFormField, FormItem, FormLabel, FormMessage, FormDescription, FormControl} from '@/components/ui/form'
import { FormField } from './types';
/**
 * Gera um ID único para cada item
 */
function generateUniqueId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
}
defineProps<{
    field: FormField;
    form: any;
    appendLabel?: boolean;
    prependLabel?: boolean;
}>();
const emit = defineEmits(['update:modelValue']);
</script>