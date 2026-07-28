<script setup lang="ts">
import type { Component } from 'vue';

type Stat = {
    label: string;
    value: string | number;
    icon: Component;
    color: string;
    sublabel?: string;
    trend?: 'up' | 'down' | 'neutral';
    trendValue?: string;
};

defineProps<{
    stats: Stat[];
    columns?: 2 | 3 | 4;
}>();
</script>

<template>
    <div :class="[
        'grid gap-4',
        columns === 2 ? 'sm:grid-cols-2' : '',
        columns === 3 ? 'sm:grid-cols-2 lg:grid-cols-3' : '',
        columns === 4 ? 'sm:grid-cols-2 lg:grid-cols-4' : 'sm:grid-cols-2 lg:grid-cols-4',
    ]">
        <div
            v-for="(stat, index) in stats"
            :key="stat.label"
            class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
            :style="{ animationDelay: `${index * 0.05}s` }"
        >
            <div class="absolute inset-0 bg-gradient-to-br opacity-0 transition-opacity duration-300 group-hover:opacity-5" :class="stat.color" />
            <div class="absolute -top-6 -right-6 h-16 w-16 rounded-full opacity-0 transition-all duration-500 group-hover:opacity-10 scale-0 group-hover:scale-100" :class="'bg-gradient-to-br ' + stat.color" />
            <div class="relative">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ stat.label }}</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-foreground">{{ stat.value }}</p>
                        <div v-if="stat.sublabel || stat.trend" class="mt-1.5 flex items-center gap-2">
                            <span v-if="stat.trend" :class="[
                                'inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-semibold',
                                stat.trend === 'up' ? 'bg-emerald-500/10 text-emerald-500' : '',
                                stat.trend === 'down' ? 'bg-red-500/10 text-red-500' : '',
                                stat.trend === 'neutral' ? 'bg-muted text-muted-foreground' : '',
                            ]">
                                <svg v-if="stat.trend === 'up'" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                <svg v-if="stat.trend === 'down'" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                {{ stat.trendValue }}
                            </span>
                            <span v-if="stat.sublabel" class="text-xs text-muted-foreground">{{ stat.sublabel }}</span>
                        </div>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl shadow-sm transition-all duration-300 group-hover:scale-110 group-hover:shadow-md" :class="stat.color">
                        <component :is="stat.icon" class="h-5 w-5 text-white" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
