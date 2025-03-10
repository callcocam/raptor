<template>
    <ShadcnFormField :name="field.name">
        <FormItem class="space-y-4">
            <FormLabel v-if="field.label">{{ field.label }}</FormLabel>
            <!-- CEP field with auto-complete -->
            <div class="grid grid-cols-6 gap-4">
                <div class="col-span-2">
                    <FormFieldAddres label="CEP" name="zip_code" :errors="form.errors" :parent-field="field.name">
                        <Input v-model="address.zip_code" placeholder="00000-000" @blur="handlePostalCodeBlur"
                            :disabled="loading" />
                        <div v-if="loading" class="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                            <Loader2 class="h-3 w-3 animate-spin" />
                            <span>Buscando CEP...</span>
                        </div>
                    </FormFieldAddres>
                </div>
            </div>
            <!-- Street & Number -->
            <div class="grid grid-cols-6 gap-4">
                <div class="col-span-4">
                    <FormFieldAddres label="Logradouro" name="street" :errors="form.errors" :parent-field="field.name">
                        <Input v-model="address.street" placeholder="Rua, Avenida, etc"
                            :disabled="field.props?.disabled" />
                    </FormFieldAddres>
                </div>
                <div class="col-span-2">
                    <FormFieldAddres label="Número" name="number" :errors="form.errors" :parent-field="field.name">
                        <Input v-model="address.number" placeholder="Número" :disabled="field.props?.disabled" />
                    </FormFieldAddres>
                </div>
            </div>
            <!-- Complement & district -->
            <div class="grid grid-cols-6 gap-4">
                <div class="col-span-3">
                    <FormFieldAddres label="Complemento" name="complement" :errors="form.errors"
                        :parent-field="field.name">
                        <Input v-model="address.complement" placeholder="Apto, Sala, etc"
                            :disabled="field.props?.disabled" />
                    </FormFieldAddres>
                </div>
                <div class="col-span-3">
                    <FormFieldAddres label="Bairro" name="district" :errors="form.errors" :parent-field="field.name">
                        <Input v-model="address.district" placeholder="Bairro" :disabled="field.props?.disabled" />
                    </FormFieldAddres>
                </div>
            </div>
            <!-- City & State -->
            <div class="grid grid-cols-6 gap-4">
                <div class="col-span-4">
                    <FormFieldAddres label="Cidade" name="city" :errors="form.errors" :parent-field="field.name">
                        <Input v-model="address.city" placeholder="Cidade" :disabled="field.props?.disabled" />
                    </FormFieldAddres>
                </div>
                <div class="col-span-2">
                    <FormFieldAddres label="UF" name="state" :errors="form.errors" :parent-field="field.name">
                        <Select v-model="address.state" :disabled="field.props?.disabled">
                            <SelectTrigger>
                                <SelectValue placeholder="UF" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="state in brazilianStates" :key="state.value" :value="state.value">
                                    {{ state.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </FormFieldAddres>
                </div>
            </div>
            <div class="col-span-12">
                <FormFieldAddres label="Padrão" name="is_default" :errors="form.errors" :parent-field="field.name">
                    <div class="flex items-center space-x-2">
                        <Switch id="airplane-mode" v-model="address.is_default" />
                    </div>
                </FormFieldAddres>
            </div>
            <FormDescription v-if="field.description">{{ field.description }}</FormDescription>
            <FormMessage :errors="form.errors" :name="field.name" />
        </FormItem>
    </ShadcnFormField>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { FormField as ShadcnFormField, FormItem, FormLabel, FormControl, FormMessage, FormDescription } from '@/components/ui/form'
import FormFieldAddres from './../FormFieldAddres.vue'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Loader2 } from 'lucide-vue-next'
import axios from 'axios'
import { Switch } from '@/components/ui/switch'

interface AddressData {
    zip_code: string | number | any
    street: string | number | any
    number: string | number
    complement: string | number | any
    district: string | number | any
    city: string | number | any
    state: string | number | any
    is_default: boolean | number | any
    [key: string]: string | number
}

interface AddressInputProps {
    field: {
        type: 'address',
        name: string,
        label?: string,
        description?: string,
        props?: Record<string, any>
    }
    form: any
    modelValue?: AddressData
}

const props = defineProps<AddressInputProps>()
const emit = defineEmits(['update:modelValue'])

// Brazilian states
const brazilianStates = [
    { value: 'AC', label: 'Acre' },
    { value: 'AL', label: 'Alagoas' },
    { value: 'AP', label: 'Amapá' },
    { value: 'AM', label: 'Amazonas' },
    { value: 'BA', label: 'Bahia' },
    { value: 'CE', label: 'Ceará' },
    { value: 'DF', label: 'Distrito Federal' },
    { value: 'ES', label: 'Espírito Santo' },
    { value: 'GO', label: 'Goiás' },
    { value: 'MA', label: 'Maranhão' },
    { value: 'MT', label: 'Mato Grosso' },
    { value: 'MS', label: 'Mato Grosso do Sul' },
    { value: 'MG', label: 'Minas Gerais' },
    { value: 'PA', label: 'Pará' },
    { value: 'PB', label: 'Paraíba' },
    { value: 'PR', label: 'Paraná' },
    { value: 'PE', label: 'Pernambuco' },
    { value: 'PI', label: 'Piauí' },
    { value: 'RJ', label: 'Rio de Janeiro' },
    { value: 'RN', label: 'Rio Grande do Norte' },
    { value: 'RS', label: 'Rio Grande do Sul' },
    { value: 'RO', label: 'Rondônia' },
    { value: 'RR', label: 'Roraima' },
    { value: 'SC', label: 'Santa Catarina' },
    { value: 'SP', label: 'São Paulo' },
    { value: 'SE', label: 'Sergipe' },
    { value: 'TO', label: 'Tocantins' }
]

// Local state for address data
const loading = ref(false)
const address = ref<AddressData | any>({
    zip_code: '',
    street: '',
    number: '',
    complement: '',
    district: '',
    city: '',
    state: '',
    is_default: true
})

// Initialize form with default values or from provided modelValue
function initializeAddress() {
    if (props.modelValue) {
        address.value = { ...props.modelValue }
    } else if (props.form && props.field.name && props.form[props.field.name]) {
        address.value = { ...props.form[props.field.name] }
    }
    // Remove console log for production
    // console.log('Initializing address...', address.value)
}

// Format CEP (postal code)
function formatCEP(cep: string) {
    cep = cep.replace(/\D/g, '')
    if (cep.length > 5) {
        cep = cep.substring(0, 5) + '-' + cep.substring(5, 8)
    }
    return cep
}

// Look up address by postal code
async function handlePostalCodeBlur() {
    const cep = address.value.zip_code?.toString().replace(/\D/g, '') || ''

    if (cep.length !== 8) {
        return
    }

    loading.value = true

    try {
        const response = await axios.get(`https://viacep.com.br/ws/${cep}/json/`)

        if (!response.data.erro) {
            address.value = {
                ...address.value,
                street: response.data.logradouro || address.value.street,
                district: response.data.bairro || address.value.district,
                city: response.data.localidade || address.value.city,
                state: response.data.uf || address.value.state
            }

            updateFormValue()
        }
    } catch (error) {
        console.error('Failed to fetch address data:', error)
    } finally {
        loading.value = false
    }
}

// Update form value with current address
function updateFormValue() {
    // Remove console log for production
    // console.log('Updating form value...', props.form[props.field.name])
    if (props.form && props.field.name) {
        props.form[props.field.name] = { ...address.value }
    }
    // Emit update event
    emit('update:modelValue', { ...address.value })
}

// FIXED: Watch the entire address ref object with deep option for nested property changes
watch(address, () => {
    updateFormValue()
}, { deep: true })

// Format postal code when it changes
watch(() => address.value.zip_code, (newValue) => {
    if (newValue && typeof newValue === 'string') {
        address.value.zip_code = formatCEP(newValue)
    }
})

// FIXED: Watch for external modelValue changes with deep option
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        // Create a new object to ensure reactivity
        address.value = { ...newValue }
    }
}, { deep: true })

// FIXED: Also watch for form value changes
watch(() => props.form?.[props.field.name], (newValue) => {
    if (newValue && Object.keys(newValue).length > 0) {
        // Avoid unnecessary updates if the values are the same
        const currentKeys = Object.keys(address.value);
        const hasChanges = Object.keys(newValue).some(key =>
            currentKeys.includes(key) && newValue[key] !== address.value[key]
        );

        if (hasChanges) {
            address.value = { ...newValue }
        }
    }
}, { deep: true })

// Initialize on mount
onMounted(() => {
    initializeAddress()
})
</script>
