<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="space-y-2">
        <div>
            <h3 class="text-lg font-semibold">{{ title }}</h3>
            <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
        </div>
        <div class="rounded-lg border p-6">
            <slot />
        </div>
    </div>
</template>
