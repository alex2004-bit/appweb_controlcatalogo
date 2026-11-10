<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ShoppingCart,
    MessageCircle,
    Check,
    ChevronLeft,
    ChevronRight,
    Truck,
    Bell,
    Star,
    LayoutList,
    LayoutGrid,
    Heart,
    Minus,
    Plus,
    ArrowUp,
    AlertTriangle,
    CheckCircle,
    CircleHelp,
    Package,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useCart } from '@/composables/useCart';
import { useFavorites } from '@/composables/useFavorites';
import { useMoney } from '@/composables/useMoney';
import { DEFAULT_PRODUCT_IMAGE as defaultImg } from '@/constants/images';

defineOptions({ inheritAttrs: false });

interface Especificacion {
    nombre: string;
    valor: string;
    imagen?: string | null;
}

const props = defineProps<{
    producto: {
        id: number;
        nombre: string;
        slug: string;
        descripcion: string | null;
        especificaciones: Record<string, string> | Especificacion[] | null;
        sku: string | null;
        codigo_fabricante: string | null;
        precio: string;
        imagen: string | null;
        imagenes: string[];
        stock: number | null;
        marca: { id: number; nombre: string; slug: string; logo: string | null } | null;
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

const whatsappNumber = '59164828148';

const cart = useCart();
const favorites = useFavorites();
const money = useMoney();

const selectedImageIndex = ref(0);
const addedToCart = ref(false);
const viewMode = ref<'list' | 'grid'>('grid');
const quantity = ref(1);
const isChatOpen = ref(false);
const showScrollTop = ref(false);
const addToCartTimeout = ref<ReturnType<typeof setTimeout> | null>(null);

const agotado = computed(() => props.producto.stock !== null && props.producto.stock <= 0);
const hayStock = computed(() => props.producto.stock === null || props.producto.stock > 0);
const maximumQuantity = computed(() => props.producto.stock === null ? 99 : Math.max(1, props.producto.stock));
const stockLabel = computed(() => {
    const stock = props.producto.stock;

    if (stock === null || stock === undefined) {
        return { label: 'Stock no gestionado', class: 'bg-gray-100 text-gray-600', icon: CircleHelp };
    }

    if (stock <= 0) {
        return { label: 'Agotado', class: 'bg-red-100 text-red-700', icon: Package };
    }

    if (stock <= 5) {
        return { label: `Stock bajo: ${stock} unidades`, class: 'bg-amber-100 text-amber-800', icon: AlertTriangle };
    }

    return { label: `Disponible: ${stock} unidades`, class: 'bg-green-100 text-green-700', icon: CheckCircle };
});
const imagenes = computed(() => props.producto.imagenes?.length ? props.producto.imagenes : [props.producto.imagen].filter(Boolean));

const especs = computed((): Especificacion[] => {
    if (!props.producto.especificaciones) {
return [];
}

    // Handle new array format: [{ nombre, valor, imagen }]
    if (Array.isArray(props.producto.especificaciones)) {
        return props.producto.especificaciones as Especificacion[];
    }

    // Handle old object format: { "nombre": "valor" }
    return Object.entries(props.producto.especificaciones as Record<string, string>).map(([nombre, valor]) => ({
        nombre,
        valor,
        imagen: null,
    }));
});

const isFavorite = computed(() => favorites.isFavorite(props.producto.id));
const productWhatsAppUrl = computed(() => createWhatsAppUrl(
    `Hola, me interesa el producto: ${props.producto.nombre} (${money.format(props.producto.precio)})`,
));
const restockWhatsAppUrl = computed(() => createWhatsAppUrl(
    `Hola, quiero que me avisen cuando vuelva a haber stock de: ${props.producto.nombre}`,
));

function createWhatsAppUrl(message: string): string {
    return `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
}

function toggleFavorite() {
    favorites.toggle({
        id: props.producto.id,
        nombre: props.producto.nombre,
        slug: props.producto.slug,
        precio: props.producto.precio,
        imagen: props.producto.imagen,
    });
}

function addToCart() {
    if (agotado.value) {
        return;
    }

    const cantidad = Math.max(1, quantity.value);

    cart.add({
        id: props.producto.id,
        nombre: props.producto.nombre,
        slug: props.producto.slug,
        precio: props.producto.precio,
        imagen: props.producto.imagen,
        stock: props.producto.stock,
    }, cantidad);

    quantity.value = 1;
    addedToCart.value = true;

    if (addToCartTimeout.value) {
        clearTimeout(addToCartTimeout.value);
    }

    addToCartTimeout.value = setTimeout(() => {
        addedToCart.value = false;
        addToCartTimeout.value = null;
    }, 2000);
    cart.open();
}

function updateQuantity(delta: number) {
    quantity.value = Math.max(1, Math.min(maximumQuantity.value, quantity.value + delta));
}

function handleQuantityInput(value: number | string) {
    const parsedValue = Number(value);

    if (!Number.isFinite(parsedValue) || parsedValue < 1) {
        quantity.value = 1;

        return;
    }

    quantity.value = Math.min(maximumQuantity.value, Math.floor(parsedValue));
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

onMounted(() => {
    showScrollTop.value = window.scrollY > 300;
    window.addEventListener('scroll', handleScroll);
});

function handleScroll() {
    showScrollTop.value = window.scrollY > 300;
}

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);

    if (addToCartTimeout.value) {
        clearTimeout(addToCartTimeout.value);
    }
});

function selectImage(index: number) {
    selectedImageIndex.value = index;
}

function prevImage() {
    selectedImageIndex.value = (selectedImageIndex.value - 1 + imagenes.value.length) % imagenes.value.length;
}

function nextImage() {
    selectedImageIndex.value = (selectedImageIndex.value + 1) % imagenes.value.length;
}

function avisarme() {
    window.open(restockWhatsAppUrl.value, '_blank', 'noopener,noreferrer');
}
</script>

<template>
    <Head :title="producto.nombre" />

    <div class="min-h-screen bg-slate-50">
        <!-- Breadcrumb -->
        <section class="border-b border-slate-200 bg-white py-3">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <Link href="/" class="hover:text-blue-600 transition">Inicio</Link>
                    <span>/</span>
                    <Link href="/productos" class="hover:text-blue-600 transition">Productos</Link>
                    <span>/</span>
                    <span class="text-gray-700 font-medium truncate max-w-[200px]">{{ producto.nombre }}</span>
                </div>
            </div>
        </section>

        <!-- Product Detail - 2 Column Layout -->
        <section class="py-6 sm:py-10">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
                <div class="grid min-w-0 items-start gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(360px,0.9fr)] lg:gap-8">
                    <!-- LEFT COLUMN: Image Gallery -->
                    <div class="relative min-w-0 rounded-3xl border border-slate-200 bg-white p-3 shadow-sm sm:p-5">
                        <!-- Main Image -->
                        <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-50 via-white to-blue-50">
                            <div class="flex aspect-[4/3] items-center justify-center p-5 sm:p-8 lg:p-10">
                                <img
                                    :src="imagenes[selectedImageIndex] || defaultImg"
                                    :alt="producto.nombre"
                                    class="max-h-full max-w-full rounded-lg object-contain transition-transform duration-500 group-hover:scale-[1.03]"
                                    loading="eager"
                                />
                            </div>
                            <span class="absolute left-4 top-4 rounded-full border border-white/80 bg-white/90 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-slate-600 shadow-sm">
                                {{ producto.categoria?.nombre || 'Producto' }}
                            </span>

                            <!-- Navigation Arrows -->
                            <button
                                v-if="imagenes.length > 1"
                                @click="prevImage"
                                class="absolute left-3 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/95 text-gray-700 shadow-lg transition hover:bg-white hover:shadow-xl sm:left-4"
                                aria-label="Imagen anterior"
                            >
                                <ChevronLeft class="h-5 w-5" />
                            </button>
                            <button
                                v-if="imagenes.length > 1"
                                @click="nextImage"
                                class="absolute right-3 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/95 text-gray-700 shadow-lg transition hover:bg-white hover:shadow-xl sm:right-4"
                                aria-label="Imagen siguiente"
                            >
                                <ChevronRight class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Thumbnail Carousel -->
                        <div v-if="imagenes.length > 1" class="mt-4 overflow-hidden px-1">
                            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide" role="list" aria-label="Miniaturas del producto">
                                <button
                                    v-for="(img, idx) in imagenes"
                                    :key="idx"
                                    @click="selectImage(idx)"
                                    :class="[
                                        'flex-shrink-0 relative h-20 w-20 sm:h-24 sm:w-24 rounded-lg border-2 overflow-hidden transition-all duration-200',
                                        idx === selectedImageIndex
                                            ? 'border-lime-500 ring-2 ring-lime-500/20 shadow-lg'
                                            : 'border-transparent hover:border-gray-300'
                                    ]"
                                    :aria-label="`Ver imagen ${idx + 1}`"
                                    :aria-current="idx === selectedImageIndex ? 'true' : 'false'"
                                >
                                    <img
                                        :src="img || defaultImg"
                                        :alt="`${producto.nombre} - Vista ${idx + 1}`"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    />
                                    <div v-if="idx === selectedImageIndex" class="absolute inset-0 bg-lime-500/10 pointer-events-none" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Product Info -->
                    <div class="flex min-w-0 flex-col gap-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
                        <!-- Category Badge -->
                        <div class="flex items-center justify-between gap-3">
                            <p v-if="producto.categoria" class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-700">
                                {{ producto.categoria.nombre }}
                            </p>
                            <button
                                type="button"
                                @click="toggleFavorite"
                                :aria-pressed="isFavorite"
                                :class="[
                                    'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border transition',
                                    isFavorite ? 'border-rose-200 bg-rose-50 text-rose-600' : 'border-slate-200 text-slate-500 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600'
                                ]"
                                :aria-label="isFavorite ? 'Quitar de favoritos' : 'Guardar en favoritos'"
                            >
                                <Heart :class="[isFavorite ? 'fill-current' : '', 'h-5 w-5']" />
                            </button>
                        </div>

                        <h1 class="text-2xl font-bold leading-tight tracking-tight text-slate-950 sm:text-3xl lg:text-[2.1rem]">
                            {{ producto.nombre }}
                        </h1>

                        <!-- Identifiers: Código + SKU -->
                        <div class="flex flex-col items-start gap-2 text-sm text-gray-600 border-t border-b border-gray-100 py-3">
                            <div v-if="producto.codigo_fabricante" class="flex items-center gap-1.5">
                                <span class="font-medium text-gray-900">Código:</span>
                                <span class="font-mono text-gray-700">{{ producto.codigo_fabricante }}</span>
                            </div>
                            <div v-if="producto.sku" class="flex items-center gap-1.5">
                                <span class="font-medium text-gray-900">SKU:</span>
                                <span class="font-mono text-gray-700 text-base">{{ producto.sku }}</span>
                            </div>
                        </div>

                        <!-- Brand Logo -->
                        <div v-if="producto.marca?.logo" class="pt-2">
                            <Link
                                :href="`/productos?marca=${producto.marca.id}`"
                                class="inline-block"
                                :aria-label="`Ver todos los productos de ${producto.marca.nombre}`"
                            >
                                <img
                                    :src="producto.marca.logo"
                                    :alt="producto.marca.nombre"
                                    class="h-10 sm:h-12 max-w-[180px] opacity-80 hover:opacity-100 transition-opacity"
                                />
                            </Link>
                        </div>

                        <!-- Description Section -->
                        <div class="border-t border-slate-100 pt-5">
                            <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                                Descripción del Producto
                                <span class="h-0.5 w-12 bg-blue-600" />
                            </h3>
                            <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">
                                {{ producto.descripcion || 'Sin descripción disponible.' }}
                            </p>
                        </div>

                        <!-- Availability Block -->
                        <div class="rounded-2xl border border-blue-100 bg-blue-50/50 p-4 sm:p-5">
                            <!-- Stock Status Badge -->
                            <div class="mb-4 inline-flex items-center gap-2 rounded-full px-3 py-1.5" :class="stockLabel.class">
                                <component :is="stockLabel.icon" class="h-4 w-4" />
                                <span class="text-sm font-medium">{{ stockLabel.label }}</span>
                            </div>

                            <!-- Case A: Sin Stock -->
                            <div v-if="agotado" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-xl bg-gray-50 border border-gray-200">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                        <Truck class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">Sin fecha de llegada</p>
                                        <p class="text-xs text-gray-500">* Sujeto a modificaciones</p>
                                    </div>
                                </div>
                                <button
                                    @click="avisarme"
                                    class="inline-flex items-center gap-2 rounded-full bg-lime-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-lime-500/25 transition-all hover:bg-lime-600 hover:shadow-xl hover:shadow-lime-500/40 hover:scale-105 active:scale-95"
                                >
                                    <Bell class="h-4 w-4" />
                                    Avisarme
                                </button>
                            </div>

                            <!-- Case B: Con Stock -->
                            <div v-else-if="hayStock" class="space-y-4">
                                <div class="flex flex-wrap items-end justify-between gap-3">
                                    <div>
                                        <p class="mb-1 text-xs font-medium uppercase tracking-wider text-slate-500">Precio</p>
                                        <span class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">{{ money.format(producto.precio) }}</span>
                                    </div>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600">IVA incluido</span>
                                </div>

                                <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-gray-50 p-3">
                                    <span class="text-sm font-medium text-gray-600">Cantidad</span>
                                    <div class="flex items-center gap-2 rounded-full border border-gray-200 bg-white px-2 py-1 shadow-sm">
                                        <button
                                            type="button"
                                            @click="updateQuantity(-1)"
                                            :disabled="quantity <= 1"
                                            class="flex h-8 w-8 items-center justify-center rounded-full text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40"
                                            aria-label="Disminuir cantidad"
                                        >
                                            <Minus class="h-4 w-4" />
                                        </button>
                                        <input
                                            :value="quantity"
                                            @input="handleQuantityInput(($event.target as HTMLInputElement).value)"
                                            type="number"
                                            min="1"
                                            step="1"
                                            :max="maximumQuantity"
                                            class="w-12 border-0 bg-transparent text-center text-sm font-semibold text-gray-900 focus:outline-none"
                                            aria-label="Cantidad del producto"
                                        />
                                        <button
                                            type="button"
                                            @click="updateQuantity(1)"
                                            :disabled="quantity >= maximumQuantity"
                                            class="flex h-8 w-8 items-center justify-center rounded-full text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40"
                                            aria-label="Aumentar cantidad"
                                        >
                                            <Plus class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-3">
                                    <button
                                        @click="addToCart"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-blue-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-500/25 transition-all hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-500/40"
                                    >
                                        <Check v-if="addedToCart" class="h-4 w-4" />
                                        <ShoppingCart v-else class="h-4 w-4" />
                                        {{ addedToCart ? 'Agregado' : 'Agregar al carrito' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- WhatsApp Button (Always visible) -->
                        <a
                            :href="productWhatsAppUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-emerald-500 px-8 py-3.5 text-sm font-bold text-emerald-600 transition hover:bg-emerald-50 hover:text-emerald-700"
                        >
                            <MessageCircle class="h-4 w-4" />
                            Cotizar por WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- BOTTOM SECTION: Características -->
        <section v-if="especs.length > 0" class="bg-gray-50 border-t border-gray-200 py-12 sm:py-16">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                        Características
                        <span class="h-0.5 w-16 bg-blue-600" />
                    </h2>

                    <!-- View Mode Toggle -->
                    <div class="flex items-center gap-2 bg-white rounded-lg border border-gray-200 p-1">
                        <button
                            @click="viewMode = 'list'"
                            :class="[
                                'px-3 py-2 rounded-md text-sm font-medium transition-all',
                                viewMode === 'list'
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                            ]"
                            aria-label="Vista lista"
                        >
                            <LayoutList class="h-4 w-4" />
                        </button>
                        <button
                            @click="viewMode = 'grid'"
                            :class="[
                                'px-3 py-2 rounded-md text-sm font-medium transition-all',
                                viewMode === 'grid'
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                            ]"
                            aria-label="Vista cuadrícula"
                        >
                            <LayoutGrid class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- List View -->
                <div v-if="viewMode === 'list'" class="prose prose-gray max-w-none">
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li v-for="spec in especs" :key="spec.nombre" class="flex items-start gap-3 p-3 bg-white rounded-lg border border-gray-100 hover:border-gray-200 transition">
                            <Check class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <strong class="text-gray-800">{{ spec.nombre.replace('_', ' ') }}:</strong>
                                <span class="ml-2">{{ spec.valor }}</span>
                            </div>
                            <div v-if="spec.imagen" class="relative w-20 h-20 flex-shrink-0 ml-4">
                                <img :src="spec.imagen" :alt="spec.nombre" class="w-full h-full object-cover rounded-lg border border-gray-200" loading="lazy" />
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Grid View with Large Images -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="spec in especs"
                        :key="spec.nombre"
                        class="group bg-white rounded-xl border border-gray-200 overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1"
                    >
                        <!-- Large Image Area -->
                        <div v-if="spec.imagen" class="aspect-video relative overflow-hidden bg-gray-100">
                            <img
                                :src="spec.imagen"
                                :alt="spec.nombre"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                loading="lazy"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
                        </div>
                        <div v-else class="aspect-video relative overflow-hidden bg-gray-100 flex items-center justify-center">
                            <Star class="h-12 w-12 text-gray-300" />
                        </div>

                        <!-- Content -->
                        <div class="p-5">
                            <h3 class="text-base font-semibold text-gray-900 leading-tight mb-2">{{ spec.nombre.replace('_', ' ') }}</h3>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ spec.valor }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- BOTTOM SECTION: Productos Relacionados -->
        <section v-if="relacionados.length > 0" class="bg-white border-t border-gray-200 py-12 sm:py-16">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-8">Productos Relacionados</h2>
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
                        <h3 class="mt-1 text-sm font-semibold text-gray-900 leading-tight line-clamp-2">{{ r.nombre }}</h3>
                        <p class="mt-2 text-base font-bold text-gray-900">{{ money.format(r.precio) }}</p>
                    </Link>
                </div>
            </div>
        </section>

        <div class="pointer-events-none fixed bottom-44 right-5 z-50 flex flex-col items-end gap-3 lg:bottom-24">
            <div v-if="isChatOpen" class="pointer-events-auto w-[280px] overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-2xl shadow-emerald-500/15">
                <div class="flex items-center justify-between bg-emerald-500 px-4 py-3 text-white">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/15 text-xs font-black">
                            C
                        </div>
                        <div>
                            <p class="text-sm font-bold">Control Catalogo</p>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-emerald-100">Online</p>
                        </div>
                    </div>
                    <button type="button" @click="isChatOpen = false" class="text-white/80 transition hover:text-white" aria-label="Cerrar chat">
                        ×
                    </button>
                </div>
                <div class="p-4">
                    <p class="text-sm leading-relaxed text-gray-700">¿Tienes dudas sobre este producto? Escríbenos y te ayudamos.</p>
                    <a
                        :href="productWhatsAppUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600"
                    >
                        <MessageCircle class="h-4 w-4" />
                        Escribir por WhatsApp
                    </a>
                </div>
            </div>

            <div class="pointer-events-auto flex items-center gap-3">
                <button
                    v-if="showScrollTop"
                    type="button"
                    @click="scrollToTop"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-900 text-white shadow-lg shadow-slate-900/30 transition hover:scale-105 hover:bg-slate-800"
                    aria-label="Volver arriba"
                >
                    <ArrowUp class="h-5 w-5" />
                </button>

                <button
                    type="button"
                    @click="isChatOpen = !isChatOpen"
                    :aria-expanded="isChatOpen"
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500 text-white shadow-lg shadow-emerald-500/35 transition hover:scale-105 hover:bg-emerald-600"
                    aria-label="Abrir chat"
                >
                    <MessageCircle class="h-6 w-6" />
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Hide scrollbar for thumbnail carousel */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Smooth image transition */
img[loading="eager"] {
    transition: opacity 0.3s ease;
}
</style>