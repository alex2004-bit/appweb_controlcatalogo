<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, X, Eye, ChevronDown, ArrowUpDown, SlidersHorizontal, RotateCcw, PackageX, Heart } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import Pagination from '@/components/shared/Pagination.vue';
import { useFavorites } from '@/composables/useFavorites';
import { useMoney } from '@/composables/useMoney';
import { DEFAULT_PRODUCT_IMAGE as defaultImg } from '@/constants/images';

const props = defineProps<{
    productos: {
        data: {
            id: number;
            nombre: string;
            slug: string;
            descripcion: string | null;
            precio: string;
            precio_anterior: string | null;
            imagen: string | null;
            stock: number | null;
            categoria: { nombre: string; slug: string } | null;
            marca: { id: number; nombre: string; slug: string } | null;
        }[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    categorias: { id: number; nombre: string; slug: string; parent: { nombre: string } | null; productos_count: number }[];
    marcas: { id: number; nombre: string; slug: string; logo: string | null; productos_count: number }[];
    filters: {
        search?: string;
        categoria?: string;
        marca?: string;
        precio_min?: string;
        precio_max?: string;
        sort?: string;
    };
}>();

const searchInput = ref(props.filters?.search ?? '');
const selectedCategoria = ref(props.filters?.categoria ?? '');
const selectedMarca = ref(props.filters?.marca ?? '');
const selectedSort = ref(props.filters?.sort ?? 'latest');
const precioMin = ref(props.filters?.precio_min ?? '');
const precioMax = ref(props.filters?.precio_max ?? '');
const showFilters = ref(false);

const hasActiveFilters = computed(() =>
    searchInput.value.trim() || selectedCategoria.value || selectedMarca.value || precioMin.value || precioMax.value || selectedSort.value !== 'latest'
);

const sortOptions = [
    { value: 'latest', label: 'Más recientes' },
    { value: 'price_asc', label: 'Menor precio' },
    { value: 'price_desc', label: 'Mayor precio' },
    { value: 'name_asc', label: 'A - Z' },
    { value: 'name_desc', label: 'Z - A' },
];

const money = useMoney();
const favorites = useFavorites();

const isFavorite = (id: number) => favorites.isFavorite(id);

function toggleFavorite(product: { id: number; nombre: string; slug: string; precio: string; imagen: string | null }) {
    favorites.toggle({
        id: product.id,
        nombre: product.nombre,
        slug: product.slug,
        precio: product.precio,
        imagen: product.imagen,
    });
}

function isAgotado(stock: number | null | undefined): boolean {
    return typeof stock === 'number' && stock <= 0;
}

function getStockLabel(stock: number | null | undefined): { label: string; class: string } {
    if (stock === null || stock === undefined) {
        return { label: 'Stock no gestionado', class: 'bg-gray-100 text-gray-600' };
    }

    if (stock <= 0) {
        return { label: 'Agotado', class: 'bg-red-100 text-red-700' };
    }

    if (stock <= 5) {
        return { label: `Quedan ${stock} unidades`, class: 'bg-amber-100 text-amber-800' };
    }

    return { label: `En stock: ${stock} unidades`, class: 'bg-green-100 text-green-700' };
}

function applyFilters() {
    const params: Record<string, string> = {};

    if (searchInput.value.trim()) {
params.search = searchInput.value.trim();
}

    if (selectedCategoria.value) {
params.categoria = selectedCategoria.value;
}

    if (selectedMarca.value) {
params.marca = selectedMarca.value;
}

    if (precioMin.value) {
params.precio_min = precioMin.value;
}

    if (precioMax.value) {
params.precio_max = precioMax.value;
}

    if (selectedSort.value !== 'latest') {
params.sort = selectedSort.value;
}

    router.get('/productos', params, { preserveState: true, preserveScroll: true });
}

function clearFilters() {
    searchInput.value = '';
    selectedCategoria.value = '';
    selectedMarca.value = '';
    precioMin.value = '';
    precioMax.value = '';
    selectedSort.value = 'latest';
    applyFilters();
}

watch([selectedCategoria, selectedMarca, selectedSort, precioMin, precioMax], () => {
    applyFilters();
});

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchInput, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});
</script>

