<template>
  <SCFormField :field="field" :form="form">
    <div class="w-full">
      <!-- Container para tags e input -->
      <div
        class="flex flex-wrap p-2 border rounded-md focus-within:border-primary focus-within:ring-1 focus-within:ring-primary"
        :class="[field.props?.disabled && 'bg-gray-100 cursor-not-allowed']" @click="focusInput">
        <!-- Tags existentes -->
        <div v-for="(tag, index) in tags" :key="index" class="flex items-center bg-muted m-1 px-2 py-1 rounded-md">
          <span>{{ tag }}</span>
          <button type="button" @click.prevent="removeTag(index)"
            class="ml-1 text-muted-foreground hover:text-foreground" :disabled="field.props?.disabled">
            <Icon name="X" class="w-3 h-3" />
          </button>
        </div>

        <!-- Input para novas tags -->
        <input ref="inputRef" v-model="inputValue" type="text"
          class="flex-grow min-w-[80px] px-2 py-1 outline-none bg-transparent"
          :placeholder="tags.length === 0 ? (field.props?.placeholder || 'Adicionar tags...') : ''"
          @keydown.enter.prevent="addTag" @keydown.backspace="handleBackspace" @keydown.delete="handleDelete"
          @keydown.tab="addTag" @keydown.188="handleComma" :disabled="field.props?.disabled" />
      </div>
    </div>
  </SCFormField>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue';
import SCFormField from '@/components/core/form/SCFormField.vue';
import Icon from '@/components/Icon.vue';
import { FormField } from '../types';

const props = defineProps<{
  field: FormField;
  form: any;
}>();

// Estado local
const inputRef = ref<HTMLInputElement | null>(null);
const inputValue = ref('');
const tags = ref<string[]>([]);

// Valor computado baseado no nome do campo
const fieldName = computed(() => props.field.name);

// Inicialização
onMounted(() => {
  // Carrega as tags existentes do formulário
  if (props.form[fieldName.value]) {
    const formValue = props.form[fieldName.value];

    if (Array.isArray(formValue)) {
      tags.value = [...formValue];
    } else if (typeof formValue === 'string') {
      // Se for uma string, tenta converter para array (ex: valores separados por vírgula)
      tags.value = formValue.split(',').map(tag => tag.trim()).filter(tag => tag);
    }
  }

  // Sincroniza o estado inicial com o formulário
  updateFormValue();
});

// Observa mudanças nos valores do formulário para sincronizar
watch(() => props.form[fieldName.value], (newValue) => {
  if (newValue === undefined || newValue === null) {
    tags.value = [];
    return;
  }

  // Evita atualizações cíclicas comparando com o estado atual
  const currentValue = Array.isArray(newValue) ? newValue : newValue.split(',').map((tag: string) => tag.trim()).filter((tag: string) => tag);

  if (JSON.stringify(currentValue) !== JSON.stringify(tags.value)) {
    tags.value = Array.isArray(newValue) ? [...newValue] : newValue.split(',').map((tag: string) => tag.trim()).filter((tag: string) => tag);
  }
}, { deep: true });

// Foca no input quando o container é clicado
function focusInput() {
  if (!props.field.props?.disabled && inputRef.value) {
    inputRef.value.focus();
  }
}

// Adiciona uma nova tag
function addTag() {
  const trimmedValue = inputValue.value.trim();

  if (trimmedValue) {
    // Verifica se é uma lista de tags separadas por vírgula
    const newTags = trimmedValue.split(',').map(tag => tag.trim()).filter(tag => tag);

    // Adiciona apenas tags que não existam ainda
    newTags.forEach(tag => {
      if (!tags.value.includes(tag)) {
        tags.value.push(tag);
      }
    });

    // Limpa o input
    inputValue.value = '';

    // Atualiza o valor no formulário
    updateFormValue();
  }
}

// Remove uma tag específica
function removeTag(index: number) {
  tags.value.splice(index, 1);
  updateFormValue();
}

// Trata tecla backspace para remover a última tag quando o input estiver vazio
function handleBackspace(event: KeyboardEvent) {
  if (inputValue.value === '' && tags.value.length > 0) {
    removeTag(tags.value.length - 1);
  }
}

// Trata tecla delete (similar ao backspace)
function handleDelete(event: KeyboardEvent) {
  if (inputValue.value === '' && tags.value.length > 0) {
    removeTag(tags.value.length - 1);
  }
}

// Trata vírgula para adicionar tag
function handleComma(event: KeyboardEvent) {
  event.preventDefault();
  addTag();
}

// Atualiza o valor no formulário
function updateFormValue() {
  const outputFormat = props.field.props?.outputFormat || 'array';

  if (outputFormat === 'string') {
    props.form[fieldName.value] = tags.value.join(',');
  } else {
    // Default é array
    props.form[fieldName.value] = [...tags.value];
  }

  // Notifica o formulário da mudança se tiver o método $touch
  if (typeof props.form.$touch === 'function') {
    props.form.$touch(fieldName.value);
  }
}
</script>

<style scoped>
/* Estilos adicionais podem ser adicionados aqui */
</style>
