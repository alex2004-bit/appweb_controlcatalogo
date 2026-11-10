<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Package, ShieldCheck, Wrench, Camera, HardDrive, Smartphone, BarChart3, MessageCircle, ShoppingCart, ChevronLeft, ChevronRight, Sparkles, Truck, RotateCcw, Headphones, CreditCard, Heart } from '@lucide/vue';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useCart } from '@/composables/useCart';
import { useFavorites } from '@/composables/useFavorites';
import { useMoney } from '@/composables/useMoney';
import { DEFAULT_PRODUCT_IMAGE as defaultImg } from '@/constants/images';

type ProductoDestacado = {
    id: number;
    nombre: string;
    slug: string;
    descripcion: string | null;
    precio: string;
    imagen: string | null;
    stock: number | null;
    categoria: { nombre: string; slug: string } | null;
};

const props = defineProps<{
    destacados: ProductoDestacado[];
    stats: { label: string; value: string; color: string }[];
    bannerImages?: (string | null)[];
}>();

const whatsappNumber = '59164828148';

const cart = useCart();
const favorites = useFavorites();
const money = useMoney();

const isFavorite = (id: number) => favorites.isFavorite(id);

function toggleFavorite(item: ProductoDestacado) {
    favorites.toggle({
        id: item.id,
        nombre: item.nombre,
        slug: item.slug,
        precio: item.precio,
        imagen: item.imagen,
    });
}

function addToCart(item: ProductoDestacado) {
    cart.add({
        id: item.id,
        nombre: item.nombre,
        slug: item.slug,
        precio: item.precio,
        imagen: item.imagen,
        stock: item.stock,
    });
    cart.open();
}

function isAgotado(stock: number | null | undefined): boolean {
    return typeof stock === 'number' && stock <= 0;
}

const features = [
    { titulo: 'Cámaras', desc: 'HD, 4K, PTZ, IP, análogas, térmicas', icon: Camera },
    { titulo: 'DVR/NVR', desc: 'Grabación y almacenamiento profesional', icon: HardDrive },
    { titulo: 'Alarmas', desc: 'Sistemas cableados e inalámbricos', icon: ShieldCheck },
    { titulo: 'Control Acceso', desc: 'Biométrico, tarjeta, cerraduras eléctricas', icon: Smartphone },
    { titulo: 'Sensores', desc: 'Movimiento, magnéticos, gas, humo', icon: BarChart3 },
    { titulo: 'Instalación', desc: 'Profesional certificada y soporte técnico', icon: Wrench },
];

const benefits = [
    { icon: Truck, label: 'Envío Rápido', desc: 'Entrega en 24-48 hrs' },
    { icon: RotateCcw, label: 'Garantía', desc: 'Cambios y devoluciones' },
    { icon: Headphones, label: 'Soporte Técnico', desc: 'Asesoría especializada' },
    { icon: CreditCard, label: 'Pago Seguro', desc: 'Transferencia, depósito, QR' },
];

// Slider
const defaultSlides = [
    {
        image: 'https://images.unsplash.com/photo-1558002038-1055907df827?q=80&w=2070&auto=format&fit=crop',
        title: 'Cámaras de Seguridad',
        subtitle: 'Protege tu hogar con tecnología de última generación.',
        link: '/productos',
        gradient: 'from-blue-900/70 via-blue-800/50 to-transparent',
    },
    {
        image: 'https://images.unsplash.com/photo-1582139329536-e7284fece509?q=80&w=2080&auto=format&fit=crop',
        title: 'Sistemas de Alarma',
        subtitle: 'Protección inteligente para tu negocio las 24 horas.',
        link: '/productos',
        gradient: 'from-indigo-900/70 via-indigo-800/50 to-transparent',
    },
    {
        image: 'https://images.unsplash.com/photo-1557324232-b8917d3c3dcb?q=80&w=1974&auto=format&fit=crop',
        title: 'Control de Acceso',
        subtitle: 'Biométrico, tarjetas y cerraduras eléctricas profesionales.',
        link: '/productos',
        gradient: 'from-gray-900/70 via-gray-800/50 to-transparent',
    },
];

const slides = computed(() => defaultSlides.map((slide, index) => ({
    ...slide,
    image: props.bannerImages?.[index] || slide.image,
})));

const currentSlide = ref(0);
let slideInterval: ReturnType<typeof setInterval> | null = null;

function startAutoplay() {
    slideInterval = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % slides.value.length;
    }, 5000);
}

function stopAutoplay() {
    if (slideInterval) {
        clearInterval(slideInterval);
        slideInterval = null;
    }
}

function goToSlide(index: number) {
    stopAutoplay();
    currentSlide.value = index;
    startAutoplay();
}

