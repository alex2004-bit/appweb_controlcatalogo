<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

interface SkeletonProps {
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<SkeletonProps>(), {
    class: '',
});
</script>

<template>
    <div
        class="animate-pulse rounded-xl bg-gradient-to-r from-muted/60 via-muted/40 to-muted/60 bg-[length:200%_100%"
        :class="props.class"
    />
</template>
