<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { Search, Heart, User, ShoppingCart, Menu, X, ChevronDown, MapPin, Clock, Phone, Mail, MessageCircle, ShieldCheck, Sparkles, Percent, Package, Building2, Layers, AlertCircle, Minus, Plus, Trash2, CreditCard } from '@lucide/vue';
import { computed, ref, onMounted } from 'vue';
import type { ComputedRef } from 'vue';
import CheckoutModal from '@/components/CheckoutModal.vue';
import { useCart } from '@/composables/useCart';
import type { CartItem } from '@/composables/useCart';
import { useFavorites } from '@/composables/useFavorites';
import type { FavoriteItem } from '@/composables/useFavorites';
import { useMoney } from '@/composables/useMoney';
import { DEFAULT_PRODUCT_IMAGE as defaultImg } from '@/constants/images';

const whatsappNumber = '59164828148';

const isMobileMenuOpen = ref(false);
const searchQuery = ref('');

const page = usePage();
const currentRoute = computed(() => page.component ?? '');

const cart = useCart();
const favorites = useFavorites();
const money = useMoney();
const cartModalOpen = computed(() => cart.isOpen.value);
const favoritesModalOpen = computed(() => favorites.isOpen.value);

function openCartModal() {
    cart.open();
}

function closeCartModal() {
    cart.close();
}

function openCheckoutModal() {
    if (cartItems.value.length === 0) {
        return;
    }

    closeCartModal();
    checkoutOpen.value = true;
}

function openFavoritesModal() {
    favorites.open();
}

function closeFavoritesModal() {
    favorites.close();
}

onMounted(() => {
    cart.close();
    favorites.close();
});

// TypeScript helpers for template type checking (Vue auto-unwraps refs in templates)
const cartItems = computed(() => cart.items.value) as ComputedRef<CartItem[]>;
const cartCount = computed(() => cart.count.value) as ComputedRef<number>;
const cartTotal = computed(() => cart.total.value) as ComputedRef<number>;
const favoritesItems = computed(() => favorites.items.value) as ComputedRef<FavoriteItem[]>;
const favoritesCount = computed(() => favorites.count.value) as ComputedRef<number>;

const checkoutModal = ref(null);
const checkoutOpen = ref(false);
const menuCategorias = ref<{ id: number; nombre: string; slug: string; productos_count: number }[]>([]);
const menuMarcas = ref<{ id: number; nombre: string; slug: string; logo: string | null; productos_count: number }[]>([]);
const menuLoading = ref(true);
const activeMegaMenu = ref<string | null>(null);
const categoriasSidebarOpen = ref(false);
onMounted(async () => {
    try {
        const res = await fetch('/menu-data');
        const data = await res.json();
        menuCategorias.value = data.categorias ?? [];
        menuMarcas.value = data.marcas ?? [];
    } catch {
        menuCategorias.value = [];
        menuMarcas.value = [];
    } finally {
        menuLoading.value = false;
    }
});

function showMegaMenu(label: string) {
    activeMegaMenu.value = label;
}

function hideMegaMenu() {
    activeMegaMenu.value = null;
}

function openCategoriasSidebar() {
    categoriasSidebarOpen.value = true;
    document.body.style.overflow = 'hidden';
}

function closeCategoriasSidebar() {
    categoriasSidebarOpen.value = false;
    document.body.style.overflow = '';
}

interface NavLink {
    label: string;
    href: string;
    icon: any;
    mega?: boolean;
}

const navLinks: NavLink[] = [
    { label: 'Categorías', href: '/productos', icon: Menu },
    { label: 'Marcas', href: '/productos', icon: null, mega: true },
    { label: 'Novedades', href: '/novedades', icon: Sparkles },
    { label: 'Ofertas', href: '/ofertas', icon: Percent },
    { label: 'Catálogo', href: '/productos', icon: Layers },
    { label: 'Servicios', href: '/servicios', icon: null },
    { label: 'Contactos', href: '/contacto', icon: null },
];
const mobileNavLinks = navLinks.filter((link) => link.label !== 'Categorías');

function handleSearch() {
    if (searchQuery.value.trim()) {
        router.get('/productos', { search: searchQuery.value.trim() });
        searchQuery.value = '';
    }
}

