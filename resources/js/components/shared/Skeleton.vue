<script setup lang="ts">
withDefaults(defineProps<{
    class?: string;
}>(), {
    class: '',
});
</script>

<template>
    <div
        class="animate-pulse rounded-xl bg-gradient-to-r from-muted/60 via-muted/40 to-muted/60 bg-[length:200%_100%"
        :class="class"
    />
</template>
