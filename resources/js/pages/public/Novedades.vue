<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Clock, Eye } from '@lucide/vue';
import Pagination from '@/components/shared/Pagination.vue';
import { useMoney } from '@/composables/useMoney';
import { DEFAULT_PRODUCT_IMAGE as defaultImg } from '@/constants/images';

defineProps<{
    productos: {
        data: {
            id: number;
            nombre: string;
            slug: string;
            descripcion: string | null;
            precio: string;
            precio_anterior: string | null;
            imagen: string | null;
            categoria: { nombre: string; slug: string } | null;
            marca: { nombre: string; slug: string } | null;
            created_at: string;
        }[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: { url: string | null; label: string; active: boolean }[];
    };
}>();

function daysAgo(date: string): string {
    const now = new Date();
    const created = new Date(date);
    const diffMs = now.getTime() - created.getTime();
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (diffDays === 0) {
return 'Hoy';
}

    if (diffDays === 1) {
return 'Ayer';
}

    return `Agregado hace ${diffDays} días`;
}

const money = useMoney();
</script>

<template>
    <Head title="Novedades" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header Section -->
        <section class="bg-white border-b border-gray-200 py-10">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
                    <Link href="/" class="hover:text-blue-600 transition">Inicio</Link>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">Novedades</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Novedades</h1>
                <p class="mt-1 text-sm text-gray-500">Los productos más recientes de nuestro catálogo</p>
            </div>
        </section>

        <!-- Products Section -->
        <section class="py-10">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
                <!-- Counter -->
                <div class="flex items-center mb-6">
                    <p class="text-sm text-gray-500">
                        <span class="text-gray-900 font-semibold">{{ productos.total }}</span>
                        producto{{ productos.total !== 1 ? 's' : '' }} recién agregado{{ productos.total !== 1 ? 's' : '' }}
                    </p>
                </div>

                <!-- Empty state -->
                <div v-if="productos.data.length === 0" class="text-center py-24">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-white border border-gray-200 mb-5">
                        <Clock class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Sin novedades</h3>
                    <p class="mt-1.5 text-sm text-gray-500 max-w-sm mx-auto">
                        Aún no hay productos nuevos en nuestro catálogo. Vuelve pronto para descubrir las últimas novedades.
                    </p>
                    <Link
                        href="/productos"
                        class="mt-6 inline-flex items-center gap-2 rounded-full bg-blue-600 px-4 sm:px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-500/40"
                    >
                        Ver catálogo completo
                    </Link>
                </div>

                <!-- Grid -->
                <div v-else class="grid grid-cols-1 gap-4 min-[375px]:grid-cols-2 sm:gap-6 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    <div
                        v-for="p in productos.data"
                        :key="p.id"
                        class="group bg-white rounded-xl border border-gray-200 overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1"
                    >
                        <Link :href="'/productos/' + p.slug" class="block relative aspect-[4/3] overflow-hidden bg-gray-100">
                            <img
                                :src="p.imagen || defaultImg"
                                :alt="p.nombre"
                                class="absolute inset-0 w-full h-full object-contain transition-all duration-500 ease-out group-hover:scale-110"
                                loading="lazy"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

                            <!-- NUEVO Badge -->
                            <span class="absolute top-3 left-3 inline-flex items-center rounded-full bg-gradient-to-r from-red-500 to-orange-500 px-2.5 py-1 text-[10px] font-bold text-white shadow-md">
                                NUEVO
                            </span>

                            <span
                                v-if="p.categoria"
                                class="absolute top-3 right-3 inline-flex items-center rounded-full bg-white/90 backdrop-blur-sm px-2.5 py-1 text-[10px] font-medium text-blue-600 shadow-sm"
                            >
                                {{ p.categoria.nombre }}
                            </span>

                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/90 backdrop-blur-sm px-4 py-2 text-xs font-semibold text-gray-900 shadow-lg">
                                    <Eye class="h-3.5 w-3.5" />
                                    Ver detalle
                                </span>
                            </div>
                        </Link>

                        <div class="p-3 sm:p-5">
                            <Link :href="'/productos/' + p.slug" class="block">
                                <h3 class="text-sm font-semibold text-gray-900 leading-snug group-hover:text-blue-600 transition-colors line-clamp-2">
                                    {{ p.nombre }}
                                </h3>
                                <p class="mt-1 text-xs text-gray-400">
                                    {{ daysAgo(p.created_at) }}
                                </p>
                                <p class="mt-1.5 text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                    {{ p.descripcion || 'Sin descripción disponible' }}
                                </p>
                            </Link>

                            <div class="mt-4 flex items-center gap-2">
                                <span class="text-lg font-bold text-gray-900">
                                    {{ money.format(p.precio) }}
                                </span>
                                <span
                                    v-if="p.precio_anterior"
                                    class="text-sm text-gray-400 line-through"
                                >
                                    {{ money.format(p.precio_anterior) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pagination -->
        <section v-if="productos.last_page > 1" class="pb-10">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
                <Pagination :pagination="productos" />
            </div>
        </section>
    </div>
</template>
