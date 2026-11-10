<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Package, Users, ShoppingCart, FileText } from '@lucide/vue';
import { computed } from 'vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { FloatingActionButton } from '@/components/shared';
import { Toaster } from '@/components/ui/sonner';
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
        color: 'bg-blue-600 text-white',
    },
    {
        label: 'Nuevo Cliente',
        icon: Users,
        onClick: () => teamSlug.value && router.visit(route('admin.clientes.create', teamSlug.value)),
        color: 'bg-emerald-600 text-white',
    },
    {
        label: 'Nuevo Pedido',
        icon: ShoppingCart,
        onClick: () => teamSlug.value && router.visit(route('admin.pedidos.create', teamSlug.value)),
        color: 'bg-cyan-600 text-white',
    },
    {
        label: 'Nueva Cotización',
        icon: FileText,
        onClick: () => teamSlug.value && router.visit(route('admin.cotizaciones.create', teamSlug.value)),
        color: 'bg-purple-600 text-white',
    },
]);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <main class="min-w-0 flex-1 p-4 pt-0 sm:p-6 sm:pt-0">
                <slot />
            </main>
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
