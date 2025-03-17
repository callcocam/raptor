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
            <FormMessage :errors="currentErros" :name="field.name" /> 
            <p v-if="Object.keys(currentErros).includes(field.name)" class="text-sm text-destructive mt-1">
                {{ currentErros[field.name] }}
            </p>
        </FormItem>
    </ShadcnFormField>
</template>
<script setup lang="ts">
import { FormControl, FormDescription, FormItem, FormLabel, FormMessage, FormField as ShadcnFormField } from '@/components/ui/form';
import { ref, watch } from 'vue';
import { FormField } from './types';
/**
 * Gera um ID único para cada item
 */
function generateUniqueId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
}
const props = defineProps<{
    field: FormField;
    form: any;
    appendLabel?: boolean;
    prependLabel?: boolean;
}>();
const emit = defineEmits(['update:modelValue']);

const currentErros = ref<string[]>([]);

watch(
    () => props.form.errors,
    (errors) => {
        currentErros.value = errors || [];
    },
    { immediate: true },
);
</script>
