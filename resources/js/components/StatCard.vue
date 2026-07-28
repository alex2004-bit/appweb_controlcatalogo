<script setup lang="ts">
import type { Component } from 'vue';

type Props = {
    label: string;
    value: string | number;
    sublabel: string;
    icon: Component;
    color: string;
};

defineProps<Props>();
</script>

<template>
    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-5">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-zinc-400">{{ label }}</p>
            <div
                class="flex h-9 w-9 items-center justify-center rounded-lg"
                :class="color"
            >
                <component :is="icon" class="h-4 w-4" />
            </div>
        </div>
        <p class="mt-3 text-2xl font-bold">{{ value }}</p>
        <p class="mt-1 text-xs text-zinc-600">{{ sublabel }}</p>
    </div>
</template>
