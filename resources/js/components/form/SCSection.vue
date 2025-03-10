<template>
    <div :class="formLayout"> 
        <div v-for="(field, index) in section.fields" :key="index" :class="generateGridClasses(field.grid)">
            <component :is="field.component" :field="field" :form="form" @update:modelValue="form[field.name] = $event" />
        </div>
    </div>
</template>
<script setup lang="ts">
import { computed, ref } from 'vue';
import { useGridClasses } from '@/composables/useGridClasses'
import { FormSection } from './types';

const props = defineProps<{
    section: FormSection;
    form: any;
}>();
const { generateGridClasses, generateLayoutClasses } = useGridClasses()
const formLayout = computed(() => {
    return generateLayoutClasses(props.section.layout)
})



</script>