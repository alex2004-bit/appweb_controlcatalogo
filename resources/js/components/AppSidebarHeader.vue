<script setup lang="ts">
import { PanelLeft } from '@lucide/vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header class="sticky top-0 z-30 flex min-h-14 items-center gap-2 border-b border-gray-200 bg-white px-3 py-2 sm:h-16 sm:gap-3 sm:px-6 sm:py-0">
        <SidebarTrigger class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700">
            <PanelLeft class="h-4 w-4" />
        </SidebarTrigger>

        <nav v-if="breadcrumbs.length > 0" class="flex min-w-0 items-center gap-1 overflow-hidden text-sm sm:gap-2">
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <span v-if="index > 0" class="shrink-0 text-gray-300">/</span>
                <span v-if="index === breadcrumbs.length - 1" class="truncate font-medium text-gray-900">
                    {{ item.title }}
                </span>
                <a v-else :href="item.href" class="hidden shrink-0 text-gray-500 transition-colors hover:text-blue-600 sm:inline">
                    {{ item.title }}
                </a>
            </template>
        </nav>

        <div class="ml-auto" />
    </header>
</template>