function prevSlide() {
    stopAutoplay();
    currentSlide.value = (currentSlide.value - 1 + slides.value.length) % slides.value.length;
    startAutoplay();
}

function nextSlide() {
    stopAutoplay();
    currentSlide.value = (currentSlide.value + 1) % slides.value.length;
    startAutoplay();
}

onMounted(() => startAutoplay());
onUnmounted(() => stopAutoplay());
</script>

<template>
    <Head title="DOMOTECH - Tienda de Seguridad y Tecnología" />

    <!-- Banner Slider -->
    <section class="relative h-[min(500px,70svh)] min-h-[400px] w-full overflow-hidden bg-gray-900 sm:h-[500px]">
        <div
            v-for="(slide, idx) in slides"
            :key="idx"
            class="absolute inset-0 transition-all duration-700 ease-in-out"
            :class="idx === currentSlide ? 'opacity-100' : 'opacity-0'"
        >
            <img
                :src="slide.image"
                :alt="slide.title"
                class="absolute inset-0 w-full h-full object-cover"
            />
            <div class="absolute inset-0" :class="slide.gradient" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
            <div class="absolute inset-0 bg-gradient-to-r from-black/30 to-transparent" />

            <div class="relative h-full flex items-center">
                <div class="mx-auto w-full max-w-[1400px] px-9 sm:px-6 lg:px-12">
                    <div class="max-w-xl">
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-white/15 backdrop-blur-sm px-4 py-1.5 text-xs font-medium text-white border border-white/20 mb-5"
                        >
                            <Sparkles class="h-3 w-3" />
                            {{ idx === 0 ? 'Nuevos ingresos' : idx === 1 ? 'Más vendidos' : 'Profesional' }}
                        </div>
                        <h2
                            class="text-3xl md:text-5xl lg:text-6xl font-bold text-white leading-tight"
                        >
                            {{ slide.title }}
                        </h2>
                        <p class="mt-4 text-base md:text-lg text-white/80 max-w-lg">
                            {{ slide.subtitle }}
                        </p>
                        <Link
                            :href="slide.link"
                            class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-8 py-3.5 text-sm font-bold text-gray-900 transition-all hover:bg-blue-600 hover:text-white hover:shadow-xl hover:shadow-blue-600/30"
                        >
                            Comprar ahora
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Arrows -->
        <button @click="prevSlide" aria-label="Mostrar banner anterior" class="absolute left-1 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-white/10 bg-white/20 text-white backdrop-blur-sm transition hover:bg-white/40 sm:left-4 sm:h-12 sm:w-12">
            <ChevronLeft class="h-5 w-5" />
        </button>
        <button @click="nextSlide" aria-label="Mostrar banner siguiente" class="absolute right-1 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-white/10 bg-white/20 text-white backdrop-blur-sm transition hover:bg-white/40 sm:right-4 sm:h-12 sm:w-12">
            <ChevronRight class="h-5 w-5" />
        </button>

        <!-- Dots -->
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-10 flex items-center gap-2.5">
            <button
                v-for="(_, idx) in slides"
                :key="idx"
                @click="goToSlide(idx)"
                class="h-2.5 rounded-full transition-all duration-300"
                :class="idx === currentSlide ? 'w-8 bg-white' : 'w-2.5 bg-white/40 hover:bg-white/60'"
            />
        </div>
    </section>

    <!-- Benefits Bar -->
    <section class="bg-white border-b border-gray-200">
        <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
            <div class="grid grid-cols-1 gap-3 py-5 min-[375px]:grid-cols-2 sm:gap-4 sm:py-6 md:grid-cols-4">
                <div v-for="b in benefits" :key="b.label" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <component :is="b.icon" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ b.label }}</p>
                        <p class="text-xs text-gray-500">{{ b.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-700 py-12">
        <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
            <div class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-4">
                <div v-for="stat in stats" :key="stat.label" class="text-center">
                    <p class="text-3xl font-bold text-white">{{ stat.value }}</p>
                    <p class="text-sm text-blue-200 mt-1">{{ stat.label }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Productos Destacados -->
    <section class="py-16 bg-gray-50">
        <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3.5 py-1 text-xs font-semibold text-blue-700">
                        <Package class="h-3 w-3" /> Destacados
                    </div>
                    <h2 class="mt-3 text-2xl font-bold text-gray-900">Productos Destacados</h2>
                    <p class="mt-1 text-sm text-gray-500">Los más buscados por nuestros clientes</p>
                </div>
                <Link
                    href="/productos"
                    class="hidden sm:inline-flex items-center gap-2 rounded-full border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-blue-600 hover:text-white hover:border-blue-600"
                >
                    Ver todo
                    <ChevronRight class="h-4 w-4" />
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-4 min-[375px]:grid-cols-2 sm:gap-6 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                <div
                    v-for="p in destacados"
                    :key="p.id"
                    class="group min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-500/5"
                >
                    <Link :href="`/productos/${p.slug}`" class="block relative aspect-[4/3] overflow-hidden bg-gray-100">
                        <img
                            :src="p.imagen || defaultImg"
                            :alt="p.nombre"
                            class="absolute inset-0 w-full h-full object-contain transition-transform duration-500 group-hover:scale-110"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                        <span v-if="p.categoria" class="absolute top-3 left-3 inline-flex items-center rounded-full bg-white/90 backdrop-blur-sm px-2.5 py-1 text-[10px] font-medium text-blue-600 shadow-sm">
                            {{ p.categoria.nombre }}
                        </span>
                    </Link>

                    <div class="p-3 sm:p-5">
                        <Link :href="`/productos/${p.slug}`">
                            <h3 class="text-sm font-semibold text-gray-900 leading-snug line-clamp-2 group-hover:text-blue-600 transition-colors">
                                {{ p.nombre }}
                            </h3>
                            <p class="mt-1.5 text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                {{ p.descripcion || 'Sin descripción disponible' }}
                            </p>
                        </Link>

                        <div class="mt-3 flex flex-col items-stretch gap-2 sm:mt-4 sm:flex-row sm:items-end sm:justify-between">
                            <div class="min-w-0">
                                <span class="break-words text-base font-bold text-gray-900 sm:text-lg">
                                    {{ money.format(p.precio) }}
                                </span>
                            </div>
                            <div class="flex w-full items-center justify-end gap-2 sm:w-auto">
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
                                <button
                                    @click="addToCart(p)"
                                    :disabled="isAgotado(p.stock)"
                                    class="inline-flex h-10 min-w-0 flex-1 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white transition-all hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/25 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40 sm:h-auto sm:flex-none sm:px-4 sm:py-2"
                                >
                                    <ShoppingCart class="h-3.5 w-3.5" />
                                    {{ isAgotado(p.stock) ? 'Agotado' : 'Cotizar' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 text-center sm:hidden">
                <Link
                    href="/productos"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-300 px-4 sm:px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-600 hover:text-white hover:border-blue-600"
                >
                    Ver catálogo completo
                    <ChevronRight class="h-4 w-4" />
                </Link>
            </div>
        </div>
    </section>

    <!-- Categorías / Soluciones -->
    <section class="py-16 bg-white">
        <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3.5 py-1 text-xs font-semibold text-blue-700">
                    <ShieldCheck class="h-3 w-3" /> Soluciones
                </div>
                <h2 class="mt-3 text-2xl font-bold text-gray-900">Soluciones completas</h2>
                <p class="mt-1 text-sm text-gray-500">Todo lo que necesitas para tu sistema de seguridad</p>
            </div>
            <div class="grid grid-cols-1 gap-4 min-[375px]:grid-cols-2 sm:gap-6 lg:grid-cols-3">
                <Link
                    v-for="f in features"
                    :key="f.titulo"
                    href="/productos"
                    class="group min-w-0 rounded-xl border border-gray-200 bg-white p-4 transition-all duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-500/5 sm:p-6"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                        <component :is="f.icon" class="h-6 w-6" />
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-gray-900">{{ f.titulo }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ f.desc }}</p>
                </Link>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-700 py-20">
        <div class="absolute inset-0 opacity-10">
            <img
                src="https://images.unsplash.com/photo-1582139329536-e7284fece509?q=80&w=2080&auto=format&fit=crop"
                alt=""
                class="absolute inset-0 w-full h-full object-cover"
                aria-hidden="true"
            />
        </div>
        <div class="relative z-10 mx-auto w-full max-w-[1400px] px-4 sm:px-6 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm mb-6">
                <MessageCircle class="h-8 w-8 text-white" />
            </div>
            <h2 class="text-3xl font-bold text-white">¿Necesitas una cotización personalizada?</h2>
            <p class="mt-3 text-lg text-blue-200 max-w-xl mx-auto">
                Contáctanos por WhatsApp y recibe asesoría especializada para tu proyecto.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a
                    :href="`https://wa.me/${whatsappNumber}`"
                    target="_blank"
                    class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-3.5 text-sm font-bold text-gray-900 shadow-lg transition-all hover:shadow-xl hover:shadow-white/25 hover:scale-105"
                >
                    <MessageCircle class="h-5 w-5" />
                    Escribirnos ahora
                </a>
                <Link
                    href="/contacto"
                    class="inline-flex items-center gap-2 rounded-full border-2 border-white/30 px-8 py-3.5 text-sm font-bold text-white transition hover:bg-white/10 hover:border-white/50"
                >
                    Formulario de contacto
                </Link>
            </div>
        </div>
    </section>
</template>
