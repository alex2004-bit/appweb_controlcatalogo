<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { PaginatedData } from '@/types';
import EmptyState from './EmptyState.vue';

type Column = {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
};

defineProps<{
    columns: Column[];
    data: PaginatedData<any>;
    loading?: boolean;
}>();
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <Table>
                <TableHeader>
                    <TableRow class="bg-gray-50 border-b border-gray-200">
                        <TableHead
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'text-sm font-semibold uppercase tracking-wider text-gray-700 py-3',
                                col.class,
                            ]"
                        >
                            {{ col.label }}
                        </TableHead>
                        <TableHead v-if="$slots.actions" class="text-right text-sm font-semibold uppercase tracking-wider text-gray-700 py-3">Acciones</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="row in data.data" :key="row.id" class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                        <TableCell
                            v-for="col in columns"
                            :key="col.key"
                            :class="['text-sm text-gray-900', col.class]"
                        >
                            <slot :name="`cell:${col.key}`" :row="row" :value="row[col.key]">
                                {{ row[col.key] ?? '—' }}
                            </slot>
                        </TableCell>
                        <TableCell v-if="$slots.actions" class="text-right text-sm text-gray-900">
                            <slot name="actions" :row="row" />
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="!data.data.length">
                        <TableCell :colspan="columns.length + ($slots.actions ? 1 : 0)" class="p-0">
                            <slot name="empty">
                                <EmptyState />
                            </slot>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
