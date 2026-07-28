<script setup lang="ts">
import { X } from '@lucide/vue';
import { Button } from '@/components/ui/button';

defineProps<{
    open: boolean;
    title?: string;
}>();

const emit = defineEmits<{
    close: [];
}>();
</script>

<template>
    <Teleport to="body">
        <Transition name="slideover">
            <div v-if="open" class="fixed inset-0 z-50 flex justify-end">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="emit('close')" />
                <div class="relative w-full max-w-lg bg-card border-l border-border/50 shadow-2xl overflow-y-auto">
                    <div class="sticky top-0 z-10 flex items-center justify-between border-b border-border/40 bg-card/80 backdrop-blur-sm px-6 py-4">
                        <h2 class="text-base font-semibold text-foreground">{{ title ?? 'Detalles' }}</h2>
                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-xl" @click="emit('close')">
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                    <div class="p-6">
                        <slot />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.slideover-enter-active {
    transition: all 0.3s ease-out;
}
.slideover-leave-active {
    transition: all 0.2s ease-in;
}
.slideover-enter-from > div:last-child {
    transform: translateX(100%);
}
.slideover-leave-to > div:last-child {
    transform: translateX(100%);
}
.slideover-enter-from > div:first-child {
    opacity: 0;
}
.slideover-leave-to > div:first-child {
    opacity: 0;
}
</style>
