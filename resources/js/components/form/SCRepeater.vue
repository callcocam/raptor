<template>
    <div> 
        <!-- Lista de itens repetíveis -->
        <div class="space-y-4">
            <div v-for="(item, index) in internalItems" :key="item.uniqueId"
                class="border rounded-md p-4 bg-gray-50 relative">
                <!-- Cabeçalho do item com opções -->
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center">
                        <!-- Botões de ordenação -->
                        <div class="flex space-x-1 mr-2" v-if="section.sortable && internalItems.length > 1">
                            <button v-if="index > 0" @click="moveItem(index, 'up')"
                                class="p-1 text-gray-600 hover:bg-gray-200 rounded" title="Mover para cima"
                                type="button">
                                <Icon name="ChevronUp" class="w-4 h-4" />
                            </button>

                            <button v-if="index < internalItems.length - 1" @click="moveItem(index, 'down')"
                                class="p-1 text-gray-600 hover:bg-gray-200 rounded" title="Mover para baixo"
                                type="button">
                                <Icon name="ChevronDown" class="w-4 h-4" />
                            </button>
                        </div>
                        <h4 class="font-medium">{{ section.label }} #{{ index + 1 }}</h4>
                    </div>

                    <!-- Botão de remoção -->
                    <button v-if="internalItems.length > 1" @click="removeItem(index)"
                        class="text-red-600 hover:text-red-800 p-1 hover:bg-red-100 rounded" title="Remover item"
                        type="button">
                        <Icon name="trash" class="w-4 h-4" />
                    </button>
                </div>

                <!-- Campos do formulário para este item -->
                <div :class="formLayout">
                    <div v-for="(field, fieldIndex) in section.fields" :key="`field-${index}-${fieldIndex}`"
                        :class="generateGridClasses(field.grid)">
                        <component :is="field.component" :field="getFieldWithUpdatedName(field, index)" :form="form" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Botão para adicionar novos itens -->
        <div class="mt-3 w-full flex justify-center items-center">
            <button @click="addItem"
                class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 flex space-x-1 items-center justify-center"
                type="button">
                <Icon :name="section.addIcon" class="w-4 h-4 mr-2" v-if="section.addIcon" />
                <span v-if="section.addText">{{ section.addText }}</span>
                <span v-else>Adicionar</span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue';
import { useGridClasses } from '@/composables/useGridClasses';
import Icon from '@/components/Icon.vue';

const props = defineProps<{
    section: any;
    form: any;
}>();

const baseFieldName = computed(() => props.section.name || 'repeater');
const internalItems = ref<any[]>([]);
const isUpdatingFormData = ref(false);

const { generateGridClasses, generateLayoutClasses } = useGridClasses();
const formLayout = computed(() => generateLayoutClasses(props.section.layout));

/**
 * Gera um ID único para os itens da lista.
 */
function generateUniqueId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
}

/**
 * Inicializa os itens a partir do formulário.
 */
function initializeItems() { 

    const formData = props.form[baseFieldName.value];
    if (Array.isArray(formData) && formData.length > 0) {
        const deepCopy = JSON.parse(JSON.stringify(formData));
        internalItems.value = deepCopy.map((item: any) => ({
            ...item,
            uniqueId: generateUniqueId()
        }));
    }
}

/**
 * Sincroniza os dados internos com o formulário.
 */
function syncToForm() {
    isUpdatingFormData.value = true;

    const cleanItems = internalItems.value.map(item => {
        const clean = { ...item };
        delete clean.uniqueId;
        return clean;
    });

    props.form[baseFieldName.value] = cleanItems;

    if (typeof props.form.$touch === 'function') {
        props.form.$touch(baseFieldName.value);
    }

    setTimeout(() => {
        isUpdatingFormData.value = false;
    }, 100);
}

/**
 * Adiciona um novo item à lista.
 */
function addItem() {
    let itemTemplate = {};

    if (internalItems.value.length > 0) {
        const lastItem = { ...internalItems.value[internalItems.value.length - 1] };
        delete lastItem.uniqueId;
        itemTemplate = lastItem;
    }

    internalItems.value.push({
        ...itemTemplate,
        uniqueId: generateUniqueId()
    });

    syncToForm();
}

/**
 * Remove um item da lista.
 */
function removeItem(index: number) {
    if (internalItems.value.length <= 1) return;

    internalItems.value.splice(index, 1);
    syncToForm();
}

/**
 * Move um item para cima ou para baixo na lista.
 */
function moveItem(index: number, direction: 'up' | 'down') {
    const newIndex = direction === 'up' ? index - 1 : index + 1;

    if (newIndex < 0 || newIndex >= internalItems.value.length) return;

    // Usando splice para garantir a reatividade
    const itemToMove = internalItems.value.splice(index, 1)[0];
    internalItems.value.splice(newIndex, 0, { ...itemToMove, uniqueId: generateUniqueId() });
    internalItems.value.map((item: any) => {
        console.log(item)
    });

    syncToForm();
}

/**
 * Atualiza o nome e o ID dos campos para refletir o índice correto.
 */
function getFieldWithUpdatedName(field: any, itemIndex: number) {
    const clonedField = JSON.parse(JSON.stringify(field));

    if (field.name) {
        clonedField.name = `${baseFieldName.value}[${itemIndex}][${field.name}]`; 
        if (field.id) {
            clonedField.id = `${field.id}_${itemIndex}`;
        }
        if (field.props) {
            if (field.props.id) {
                clonedField.props.id = `${field.props.id}_${itemIndex}`;
            }
            if (field.props['aria-describedby']) {
                clonedField.props['aria-describedby'] = `${field.props['aria-describedby']}_${itemIndex}`;
            }
        }
    }
    return clonedField;
}

// Inicializa os dados na montagem do componente
onMounted(() => {
    initializeItems();
    syncToForm();
});

// Observa mudanças externas nos dados do formulário para evitar sobrescrever mudanças manuais
watch(() => props.form[baseFieldName.value], (newValue) => { 
    if (isUpdatingFormData.value) return;
    if (JSON.stringify(newValue) === JSON.stringify(internalItems.value)) return;
    initializeItems();
}, { deep: true });
</script>
