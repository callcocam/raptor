<template>
    <SCFormField :field="field" :form="form">
        <Popover>
            <PopoverTrigger as-child>
                <Button variant="outline" :class="cn(
                    'w-full justify-start text-left font-normal',
                    !dateRange.start && 'text-muted-foreground',
                )">
                    <CalendarIcon class="mr-2 h-4 w-4" />
                    <template v-if="dateRange.start">
                        <template v-if="dateRange.end">
                            {{ formatDate(dateRange.start as any) }} - {{ formatDate(dateRange.end as any) }}
                        </template>
                        <template v-else>
                            {{ formatDate(dateRange.start as any) }}
                        </template>
                    </template>
                    <template v-else>
                        {{ field.placeholder || 'Pick a date range' }}
                    </template>
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0">
                <RangeCalendar v-model="dateRange" initial-focus :number-of-months="field.numberOfMonths || 2"
                    @update:model-value="handleDateRangeChange" class="p-2" />
            </PopoverContent>
        </Popover>
    </SCFormField>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import type { DateRange } from 'reka-ui'
import { cn } from '@/lib/utils'
import { RangeCalendar } from '@/components/ui/range-calendar'
import { Button } from '@/components/ui/button'
import SCFormField from '@/components/core/form/SCFormField.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { FormField } from '../types';
import {
    CalendarDate,
    DateFormatter,
    getLocalTimeZone,
    type ZonedDateTime
} from '@internationalized/date'
import { CalendarIcon } from 'lucide-vue-next'

// Define props with TypeScript interface for better type checking
interface DateRangeInputProps {
    field: FormField & {
        type: 'date-range';
        format?: 'full' | 'long' | 'medium' | 'short';
        locale?: string;
        placeholder?: string;
        numberOfMonths?: number;
        defaultValue?: {
            start?: string | null;
            end?: string | null;
        };
    };
    form: any;
    modelValue?: DateRange;
}

const props = defineProps<DateRangeInputProps>();
const emit = defineEmits(['update:modelValue']);

// Create date formatter with proper locale and format settings
const dateFormatter = computed(() => {
    return new DateFormatter(props.field.locale || 'en', {
        dateStyle: props.field.format || 'medium',
    });
});

// Initialize the date range with either provided model value or default value
const dateRange = ref<DateRange | any>(initializeDateRange());

// Format a date object for display
function formatDate(date: CalendarDate | ZonedDateTime) {
    if (!date) return '';
    return dateFormatter.value.format(date.toDate(getLocalTimeZone()));
}

// Handle date range change and emit the updated value
function handleDateRangeChange(range: DateRange) {
    emit('update:modelValue', range);

    // Update the form value if the form object is available
    if (props.form && props.field.name) {
        props.form[props.field.name] = {
            start: range.start ? formatISODate(range.start as any) : null,
            end: range.end ? formatISODate(range.end as any) : null
        };
    }
}

// Format date to ISO string for storing in the form
function formatISODate(date: CalendarDate | ZonedDateTime) {
    if (!date) return null;
    return date.toDate(getLocalTimeZone()).toISOString();
}

// Initialize the date range from props or use defaults
function initializeDateRange() {
    if (props.modelValue) {
        return props.modelValue;
    }

    // Use default values if provided
    if (props.field.defaultValue) {
        const start = props.field.defaultValue.start ?
            parseDate(props.field.defaultValue.start) :
            new CalendarDate(new Date().getFullYear(), new Date().getMonth() + 1, new Date().getDate());

        const end = props.field.defaultValue.end ?
            parseDate(props.field.defaultValue.end) :
            new CalendarDate(new Date().getFullYear(), new Date().getMonth() + 1, new Date().getDate()).add({ days: 7 });

        return { start, end };
    }

    // Fallback to today and a week from now
    const today = new CalendarDate(new Date().getFullYear(), new Date().getMonth() + 1, new Date().getDate());
    return {
        start: today,
        end: today.add({ days: 7 })
    };
}

// Parse a date string into a CalendarDate object
function parseDate(dateString: string): CalendarDate {
    const date = new Date(dateString);
    return new CalendarDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
}

// Watch for external model value changes
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        dateRange.value = newValue;
    }
}, { deep: true });

// On component mount, emit the initial value
onMounted(() => {
    handleDateRangeChange(dateRange.value as DateRange);
});
</script>