<script setup lang="ts">
import Skeleton from './Skeleton.vue';

withDefaults(defineProps<{
    rows?: number;
    cols?: number;
}>(), {
    rows: 5,
    cols: 4,
});
</script>

<template>
    <div class="space-y-3">
        <div class="flex gap-4 pb-2 border-b border-border/30">
            <Skeleton v-for="c in cols" :key="c" class="h-4 flex-1" />
        </div>
        <div v-for="r in rows" :key="r" class="flex gap-4 py-2">
            <Skeleton v-for="c in cols" :key="c" class="h-5 flex-1" />
        </div>
    </div>
</template>
