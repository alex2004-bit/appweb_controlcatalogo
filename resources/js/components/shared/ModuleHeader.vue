<script setup lang="ts">
import type { Component } from 'vue';

type Props = {
    title: string;
    description?: string;
    icon?: Component;
};

defineProps<Props>();
</script>

<template>
    <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <div class="flex min-w-0 items-center gap-3">
            <div v-if="icon" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                <component :is="icon" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <h1 class="text-lg font-bold leading-tight text-gray-900 [overflow-wrap:anywhere] sm:text-xl">{{ title }}</h1>
                <p v-if="description" class="mt-1 text-xs text-gray-500 sm:text-sm">{{ description }}</p>
            </div>
        </div>
        <div v-if="$slots.actions" class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:justify-end">
            <slot name="actions" />
        </div>
    </div>
</template>
