<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Package, Users, ShoppingCart, FileText, Plus } from '@lucide/vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import { FloatingActionButton } from '@/components/shared';
import { usePage, useRemember } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const fabActions = computed(() => [
    {
        label: 'Nuevo Producto',
        icon: Package,
        onClick: () => teamSlug.value && router.visit(route('admin.catalogo.productos.create', teamSlug.value)),
        color: 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white',
    },
    {
        label: 'Nuevo Cliente',
        icon: Users,
        onClick: () => teamSlug.value && router.visit(route('admin.clientes.create', teamSlug.value)),
        color: 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white',
    },
    {
        label: 'Nuevo Pedido',
        icon: ShoppingCart,
        onClick: () => teamSlug.value && router.visit(route('admin.pedidos.create', teamSlug.value)),
        color: 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white',
    },
    {
        label: 'Nueva Cotización',
        icon: FileText,
        onClick: () => teamSlug.value && router.visit(route('admin.cotizaciones.create', teamSlug.value)),
        color: 'bg-gradient-to-r from-purple-500 to-pink-600 text-white',
    },
]);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <FloatingActionButton :actions="fabActions" />
        <Toaster
            position="top-right"
            :expand="true"
            :rich-colors="true"
            :close-button="true"
            :duration="4000"
        />
    </AppShell>
</template>
