// fields/RichTextEditor.vue
<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3'
import { ref } from 'vue'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import Link from '@tiptap/extension-link'
import { Button } from '@/components/ui/button'
import {
    Bold,
    Italic,
    List,
    ListOrdered,
    Quote,
    Undo,
    Redo,
    Link as LinkIcon,
    Image as ImageIcon
} from 'lucide-vue-next'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import FileInput from './FileInput.vue'
import { FormField as IFormField } from '../types'

const props = defineProps<{
    field: IFormField,
    form: any
}>()

const emit = defineEmits(['update:modelValue'])
const showImageDialog = ref(false)
const imageFile = ref<File | undefined>(undefined)

const editor = useEditor({
    content: props.form[props.field.name] || '',
    extensions: [
        StarterKit,
        Image,
        Link.configure({
            openOnClick: false
        })
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose lg:prose-lg xl:prose-2xl mx-auto focus:outline-none'
        }
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML())
    }
}) as any

const addLink = () => {
    const url = window.prompt('URL:')
    if (url) {
        editor.value.chain().focus().setLink({ href: url }).run()
    }
}

const addImage = () => {
    showImageDialog.value = true
}

const handleImageUpload = () => {
    if (imageFile.value) {
        const url = URL.createObjectURL(imageFile.value)
        editor.value.chain().focus().setImage({ src: url }).run()
        showImageDialog.value = false
        imageFile.value = undefined
    }
}
</script>

<template>
    <div class="border rounded-lg p-4">
        <!-- Barra de ferramentas -->
        <div class="flex flex-wrap gap-2 mb-4 border-b ">
            <!-- Grupo de formatação básica -->
            <div class="flex -space-x-px">
                <Button type="button" variant="outline" size="sm" :class="[
                    'rounded-r-none',
                    { 'bg-muted': editor?.isActive('bold') }
                ]" @click="editor?.chain().focus().toggleBold().run()">
                    <Bold class="h-4 w-4" />
                </Button>
                <Button type="button" variant="outline" size="sm" :class="[
                    'rounded-l-none',
                    { 'bg-muted': editor?.isActive('italic') }
                ]" @click="editor?.chain().focus().toggleItalic().run()">
                    <Italic class="h-4 w-4" />
                </Button>
            </div>

            <!-- Grupo de listas -->
            <div class="flex -space-x-px">
                <Button type="button" variant="outline" size="sm" :class="[
                    'rounded-r-none',
                    { 'bg-muted': editor?.isActive('bulletList') }
                ]" @click="editor?.chain().focus().toggleBulletList().run()">
                    <List class="h-4 w-4" />
                </Button>
                <Button type="button" variant="outline" size="sm" :class="[
                    'rounded-l-none',
                    { 'bg-muted': editor?.isActive('orderedList') }
                ]" @click="editor?.chain().focus().toggleOrderedList().run()">
                    <ListOrdered class="h-4 w-4" />
                </Button>
            </div>

            <!-- Grupo de citação e links -->
            <div class="flex -space-x-px">
                <Button type="button" variant="outline" size="sm" :class="[
                    'rounded-r-none',
                    { 'bg-muted': editor?.isActive('blockquote') }
                ]" @click="editor?.chain().focus().toggleBlockquote().run()">
                    <Quote class="h-4 w-4" />
                </Button>
                <Button type="button" variant="outline" size="sm" :class="[
                    'rounded-none',
                    { 'bg-muted': editor?.isActive('link') }
                ]" @click="addLink">
                    <LinkIcon class="h-4 w-4" />
                </Button>
                <Button type="button" variant="outline" size="sm" class="rounded-l-none" @click="addImage">
                    <ImageIcon class="h-4 w-4" />
                </Button>
            </div>

            <!-- Grupo de histórico -->
            <div class="flex -space-x-px">
                <Button type="button" variant="outline" size="sm" class="rounded-r-none"
                    @click="editor?.chain().focus().undo().run()">
                    <Undo class="h-4 w-4" />
                </Button>
                <Button type="button" variant="outline" size="sm" class="rounded-l-none"
                    @click="editor?.chain().focus().redo().run()">
                    <Redo class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- Área do editor -->
        <EditorContent :editor="editor" class="min-h-[200px] prose max-w-none bg-slate-300/10" />

        <!-- Modal de Upload de Imagem -->
        <Dialog :open="showImageDialog" @update:open="showImageDialog = false">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Upload de Imagem</DialogTitle>
                </DialogHeader>

                <div class="space-y-4">
                    <FileInput v-model:value="imageFile" :multiple="false" accept="image/*" />

                    <div class="flex justify-end gap-2">
                        <Button variant="outline" @click="showImageDialog = false">
                            Cancelar
                        </Button>
                        <Button @click="handleImageUpload" :disabled="!imageFile">
                            Inserir Imagem
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style>
.ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #adb5bd;
    pointer-events: none;
    height: 0;
}

.ProseMirror {
    >*+* {
        margin-top: 0.75em;
    }
}

img {
    display: block;
    height: auto;
    margin: 1.5rem 0;
    max-width: 100%;
}

.ProseMirror-selectednode img::selection {
    outline: 3px solid var(--purple);
}
</style>