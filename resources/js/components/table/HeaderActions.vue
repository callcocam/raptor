<script setup lang="ts">
import { Link } from '@inertiajs/vue3'; 
import { computed } from 'vue';
import Icon from '@/components/Icon.vue';
import { HeaderAction } from './types';
const props = defineProps<{
    actions?: HeaderAction[];
}>();

const validActions = computed(() => {
    // @ts-expect-error
    return props.actions?.filter((action: HeaderAction) => route().has(action.route));
});
</script>

<template>
    <div class="flex items-center space-x-1" v-if="validActions && validActions.length">
        <Link v-for="action in validActions" :key="action.route" :href="route(action.route)"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-1 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-800">
        <template v-if="action.icon" >
            <Icon :name="action.icon" class="mr-2" :size="action.iconSize" />
        </template>
        {{ action.label }}
        </Link>
    </div>
</template>
