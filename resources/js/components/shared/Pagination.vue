<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { PaginatedData } from '@/types';

defineProps<{
    pagination: PaginatedData<any>;
}>();
</script>

<template>
    <div v-if="pagination.last_page > 1" class="flex flex-col items-center justify-between gap-2 border-t border-gray-200 bg-white px-3 py-3 sm:flex-row sm:px-6">
        <p class="text-xs text-gray-600 sm:text-sm">
            Mostrando {{ pagination.from ?? 0 }} - {{ pagination.to ?? 0 }} de {{ pagination.total }}
        </p>
        <div class="flex max-w-full items-center gap-0.5 sm:gap-1">
            <template v-for="(link, i) in pagination.links" :key="i">
                <Button
                    v-if="link.label.includes('Previous')"
                    variant="outline"
                    size="sm"
                    :disabled="!link.url"
                    as-child
                    class="h-8 w-8 rounded-lg border-gray-300 p-0"
                >
                    <Link v-if="link.url" :href="link.url" preserve-state preserve-scroll>
                        <ChevronLeft class="h-4 w-4" />
                    </Link>
                    <span v-else><ChevronLeft class="h-4 w-4" /></span>
                </Button>
                <Button
                    v-else-if="link.label.includes('Next')"
                    variant="outline"
                    size="sm"
                    :disabled="!link.url"
                    as-child
                    class="h-8 w-8 rounded-lg border-gray-300 p-0"
                >
                    <Link v-if="link.url" :href="link.url" preserve-state preserve-scroll>
                        <ChevronRight class="h-4 w-4" />
                    </Link>
                    <span v-else><ChevronRight class="h-4 w-4" /></span>
                </Button>
                <Button
                    v-else
                    variant="ghost"
                    size="sm"
                    as-child
                    :class="[
                        'hidden h-8 min-w-[32px] rounded-lg px-2 text-sm sm:inline-flex',
                        link.active
                            ? 'bg-blue-600 text-white hover:bg-blue-700'
                            : 'text-gray-700 hover:bg-gray-100',
                    ]"
                >
                    <Link v-if="link.url" :href="link.url" preserve-state preserve-scroll>{{ link.label }}</Link>
                    <span v-else>{{ link.label }}</span>
                </Button>
            </template>
        </div>
    </div>
</template>
