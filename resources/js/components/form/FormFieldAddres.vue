<template>
    <div class="space-y-1">
        <FormLabel v-if="label" :for="name">{{ label }}</FormLabel>
        <slot></slot>
        <p v-if="hint" class="text-sm text-muted-foreground">{{ hint }}</p>
        <!-- Error handling for nested fields -->
        <div v-if="hasError" class="mt-1 text-sm text-destructive">
            {{ errorMessage }}
        </div>
    </div>
</template>

<script setup lang="ts">
import { FormLabel } from '@/components/ui/form';
import { computed, watch } from 'vue';

const props = defineProps<{
    label?: string;
    name: string;
    hint?: string;
    errors?: Record<string, string[]>;
    parentField?: string;
}>();
console.log(props.errors);
// Check if there's an error for this field
const hasError = computed(() => {
    if (!props.errors) return false;
    // Check for direct error on this field
    if (props.errors[props.name]) return true;

    // Check for nested error (parentField.name format)
    if (props.parentField && props.errors[`${props.parentField}.${props.name}`]) {
        return true;
    }

    // Check for array notation error (parentField[name] format)
    if (props.parentField && props.errors[`${props.parentField}[${props.name}]`]) {
        return true;
    }

    return false;
});

// Get error message from errors object
const errorMessage = computed(() => {
    if (!props.errors) return '';
    // Direct error
    if (props.errors[props.name]) {
        return props.errors[props.name];
    }

    // Nested error (dot notation)
    if (props.parentField && props.errors[`${props.parentField}.${props.name}`]) {
        return props.errors[`${props.parentField}.${props.name}`];
    }

    // Array notation error
    if (props.parentField && props.errors[`${props.parentField}[${props.name}]`]) {
        return props.errors[`${props.parentField}[${props.name}]`];
    }

    return '';
});
watch(
    () => props.errors,
    () => {
        console.log(props.errors);
    },
);
</script>
