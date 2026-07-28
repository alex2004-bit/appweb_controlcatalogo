<script setup lang="ts">
import Skeleton from './Skeleton.vue';
</script>

<template>
    <div class="rounded-2xl border border-border/50 bg-card p-5 shadow-sm">
        <Skeleton class="h-40 w-full rounded-xl mb-4" />
        <Skeleton class="h-5 w-3/4 mb-2" />
        <Skeleton class="h-4 w-1/2 mb-3" />
        <div class="flex items-center justify-between">
            <Skeleton class="h-4 w-1/4" />
            <Skeleton class="h-8 w-8 rounded-lg" />
        </div>
    </div>
</template>
