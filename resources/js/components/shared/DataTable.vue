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

const props = defineProps<{
    columns: Column[];
    data: PaginatedData<any>;
    loading?: boolean;
}>();

</script>

<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead
                    v-for="col in columns"
                    :key="col.key"
                    :class="col.class"
                >
                    {{ col.label }}
                </TableHead>
                <TableHead v-if="$slots.actions" class="text-right">Acciones</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="row in data.data" :key="row.id">
                <TableCell
                    v-for="col in columns"
                    :key="col.key"
                    :class="col.class"
                >
                    <slot :name="`cell:${col.key}`" :row="row" :value="row[col.key]">
                        {{ row[col.key] ?? '—' }}
                    </slot>
                </TableCell>
                <TableCell v-if="$slots.actions" class="text-right">
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
</template>
