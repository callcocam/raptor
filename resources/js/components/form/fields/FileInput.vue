// fields/FileUpload.vue
<script setup>
import { onBeforeUnmount, ref, watch } from 'vue'
import {
    Button
} from '@/components/ui/button'
import {
    Card,
    CardContent
} from '@/components/ui/card'
import {
    Upload,
    X,
    FileText,
    Image as ImageIcon,
    ExternalLink,
    Download
} from 'lucide-vue-next'
import { Progress } from '@/components/ui/progress'

const props = defineProps({
    field: {
        type: Object,
        default: () => ({})
    },
    value: {
        type: [File, Array],
        default: null
    },
    multiple: {
        type: Boolean,
        default: false
    },
    accept: {
        type: String,
        default: '*/*'
    },
    maxSize: {
        type: Number,
        default: 5 * 1024 * 1024 // 5MB
    }
})

const emit = defineEmits(['update:value'])

const fileInput = ref(null)
const files = ref([])
const uploadProgress = ref({})
const previewUrls = ref({})

// Verifica se é uma imagem
const isImage = (file) => {
    return file.type.startsWith('image/')
}

// Gera preview para arquivos
const generatePreview = async (file) => {
    if (isImage(file)) {
        previewUrls.value[file.name] = URL.createObjectURL(file)
    }
}

// Simula upload com progresso
const simulateUpload = (file) => {
    uploadProgress.value[file.name] = 0
    const interval = setInterval(() => {
        if (uploadProgress.value[file.name] < 100) {
            uploadProgress.value[file.name] += 10
        } else {
            clearInterval(interval)
        }
    }, 200)
}

// Adiciona arquivos
const addFiles = async (newFiles) => {
    for (const file of newFiles) {
        if (file.size > props.maxSize) {
            alert(`Arquivo ${file.name} é muito grande. Tamanho máximo: ${props.maxSize / 1024 / 1024}MB`)
            continue
        }

        await generatePreview(file)
        simulateUpload(file)

        if (props.multiple) {
            files.value.push(file)
        } else {
            files.value = [file]
        }
    }

    emit('update:value', props.multiple ? files.value : files.value[0])
}

// Remove arquivo
const removeFile = (index) => {
    if (previewUrls.value[files.value[index].name]) {
        URL.revokeObjectURL(previewUrls.value[files.value[index].name])
        delete previewUrls.value[files.value[index].name]
    }
    files.value.splice(index, 1)
    emit('update:value', props.multiple ? files.value : files.value[0])
}

// Função para abrir preview em nova aba
const openPreview = (url) => {
    if (url) {
        window.open(url, '_blank')
    }
}

// Função para download do arquivo
const downloadFile = (file) => {
    const url = previewUrls.value[file.name] || URL.createObjectURL(file)
    const link = document.createElement('a')
    link.href = url
    link.download = file.name
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    if (!previewUrls.value[file.name]) {
        URL.revokeObjectURL(url)
    }
}

// Limpa previews quando componente é destruído
onBeforeUnmount(() => {
    Object.values(previewUrls.value).forEach(url => URL.revokeObjectURL(url))
})

watch(() => props.value, (newValue) => {
    if (!newValue) {
        files.value = []
    }
})

const generateUniqueId = () => {
    return Date.now().toString(36) + Math.random().toString(36).substr(2, 5)
}
</script>

<template>
    <div class="space-y-4">
        <!-- Área de upload -->
        <div class="border-2 border-dashed rounded-lg p-8 text-center hover:border-primary cursor-pointer"
            @click="fileInput?.click()" @dragover.prevent
            @drop.prevent="(e) => addFiles(Array.from(e.dataTransfer.files))">
            <input ref="fileInput" type="file" :accept="accept" :multiple="multiple" class="hidden"
                @change="(e) => addFiles(Array.from(e.target.files))" :id="`${field.name}_${generateUniqueId()}`" />

            <Upload class="h-12 w-12 mx-auto mb-4 text-muted-foreground" />
            <div class="text-sm text-muted-foreground">
                <span class="font-medium">Clique para upload</span> ou arraste e solte
                <br />
                {{ multiple ? 'Arquivos' : 'Arquivo' }} {{ accept !== '*/*' ? accept : '' }}
            </div>
        </div>

        <!-- Lista de arquivos -->
        <div v-if="files.length" class="space-y-2">
            <Card v-for="(file, index) in files" :key="file.name" class="overflow-hidden">
                <CardContent class="p-4 flex items-center gap-4">
                    <!-- Preview/Ícone -->
                    <div class="h-16 w-16 flex-shrink-0">
                        <img v-if="previewUrls[file.name]" :src="previewUrls[file.name]"
                            class="h-full w-full object-cover rounded cursor-pointer"
                            @click="openPreview(previewUrls[file.name])" alt="" />
                        <div v-else class="h-full w-full bg-muted rounded flex items-center justify-center">
                            <FileText class="h-8 w-8 text-muted-foreground" />
                        </div>
                    </div>

                    <!-- Informações do arquivo -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ file.name }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ (file.size / 1024).toFixed(1) }}KB
                        </p>
                        <Progress v-if="uploadProgress[file.name] < 100" :value="uploadProgress[file.name]"
                            class="mt-2" />
                    </div>

                    <!-- Ações -->
                    <div class="flex items-center gap-2">
                        <!-- Botão visualizar (apenas para imagens) -->
                        <Button v-if="isImage(file)" variant="ghost" size="sm" type="button"
                            @click="openPreview(previewUrls[file.name])">
                            <ExternalLink class="h-4 w-4" />
                        </Button>

                        <!-- Botão download -->
                        <Button variant="ghost" size="sm" @click="downloadFile(file)" type="button">
                            <Download class="h-4 w-4" />
                        </Button>

                        <!-- Botão remover -->
                        <Button variant="ghost" size="sm" @click.stop="removeFile(index)" type="button">
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>