<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh">
        <div class="relative hidden w-1/2 lg:flex flex-col items-center justify-center overflow-hidden p-12">
            <img
                src="https://images.unsplash.com/photo-1557597774-9d273605dfa9?q=80&w=2070&auto=format&fit=crop"
                alt="Security and surveillance equipment"
                class="absolute inset-0 w-full h-full object-cover"
            />
            <div class="absolute inset-0 bg-gradient-to-br from-cyan-600/95 via-blue-700/90 to-indigo-900/85 z-10" />
            <div class="pointer-events-none absolute inset-0 z-20 opacity-40" style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.15) 0%, transparent 50%), radial-gradient(circle at 80% 70%, rgba(6,182,212,0.25) 0%, transparent 50%)" />
            <div class="pointer-events-none absolute inset-0 z-20 opacity-[0.06]" style="background-image: radial-gradient(circle, rgba(255,255,255,0.5) 1px, transparent 1px); background-size: 32px 32px;" />

            <div class="relative z-30 text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-sm shadow-2xl ring-1 ring-white/20">
                    <ShieldCheck class="h-10 w-10 text-white" />
                </div>
                <h1 class="mt-8 text-4xl font-bold tracking-tight text-white drop-shadow-lg">ControlCatálogo</h1>
                <p class="mt-4 max-w-md text-lg text-white/80 leading-relaxed drop-shadow">
                    Plataforma profesional de gestión de catálogos de seguridad y videovigilancia
                </p>

                <div class="mt-12 grid grid-cols-2 gap-6 text-left">
                    <div class="rounded-xl bg-white/10 backdrop-blur-sm p-4 ring-1 ring-white/10">
                        <p class="text-2xl font-bold text-white">200+</p>
                        <p class="text-xs text-white/60 mt-1">Productos</p>
                    </div>
                    <div class="rounded-xl bg-white/10 backdrop-blur-sm p-4 ring-1 ring-white/10">
                        <p class="text-2xl font-bold text-white">1,200+</p>
                        <p class="text-xs text-white/60 mt-1">Clientes</p>
                    </div>
                    <div class="rounded-xl bg-white/10 backdrop-blur-sm p-4 ring-1 ring-white/10">
                        <p class="text-2xl font-bold text-white">10+</p>
                        <p class="text-xs text-white/60 mt-1">Años</p>
                    </div>
                    <div class="rounded-xl bg-white/10 backdrop-blur-sm p-4 ring-1 ring-white/10">
                        <p class="text-2xl font-bold text-white">50+</p>
                        <p class="text-xs text-white/60 mt-1">Servicios</p>
                    </div>
                </div>
            </div>

            <div class="absolute bottom-8 left-8 right-8 z-30">
                <p class="text-center text-xs text-white/40">&copy; {{ new Date().getFullYear() }} ControlCatálogo. Todos los derechos reservados.</p>
            </div>
        </div>

        <div class="flex w-full lg:w-1/2 flex-col items-center justify-center bg-background p-6 md:p-10">
            <div class="w-full max-w-sm">
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col items-center gap-4">
                        <Link :href="home()" class="flex flex-col items-center gap-2 font-medium lg:hidden">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                                <ShieldCheck class="h-6 w-6 text-white" />
                            </div>
                            <span class="text-lg font-semibold tracking-tight">ControlCatálogo</span>
                        </Link>
                        <div class="space-y-2 text-center">
                            <h1 class="text-2xl font-bold tracking-tight">{{ title }}</h1>
                            <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
                        </div>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
