<script setup lang="ts">
import { AlertTriangle } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

defineProps<{
    open: boolean;
    title?: string;
    description?: string;
    loading?: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
}>();

</script>

<template>
    <Dialog :open="open" @update:open="(v) => emit('update:open', v)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-destructive/10">
                        <AlertTriangle class="h-5 w-5 text-destructive" />
                    </div>
                    <div>
                        <DialogTitle>{{ title ?? 'Confirmar eliminación' }}</DialogTitle>
                        <DialogDescription>
                            {{ description ?? '¿Estás seguro? Esta acción no se puede deshacer.' }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>
            <DialogFooter class="gap-2 sm:gap-0">
                <Button variant="outline" @click="emit('update:open', false)">
                    Cancelar
                </Button>
                <Button variant="destructive" :disabled="loading" @click="emit('confirm')">
                    {{ loading ? 'Eliminando...' : 'Eliminar' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
