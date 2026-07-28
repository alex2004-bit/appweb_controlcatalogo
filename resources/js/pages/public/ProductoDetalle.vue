<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Package, ShoppingCart, MessageCircle, ChevronLeft, Check, Clock, DollarSign } from '@lucide/vue';
import { computed, ref } from 'vue';

const props = defineProps<{
    producto: {
        id: number;
        nombre: string;
        slug: string;
        descripcion: string | null;
        especificaciones: Record<string, string> | null;
        sku: string | null;
        precio: string;
        imagen: string | null;
        categoria: { nombre: string; slug: string } | null;
    };
    relacionados: {
        id: number;
        nombre: string;
        slug: string;
        precio: string;
        imagen: string | null;
        categoria: { nombre: string } | null;
    }[];
}>();

const whatsappNumber = '521XXXXXXXXXX';
const addedToCart = ref(false);
const defaultImg = 'https://images.unsplash.com/photo-1557324232-b8917d3c3dcb?q=80&w=1974&auto=format&fit=crop';

function addToCart() {
    addedToCart.value = true;
    setTimeout(() => { addedToCart.value = false; }, 2000);
}

const especs = computed(() => {
    if (!props.producto.especificaciones) return [];
    return Object.entries(props.producto.especificaciones);
});
</script>

<template>
    <Head :title="`${producto.nombre} - ControlCatálogo`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Breadcrumb -->
        <section class="bg-white border-b border-gray-200 py-4">
            <div class="mx-auto w-full max-w-[1400px] px-6">
                <div class="flex items-center gap-2 text-xs text-gray-400">
                    <Link href="/" class="hover:text-blue-600 transition">Inicio</Link>
                    <span>/</span>
                    <Link href="/productos" class="hover:text-blue-600 transition">Productos</Link>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">{{ producto.nombre }}</span>
                </div>
            </div>
        </section>

        <!-- Product Detail -->
        <section class="py-10">
            <div class="mx-auto w-full max-w-[1400px] px-6">
                <div class="grid gap-10 lg:grid-cols-2">
                    <!-- Image -->
                    <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white">
                        <div class="aspect-square">
                            <img
                                :src="producto.imagen || defaultImg"
                                :alt="producto.nombre"
                                class="absolute inset-0 w-full h-full object-cover"
                            />
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex flex-col">
                        <p v-if="producto.categoria" class="text-xs font-semibold text-blue-600 uppercase tracking-wider">{{ producto.categoria.nombre }}</p>
                        <h1 class="mt-2 text-2xl font-bold lg:text-3xl text-gray-900">{{ producto.nombre }}</h1>

                        <p class="mt-4 text-sm text-gray-500 leading-relaxed">{{ producto.descripcion }}</p>

                        <div class="mt-6 flex items-baseline gap-2">
                            <span class="text-3xl font-bold text-gray-900">${{ parseFloat(producto.precio).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</span>
                        </div>

                        <div v-if="producto.sku" class="mt-2 text-xs text-gray-400">
                            SKU: {{ producto.sku }}
                        </div>

                        <div class="mt-8 flex flex-col sm:flex-row gap-3">
                            <button
                                @click="addToCart"
                                class="inline-flex items-center justify-center gap-2 rounded-full bg-blue-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-500/25 transition-all hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-500/40"
                            >
                                <Check v-if="addedToCart" class="h-4 w-4" />
                                <ShoppingCart v-else class="h-4 w-4" />
                                {{ addedToCart ? 'Agregado' : 'Agregar a cotización' }}
                            </button>
                            <a
                                :href="`https://wa.me/${whatsappNumber}?text=Hola, me interesa el producto: ${producto.nombre} ($${parseFloat(producto.precio).toLocaleString('es-MX', { minimumFractionDigits: 2 })})`"
                                target="_blank"
                                class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-gray-300 px-8 py-3.5 text-sm font-bold text-gray-700 transition hover:border-emerald-500 hover:text-emerald-600"
                            >
                                <MessageCircle class="h-4 w-4" />
                                Cotizar por WhatsApp
                            </a>
                        </div>

                        <!-- Especificaciones -->
                        <div v-if="especs.length > 0" class="mt-10">
                            <h3 class="text-sm font-bold text-gray-900 mb-4">Especificaciones</h3>
                            <div class="space-y-2">
                                <div
                                    v-for="[key, value] in especs"
                                    :key="key"
                                    class="flex items-center justify-between rounded-lg border border-gray-100 bg-gray-50 px-4 py-2.5"
                                >
                                    <span class="text-xs text-gray-500 capitalize">{{ key.replace('_', ' ') }}</span>
                                    <span class="text-sm text-gray-900 font-medium">{{ value }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Products -->
        <section v-if="relacionados.length > 0" class="bg-white border-t border-gray-200 py-14">
            <div class="mx-auto w-full max-w-[1400px] px-6">
                <h2 class="text-xl font-bold text-gray-900 mb-8">Productos Relacionados</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <Link
                        v-for="r in relacionados"
                        :key="r.id"
                        :href="`/productos/${r.slug}`"
                        class="group bg-white rounded-xl border border-gray-200 p-4 transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1"
                    >
                        <div class="aspect-video rounded-lg overflow-hidden mb-3 relative bg-gray-100">
                            <img
                                :src="r.imagen || defaultImg"
                                :alt="r.nombre"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                loading="lazy"
                            />
                        </div>
                        <p v-if="r.categoria" class="text-[10px] font-semibold text-blue-600 uppercase tracking-wider">{{ r.categoria.nombre }}</p>
                        <h3 class="mt-1 text-sm font-semibold text-gray-900 leading-tight">{{ r.nombre }}</h3>
                        <p class="mt-2 text-base font-bold text-gray-900">${{ parseFloat(r.precio).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</p>
                    </Link>
                </div>
            </div>
        </section>
    </div>
</template>
