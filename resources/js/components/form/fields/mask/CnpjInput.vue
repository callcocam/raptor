<template>
    <ShadcnFormField :name="field.name">
        <FormItem >
            <FormLabel v-if="field.label">{{ field.label }}</FormLabel>
            <FormControl> 
                <Input v-bind="field.props || {}" v-model="form[field.name]" :class="[
                    'w-full',
                    field.props?.disabled && 'bg-gray-100 cursor-not-allowed'
                ]" :id="`${field.name}_${generateUniqueId()}`" v-mask-cnpj/>
            </FormControl>
            <FormDescription v-if="field.description">{{ field.description }}</FormDescription>            
            <FormMessage :errors="form.errors" :name="field.name" />
        </FormItem>
    </ShadcnFormField>
</template>
<script setup lang="ts">
import { FormField as ShadcnFormField, FormItem, FormLabel, FormControl, FormMessage, FormDescription } from '@/components/ui/form'
import { Input } from '@/components/ui/input';
import { FormField } from '../../types';
 
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