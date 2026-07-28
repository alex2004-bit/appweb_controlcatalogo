<script setup lang="ts">
import { Inbox, Plus } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { Component } from 'vue';

type Action = {
    label: string;
    href?: string;
    onClick?: () => void;
    icon?: Component;
};

defineProps<{
    title?: string;
    description?: string;
    icon?: Component;
    gradient?: string;
    action?: Action;
    compact?: boolean;
}>();
</script>

<template>
    <div :class="['flex flex-col items-center justify-center text-center', compact ? 'py-10' : 'py-20']">
        <div v-if="gradient" class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br mb-5 shadow-lg" :class="gradient ?? 'from-cyan-500/10 to-blue-600/10'">
            <component :is="icon ?? Inbox" class="h-10 w-10" :class="gradient ? 'text-white' : 'text-muted-foreground/40'" />
        </div>
        <component v-else :is="icon ?? Inbox" class="mb-4 h-16 w-16 text-muted-foreground/30" />
        <h3 class="text-lg font-semibold text-foreground">{{ title ?? 'Sin registros' }}</h3>
        <p class="mt-1 max-w-sm text-sm text-muted-foreground">
            {{ description ?? 'No hay datos disponibles para mostrar.' }}
        </p>
        <div v-if="action" class="mt-5">
            <Link v-if="action.href" :href="action.href">
                <Button class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 rounded-xl">
                    <component :is="action.icon ?? Plus" class="mr-2 h-4 w-4" />
                    {{ action.label }}
                </Button>
            </Link>
            <Button v-else class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 rounded-xl" @click="action.onClick">
                <component :is="action.icon ?? Plus" class="mr-2 h-4 w-4" />
                {{ action.label }}
            </Button>
        </div>
        <slot />
    </div>
</template>
