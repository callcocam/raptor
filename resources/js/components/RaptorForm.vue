<template>
    <div>
        <slot />
        <div class="py-12">
            <SCForm :sections="sections" :record="record" :actions="actions" :config="config"  :values="values" />
        </div>
    </div>
</template>
<script setup lang="ts"> 
import { FormConfig } from './form/types';

defineProps<{
    sections: any[];
    record: any;
    values: any;
    config: FormConfig & {
        action: string;
        method: 'post' | 'put' | 'patch';
    };
    actions: any[];
}>();
</script>
