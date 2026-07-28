<script setup lang="ts">
import type { Component } from 'vue';

type Props = {
    title: string;
    description?: string;
    icon: Component;
    gradient?: string;
};

withDefaults(defineProps<Props>(), {
    gradient: 'from-cyan-500 to-blue-600',
});
</script>

<template>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl shadow-lg transition-transform duration-300 hover:scale-105" :class="'bg-gradient-to-br ' + gradient">
                <div class="absolute inset-0 rounded-2xl opacity-20 blur-xl" :class="'bg-gradient-to-br ' + gradient" />
                <component :is="icon" class="relative h-7 w-7 text-white" />
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ title }}</h1>
                <p v-if="description" class="mt-0.5 text-sm text-muted-foreground">{{ description }}</p>
            </div>
        </div>
        <slot name="actions" />
    </div>
</template>
