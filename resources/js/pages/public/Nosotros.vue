<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ShieldCheck, Users, Award, Target, Eye, Heart, Building2, CheckCircle } from '@lucide/vue';

const values = [
    { icon: ShieldCheck, titulo: 'Seguridad', desc: 'Compromiso con la protección de personas y bienes mediante soluciones confiables.' },
    { icon: Award, titulo: 'Calidad', desc: 'Equipos de las mejores marcas con garantía y certificación oficial.' },
    { icon: Users, titulo: 'Confianza', desc: 'Más de 10 años respaldando a nuestros clientes con servicio profesional.' },
    { icon: Heart, titulo: 'Servicio', desc: 'Atención personalizada y soporte técnico continuo.' },
];

const timeline = [
    { year: '2014', event: 'Fundación de la empresa con enfoque en seguridad electrónica.' },
    { year: '2016', event: 'Ampliación de catálogo: videovigilancia IP y control de acceso.' },
    { year: '2019', event: 'Certificación como instaladores autorizados por principales marcas.' },
    { year: '2022', event: 'Lanzamiento de plataforma digital y catálogo en línea.' },
    { year: '2024', event: 'Más de 1,200 clientes atendidos y 200+ productos disponibles.' },
];
</script>

<template>
    <Head title="Nosotros - ControlCatálogo" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <section class="bg-white border-b border-gray-200 py-10">
            <div class="mx-auto w-full max-w-[1400px] px-6">
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
                    <Link href="/" class="hover:text-blue-600 transition">Inicio</Link>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">Nosotros</span>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3.5 py-1 text-xs font-semibold text-blue-700">
                    <Building2 class="h-3 w-3" /> Nosotros
                </div>
                <h1 class="mt-3 text-3xl font-bold text-gray-900">Conoce ControlCatálogo</h1>
                <p class="mt-1 text-sm text-gray-500">Somos especialistas en seguridad electrónica y videovigilancia</p>
            </div>
        </section>

        <!-- Mission & Vision -->
        <section class="py-16">
            <div class="mx-auto w-full max-w-[1400px] px-6">
                <div class="grid gap-8 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 bg-white p-8">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            <Target class="h-6 w-6" />
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">Nuestra Misión</h3>
                        <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                            Proveer soluciones de seguridad electrónica accesibles y de alta calidad que protejan
                            a las personas, sus hogares y negocios, respaldadas por asesoría experta y servicio técnico profesional.
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-8">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <Eye class="h-6 w-6" />
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">Nuestra Visión</h3>
                        <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                            Ser la plataforma líder en distribución de equipos de seguridad en México,
                            reconocida por nuestra calidad, variedad de productos y compromiso con la satisfacción del cliente.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Values -->
        <section class="bg-white border-t border-gray-200 py-16">
            <div class="mx-auto w-full max-w-[1400px] px-6">
                <div class="text-center mb-12">
                    <h2 class="text-2xl font-bold text-gray-900">Nuestros Valores</h2>
                    <p class="mt-2 text-sm text-gray-500">Principios que guían cada proyecto</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="v in values" :key="v.titulo" class="rounded-xl border border-gray-200 bg-white p-6 text-center transition hover:shadow-lg hover:-translate-y-1">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                            <component :is="v.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="mt-3 text-sm font-semibold text-gray-900">{{ v.titulo }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ v.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Timeline -->
        <section class="py-16">
            <div class="mx-auto max-w-3xl px-6">
                <div class="text-center mb-12">
                    <h2 class="text-2xl font-bold text-gray-900">Nuestra Historia</h2>
                    <p class="mt-2 text-sm text-gray-500">Más de 10 años de experiencia</p>
                </div>
                <div class="relative">
                    <div class="absolute left-4 top-0 bottom-0 w-px bg-gradient-to-b from-blue-500/50 via-blue-500/50 to-transparent" />
                    <div v-for="(t, idx) in timeline" :key="idx" class="relative pl-12 pb-10 last:pb-0">
                        <div class="absolute left-2.5 top-1 h-3 w-3 rounded-full border-2 border-blue-500 bg-white" />
                        <span class="text-sm font-bold text-blue-600">{{ t.year }}</span>
                        <p class="mt-1 text-sm text-gray-500">{{ t.event }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
