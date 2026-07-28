<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Package, ShieldCheck, Wrench, Camera, HardDrive, Smartphone, BarChart3, MessageCircle, ShoppingCart, ChevronLeft, ChevronRight, Sparkles, Truck, RotateCcw, Headphones, CreditCard, Check, X } from '@lucide/vue';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps<{
    destacados: {
        id: number;
        nombre: string;
        slug: string;
        descripcion: string | null;
        precio: string;
        imagen: string | null;
        categoria: { nombre: string; slug: string } | null;
    }[];
    stats: { label: string; value: string; color: string }[];
}>();

const whatsappNumber = '521XXXXXXXXXX';
const isCartOpen = ref(false);
const cartItems = ref<{ name: string; price: string; slug: string }[]>([]);

function addToCart(item: { name: string; price: string; slug: string }) {
    cartItems.value.push(item);
    isCartOpen.value = true;
}

function removeFromCart(index: number) {
    cartItems.value.splice(index, 1);
}

const totalCarrito = computed(() =>
    cartItems.value.reduce((acc, i) => acc + parseFloat((i.price ?? '0').replace('$', '').replace(',', '')), 0).toFixed(2)
);

function cotizarWhatsApp(items: { name: string; price: string; slug: string }[]) {
    const mensaje = items.length > 0
        ? `Hola, me interesa cotizar los siguientes productos:%0A${items.map(i => `- ${i.name} ($${i.price})`).join('%0A')}%0A%0A¿Podrían darme más información?`
        : 'Hola, me gustaría recibir información sobre sus productos de seguridad y videovigilancia.';
    window.open(`https://wa.me/${whatsappNumber}?text=${mensaje}`, '_blank');
    isCartOpen.value = false;
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

const defaultImg = 'https://images.unsplash.com/photo-1557324232-b8917d3c3dcb?q=80&w=1974&auto=format&fit=crop';

// Slider
const slides = [
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

const currentSlide = ref(0);
let slideInterval: ReturnType<typeof setInterval> | null = null;

function startAutoplay() {
    slideInterval = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % slides.length;
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
    currentSlide.value = (currentSlide.value - 1 + slides.length) % slides.length;
    startAutoplay();
}

function nextSlide() {
    stopAutoplay();
    currentSlide.value = (currentSlide.value + 1) % slides.length;
    startAutoplay();
}

onMounted(() => startAutoplay());
onUnmounted(() => stopAutoplay());
</script>

<template>
    <Head title="ControlCatálogo - Tienda de Seguridad y Tecnología" />

    <!-- Banner Slider -->
    <section class="relative w-full overflow-hidden bg-gray-900" style="height: 500px;">
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
                <div class="mx-auto w-full max-w-[1400px] px-6 lg:px-12">
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
        <button @click="prevSlide" class="absolute left-4 top-1/2 -translate-y-1/2 z-10 flex h-12 w-12 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm text-white hover:bg-white/40 transition border border-white/10">
            <ChevronLeft class="h-5 w-5" />
        </button>
        <button @click="nextSlide" class="absolute right-4 top-1/2 -translate-y-1/2 z-10 flex h-12 w-12 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm text-white hover:bg-white/40 transition border border-white/10">
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
        <div class="mx-auto w-full max-w-[1400px] px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-6">
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
        <div class="mx-auto w-full max-w-[1400px] px-6">
            <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                <div v-for="stat in stats" :key="stat.label" class="text-center">
                    <p class="text-3xl font-bold text-white">{{ stat.value }}</p>
                    <p class="text-sm text-blue-200 mt-1">{{ stat.label }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Productos Destacados -->
    <section class="py-16 bg-gray-50">
        <div class="mx-auto w-full max-w-[1400px] px-6">
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

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="p in destacados"
                    :key="p.id"
                    class="group bg-white rounded-xl border border-gray-200 overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1"
                >
                    <Link :href="`/productos/${p.slug}`" class="block relative aspect-[4/3] overflow-hidden bg-gray-100">
                        <img
                            :src="p.imagen || defaultImg"
                            :alt="p.nombre"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                        <span v-if="p.categoria" class="absolute top-3 left-3 inline-flex items-center rounded-full bg-white/90 backdrop-blur-sm px-2.5 py-1 text-[10px] font-medium text-blue-600 shadow-sm">
                            {{ p.categoria.nombre }}
                        </span>
                    </Link>

                    <div class="p-5">
                        <Link :href="`/productos/${p.slug}`">
                            <h3 class="text-sm font-semibold text-gray-900 leading-snug line-clamp-2 group-hover:text-blue-600 transition-colors">
                                {{ p.nombre }}
                            </h3>
                            <p class="mt-1.5 text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                {{ p.descripcion || 'Sin descripción disponible' }}
                            </p>
                        </Link>

                        <div class="mt-4 flex items-end justify-between gap-2">
                            <div>
                                <span class="text-lg font-bold text-gray-900">
                                    ${{ parseFloat(p.precio).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}
                                </span>
                            </div>
                            <button
                                @click="addToCart({ name: p.nombre, price: p.precio, slug: p.slug })"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white transition-all hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/25 active:scale-95"
                            >
                                <ShoppingCart class="h-3.5 w-3.5" />
                                Cotizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 text-center sm:hidden">
                <Link
                    href="/productos"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-600 hover:text-white hover:border-blue-600"
                >
                    Ver catálogo completo
                    <ChevronRight class="h-4 w-4" />
                </Link>
            </div>
        </div>
    </section>

    <!-- Categorías / Soluciones -->
    <section class="py-16 bg-white">
        <div class="mx-auto w-full max-w-[1400px] px-6">
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3.5 py-1 text-xs font-semibold text-blue-700">
                    <ShieldCheck class="h-3 w-3" /> Soluciones
                </div>
                <h2 class="mt-3 text-2xl font-bold text-gray-900">Soluciones completas</h2>
                <p class="mt-1 text-sm text-gray-500">Todo lo que necesitas para tu sistema de seguridad</p>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="f in features"
                    :key="f.titulo"
                    href="/productos"
                    class="group rounded-xl border border-gray-200 bg-white p-6 transition-all duration-300 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-500/5 hover:-translate-y-1"
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
        <div class="relative z-10 mx-auto w-full max-w-[1400px] px-6 text-center">
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

    <!-- Floating Cart -->
    <div class="fixed bottom-20 lg:bottom-6 right-6 z-50 flex flex-col items-end gap-3">
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 scale-75 translate-y-4"
            leave-active-class="transition-all duration-200 ease-in"
            leave-to-class="opacity-0 scale-75 translate-y-4"
        >
            <div
                v-if="isCartOpen"
                class="mb-2 w-72 rounded-xl border border-gray-200 bg-white shadow-2xl shadow-blue-500/10 overflow-hidden"
            >
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                    <span class="text-sm font-semibold text-gray-900">Mi cotización</span>
                    <button @click="isCartOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div class="max-h-60 overflow-y-auto px-4 py-2">
                    <div v-for="(item, idx) in cartItems" :key="idx" class="flex items-center justify-between py-2.5 border-b border-gray-100 last:border-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 truncate font-medium">{{ item.name }}</p>
                            <p class="text-xs text-blue-600 font-semibold">${{ parseFloat(item.price).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</p>
                        </div>
                        <button @click="removeFromCart(idx)" class="ml-2 shrink-0 text-gray-400 hover:text-red-500 transition">
                            <X class="h-3.5 w-3.5" />
                        </button>
                    </div>
                    <p v-if="cartItems.length === 0" class="py-4 text-center text-sm text-gray-400">Carrito vacío</p>
                </div>
                <div class="border-t border-gray-100 px-4 py-3" v-if="cartItems.length > 0">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs text-gray-500">{{ cartItems.length }} producto(s)</span>
                        <span class="text-sm font-bold text-blue-600">${{ totalCarrito }}</span>
                    </div>
                    <button
                        @click="cotizarWhatsApp(cartItems)"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500 hover:shadow-lg hover:shadow-emerald-500/25"
                    >
                        <MessageCircle class="h-4 w-4" />
                        Cotizar por WhatsApp
                    </button>
                </div>
            </div>
        </Transition>

        <button
            @click="isCartOpen = !isCartOpen"
            class="group relative flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-lg shadow-blue-500/25 transition-all hover:shadow-xl hover:shadow-blue-500/40 hover:scale-110 active:scale-95"
            v-if="cartItems.length > 0"
        >
            <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-lg">
                {{ cartItems.length }}
            </span>
            <ShoppingCart class="h-5 w-5 relative z-10" />
        </button>
    </div>
</template>
