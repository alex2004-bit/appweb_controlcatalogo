<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div v-if="title" class="border-b border-gray-100 px-4 py-3 sm:px-6 sm:py-4">
            <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
            <p v-if="description" class="mt-0.5 text-sm text-gray-500">{{ description }}</p>
        </div>
        <div class="space-y-4 px-4 py-4 sm:space-y-5 sm:px-6 sm:py-5">
            <slot />
        </div>
    </div>
</template>
