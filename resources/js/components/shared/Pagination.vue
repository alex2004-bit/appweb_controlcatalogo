<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { PaginatedData } from '@/types';

const props = defineProps<{
    pagination: PaginatedData<any>;
}>();

</script>

<template>
    <div v-if="pagination.last_page > 1" class="mt-4 flex items-center justify-between">
        <p class="text-sm text-muted-foreground">
            Mostrando {{ pagination.from ?? 0 }} - {{ pagination.to ?? 0 }} de {{ pagination.total }}
        </p>
        <div class="flex items-center gap-1">
            <template v-for="(link, i) in pagination.links" :key="i">
                <Button
                    v-if="link.label.includes('Previous')"
                    variant="outline"
                    size="sm"
                    :disabled="!link.url"
                    as-child
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
                    :class="{ 'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground': link.active }"
                    as-child
                >
                    <Link v-if="link.url" :href="link.url" preserve-state preserve-scroll v-html="link.label" />
                    <span v-else v-html="link.label" />
                </Button>
            </template>
        </div>
    </div>
</template>
