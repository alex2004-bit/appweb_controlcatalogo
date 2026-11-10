<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ShoppingCart,
    ShieldCheck,
    Camera,
    Wrench,
    MessageCircle,
    ChevronDown,
    Package,
    Building2,
    Users,
    HardDrive,
    Smartphone,
    BarChart3,
    Sparkles,
} from '@lucide/vue';

const whatsappNumber = '59164828148';

function addToCart(item: { nombre: string; precio: string }) {
    const message = `Hola, quisiera cotizar este producto: ${item.nombre} (${item.precio}).`;
    window.open(`https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`, '_blank');
}

function cotizarTodo() {
    const products = productosDestacados
        .map((product) => `- ${product.nombre} (${product.precio})`)
        .join('\n');
    const message = `Hola, quisiera cotizar estos productos:\n${products}`;

    window.open(`https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`, '_blank');
}

const productosDestacados = [
    { nombre: 'Cámara 4K Ultra HD', desc: 'Resolución 3840x2160, visión nocturna, IP67', precio: 'Bs 4.299', categoria: 'Cámaras', img: 'https://images.unsplash.com/photo-1557324232-b8917d3c3dcb?q=80&w=1974&auto=format&fit=crop' },
    { nombre: 'DVR 16 Canales', desc: 'Grabación H.265+, 2 bahías HDD, remote access', precio: 'Bs 8.599', categoria: 'DVR/NVR', img: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?q=80&w=2034&auto=format&fit=crop' },
    { nombre: 'Sensor de Movimiento', desc: 'Detección por microondas + infrarrojo, 12m alcance', precio: 'Bs 599', categoria: 'Sensores', img: 'https://images.unsplash.com/photo-1558002038-1055907df827?q=80&w=2070&auto=format&fit=crop' },
    { nombre: 'Alarma Inalámbrica', desc: 'Panel LCD, sensor puerta/ventana, sirena 110dB', precio: 'Bs 2.899', categoria: 'Alarmas', img: 'https://images.unsplash.com/photo-1582139329536-e7284fece509?q=80&w=2080&auto=format&fit=crop' },
    { nombre: 'Control Biométrico', desc: 'Huella + tarjeta + PIN, 3000 usuarios, TCP/IP', precio: 'Bs 5.499', categoria: 'Control Acceso', img: 'https://images.unsplash.com/photo-1553034545-32d3fefc9f1f?q=80&w=1974&auto=format&fit=crop' },
    { nombre: 'Cámara PTZ', desc: 'Zoom 30x, auto tracking, 360° continuo, PoE+', precio: 'Bs 12.999', categoria: 'Cámaras', img: 'https://images.unsplash.com/photo-1586105449897-20b5ef3233f1?q=80&w=1974&auto=format&fit=crop' },
];

const stats = [
    { label: 'Productos', value: '200+', icon: Package, color: 'cyan' },
    { label: 'Servicios', value: '50+', icon: Wrench, color: 'amber' },
    { label: 'Clientes', value: '1,200+', icon: Users, color: 'emerald' },
    { label: 'Años', value: '10+', icon: Building2, color: 'blue' },
];

const features = [
    { titulo: 'Cámaras', desc: 'HD, 4K, PTZ, IP, análogas, térmicas', icon: Camera },
    { titulo: 'DVR/NVR', desc: 'Grabación y almacenamiento profesional', icon: HardDrive },
    { titulo: 'Alarmas', desc: 'Sistemas cableados e inalámbricos', icon: ShieldCheck },
    { titulo: 'Control Acceso', desc: 'Biométrico, tarjeta, cerraduras eléctricas', icon: Smartphone },
    { titulo: 'Sensores', desc: 'Movimiento, magnéticos, gas, humo', icon: BarChart3 },
    { titulo: 'Instalación', desc: 'Profesional certificada y soporte técnico', icon: Wrench },
];
</script>

<template>
    <Head title="DOMOTECH - Seguridad y Videovigilancia" />

    <div class="min-h-screen bg-zinc-950 text-zinc-100">
        <!-- Header -->
        <header class="fixed top-0 z-40 w-full border-b border-zinc-800/50 bg-zinc-950/80 backdrop-blur-xl">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                        <ShieldCheck class="h-5 w-5 text-zinc-950" />
                    </div>
                    <span class="text-lg font-semibold tracking-tight">DOMOTECH</span>
                </div>
                <nav class="hidden md:flex items-center gap-6 text-sm text-zinc-400">
                    <a href="#productos" class="hover:text-cyan-400 transition">Productos</a>
                    <a href="#servicios" class="hover:text-cyan-400 transition">Servicios</a>
                    <a href="#contacto" class="hover:text-cyan-400 transition">Contacto</a>
                </nav>
                <div class="flex items-center gap-3">
                    <Link
                        href="/login"
                        class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 px-4 py-2 text-sm font-medium text-zinc-300 transition hover:border-cyan-500 hover:text-cyan-400"
                    >
                        Iniciar sesión
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden pt-36 pb-28">
            <div class="absolute inset-0">
                <img
                    src="https://images.unsplash.com/photo-1558002038-1055907df827?q=80&w=2070&auto=format&fit=crop"
                    alt=""
                    class="absolute inset-0 w-full h-full object-cover"
                    aria-hidden="true"
                />
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-zinc-950/95 via-zinc-950/80 to-zinc-950/95" />
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(6,182,212,0.2)_0%,_transparent_60%)]" />
            <div class="pointer-events-none absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(circle, rgba(6,182,212,0.4) 1px, transparent 1px); background-size: 40px 40px;" />

            <div class="mx-auto max-w-7xl px-6 text-center relative">
                <div class="mx-auto inline-flex items-center gap-2 rounded-full border border-cyan-500/20 bg-cyan-500/10 px-4 py-1.5 text-sm text-cyan-400 backdrop-blur-sm">
                    <Sparkles class="h-3.5 w-3.5" />
                    Catálogo profesional de seguridad y videovigilancia
                </div>

                <h1 class="mt-8 text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl leading-tight">
                    Equipos de seguridad<br>
                    <span class="bg-gradient-to-r from-cyan-300 via-cyan-400 to-blue-500 bg-clip-text text-transparent">para proteger lo que importa</span>
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-zinc-400">
                    Explora nuestro catálogo de cámaras, alarmas, sensores, sistemas de control de acceso y servicios profesionales de instalación.
                </p>

                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a
                        href="#productos"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 px-8 py-3.5 text-base font-semibold text-zinc-950 shadow-lg shadow-cyan-500/25 transition-all hover:shadow-cyan-500/40 hover:scale-105"
                    >
                        Ver productos
                        <ChevronDown class="h-5 w-5" />
                    </a>
                    <button
                        @click="cotizarTodo"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-8 py-3.5 text-base font-medium text-white shadow-lg shadow-emerald-500/25 transition-all hover:bg-emerald-500 hover:shadow-emerald-500/40 hover:scale-105"
                    >
                        <MessageCircle class="h-5 w-5" />
                        Cotizar Todo por WhatsApp
                    </button>
                </div>
            </div>
        </section>

        <!-- Stats -->
        <section class="relative border-y border-zinc-800/50 py-12 overflow-hidden">
            <div class="absolute inset-0 opacity-[0.04]">
                <img
                    src="https://images.unsplash.com/photo-1558002038-1055907df827?q=80&w=2070&auto=format&fit=crop"
                    alt=""
                    class="absolute inset-0 w-full h-full object-cover"
                    aria-hidden="true"
                />
            </div>
            <div class="mx-auto max-w-7xl px-6 relative z-10">
                <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="text-center"
                    >
                        <component :is="stat.icon" class="mx-auto h-6 w-6 text-cyan-400" />
                        <p class="mt-2 text-2xl font-bold text-zinc-100">{{ stat.value }}</p>
                        <p class="text-xs text-zinc-500">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Productos Destacados -->
        <section id="productos" class="py-20">
            <div class="mx-auto max-w-7xl px-6">
                <div class="text-center mb-12">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-500/20 bg-cyan-500/10 px-3 py-1 text-xs text-cyan-400">
                        <Package class="h-3 w-3" /> Catálogo
                    </div>
                    <h2 class="mt-3 text-2xl font-bold">Productos Destacados</h2>
                    <p class="mt-1 text-sm text-zinc-500">Equipos de seguridad y videovigilancia</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(p, i) in productosDestacados"
                        :key="i"
                        class="group relative overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/30 p-5 transition-all duration-300 hover:border-cyan-500/50 hover:bg-zinc-900 hover:shadow-xl hover:shadow-cyan-500/5 hover:-translate-y-1"
                    >
                        <div class="aspect-video rounded-lg overflow-hidden mb-4 relative">
                            <img
                                :src="p.img"
                                :alt="p.nombre"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                loading="lazy"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/60 to-transparent" />
                        </div>

                        <p class="text-xs font-medium text-cyan-400">{{ p.categoria }}</p>
                        <h3 class="mt-1 text-sm font-semibold leading-tight">{{ p.nombre }}</h3>
                        <p class="mt-1.5 text-xs text-zinc-500 line-clamp-2">{{ p.desc }}</p>

                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-lg font-bold text-zinc-100">{{ p.precio }}</span>
                            <button
                                @click="addToCart({ nombre: p.nombre, precio: p.precio })"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-cyan-600/20 px-3 py-1.5 text-xs font-medium text-cyan-400 transition hover:bg-cyan-600 hover:text-white"
                            >
                                <ShoppingCart class="h-3.5 w-3.5" />
                                Agregar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="relative border-t border-zinc-800/50 py-20 overflow-hidden">
            <div class="absolute inset-0">
                <img
                    src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?q=80&w=2070&auto=format&fit=crop"
                    alt=""
                    class="absolute inset-0 w-full h-full object-cover opacity-[0.06]"
                    aria-hidden="true"
                />
            </div>
            <div class="relative z-10 mx-auto max-w-7xl px-6">
                <div class="text-center mb-12">
                    <h2 class="text-2xl font-bold">Soluciones completas</h2>
                    <p class="mt-2 text-sm text-zinc-500">Todo lo que necesitas para tu sistema de seguridad</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="f in features"
                        :key="f.titulo"
                        class="rounded-xl border border-zinc-800 bg-zinc-900/30 p-6 transition hover:border-cyan-500/30 hover:bg-zinc-900"
                    >
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500/20 to-blue-500/20 text-cyan-400">
                            <component :is="f.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold">{{ f.titulo }}</h3>
                        <p class="mt-2 text-sm text-zinc-500">{{ f.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact -->
        <section id="contacto" class="relative border-t border-zinc-800/50 py-20 overflow-hidden">
            <div class="absolute inset-0">
                <img
                    src="https://images.unsplash.com/photo-1582139329536-e7284fece509?q=80&w=2080&auto=format&fit=crop"
                    alt=""
                    class="absolute inset-0 w-full h-full object-cover opacity-[0.04]"
                    aria-hidden="true"
                />
            </div>
            <div class="relative z-10">
                <div class="mx-auto max-w-4xl px-6 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/25 mb-4">
                        <MessageCircle class="h-8 w-8 text-white" />
                    </div>
                    <h2 class="text-2xl font-bold">¿Necesitas una cotización?</h2>
                    <p class="mt-3 text-sm text-zinc-400 max-w-lg mx-auto">
                        Contáctanos por WhatsApp para recibir asesoría personalizada en la selección de equipos de seguridad.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <button
                            @click="cotizarTodo"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-3 text-sm font-medium text-white transition hover:bg-emerald-500 hover:shadow-lg hover:shadow-emerald-500/25"
                        >
                            <MessageCircle class="h-5 w-5" />
                            Cotizar Todo por WhatsApp
                        </button>
                        <a
                            :href="`https://wa.me/${whatsappNumber}`"
                            target="_blank"
                            class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 px-6 py-3 text-sm font-medium text-zinc-300 transition hover:border-emerald-500 hover:text-emerald-400"
                        >
                            <MessageCircle class="h-5 w-5" />
                            Chat Directo
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-zinc-800/50 py-8">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 text-sm text-zinc-600">
                <div class="flex items-center gap-2">
                    <ShieldCheck class="h-4 w-4" />
                    <span>DOMOTECH &mdash; Seguridad y Videovigilancia</span>
                </div>
                <span>&copy; {{ new Date().getFullYear() }} Todos los derechos reservados</span>
            </div>
        </footer>

        <!-- Floating WhatsApp -->
        <a
            :href="`https://wa.me/${whatsappNumber}`"
            target="_blank"
            class="fixed bottom-6 left-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/25 transition-all hover:shadow-xl hover:shadow-emerald-500/40 hover:scale-110 active:scale-95"
            title="Contáctanos por WhatsApp"
        >
            <span class="absolute inset-0 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 animate-ping opacity-30" />
            <MessageCircle class="h-7 w-7 relative z-10" />
        </a>

    </div>
</template>