function cotizarWhatsApp() {
    if (cartItems.value.length === 0) {
        return;
    }

    let message = 'Hola, me gustaría cotizar los siguientes productos:%0A%0A';

    cartItems.value.forEach((item, index) => {
        message += `${index + 1}. ${item.nombre} - Cantidad: ${item.cantidad} - Precio: ${money.format(item.precio)}%0A`;
    });

    message += `%0ATotal: ${money.format(cartTotal.value)}%0A%0A`;
    message += 'Quedo a la espera de su respuesta. Gracias.';

    const url = `https://wa.me/${whatsappNumber}?text=${message}`;
    window.open(url, '_blank');
}
</script>

<template>
    <div class="min-h-screen bg-white text-gray-900">
        <!-- Top Bar -->
        <div class="hidden lg:flex h-[35px] items-center bg-[#0A1F5D] text-white text-[11px]">
            <div class="mx-auto flex w-full max-w-[1400px] items-center justify-between px-4 sm:px-6">
                <div class="flex items-center gap-5">
                    <span class="flex items-center gap-1.5">
                        <MapPin class="h-3 w-3" />
                        Villa Tunari, Cochabamba
                    </span>
                    <span class="flex items-center gap-1.5">
                        <Clock class="h-3 w-3" />
                        Lun - Sáb 08:00 - 19:00
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="tel:+59164828148" class="flex items-center gap-1.5 hover:text-blue-200 transition">
                        <Phone class="h-3 w-3" />
                        +591 64828148
                    </a>
                    <a href="mailto:penarandacarbajaljuan@gmail.com" class="hidden items-center gap-1.5 transition hover:text-blue-200 xl:flex">
                        <Mail class="h-3 w-3" />
                        penarandacarbajaljuan@gmail.com
                    </a>
                    <div class="flex items-center gap-2 pl-2 border-l border-white/20">
                        <a href="#" target="_blank" class="hover:opacity-80 transition" aria-label="Facebook">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a :href="`https://wa.me/${whatsappNumber}`" target="_blank" class="hover:opacity-80 transition" aria-label="WhatsApp">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        <a href="#" target="_blank" class="hover:opacity-80 transition" aria-label="Instagram">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <header class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
            <div class="mx-auto flex h-[70px] w-full max-w-[1400px] items-center justify-between gap-2 px-3 sm:gap-4 sm:px-4 lg:h-20 lg:px-6">
                <!-- Logo -->
                <Link href="/" class="flex shrink-0 items-center gap-2 sm:gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-blue-500/20 sm:h-9 sm:w-9">
                        <ShieldCheck class="h-4 w-4 text-white sm:h-5 sm:w-5" />
                    </div>
                    <span class="text-base font-bold tracking-tight text-gray-900 sm:text-lg">DOMOTECH</span>
                </Link>

                <!-- Search Bar - Desktop -->
                <div class="hidden md:flex flex-1 max-w-lg lg:max-w-[50%]">
                    <form @submit.prevent="handleSearch" class="relative w-full">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar productos..."
                            class="w-full rounded-full border border-gray-300 bg-gray-50 py-2.5 pl-11 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                        />
                    </form>
                </div>

                <!-- Icons -->
                <div class="flex shrink-0 items-center gap-0.5 sm:gap-1 lg:gap-3">
                    <Link href="/login" class="flex h-9 w-8 flex-col items-center justify-center gap-0.5 rounded-lg px-1 py-1 text-gray-600 transition hover:bg-blue-50 hover:text-blue-600 sm:h-auto sm:w-auto sm:px-2">
                        <User class="h-5 w-5" />
                        <span class="hidden text-[10px] font-medium min-[375px]:inline">Usuario</span>
                    </Link>
                    <button @click="openFavoritesModal()" class="relative flex h-9 w-8 flex-col items-center justify-center gap-0.5 rounded-lg px-1 py-1 text-gray-600 transition hover:bg-red-50 hover:text-red-600 sm:h-auto sm:w-auto sm:px-2">
                        <Heart class="h-5 w-5" />
                        <span class="hidden text-[10px] font-medium min-[375px]:inline">Favoritos</span>
                        <span v-if="favoritesCount > 0" class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white shadow-sm">
                            {{ favoritesCount > 9 ? '9+' : favoritesCount }}
                        </span>
                    </button>
                    <button @click="openCartModal()" class="relative flex h-9 w-8 flex-col items-center justify-center gap-0.5 rounded-lg px-1 py-1 text-gray-600 transition hover:bg-blue-50 hover:text-blue-600 sm:h-auto sm:w-auto sm:px-2">
                        <ShoppingCart class="h-5 w-5" />
                        <span class="hidden text-[10px] font-medium min-[375px]:inline">Carrito</span>
                        <span v-if="cartCount > 0" class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white shadow-sm">
                            {{ cartCount > 9 ? '9+' : cartCount }}
                        </span>
                    </button>

                    <!-- Mobile Menu Toggle -->
                    <button
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                        :aria-expanded="isMobileMenuOpen"
                        aria-label="Abrir menú de navegación"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-600 transition hover:bg-blue-50 hover:text-blue-600 lg:hidden"
                    >
                        <Menu v-if="!isMobileMenuOpen" class="h-5 w-5" />
                        <X v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Mobile Search -->
            <div class="md:hidden px-4 pb-3">
                <form @submit.prevent="handleSearch" class="relative">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Buscar productos..."
                        class="w-full rounded-full border border-gray-300 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                    />
                </form>
            </div>
        </header>

        <!-- Navigation Menu -->
        <nav class="hidden lg:block bg-gradient-to-r from-[#0D6EFD] to-[#0066CC]" @mouseleave="hideMegaMenu">
            <div class="mx-auto flex h-[55px] w-full max-w-[1400px] items-center px-4 sm:px-6">
                <div class="flex items-center h-full">
                    <template v-for="link in navLinks" :key="link.label">
                        <!-- Categorías: Sidebar Button -->
                        <button
                            v-if="link.label === 'Categorías'"
                            @click="openCategoriasSidebar"
                            class="group relative flex items-center gap-2 h-full px-5 text-sm font-semibold text-white/90 transition-all duration-200 hover:bg-white/10 hover:text-white"
                        >
                            <component :is="link.icon" class="h-4 w-4" />
                            <span class="flex items-center gap-1 h-full relative">
                                {{ link.label }}
                                <ChevronDown class="h-3 w-3 transition-transform duration-200" />
                                <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-white scale-x-0 group-hover:scale-x-100 transition-transform duration-200" />
                            </span>
                        </button>

                        <!-- Marcas: Mega Menu -->
                        <div
                            v-else-if="link.mega"
                            class="relative h-full"
                            @mouseenter="showMegaMenu(link.label)"
                        >
                            <button
                                class="group relative flex items-center gap-2 h-full px-5 text-sm font-semibold text-white/90 transition-all duration-200 hover:bg-white/10 hover:text-white"
                                :class="{ 'bg-white/10 text-white': activeMegaMenu === link.label }"
                            >
                                <component :is="link.icon" v-if="link.icon" class="h-4 w-4" />
                                <span class="flex items-center gap-1 h-full relative">
                                    {{ link.label }}
                                    <ChevronDown class="h-3 w-3 transition-transform duration-200" :class="{ 'rotate-180': activeMegaMenu === link.label }" />
                                    <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-white scale-x-0 group-hover:scale-x-100 transition-transform duration-200" />
                                </span>
                            </button>

                            <!-- Mega Menu -->
                            <Transition
                                enter-active-class="transition-all duration-200 ease-out"
                                enter-from-class="opacity-0 translate-y-2"
                                leave-active-class="transition-all duration-150 ease-in"
                                leave-to-class="opacity-0 translate-y-2"
                            >
                                <div
                                    v-if="activeMegaMenu === link.label"
                                    class="absolute top-full left-0 z-50 mt-0 w-[600px] rounded-b-2xl bg-white shadow-2xl shadow-black/10 border border-gray-100 overflow-hidden"
                                >
                                    <!-- Marcas Mega Menu -->
                                    <div v-if="link.label === 'Marcas'" class="p-6">
                                        <div class="flex items-center gap-2 mb-4">
                                            <Building2 class="h-5 w-5 text-blue-600" />
                                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Marcas</h3>
                                        </div>
                                        <div v-if="menuLoading && menuMarcas.length === 0" class="flex items-center justify-center py-8 text-sm text-gray-400">
                                            <div class="flex items-center gap-2">
                                                <div class="h-5 w-5 animate-spin rounded-full border-2 border-blue-600 border-t-transparent" />
                                                Cargando...
                                            </div>
                                        </div>
                                        <div v-else-if="menuMarcas.length === 0" class="flex flex-col items-center py-8 text-sm text-gray-400">
                                            <AlertCircle class="h-8 w-8 text-gray-300 mb-2" />
                                            <p>No hay marcas registradas</p>
                                        </div>
                                        <div v-else class="grid grid-cols-2 gap-2">
                                            <Link
                                                v-for="marca in menuMarcas"
                                                :key="marca.id"
                                                :href="`/productos?marca=${marca.id}`"
                                                @click="hideMegaMenu"
                                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition group"
                                            >
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 font-bold text-xs uppercase">
                                                    {{ marca.logo ? '' : marca.nombre.substring(0, 2) }}
                                                    <img v-if="marca.logo" :src="marca.logo" :alt="marca.nombre" class="h-full w-full object-contain rounded-lg" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium truncate">{{ marca.nombre }}</p>
                                                </div>
                                                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full group-hover:bg-blue-100 group-hover:text-blue-600 transition shrink-0">
                                                    {{ marca.productos_count }}
                                                </span>
                                            </Link>
                                        </div>
                                        <div v-if="menuMarcas.length > 0" class="mt-4 pt-4 border-t border-gray-100">
                                            <Link
                                                href="/productos"
                                                @click="hideMegaMenu"
                                                class="flex items-center justify-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-700 transition"
                                            >
                                                Ver todas las marcas
                                                <ChevronDown class="h-4 w-4 -rotate-90" />
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <Link
                            v-else
                            :href="link.href"
                            class="group relative flex items-center gap-2 h-full px-5 text-sm font-semibold text-white/90 transition-all duration-200 hover:bg-white/10 hover:text-white"
                        >
                            <component :is="link.icon" v-if="link.icon" class="h-4 w-4" />
                            <span class="flex items-center gap-1 h-full relative">
                                {{ link.label }}
                                <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-white scale-x-0 group-hover:scale-x-100 transition-transform duration-200" />
                            </span>
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Categorías Sidebar -->
        <Transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-to-class="opacity-0"
        >
            <div v-if="categoriasSidebarOpen" class="fixed inset-0 z-[60] bg-black/50" @click="closeCategoriasSidebar" />
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="-translate-x-full"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-to-class="-translate-x-full"
        >
            <aside v-if="categoriasSidebarOpen" class="fixed left-0 top-0 z-[61] flex h-dvh w-[min(320px,calc(100vw-2.5rem))] flex-col bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <Package class="h-5 w-5 text-blue-600" />
                        <h3 class="text-base font-bold text-gray-900">Categorías</h3>
                    </div>
                    <button @click="closeCategoriasSidebar" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-4">
                    <div v-if="menuLoading && menuCategorias.length === 0" class="flex items-center justify-center py-8 text-sm text-gray-400">
                        <div class="flex items-center gap-2">
                            <div class="h-5 w-5 animate-spin rounded-full border-2 border-blue-600 border-t-transparent" />
                            Cargando...
                        </div>
                    </div>
                    <div v-else-if="menuCategorias.length === 0" class="flex flex-col items-center py-8 text-sm text-gray-400">
                        <AlertCircle class="h-8 w-8 text-gray-300 mb-2" />
                        <p>No hay categorías registradas</p>
                    </div>
                    <div v-else class="space-y-1">
                        <Link
                            v-for="cat in menuCategorias"
                            :key="cat.id"
                            :href="`/productos?categoria=${cat.id}`"
                            @click="closeCategoriasSidebar"
                            class="flex items-center justify-between rounded-lg px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition group"
                        >
                            <span class="font-medium truncate">{{ cat.nombre }}</span>
                            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full group-hover:bg-blue-100 group-hover:text-blue-600 transition shrink-0">
                                {{ cat.productos_count }}
                            </span>
                        </Link>
                    </div>
                    <div v-if="menuCategorias.length > 0" class="mt-4 pt-4 border-t border-gray-100">
                        <Link
                            href="/productos"
                            @click="closeCategoriasSidebar"
                            class="flex items-center justify-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-700 transition"
                        >
                            Ver todas las categorías
                            <ChevronDown class="h-4 w-4 -rotate-90" />
                        </Link>
                    </div>
                </div>
            </aside>
        </Transition>

        <!-- Mobile Menu -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-4"
            leave-active-class="transition-all duration-200 ease-in"
            leave-to-class="opacity-0 -translate-y-4"
        >
            <div v-if="isMobileMenuOpen" class="fixed inset-x-0 top-[119px] z-40 max-h-[calc(100dvh-8rem)] overflow-y-auto border-t border-gray-100 bg-white shadow-xl md:top-[70px] md:max-h-[calc(100dvh-5rem)] lg:hidden">
                <div class="px-4 py-4 space-y-1">
                    <!-- Categorías: Sidebar Button -->
                    <button
                        @click="openCategoriasSidebar; isMobileMenuOpen = false"
                        class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition"
                    >
                        <Menu class="h-4 w-4" />
                        Categorías
                        <ChevronDown class="ml-auto h-4 w-4 text-gray-400" />
                    </button>

                    <Link
                        v-for="link in mobileNavLinks"
                        :key="link.label"
                        :href="link.href"
                        @click="isMobileMenuOpen = false"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition"
                    >
                        <component :is="link.icon" v-if="link.icon" class="h-4 w-4" />
                        {{ link.label }}
                    </Link>
                    <hr class="my-3 border-gray-200" />
                    <div class="px-4 py-2 space-y-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Contacto</p>
                        <a href="tel:+59164828148" class="flex items-center gap-3 text-sm text-gray-600 hover:text-blue-600">
                            <Phone class="h-4 w-4" /> +591 64828148
                        </a>
                        <a href="mailto:penarandacarbajaljuan@gmail.com" class="flex items-center gap-3 text-sm text-gray-600 hover:text-blue-600">
                            <Mail class="h-4 w-4" /> penarandacarbajaljuan@gmail.com
                        </a>
                        <div class="flex items-center gap-2 pt-1">
                            <a href="#" class="p-2 rounded-lg bg-gray-100 text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a :href="`https://wa.me/${whatsappNumber}`" class="p-2 rounded-lg bg-gray-100 text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 transition">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                            <a href="#" class="p-2 rounded-lg bg-gray-100 text-gray-600 hover:text-pink-600 hover:bg-pink-50 transition">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Page Content -->
        <main class="pb-16 lg:pb-0">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 pb-16 text-gray-300 lg:pb-0">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 py-14">
                <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-blue-500/20">
                                <ShieldCheck class="h-5 w-5 text-white" />
                            </div>
                            <span class="text-lg font-bold text-white">DOMOTECH</span>
                        </div>
                        <p class="mt-4 text-sm text-gray-400 leading-relaxed">
                            Tu tienda tecnológica de confianza. Equipos de seguridad, videovigilancia, control de acceso y más.
                        </p>
                        <div class="flex items-center gap-3 mt-5">
                            <a href="#" class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-800 text-gray-400 hover:bg-blue-600 hover:text-white transition">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a :href="`https://wa.me/${whatsappNumber}`" class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-800 text-gray-400 hover:bg-emerald-600 hover:text-white transition">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                            <a href="#" class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-800 text-gray-400 hover:bg-pink-600 hover:text-white transition">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                            </a>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Categorías</h4>
                        <div class="space-y-2.5">
                            <Link href="/productos" class="block text-sm text-gray-400 hover:text-blue-400 transition">Cámaras de Seguridad</Link>
                            <Link href="/productos" class="block text-sm text-gray-400 hover:text-blue-400 transition">DVR / NVR</Link>
                            <Link href="/productos" class="block text-sm text-gray-400 hover:text-blue-400 transition">Alarmas</Link>
                            <Link href="/productos" class="block text-sm text-gray-400 hover:text-blue-400 transition">Control de Acceso</Link>
                            <Link href="/productos" class="block text-sm text-gray-400 hover:text-blue-400 transition">Sensores</Link>
                            <Link href="/servicios" class="block text-sm text-gray-400 hover:text-blue-400 transition">Servicios</Link>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Atención al Cliente</h4>
                        <div class="space-y-2.5">
                            <Link href="/contacto" class="block text-sm text-gray-400 hover:text-blue-400 transition">Contacto</Link>
                            <Link href="/nosotros" class="block text-sm text-gray-400 hover:text-blue-400 transition">Nosotros</Link>
                            <a href="#" class="block text-sm text-gray-400 hover:text-blue-400 transition">Términos y Condiciones</a>
                            <a href="#" class="block text-sm text-gray-400 hover:text-blue-400 transition">Política de Privacidad</a>
                            <a href="#" class="block text-sm text-gray-400 hover:text-blue-400 transition">Cambios y Devoluciones</a>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Contacto</h4>
                        <div class="space-y-3 text-sm text-gray-400">
                            <div class="flex items-start gap-2.5">
                                <MapPin class="h-4 w-4 text-blue-400 mt-0.5 shrink-0" />
                                <span>Villa Tunari, Cochabamba, Bolivia</span>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <Phone class="h-4 w-4 text-blue-400 mt-0.5 shrink-0" />
                                <span>+591 64828148</span>
                            </div>
                            <div class="flex min-w-0 items-start gap-2.5">
                                <Mail class="h-4 w-4 text-blue-400 mt-0.5 shrink-0" />
                                <span class="min-w-0 break-words">penarandacarbajaljuan@gmail.com</span>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <Clock class="h-4 w-4 text-blue-400 mt-0.5 shrink-0" />
                                <span>Lun - Sáb: 08:00 - 19:00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 py-6">
                <div class="mx-auto flex w-full max-w-[1400px] flex-col sm:flex-row items-center justify-between gap-2 px-4 sm:px-6 text-sm text-gray-500">
                    <span>&copy; {{ new Date().getFullYear() }} DOMOTECH. Todos los derechos reservados.</span>
                    <span>Tecnología y Seguridad Profesional</span>
                </div>
            </div>
        </footer>

        <!-- Floating WhatsApp -->
        <a
            :href="`https://wa.me/${whatsappNumber}`"
            target="_blank"
            rel="noopener noreferrer"
            class="fixed bottom-24 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/25 transition-all hover:shadow-xl hover:shadow-emerald-500/40 hover:scale-110 active:scale-95 lg:bottom-6"
            title="Contáctanos por WhatsApp"
        >
            <span class="absolute inset-0 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 animate-ping opacity-30" />
            <MessageCircle class="h-7 w-7 relative z-10" />
        </a>

        <!-- Cart Modal -->
        <Transition name="cart-modal">
            <div v-if="cartModalOpen" class="fixed inset-0 z-[70]" @click.self="closeCartModal()">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" />

                <!-- Modal Container - centered with space around -->
                <div class="flex h-[100dvh] items-center justify-center p-2 sm:p-4">
                    <div class="relative flex max-h-[calc(100dvh-1rem)] w-full max-w-[650px] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl animate-slide-up">

                        <!-- Header (Sticky) -->
                        <header class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 bg-white/95 backdrop-blur-sm flex-shrink-0 sticky top-0 z-10">
                            <h3 class="flex items-center gap-2 text-lg font-bold text-gray-900">
                                <ShoppingCart class="h-5 w-5 text-blue-600" />
                                Mi cotización
                                <span v-if="cartCount > 0" class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-600">{{ cartCount }}</span>
                            </h3>
                            <button @click="closeCartModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 -ml-1 -mr-1" aria-label="Cerrar">
                                <X class="h-6 w-6" />
                            </button>
                        </header>

                        <!-- Content (Scrollable) -->
                        <main class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                            <div v-if="cartItems.length === 0" class="flex flex-col items-center justify-center gap-3 text-center py-12">
                                <ShoppingCart class="h-12 w-12 text-gray-300" />
                                <p class="text-base font-medium text-gray-500">Tu carrito está vacío</p>
                                <p class="text-sm text-gray-400">Agrega productos para cotizar</p>
                            </div>

                            <div v-else class="space-y-3">
                                <div v-for="item in cartItems" :key="String(item.id)" class="flex gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <Link :href="`/productos/${item.slug}`" class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-gray-100" @click="closeCartModal()">
                                        <img :src="item.imagen || defaultImg" :alt="item.nombre" class="h-full w-full object-cover" />
                                    </Link>
                                    <div class="flex-1 min-w-0">
                                        <Link :href="`/productos/${item.slug}`" class="truncate block text-sm font-medium text-gray-900 hover:text-blue-600 transition" @click="closeCartModal()">
                                            {{ item.nombre }}
                                        </Link>
                                        <p class="mt-0.5 text-sm font-bold text-blue-600">
                                            {{ money.format(item.precio) }}
                                        </p>
                                        <div class="mt-2 flex items-center gap-1.5">
                                            <button
                                                type="button"
                                                @click="cart.setQuantity(item.id, item.cantidad - 1)"
                                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-blue-400 hover:text-blue-600 hover:bg-gray-100"
                                                aria-label="Disminuir cantidad"
                                            >
                                                <Minus class="h-3 w-3" />
                                            </button>
                                            <span class="w-8 text-center text-xs font-semibold text-gray-900">{{ item.cantidad }}</span>
                                            <button
                                                type="button"
                                                @click="cart.setQuantity(item.id, item.cantidad + 1)"
                                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-blue-400 hover:text-blue-600 hover:bg-gray-100"
                                                aria-label="Aumentar cantidad"
                                            >
                                                <Plus class="h-3 w-3" />
                                            </button>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="cart.remove(item.id)"
                                        class="self-start rounded-lg p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                                        aria-label="Eliminar del carrito"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </main>

                        <!-- Footer Actions (Sticky) -->
                        <footer v-if="cartItems.length > 0" class="flex flex-col gap-3 border-t border-gray-100 px-4 sm:px-6 py-3 sm:py-4 bg-white/95 backdrop-blur-sm flex-shrink-0 sticky bottom-0 z-10">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">{{ cartCount }} producto(s)</span>
                                <span class="font-bold text-gray-900">{{ money.format(cartTotal) }}</span>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3">
                                <button
                                    type="button"
                                    @click="cotizarWhatsApp"
                                    class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500 hover:shadow-lg hover:shadow-emerald-500/25"
                                >
                                    <MessageCircle class="h-4 w-4" />
                                    Cotizar por WhatsApp
                                </button>
                                <button
                                    type="button"
                                    @click="openCheckoutModal"
                                    class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500 hover:shadow-lg hover:shadow-blue-500/25"
                                >
                                    <CreditCard class="h-4 w-4" />
                                    Pagar
                                </button>
                            </div>

                            <button
                                type="button"
                                @click="cart.clear()"
                                class="text-center text-xs text-gray-400 transition hover:text-red-500"
                            >
                                Vaciar carrito
                            </button>
                        </footer>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Favorites Modal -->
        <Transition name="favorites-modal">
            <div v-if="favoritesModalOpen" class="fixed inset-0 z-[70]" @click.self="closeFavoritesModal()">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" />

                <div class="flex h-[100dvh] items-center justify-center p-2 sm:p-4">
                    <div class="relative flex max-h-[calc(100dvh-1rem)] w-full max-w-[650px] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl animate-slide-up">

                        <header class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 bg-white/95 backdrop-blur-sm flex-shrink-0 sticky top-0 z-10">
                            <h3 class="flex items-center gap-2 text-lg font-bold text-gray-900">
                                <Heart class="h-5 w-5 text-red-600" />
