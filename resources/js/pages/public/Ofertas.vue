<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Percent, Tag, PackageOpen } from '@lucide/vue';
import { computed } from 'vue';
import Pagination from '@/components/shared/Pagination.vue';
import type { PaginatedData } from '@/types';

type ProductoOferta = {
    id: number;
    nombre: string;
    slug: string;
    descripcion: string | null;
    precio: string;
    precio_anterior: string | null;
    imagen: string | null;
    categoria: { nombre: string; slug: string } | null;
    marca: { nombre: string; slug: string } | null;
};

const props = defineProps<{
    productos: PaginatedData<ProductoOferta>;
}>();

const defaultImg = 'https://images.unsplash.com/photo-1557324232-b8917d3c3dcb?q=80&w=1974&auto=format&fit=crop';

const ofertas = computed(() => props.productos.data.filter(p => p.precio_anterior !== null && parseFloat(p.precio_anterior) > parseFloat(p.precio)));

function calcularDescuento(precio: string, anterior: string | null): number {
    if (!anterior) return 0;
    const p = parseFloat(precio);
    const a = parseFloat(anterior);
    if (a <= 0 || p <= 0) return 0;
    return Math.round((1 - p / a) * 100);
}
</script>

<template>
    <Head title="Ofertas - ControlCatálogo" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header Section -->
        <section class="bg-white border-b border-gray-200 py-10">
            <div class="mx-auto w-full max-w-[1400px] px-6">
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
                    <Link href="/" class="hover:text-blue-600 transition">Inicio</Link>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">Ofertas</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Ofertas</h1>
                <p class="mt-1 text-sm text-gray-500">Productos con descuentos especiales</p>
            </div>
        </section>

        <!-- Products Section -->
        <section class="py-10">
            <div class="mx-auto w-full max-w-[1400px] px-6">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-gray-500">
                        <span class="text-gray-900 font-semibold">{{ ofertas.length }}</span>
                        producto{{ ofertas.length !== 1 ? 's' : '' }} en oferta
                    </p>
                </div>

                <!-- Empty state -->
                <div v-if="ofertas.length === 0" class="text-center py-24">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-white border border-gray-200 mb-5">
                        <PackageOpen class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Sin ofertas por ahora</h3>
                    <p class="mt-1.5 text-sm text-gray-500 max-w-sm mx-auto">
                        Actualmente no hay productos con descuento. Vuelve a consultar pronto.
                    </p>
                    <Link
                        href="/productos"
                        class="mt-6 inline-flex items-center gap-2 rounded-full bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-500/40"
                    >
                        <Tag class="h-4 w-4" />
                        Ver catálogo completo
                    </Link>
                </div>

                <!-- Grid -->
                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="p in ofertas"
                        :key="p.id"
                        class="group bg-white rounded-xl border border-gray-200 overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1"
                    >
                        <Link :href="'/productos/' + p.slug" class="block relative aspect-[4/3] overflow-hidden bg-gray-100">
                            <img
                                :src="p.imagen || defaultImg"
                                :alt="p.nombre"
                                class="absolute inset-0 w-full h-full object-cover transition-all duration-500 ease-out group-hover:scale-110"
                                loading="lazy"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

                            <!-- Oferta badge -->
                            <span class="absolute top-3 left-3 inline-flex items-center gap-1 rounded-full bg-red-600 px-2.5 py-1 text-[10px] font-bold text-white shadow-md">
                                <Percent class="h-3 w-3" />
                                OFERTA
                            </span>

                            <!-- Category badge -->
                            <span
                                v-if="p.categoria"
                                class="absolute top-3 right-3 inline-flex items-center rounded-full bg-white/90 backdrop-blur-sm px-2.5 py-1 text-[10px] font-medium text-blue-600 shadow-sm"
                            >
                                {{ p.categoria.nombre }}
                            </span>
                        </Link>

                        <div class="p-5">
                            <Link :href="'/productos/' + p.slug" class="block">
                                <h3 class="text-sm font-semibold text-gray-900 leading-snug group-hover:text-blue-600 transition-colors line-clamp-2">
                                    {{ p.nombre }}
                                </h3>
                                <p class="mt-1.5 text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                    {{ p.descripcion || 'Sin descripción disponible' }}
                                </p>
                            </Link>

                            <div class="mt-4 flex items-end justify-between gap-2">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-400 line-through" v-if="p.precio_anterior">
                                        ${{ parseFloat(p.precio_anterior).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg font-bold text-blue-600">
                                            ${{ parseFloat(p.precio).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                                        </span>
                                        <span
                                            v-if="p.precio_anterior"
                                            class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-bold text-green-700"
                                        >
                                            -{{ calcularDescuento(p.precio, p.precio_anterior) }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <Pagination :pagination="productos" />
            </div>
        </section>
    </div>
</template>
