<script setup lang="ts">
import type { Component } from 'vue';

type Stat = {
    label: string;
    value: string | number;
    icon: Component;
    color: string;
    sublabel?: string;
};

defineProps<{
    stats: Stat[];
    columns?: 2 | 3 | 4;
}>();
</script>

<template>
    <div :class="[
        'grid grid-cols-1 gap-3 sm:gap-5',
        columns === 2 ? 'sm:grid-cols-2' : '',
        columns === 3 ? 'sm:grid-cols-2 lg:grid-cols-3' : '',
        columns === 4 ? 'sm:grid-cols-2 lg:grid-cols-4' : 'sm:grid-cols-2 lg:grid-cols-4',
    ]">
        <div
            v-for="stat in stats"
            :key="stat.label"
            class="min-w-0 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md sm:p-5"
        >
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-600 sm:text-sm">{{ stat.label }}</p>
                    <p class="mt-1 truncate text-xl font-bold text-gray-900 sm:text-2xl">{{ stat.value }}</p>
                    <p v-if="stat.sublabel" class="mt-1 text-sm text-gray-500">{{ stat.sublabel }}</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg shadow-sm" :class="stat.color">
                    <component :is="stat.icon" class="h-5 w-5 text-white" />
                </div>
            </div>
        </div>
    </div>
</template>