Mis favoritos
                            <span v-if="favoritesCount > 0" class="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-600">{{ favoritesCount }}</span>
                        </h3>
                            <button @click="closeFavoritesModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 -ml-1 -mr-1" aria-label="Cerrar">
                                <X class="h-6 w-6" />
                            </button>
                        </header>

                        <main class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                            <div v-if="favoritesItems.length === 0" class="flex flex-col items-center justify-center gap-3 text-center py-12">
                                <Heart class="h-12 w-12 text-gray-300" />
                                <p class="text-base font-medium text-gray-500">No tienes favoritos</p>
                                <p class="text-sm text-gray-400">Guarda productos que te interesen</p>
                            </div>

                            <div v-else class="space-y-3">
                                <div v-for="item in favoritesItems" :key="String(item.id)" class="flex gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <Link :href="`/productos/${item.slug}`" class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-gray-100" @click="closeFavoritesModal()">
                                        <img :src="item.imagen || defaultImg" :alt="item.nombre" class="h-full w-full object-cover" />
                                    </Link>
                                    <div class="flex-1 min-w-0">
                                        <Link :href="`/productos/${item.slug}`" class="truncate block text-sm font-medium text-gray-900 hover:text-blue-600 transition" @click="closeFavoritesModal()">
                                            {{ item.nombre }}
                                        </Link>
                                        <p class="mt-0.5 text-sm font-bold text-blue-600">
                                            {{ money.format(item.precio) }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="favorites.remove(item.id)"
                                        class="self-start rounded-lg p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                                        aria-label="Eliminar de favoritos"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </main>

                        <footer v-if="favoritesItems.length > 0" class="flex flex-col gap-3 border-t border-gray-100 px-4 sm:px-6 py-3 sm:py-4 bg-white/95 backdrop-blur-sm flex-shrink-0 sticky bottom-0 z-10">
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button
                                    type="button"
                                    @click="favorites.clear()"
                                    class="flex-1 flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-red-50 hover:text-red-600 hover:border-red-300"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    Vaciar favoritos
                                </button>
                            </div>
                        </footer>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Mobile Bottom Nav -->
        <nav class="fixed inset-x-0 bottom-0 z-50 flex min-h-14 items-center justify-around border-t border-gray-200 bg-white px-1 pt-1.5 pb-[max(0.375rem,env(safe-area-inset-bottom))] shadow-lg lg:hidden">
            <Link href="/" class="flex min-w-0 flex-1 flex-col items-center gap-0.5 px-1 py-1 text-gray-500 transition hover:text-blue-600" :class="{ 'text-blue-600': currentRoute === 'public/Home' }">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span class="text-[9px] font-medium">Inicio</span>
            </Link>
            <Link href="/productos" class="flex min-w-0 flex-1 flex-col items-center gap-0.5 px-1 py-1 text-gray-500 transition hover:text-blue-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                <span class="text-[9px] font-medium">Productos</span>
            </Link>
<Link href="/servicios" class="flex min-w-0 flex-1 flex-col items-center gap-0.5 px-1 py-1 text-gray-500 transition hover:text-blue-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span class="text-[9px] font-medium">Servicios</span>
            </Link>
            <Link href="/login" class="flex min-w-0 flex-1 flex-col items-center gap-0.5 px-1 py-1 text-gray-500 transition hover:text-blue-600">
                <User class="h-5 w-5" />
                <span class="text-[9px] font-medium">Usuario</span>
            </Link>
            <button @click="openFavoritesModal()" class="relative flex min-w-0 flex-1 flex-col items-center gap-0.5 px-1 py-1 text-gray-500 transition hover:text-red-600">
                <Heart class="h-5 w-5" />
                <span class="text-[9px] font-medium">Favoritos</span>
                <span v-if="favoritesCount > 0" class="absolute -top-0.5 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white shadow-sm">
                    {{ favoritesCount > 9 ? '9+' : favoritesCount }}
                </span>
            </button>
            <button @click="openCartModal()" class="relative flex min-w-0 flex-1 flex-col items-center gap-0.5 px-1 py-1 text-gray-500 transition hover:text-blue-600">
                <ShoppingCart class="h-5 w-5" />
                <span class="text-[9px] font-medium">Carrito</span>
                <span v-if="cartCount > 0" class="absolute -top-0.5 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white shadow-sm">
                    {{ cartCount > 9 ? '9+' : cartCount }}
                </span>
            </button>
        </nav>

        <!-- Checkout Modal -->
        <CheckoutModal ref="checkoutModal" :isOpen="checkoutOpen" @close="checkoutOpen = false" @backToCart="checkoutOpen = false; cart.open()" />

    </div>
</template>

<style scoped>
.cart-modal-enter-active,
.cart-modal-leave-active {
    transition: all 0.3s ease-out;
}
.cart-modal-enter-from,
.cart-modal-leave-to {
    opacity: 0;
}
.cart-modal-enter-from > div > div,
.cart-modal-leave-to > div > div {
    transform: scale(0.95) translateY(20px);
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.animate-slide-up {
    animation: slideUp 0.3s ease-out;
}

.favorites-modal-enter-active,
.favorites-modal-leave-active {
    transition: all 0.3s ease-out;
}
.favorites-modal-enter-from,
.favorites-modal-leave-to {
    opacity: 0;
}
.favorites-modal-enter-from > div > div,
.favorites-modal-leave-to > div > div {
    transform: scale(0.95) translateY(20px);
}

/* Thin scrollbars */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Focus visible for accessibility */
button:focus-visible,
input:focus-visible,
textarea:focus-visible,
select:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
</style>
