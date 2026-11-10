<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ShieldCheck, Camera, Wrench, ScanQrCode } from '@lucide/vue';
import { home } from '@/routes';

usePage();

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative grid h-dvh flex-col items-center justify-center bg-zinc-950 lg:grid-cols-2">
        <!-- Brand panel with background image/pattern -->
        <div class="relative hidden h-full flex-col justify-between overflow-hidden lg:flex">
            <!-- Realistic security background image layers -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-zinc-950 to-slate-900" />
            <div class="absolute inset-0 opacity-[0.06]" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100%25\' height=\'100%25\'%3E%3Cdefs%3E%3Cpattern id=\'security-grid\' width=\'40\' height=\'40\' patternUnits=\'userSpaceOnUse\'%3E%3Cpath d=\'M40 0L0 0 0 40\' fill=\'none\' stroke=\'%2306b6d4\' stroke-width=\'0.3\'/%3E%3C/pattern%3E%3Cpattern id=\'cctv-dots\' width=\'120\' height=\'120\' patternUnits=\'userSpaceOnUse\'%3E%3Ccircle cx=\'30\' cy=\'30\' r=\'1.5\' fill=\'%2306b6d4\' opacity=\'0.4\'/%3E%3Ccircle cx=\'90\' cy=\'70\' r=\'1\' fill=\'%233b82f6\' opacity=\'0.3\'/%3E%3Ccircle cx=\'60\' cy=\'100\' r=\'1.5\' fill=\'%2306b6d4\' opacity=\'0.35\'/%3E%3Ccircle cx=\'10\' cy=\'80\' r=\'1\' fill=\'%233b82f6\' opacity=\'0.25\'/%3E%3Ccircle cx=\'110\' cy=\'20\' r=\'1\' fill=\'%2306b6d4\' opacity=\'0.3\'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width=\'100%25\' height=\'100%25\' fill=\'url(%23security-grid)\'/%3E%3Crect width=\'100%25\' height=\'100%25\' fill=\'url(%23cctv-dots)\'/%3E%3C/svg%3E');" />

            <!-- Large camera lens visual -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full border border-cyan-500/5" />
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] rounded-full border border-cyan-500/8" />
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] rounded-full border border-cyan-500/10" />
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[200px] h-[200px] rounded-full border border-cyan-500/15" />
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[100px] h-[100px] rounded-full border-2 border-cyan-500/20 shadow-[0_0_60px_15px_rgba(6,182,212,0.1)]" />

            <!-- Camera iris blades (hexagonal fake) -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120px] h-[120px] rounded-full border-2 border-cyan-500/10 rotate-45" style="clip-path: polygon(50% 0%, 93% 25%, 93% 75%, 50% 100%, 7% 75%, 7% 25%);" />
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[140px] h-[140px] rounded-full border border-cyan-500/8 rotate-0" style="clip-path: polygon(50% 0%, 93% 25%, 93% 75%, 50% 100%, 7% 75%, 7% 25%);" />

            <!-- Scan line effect -->
            <div class="absolute inset-0 opacity-[0.02]" style="background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(6,182,212,0.3) 2px, rgba(6,182,212,0.3) 4px);" />

            <!-- Animated scan line -->
            <div class="absolute left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-cyan-400/30 to-transparent animate-scan" />

            <!-- Animated glowing dots (surveillance LEDs) -->
            <div class="absolute top-[15%] left-[20%] h-1.5 w-1.5 rounded-full bg-cyan-400 shadow-[0_0_10px_3px_rgba(6,182,212,0.6)] animate-pulse" />
            <div class="absolute top-[25%] right-[15%] h-1 w-1 rounded-full bg-cyan-400 shadow-[0_0_8px_2px_rgba(6,182,212,0.5)] animate-pulse" style="animation-delay: 0.4s" />
            <div class="absolute bottom-[30%] left-[30%] h-1.5 w-1.5 rounded-full bg-blue-400 shadow-[0_0_10px_3px_rgba(59,130,246,0.5)] animate-pulse" style="animation-delay: 0.8s" />
            <div class="absolute bottom-[20%] right-[25%] h-1 w-1 rounded-full bg-cyan-400 shadow-[0_0_8px_2px_rgba(6,182,212,0.5)] animate-pulse" style="animation-delay: 1.2s" />
            <div class="absolute top-[45%] left-[10%] h-1 w-1 rounded-full bg-cyan-300 shadow-[0_0_6px_2px_rgba(6,182,212,0.4)] animate-pulse" style="animation-delay: 0.6s" />
            <div class="absolute top-[60%] right-[10%] h-1.5 w-1.5 rounded-full bg-blue-300 shadow-[0_0_10px_3px_rgba(59,130,246,0.5)] animate-pulse" style="animation-delay: 1.6s" />

            <div class="relative z-20 flex flex-col h-full justify-between p-10">
                <!-- Logo -->
                <Link :href="home()" class="flex items-center gap-2.5 text-lg font-medium text-white">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                        <ShieldCheck class="h-5 w-5 text-zinc-950" />
                    </div>
                    <span>Control<span class="text-cyan-400">Catálogo</span></span>
                </Link>

                <!-- Hero content -->
                <div class="space-y-6">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-500/20 bg-cyan-500/10 px-3 py-1 text-xs text-cyan-400 backdrop-blur-sm">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-75" />
                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-cyan-500" />
                        </span>
                        Plataforma de seguridad
                    </div>
                    <h2 class="text-3xl font-bold leading-tight tracking-tight text-white">
                        Catálogo digital de<br>
                        <span class="bg-gradient-to-r from-cyan-300 to-blue-400 bg-clip-text text-transparent">seguridad y videovigilancia</span>
                    </h2>
                    <p class="max-w-md text-sm leading-relaxed text-zinc-400">
                        Gestiona tu inventario de productos, servicios técnicos y cotizaciones profesionales desde un solo panel de control.
                    </p>
                    <div class="flex flex-wrap items-center gap-4 text-zinc-500">
                        <div class="flex items-center gap-1.5 rounded-lg border border-zinc-800 px-3 py-1.5 text-xs">
                            <Camera class="h-3.5 w-3.5 text-cyan-400" /> Videovigilancia
                        </div>
                        <div class="flex items-center gap-1.5 rounded-lg border border-zinc-800 px-3 py-1.5 text-xs">
                            <ShieldCheck class="h-3.5 w-3.5 text-cyan-400" /> Alarmas
                        </div>
                        <div class="flex items-center gap-1.5 rounded-lg border border-zinc-800 px-3 py-1.5 text-xs">
                            <Wrench class="h-3.5 w-3.5 text-cyan-400" /> Instalación
                        </div>
                        <div class="flex items-center gap-1.5 rounded-lg border border-zinc-800 px-3 py-1.5 text-xs">
                            <ScanQrCode class="h-3.5 w-3.5 text-cyan-400" /> Control Acceso
                        </div>
                    </div>
                </div>

                <div class="text-xs text-zinc-600">
                    &copy; {{ new Date().getFullYear() }} DOMOTECH &mdash; Todos los derechos reservados
                </div>
            </div>
        </div>

        <!-- Form panel -->
        <div class="relative flex w-full items-center justify-center p-8">
            <div class="absolute inset-0 bg-gradient-to-b from-zinc-950 via-zinc-900/50 to-zinc-950 lg:hidden" />
            <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[400px] relative">
                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-xl font-medium tracking-tight text-zinc-100" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-zinc-500" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
