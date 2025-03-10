<template>
    <SCFormField :field="field" :form="form">
        <Popover>
            <PopoverTrigger as-child>
                <Button variant="outline" :class="cn(
                    'w-full justify-start text-left font-normal',
                    !selectedDate && 'text-muted-foreground',
                )" type="button">
                    <CalendarIcon class="mr-2 h-4 w-4" />
                    {{ selectedDate && 'toDate' in selectedDate ? formatDate(selectedDate) : field.placeholder
                        || "Pick a date" }}
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto ">
                <Calendar initial-focus v-model="selectedDate" :class="[
                    'w-full',
                    field.props?.disabled && 'bg-gray-100 cursor-not-allowed'
                ]" :id="`${field.name}_${uniqueId}`" @update:model-value="handleDateChange as any"
                    v-bind="field.props || {}" />
            </PopoverContent>
        </Popover>
    </SCFormField>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import SCFormField from '@/components/core/form/SCFormField.vue';
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { FormField } from '../types';
import {
    DateFormatter,
    type DateValue,
    getLocalTimeZone,
    CalendarDate,
    type ZonedDateTime
} from '@internationalized/date'
import { CalendarIcon } from 'lucide-vue-next'

// Define props with TypeScript interface for better type checking
interface DatePickerInputProps {
    field: FormField & {
        type: 'date';
        format?: 'full' | 'long' | 'medium' | 'short';
        locale?: string;
        placeholder?: string;
        props?: Record<string, any>;
        defaultValue?: string | null;
    };
    form: any;
    modelValue?: DateValue;
}

const props = defineProps<DatePickerInputProps>();
const emit = defineEmits(['update:modelValue']);

// Create date formatter with proper locale and format settings
const dateFormatter = computed(() => {
    return new DateFormatter(props.field.locale || 'en', {
        dateStyle: props.field.format || 'medium',
    });
});

// Generate unique ID once for this instance
const uniqueId = generateUniqueId();

// Initialize the selected date with either provided model value or default value
const selectedDate = ref<DateValue | any>(initializeDate());

// Format a date object for display
function formatDate(date: DateValue) {
    if (!date || !('toDate' in date)) return '';
    return dateFormatter.value.format(date.toDate(getLocalTimeZone()));
}

// Handle date change and emit the updated value
function handleDateChange(date: DateValue) {
    emit('update:modelValue', date);

    // Update the form value if the form object is available
    if (props.form && props.field.name) {
        props.form[props.field.name] = date;

        // If the form needs an ISO string format instead of the date object
        if (props.field.props?.useISOString) {
            props.form[props.field.name] = formatISODate(date);
        }
    }
}

// Format date to ISO string for storing in the form
function formatISODate(date: DateValue) {
    if (!date) return null;
    return date.toDate(getLocalTimeZone()).toISOString();
}

// Initialize the date from props or use defaults
function initializeDate() {
    // Use model value if provided
    if (props.modelValue) {
        return props.modelValue;
    }

    // Use form value if available
    if (props.form && props.field.name && props.form[props.field.name]) {
        return props.form[props.field.name];
    }

    // Use default value if provided
    if (props.field.defaultValue) {
        return parseDate(props.field.defaultValue);
    }

    // Return null if no default value
    return null;
}

// Parse a date string into a CalendarDate object
function parseDate(dateString: string): CalendarDate | null {
    if (!dateString) return null;
    try {
        const date = new Date(dateString);
        return new CalendarDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
    } catch (e) {
        console.error('Error parsing date:', e);
        return null;
    }
}

/**
 * Generate a unique ID for each instance
 */
function generateUniqueId(): string {
    return Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
}

// Watch for external form value changes
watch(() => props.form[props.field.name], (newValue) => {
    if (newValue && newValue !== selectedDate.value) {
        selectedDate.value = newValue;
    }
}, { deep: true });

// Watch for external model value changes
watch(() => props.modelValue, (newValue) => {
    if (newValue && newValue !== selectedDate.value) {
        selectedDate.value = newValue;
    }
}, { deep: true });

// On component mount, emit the initial value if available
onMounted(() => {
    if (selectedDate.value) {
        handleDateChange(selectedDate.value as DateValue);
    }
});
</script>