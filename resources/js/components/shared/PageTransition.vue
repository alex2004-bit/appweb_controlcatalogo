<script setup lang="ts">
import { Transition } from 'vue';

withDefaults(defineProps<{
    appear?: boolean;
}>(), {
    appear: true,
});
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-400 ease-out"
        enter-from-class="opacity-0 translate-y-6 scale-[0.98]"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-4 scale-[0.98]"
        :appear="appear"
        mode="out-in"
    >
        <slot />
    </Transition>
</template>
