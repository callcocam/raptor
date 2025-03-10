<script setup lang="ts">
import type { Component } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList, CommandSeparator } from '@/components/ui/command'
import { router } from '@inertiajs/vue3'
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'
import { Separator } from '@/components/ui/separator'
import { cn } from '@/lib/utils'
import { ref, computed, onMounted, watch } from 'vue'
import { CheckIcon, PlusCircle } from 'lucide-vue-next'

interface Props {
    title: string
    column: string
    options: {
        label: string
        value: string
        icon?: Component
    }[]
    routeName?: string
}

const props = withDefaults(defineProps<Props>(), {
    routeName: route().current()
})

const selectedValues = ref(new Set())

// Sincroniza o estado inicial com os parâmetros da URL
onMounted(() => {
    const params = new URLSearchParams(window.location.search)
    const filterValue = params.get(props.column)
    if (filterValue) {
        selectedValues.value = new Set(filterValue.split(','))
    }
})

// Observa mudanças na URL e atualiza o estado local
watch(() => window.location.search, (newSearch) => {
    const params = new URLSearchParams(newSearch)
    const filterValue = params.get(props.column)
    selectedValues.value = new Set(filterValue ? filterValue.split(',') : [])
})

const handleFilterChange = (option: { value: string }) => {
    const newSelectedValues = new Set(selectedValues.value)
    
    if (newSelectedValues.has(option.value)) {
        newSelectedValues.delete(option.value)
    } else {
        newSelectedValues.add(option.value)
    }

    selectedValues.value = newSelectedValues

    const params = new URLSearchParams(window.location.search)
    if (newSelectedValues.size > 0) {
        params.set(props.column, Array.from(newSelectedValues).join(','))
    } else {
        params.delete(props.column)
    }

    router.get(route(props.routeName), Object.fromEntries(params), {
        preserveState: true,
        preserveScroll: true
    })
}

const clearFilters = () => {
    selectedValues.value = new Set()
    const params = new URLSearchParams(window.location.search)
    params.delete(props.column)
    
    router.get(route(props.routeName), Object.fromEntries(params), {
        preserveState: true,
        preserveScroll: true
    })
}
 
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" size="sm" class="h-8 border-dashed">
                <PlusCircle class="mr-2 h-4 w-4" />
                {{ title }}
                <template v-if="selectedValues.size > 0">
                    <Separator orientation="vertical" class="mx-2 h-4" />
                    <Badge variant="secondary" class="rounded-sm px-1 font-normal lg:hidden">
                        {{ selectedValues.size }}
                    </Badge>
                    <div class="hidden space-x-1 lg:flex">
                        <Badge v-if="selectedValues.size > 2" variant="secondary" class="rounded-sm px-1 font-normal">
                            {{ selectedValues.size }} selected
                        </Badge>
                        <template v-else>
                            <Badge v-for="option in options.filter((option) => selectedValues.has(option.value))" 
                                :key="option.value"
                                variant="secondary" 
                                class="rounded-sm px-1 font-normal"
                            >
                                {{ option.label }}
                            </Badge>
                        </template>
                    </div>
                </template>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[200px] p-0" align="start">
            <Command :filter-function="(list: any[], term: string) => 
                list.filter((i: any) => String(i.label).toLowerCase().includes(term.toLowerCase()))">
                <CommandInput :placeholder="title" />
                <CommandList>
                    <CommandEmpty>No results found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem v-for="option in options" 
                            :key="option.value" 
                            :value="option" 
                            @select="() => handleFilterChange(option)"
                        >
                            <div :class="cn(
                                'mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary',
                                selectedValues.has(option.value)
                                    ? 'bg-primary text-primary-foreground'
                                    : 'opacity-50 [&_svg]:invisible',
                            )">
                                <CheckIcon :class="cn('h-4 w-4')" />
                            </div>
                            <component :is="option.icon" v-if="option.icon"
                                class="mr-2 h-4 w-4 text-muted-foreground" />
                            <span>{{ option.label }}</span>
                        </CommandItem>
                    </CommandGroup>

                    <template v-if="selectedValues.size > 0">
                        <CommandSeparator />
                        <CommandGroup>
                            <CommandItem 
                                :value="{ label: 'Clear filters' }" 
                                class="justify-center text-center"
                                @select="clearFilters"
                            >
                                Clear filters
                            </CommandItem>
                        </CommandGroup>
                    </template>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>