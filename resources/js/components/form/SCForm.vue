<template>
    <Form @submit="handleSubmit">
        <Card>
            <CardHeader>
                <CardTitle>{{ config.modelLabel }}</CardTitle>
                <CardDescription v-if="config.modelDescription">
                    {{ config.modelDescription }}
                </CardDescription>
                <template v-if="actions.length">
                    <div class="flex justify-end">
                        <div class="flex space-x-2">
                            <Link v-for="action in actions" :key="action.label" :href="action.href"
                                class="btn btn-primary">
                            {{ action.label }}
                            </Link>
                        </div>
                    </div>
                </template>
            </CardHeader>
            <CardContent>
                <div :class="formLayout" data-layout="formLayout">
                    <template v-for="(section, index) in sections" :key="index">
                        <div :class="generateGridClasses(section.grid)">
                            <div class="space-y-1">
                                <h4 class="text-sm font-medium leading-none">
                                    {{ section.label }}
                                </h4>
                                <p class="text-sm text-muted-foreground" v-if="section.description">
                                    {{ section.description }}
                                </p>
                            </div>
                            <Separator class="my-4" :label="section.separator" />
                            <component :is="section.component" :section="section" :form="form"
                                @form:update="handleUpdate" />
                        </div>
                    </template>
                    <slot />
                </div>
            </CardContent>
            <CardFooter>
                <div class="col-span-full flex justify-end gap-4 mt-6 w-full">
                    <Button type="submit" :disabled="form.processing">
                        {{ config.submitText || 'Salvar' }}
                    </Button>
                </div>
            </CardFooter>
        </Card>
    </Form>
</template>
<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useGridClasses } from '@/composables/useGridClasses'
import { Form } from '@/components/ui/form';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { FormConfig } from './types';
import Separator from '@/components/ui/separator/Separator.vue';

const props = defineProps<{
    sections: any[];
    record: any;
    actions: any[];
    config: FormConfig & {
        action: string;
        method: 'POST' | 'PUT' | 'PATCH' | 'DELETE' | 'post' | 'put' | 'patch' | 'delete';
    };
}>();

const { generateGridClasses, generateLayoutClasses } = useGridClasses()

const formLayout = computed(() => {
    return generateLayoutClasses(props.config.layout)
})

const form = useForm(props.record);


const handleSubmit = () => {
    if (props.config.method.toLowerCase() === 'post') {
        form.post(props.config.action, { preserveState: true })
    }
    else {
        form.put(props.config.action, { preserveState: true })
    }
};

const handleUpdate = (data: any) => {
    console.log('update', data)
};
</script>