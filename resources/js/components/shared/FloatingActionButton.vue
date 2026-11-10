<script setup lang="ts">
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';

type Action = {
    label: string;
    icon: object;
    onClick: () => void;
    color?: string;
};

withDefaults(defineProps<{
    actions?: Action[];
    position?: 'br' | 'bl' | 'tr' | 'tl';
}>(), {
    actions: () => [],
    position: 'br',
});

const open = ref(false);

const positionClasses: Record<string, string> = {
    br: 'bottom-4 right-3 sm:bottom-6 sm:right-6',
    bl: 'bottom-4 left-3 sm:bottom-6 sm:left-6',
    tr: 'top-20 right-3 sm:top-24 sm:right-6',
    tl: 'top-20 left-3 sm:top-24 sm:left-6',
};
</script>

<template>
    <div :class="['fixed z-50 flex flex-col items-end gap-3', positionClasses[position]]">
        <TransitionGroup
            name="fab-item"
            tag="div"
            class="flex flex-col gap-2"
        >
            <div
                v-for="action in open ? actions : []"
                :key="action.label"
                class="flex items-center gap-2"
            >
                <span class="rounded-xl border border-border/50 bg-card px-3 py-1.5 text-xs font-medium text-foreground shadow-lg backdrop-blur-sm whitespace-nowrap">
                    {{ action.label }}
                </span>
                <Button
                    variant="default"
                    size="icon"
                    class="h-11 w-11 rounded-xl shadow-lg transition-all duration-200 hover:scale-105 active:scale-95"
                    :class="action.color ?? 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white'"
                    @click="action.onClick"
                >
                    <component :is="action.icon" class="h-5 w-5" />
                </Button>
            </div>
        </TransitionGroup>

        <Button
            variant="default"
            size="icon"
            class="h-12 w-12 rounded-2xl shadow-xl transition-all duration-300 hover:shadow-2xl active:scale-95 sm:h-14 sm:w-14"
            :class="open
                ? 'bg-gradient-to-r from-red-500 to-rose-600 text-white rotate-45'
                : 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white'"
            @click="open = !open"
        >
            <Plus class="h-6 w-6 transition-transform duration-300" />
        </Button>
    </div>
</template>

<style scoped>
.fab-item-enter-active {
    transition: all 0.2s ease-out;
}
.fab-item-leave-active {
    transition: all 0.15s ease-in;
}
.fab-item-enter-from {
    opacity: 0;
    transform: translateY(10px) scale(0.9);
}
.fab-item-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.9);
}
</style>
