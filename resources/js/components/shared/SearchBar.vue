<script setup lang="ts">
import { Search } from '@lucide/vue';
import { useDebounceFn } from '@vueuse/core';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    modelValue?: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    search: [value: string];
}>();

const localValue = ref(props.modelValue ?? '');

watch(() => props.modelValue, (v) => {
    localValue.value = v ?? '';
});

const debouncedSearch = useDebounceFn((value: string) => {
    emit('search', value);
}, 300);

function onInput(e: Event) {
    const value = (e.target as HTMLInputElement).value;
    localValue.value = value;
    emit('update:modelValue', value);
    debouncedSearch(value);
}
</script>

<template>
    <div class="relative">
        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-400" />
        <Input
            :placeholder="placeholder ?? 'Buscar...'"
            class="pl-8 w-64"
            :value="localValue"
            @input="onInput"
        />
    </div>
</template>
