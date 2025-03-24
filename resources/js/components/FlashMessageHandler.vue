<template>
  <!-- This component doesn't render anything, it just processes flash messages -->
</template>

<script setup lang="ts">
// @ts-ignore
import { useToast } from '@/components/ui/toast';
import { usePage } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';

const { toast } = useToast();
const page = usePage();

// Process flash messages and convert them to toasts
const processFlashMessages = () => {
  const flash:any = page.props.flash || {};
  
  if (flash.success) {
    toast({
      title: 'Sucesso',
      description: flash.success, 
      duration: 5000
    });
  }
  
  if (flash.error) {
    toast({
      title: 'Erro',
      description: flash.error,
      variant: 'destructive',
      duration: 8000
    });
  }
  
  if (flash.info) {
    toast({
      title: 'Informação',
      description: flash.info, 
      duration: 5000
    });
  }
  
  if (flash.warning) {
    toast({
      title: 'Alerta',
      description: flash.warning, 
      duration: 5000
    });
  }

  if(page.props.errors) {
    for (const [key, value] of Object.entries(page.props.errors)) {
      console.log(key, value);
      toast({
        title: 'Erro',
        description: value,
        variant: 'destructive',
        duration: 8000
      });
    }
  }
};

// Process messages on component mount
onMounted(() => {
  processFlashMessages();
});

// Watch for changes to flash messages
watch(() => page.props.flash, () => {
  processFlashMessages();
}, { deep: true });
</script>
