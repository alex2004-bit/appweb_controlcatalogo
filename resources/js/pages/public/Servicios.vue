<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Wrench, Check, Clock, MessageCircle } from '@lucide/vue';
import { ref } from 'vue';
import { useMoney } from '@/composables/useMoney';

const props = defineProps<{
    servicios: {
        id: number;
        nombre: string;
        slug: string;
        descripcion: string | null;
        imagen: string | null;
        caracteristicas: string[] | null;
        precio: string;
        duracion_estimada: string | null;
        categoria: { nombre: string; slug: string } | null;
    }[];
    categorias: { id: number; nombre: string; slug: string }[];
}>();

const whatsappNumber = '59164828148';
const selectedCategoria = ref<string | null>(null);
const filteredServicios = ref(props.servicios);

function filterByCategoria(slug: string | null) {
    selectedCategoria.value = slug;

    if (!slug) {
        filteredServicios.value = props.servicios;
    } else {
        filteredServicios.value = props.servicios.filter(s => s.categoria?.slug === slug);
    }
}

const money = useMoney();
</script>

<template>
    <Head title="Servicios" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <section class="bg-white border-b border-gray-200 py-10">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
                    <Link href="/" class="hover:text-blue-600 transition">Inicio</Link>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">Servicios</span>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3.5 py-1 text-xs font-semibold text-amber-700">
                    <Wrench class="h-3 w-3" /> Servicios
                </div>
                <h1 class="mt-3 text-3xl font-bold text-gray-900">Servicios Profesionales</h1>
                <p class="mt-1 text-sm text-gray-500">Instalación, mantenimiento y soporte técnico especializado</p>
            </div>
        </section>

        <!-- Category Filter -->
        <section v-if="categorias.length > 0" class="bg-white border-b border-gray-200">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 py-4">
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        @click="filterByCategoria(null)"
                        class="px-4 py-2 rounded-lg text-xs font-medium transition border"
                        :class="!selectedCategoria ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-600 border-gray-300 hover:text-blue-600 hover:border-blue-300 bg-white'"
                    >
                        Todos
                    </button>
                    <button
                        v-for="cat in categorias"
                        :key="cat.id"
                        @click="filterByCategoria(cat.slug)"
                        class="px-4 py-2 rounded-lg text-xs font-medium transition border"
                        :class="selectedCategoria === cat.slug ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-600 border-gray-300 hover:text-blue-600 hover:border-blue-300 bg-white'"
                    >
                        {{ cat.nombre }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Services Grid -->
        <section class="py-12">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6">
                <div v-if="filteredServicios.length === 0" class="text-center py-20">
                    <Wrench class="mx-auto h-12 w-12 text-gray-300" />
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">Sin servicios</h3>
                    <p class="mt-1 text-sm text-gray-500">No hay servicios disponibles en esta categoría.</p>
                </div>

                <div v-else class="grid grid-cols-1 gap-4 min-[375px]:grid-cols-2 sm:gap-6 md:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="s in filteredServicios"
                        :key="s.id"
                        class="group min-w-0 rounded-xl border border-gray-200 bg-white p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/5 sm:p-6"
                    >
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <Wrench class="h-6 w-6" />
                        </div>

                        <h3 class="mt-4 text-base font-semibold text-gray-900">{{ s.nombre }}</h3>
                        <p v-if="s.categoria" class="mt-1 text-xs text-amber-600 font-medium">{{ s.categoria.nombre }}</p>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ s.descripcion }}</p>

                        <div v-if="s.caracteristicas && s.caracteristicas.length > 0" class="mt-4 space-y-1.5">
                            <div v-for="(c, idx) in s.caracteristicas.slice(0, 4)" :key="idx" class="flex items-start gap-2">
                                <Check class="h-3.5 w-3.5 text-emerald-500 mt-0.5 shrink-0" />
                                <span class="text-xs text-gray-600">{{ c }}</span>
                            </div>
                            <p v-if="s.caracteristicas.length > 4" class="text-xs text-gray-400 ml-5">+{{ s.caracteristicas.length - 4 }} más</p>
                        </div>

                        <div class="mt-5 pt-5 border-t border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-gray-900 font-bold">{{ money.format(s.precio) }}</span>
                                </div>
                                <div v-if="s.duracion_estimada" class="flex items-center gap-1 text-xs text-gray-400">
                                    <Clock class="h-3 w-3" />
                                    {{ s.duracion_estimada }}
                                </div>
                            </div>
                            <a
                                :href="`https://wa.me/${whatsappNumber}?text=Hola, me interesa el servicio: ${s.nombre}`"
                                target="_blank"
                                class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-2.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-600 hover:text-white"
                            >
                                <MessageCircle class="h-3.5 w-3.5" />
                                Solicitar información
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
