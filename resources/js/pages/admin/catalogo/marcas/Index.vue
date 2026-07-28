<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Tag, Plus, Pencil, Trash2 } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { ConfirmDeleteDialog, PageTransition, ModuleHeader } from '@/components/shared';

type Marca = {
    id: number;
    nombre: string;
    slug: string;
    logo: string | null;
    descripcion: string | null;
    activo: boolean;
    productos_count: number;
    created_at: string;
};

const props = defineProps<{ marcas: Marca[] }>();

const deleteId = ref<number | null>(null);
const deleteNombre = ref('');
const showDelete = ref(false);

function confirmDelete(marca: Marca) {
    deleteId.value = marca.id;
    deleteNombre.value = marca.nombre;
    showDelete.value = true;
}

function executeDelete() {
    if (!deleteId.value) return;
    router.delete(route('admin.catalogo.marcas.destroy', deleteId.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDelete.value = false;
            deleteId.value = null;
        },
    });
}

function getInitial(nombre: string) {
    return nombre.charAt(0).toUpperCase();
}

const colors = ['from-cyan-500 to-teal-600', 'from-blue-500 to-indigo-600', 'from-amber-500 to-orange-600', 'from-purple-500 to-pink-600', 'from-emerald-500 to-teal-600', 'from-rose-500 to-red-600'];

function getColor(i: number) {
    return colors[i % colors.length];
}
</script>

<template>
    <Head title="Marcas" />
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Marcas" description="Gestiona las marcas del catálogo" :icon="Tag" gradient="from-cyan-500 to-blue-600">
                <template #actions>
                    <Link :href="route('admin.catalogo.marcas.create')">
                        <Button class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 hover:from-cyan-600 hover:to-blue-700 rounded-xl h-10">
                            <Plus class="mr-2 h-4 w-4" /> Nueva Marca
                        </Button>
                    </Link>
                </template>
            </ModuleHeader>

            <div class="overflow-hidden rounded-2xl border border-border/50 bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow class="border-border/50 hover:bg-transparent">
                                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Logo</TableHead>
                                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Nombre</TableHead>
                                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Slug</TableHead>
                                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Productos</TableHead>
                                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Estado</TableHead>
                                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Creado</TableHead>
                                <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(marca, i) in marcas" :key="marca.id" class="border-border/30 transition-colors hover:bg-accent/50">
                                <TableCell>
                                    <div v-if="marca.logo" class="h-10 w-10 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-muted to-muted/50 shadow-sm">
                                        <img :src="marca.logo" :alt="marca.nombre" class="h-full w-full object-cover" />
                                    </div>
                                    <div v-else class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-sm bg-gradient-to-br" :class="getColor(i)">
                                        <span class="text-sm font-bold text-white">{{ getInitial(marca.nombre) }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="font-medium text-foreground">{{ marca.nombre }}</TableCell>
                                <TableCell class="text-muted-foreground font-mono text-xs">{{ marca.slug }}</TableCell>
                                <TableCell>
                                    <Badge variant="secondary" class="rounded-full text-xs shadow-none">{{ marca.productos_count }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="marca.activo ? 'bg-emerald-500/15 text-emerald-500 border-emerald-500/20 border' : 'bg-slate-500/15 text-slate-400 border-slate-500/20 border'" class="shadow-none">
                                        {{ marca.activo ? 'Activo' : 'Inactivo' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-muted-foreground text-xs">{{ new Date(marca.created_at).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' }) }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('admin.catalogo.marcas.edit', marca.id)">
                                            <Button variant="outline" size="sm" class="rounded-xl border-border bg-card"><Pencil class="h-4 w-4" /></Button>
                                        </Link>
                                        <Button variant="destructive" size="sm" class="rounded-xl" @click="confirmDelete(marca)"><Trash2 class="h-4 w-4" /></Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!marcas.length">
                                <TableCell colspan="7" class="py-16 text-center text-muted-foreground">
                                    <div class="flex flex-col items-center gap-2">
                                        <Tag class="h-10 w-10 text-muted-foreground/30" />
                                        <p class="text-sm font-medium">No hay marcas registradas</p>
                                        <p class="text-xs">Crea tu primera marca para comenzar.</p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

            <ConfirmDeleteDialog
                :open="showDelete"
                title="Eliminar Marca"
                :description="`¿Estás seguro de eliminar &quot;${deleteNombre}&quot;? Esta acción no se puede deshacer.`"
                @confirm="executeDelete"
                @update:open="showDelete = false"
            />
        </div>
    </PageTransition>
</template>