<template>
    <Head title="Catálogo" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <section class="bg-white border-b border-gray-200 py-8">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
                    <Link href="/" class="hover:text-blue-600 transition">Inicio</Link>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">Catálogo</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Catálogo de Productos</h1>
                <p class="mt-1 text-sm text-gray-500">{{ productos.total }} producto{{ productos.total !== 1 ? 's' : '' }} disponibles</p>
            </div>
        </section>

        <!-- Filters Bar -->
        <section class="sticky top-[70px] lg:top-[80px] z-30 bg-white border-b border-gray-200 shadow-sm">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 py-3">
                <div class="flex flex-col lg:flex-row items-start lg:items-center gap-3">
                    <div class="relative w-full lg:w-72">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <input
                            v-model="searchInput"
                            type="text"
                            placeholder="Buscar productos..."
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                        />
                    </div>

                    <div class="flex items-center gap-2 w-full lg:w-auto">
                        <div class="relative flex-1 lg:flex-none">
                            <ArrowUpDown class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400 pointer-events-none" />
                            <select
                                v-model="selectedSort"
                                class="w-full lg:w-44 appearance-none rounded-lg border border-gray-300 bg-gray-50 pl-9 pr-8 py-2.5 text-sm text-gray-700 focus:outline-none focus:border-blue-500 transition cursor-pointer"
                            >
                                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                            <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400 pointer-events-none" />
                        </div>

                        <button
                            @click="showFilters = !showFilters"
                            class="flex items-center gap-1.5 rounded-lg border px-3 py-2.5 text-xs font-medium transition shrink-0"
                            :class="showFilters ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:border-gray-400'"
                        >
                            <SlidersHorizontal class="h-3.5 w-3.5" />
                            Filtros
                        </button>

                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2.5 text-xs text-gray-500 hover:text-gray-700 hover:border-gray-400 transition shrink-0"
                        >
                            <RotateCcw class="h-3.5 w-3.5" />
                            Limpiar
                        </button>
                    </div>
                </div>

                <!-- Filters Panel -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 max-h-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-to-class="opacity-0 max-h-0"
                >
                    <div v-if="showFilters" class="overflow-hidden">
                        <div class="grid grid-cols-1 gap-3 border-t border-gray-100 pt-4 pb-2 mt-3 min-[375px]:grid-cols-2 sm:gap-4 md:grid-cols-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Categoría</label>
                                <select
                                    v-model="selectedCategoria"
                                    class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-blue-500 transition cursor-pointer"
                                >
                                    <option value="">Todas</option>
                                    <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                                        {{ cat.parent ? `${cat.parent.nombre} / ${cat.nombre}` : cat.nombre }} ({{ cat.productos_count }})
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Marca</label>
                                <select
                                    v-model="selectedMarca"
                                    class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-blue-500 transition cursor-pointer"
                                >
                                    <option value="">Todas</option>
                                    <option v-for="m in marcas" :key="m.id" :value="m.id">
                                        {{ m.nombre }} ({{ m.productos_count }})
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Precio Mínimo</label>
                                <input
                                    v-model="precioMin"
                                    type="number"
                                    min="0"
                                    placeholder="Bs. 0"
                                    class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-blue-500 transition"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Precio Máximo</label>
                                <input
                                    v-model="precioMax"
                                    type="number"
                                    min="0"
                                    placeholder="Bs. 99999"
                                    class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-blue-500 transition"
                                />
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Active filter chips -->
                <div v-if="hasActiveFilters && !showFilters" class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100">
                    <span class="text-xs text-gray-500">Filtros activos:</span>
                    <button
                        v-if="selectedCategoria"
                        @click="selectedCategoria = ''"
                        class="inline-flex items-center gap-1 rounded-full bg-blue-100 text-blue-700 px-2.5 py-1 text-xs font-medium"
                    >
                        {{ categorias.find(c => c.id === Number(selectedCategoria))?.nombre || selectedCategoria }}
                        <X class="h-3 w-3" />
                    </button>
                    <button
                        v-if="selectedMarca"
                        @click="selectedMarca = ''"
                        class="inline-flex items-center gap-1 rounded-full bg-blue-100 text-blue-700 px-2.5 py-1 text-xs font-medium"
                    >
                        {{ marcas.find(m => m.id === Number(selectedMarca))?.nombre || selectedMarca }}
                        <X class="h-3 w-3" />
                    </button>
                    <button
                        v-if="precioMin || precioMax"
                        @click="precioMin = ''; precioMax = ''"
                        class="inline-flex items-center gap-1 rounded-full bg-blue-100 text-blue-700 px-2.5 py-1 text-xs font-medium"
                    >
                        Bs.{{ precioMin || '0' }} - Bs.{{ precioMax || '∞' }}
                        <X class="h-3 w-3" />
                    </button>
                    <button
                        v-if="selectedSort !== 'latest'"
                        @click="selectedSort = 'latest'"
                        class="inline-flex items-center gap-1 rounded-full bg-blue-100 text-blue-700 px-2.5 py-1 text-xs font-medium"
                    >
                        {{ sortOptions.find(o => o.value === selectedSort)?.label }}
                        <X class="h-3 w-3" />
                    </button>
                    <button
                        v-if="searchInput"
                        @click="searchInput = ''"
                        class="inline-flex items-center gap-1 rounded-full bg-blue-100 text-blue-700 px-2.5 py-1 text-xs font-medium"
                    >
                        "{{ searchInput }}"
                        <X class="h-3 w-3" />
                    </button>
                </div>
            </div>
        </section>

        <!-- Products -->
        <section class="py-10">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-gray-500">
                        Mostrando <span class="text-gray-900 font-semibold">{{ productos.from }}-{{ productos.to }}</span>
                        de <span class="text-gray-900 font-semibold">{{ productos.total }}</span> producto{{ productos.total !== 1 ? 's' : '' }}
                    </p>
                </div>

                <div v-if="productos.data.length === 0" class="text-center py-24">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-white border border-gray-200 mb-5">
                        <Search class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Sin resultados</h3>
                    <p class="mt-1.5 text-sm text-gray-500 max-w-sm mx-auto">
                        No encontramos productos que coincidan con tu búsqueda o filtros.
                    </p>
                    <button
                        @click="clearFilters"
                        class="mt-6 inline-flex items-center gap-2 rounded-full bg-blue-600 px-4 sm:px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-500/40"
                    >
                        <RotateCcw class="h-4 w-4" />
                        Limpiar filtros
                    </button>
                </div>

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
                            <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                                <span
                                    v-if="p.categoria"
                                    class="inline-flex items-center rounded-full bg-white/90 backdrop-blur-sm px-2.5 py-1 text-[10px] font-medium text-blue-600 shadow-sm"
                                >
                                    {{ p.categoria?.nombre }}
                                </span>
                                <span
                                    v-if="p.marca"
                                    class="inline-flex items-center rounded-full bg-white/90 backdrop-blur-sm px-2.5 py-1 text-[10px] font-medium text-gray-700 shadow-sm"
                                >
                                    {{ p.marca?.nombre }}
                                </span>
                            </div>

                            <div class="absolute top-3 right-3 flex flex-col gap-1.5 items-end">
                                <span v-if="getStockLabel(p.stock)" :class="['inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium shadow-sm', getStockLabel(p.stock).class]">
                                    {{ getStockLabel(p.stock).label }}
                                </span>
                            </div>

                            <div v-if="p.precio_anterior && parseFloat(p.precio_anterior) > 0" class="absolute top-3 right-3">
                                <span class="inline-flex items-center rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">
                                    -{{ Math.round((1 - parseFloat(p.precio) / parseFloat(p.precio_anterior)) * 100) }}%
                                </span>
                            </div>

                            <div v-if="isAgotado(p.stock)" class="absolute top-3 right-3">
                                <span class="inline-flex items-center rounded-full bg-gray-700 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">
                                    Agotado
                                </span>
                            </div>

                            <div v-if="isAgotado(p.stock)" class="absolute inset-0 flex items-center justify-center bg-white/60 backdrop-blur-[1px]">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-900 px-4 py-2 text-xs font-bold text-white shadow-lg">
                                    <PackageX class="h-3.5 w-3.5" />
                                    Sin stock
                                </span>
                            </div>

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
                                <p class="mt-1.5 text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                    {{ p.descripcion || 'Sin descripción disponible' }}
                                </p>
                            </Link>

                            <div class="mt-4 flex flex-col items-start gap-3 min-[375px]:flex-col sm:flex-row sm:items-end sm:justify-between sm:gap-2">
                                <div>
                                    <div v-if="p.precio_anterior && parseFloat(p.precio_anterior) > 0" class="text-xs text-gray-400 line-through mb-0.5">
                                        {{ money.format(p.precio_anterior) }}
                                    </div>
                                    <span class="text-lg font-bold text-gray-900">
                                        {{ money.format(p.precio) }}
                                    </span>
                                </div>
                                <div class="flex w-full items-center gap-2 sm:w-auto">
                                    <button
                                        @click="toggleFavorite(p)"
                                        :class="[
                                            'inline-flex h-10 items-center justify-center gap-1.5 rounded-lg px-3 text-xs font-semibold transition-all active:scale-95 sm:h-auto sm:px-4 sm:py-2',
                                            isFavorite(p.id) ? 'bg-red-600 text-white shadow-lg shadow-red-500/25 hover:bg-red-700' : 'bg-white border border-gray-300 text-gray-700 hover:bg-red-50 hover:text-red-600 hover:border-red-300'
                                        ]"
                                        :disabled="isAgotado(p.stock)"
                                    >
                                        <Heart :class="[isFavorite(p.id) ? 'fill-current' : '', 'h-3.5 w-3.5']" />
                                    </button>
                                    <Link
                                        :href="'/productos/' + p.slug"
                                        class="inline-flex h-10 min-w-0 flex-1 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white transition-all hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/20 active:scale-95 sm:h-auto sm:flex-none sm:px-4 sm:py-2"
                                    >
                                        <Eye class="h-3.5 w-3.5" />
                                        Ver
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="productos.last_page > 1" class="mt-10">
                    <Pagination :pagination="productos" />
                </div>
            </div>
        </section>
    </div>
</template>
