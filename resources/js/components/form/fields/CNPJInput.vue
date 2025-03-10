<template>
  <ShadcnFormField :name="field.name">
    <FormItem>
      <FormLabel v-if="field.label">{{ field.label }}</FormLabel>
      <FormControl>
        <Input 
          ref="el" 
          type="text" 
          v-bind="field.props || {}" 
          :class="[
            'w-full',
            field.props?.disabled && 'bg-gray-100 cursor-not-allowed'
          ]" 
          :id="`${field.name}_${uniqueId}`" 
          v-model="inputValue" 
        />
      </FormControl>
      <FormDescription v-if="field.description">{{ field.description }}</FormDescription>
      <FormMessage :errors="form.errors" :name="field.name" />
    </FormItem>
  </ShadcnFormField>
</template>

<script setup lang="ts">
import { FormField as ShadcnFormField, FormItem, FormLabel, FormControl, FormMessage, FormDescription } from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import { watch, ref, onMounted } from 'vue'

interface CNPJInputProps {
  field: {
    name: string;
    label?: string;
    description?: string;
    type: string;
    props?: Record<string, any>;
  }
  form: any;
  modelValue?: string;
}

const props = defineProps<CNPJInputProps>();
const emit = defineEmits(['update:modelValue']);

const uniqueId = ref(generateUniqueId());
const inputValue = ref<any>('');

// Format CNPJ as XX.XXX.XXX/XXXX-XX
function formatCNPJ(value: string): string {
  // Remove non-digit characters
  const digits = value.replace(/\D/g, '');
  
  // Apply CNPJ mask
  if (digits.length <= 2) {
    return digits;
  } else if (digits.length <= 5) {
    return digits.substring(0, 2) + '.' + digits.substring(2);
  } else if (digits.length <= 8) {
    return digits.substring(0, 2) + '.' + digits.substring(2, 5) + '.' + digits.substring(5);
  } else if (digits.length <= 12) {
    return digits.substring(0, 2) + '.' + digits.substring(2, 5) + '.' + digits.substring(5, 8) + '/' + digits.substring(8);
  } else {
    return digits.substring(0, 2) + '.' + digits.substring(2, 5) + '.' + digits.substring(5, 8) + '/' + 
           digits.substring(8, 12) + '-' + digits.substring(12, 14);
  }
}

// Watch for input value changes to format CNPJ
watch(inputValue, (newValue) => {
  if (newValue === null || newValue === undefined) return;
  
  // Format only if the change is from user input, not from programmatic changes
  const formatted = formatCNPJ(newValue);
  
  // Only update if it's different to avoid infinite loops
  if (formatted !== newValue) {
    inputValue.value = formatted;
  }
  
  // Update form
  if (props.form && props.field.name) {
    props.form[props.field.name] = inputValue.value;
  }
  
  // Emit event
  emit('update:modelValue', inputValue.value);
});

// Watch for external model changes
watch(() => props.modelValue, (newValue) => {
  if (newValue !== undefined && newValue !== inputValue.value) {
    inputValue.value = formatCNPJ(newValue);
  }
});

// Initialize value from form or model
onMounted(() => {
  if (props.modelValue) {
    inputValue.value = formatCNPJ(props.modelValue);
  } else if (props.form && props.field.name && props.form[props.field.name]) {
    inputValue.value = formatCNPJ(props.form[props.field.name]);
  }
});

function generateUniqueId() {
  return Math.random().toString(36).substring(2, 15);
}
</script>
