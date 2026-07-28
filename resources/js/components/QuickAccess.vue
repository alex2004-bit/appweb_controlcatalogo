<script setup lang="ts">
import { ArrowRight } from '@lucide/vue';
import type { Component } from 'vue';

type Props = {
    href: string;
    icon: Component;
    title: string;
    description: string;
    bgColor: string;
    borderHover: string;
    iconHover: string;
};

defineProps<Props>();
</script>

<template>
    <a
        :href="href"
        class="group flex items-center justify-between rounded-xl border border-zinc-800 bg-zinc-900/50 p-5 transition hover:bg-zinc-900"
        :class="borderHover"
    >
        <div class="flex items-center gap-3">
            <div
                class="flex h-10 w-10 items-center justify-center rounded-lg"
                :class="bgColor"
            >
                <component :is="icon" class="h-5 w-5" />
            </div>
            <div>
                <p class="text-sm font-medium">{{ title }}</p>
                <p class="text-xs text-zinc-500">{{ description }}</p>
            </div>
        </div>
        <ArrowRight
            class="h-4 w-4 text-zinc-600 transition group-hover:translate-x-0.5"
            :class="iconHover"
        />
    </a>
</template>
