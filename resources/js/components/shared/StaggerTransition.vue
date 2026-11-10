<script setup lang="ts">
withDefaults(defineProps<{
    tag?: string;
    move?: boolean;
}>(), {
    tag: 'div',
    move: true,
});
</script>

<template>
    <TransitionGroup
        :tag="tag"
        :css="true"
        :class="move ? 'relative' : ''"
        enter-active-class="transition-all duration-400 ease-out"
        enter-from-class="opacity-0 scale-90 translate-y-4"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in absolute"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-90 translate-y-4"
        move-class="transition-all duration-400 ease-out"
    >
        <slot />
    </TransitionGroup>
</template>
