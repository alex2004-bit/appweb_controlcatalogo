<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { MessageCircle, Mail, MapPin, Phone, Clock, Send, CheckCircle, AlertCircle } from '@lucide/vue';
import { computed } from 'vue';

const whatsappNumber = '521XXXXXXXXXX';

const page = usePage();
const sent = computed(() => page.props.flash?.contact_sent);

const form = useForm({
    nombre: '',
    email: '',
    telefono: '',
    empresa: '',
    mensaje: '',
});

function handleSubmit() {
    form.post('/contacto', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

const contactInfo = [
    { icon: MessageCircle, label: 'WhatsApp', value: '+52 1 55 1234 5678', href: `https://wa.me/${whatsappNumber}`, color: 'emerald' },
    { icon: Mail, label: 'Email', value: 'info@controlcatalogo.com', href: 'mailto:info@controlcatalogo.com', color: 'blue' },
    { icon: MapPin, label: 'Ubicación', value: 'Villa Tunari, Cochabamba, Bolivia', href: null, color: 'blue' },
    { icon: Clock, label: 'Horario', value: 'Lun-Sáb 08:00-19:00', href: null, color: 'amber' },
];
</script>

<template>
    <Head title="Contacto - ControlCatálogo" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <section class="bg-white border-b border-gray-200 py-10">
            <div class="mx-auto w-full max-w-[1400px] px-6">
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
                    <Link href="/" class="hover:text-blue-600 transition">Inicio</Link>
                    <span>/</span>
                    <span class="text-gray-600 font-medium">Contacto</span>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3.5 py-1 text-xs font-semibold text-emerald-700">
                    <MessageCircle class="h-3 w-3" /> Contacto
                </div>
                <h1 class="mt-3 text-3xl font-bold text-gray-900">Contáctanos</h1>
                <p class="mt-1 text-sm text-gray-500">Estamos para ayudarte. Escríbenos y te responderemos a la brevedad.</p>
            </div>
        </section>

        <!-- Contact Content -->
        <section class="py-12">
            <div class="mx-auto w-full max-w-[1400px] px-6">
                <div class="grid gap-10 lg:grid-cols-5">
                    <!-- Contact Info -->
                    <div class="lg:col-span-2 space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Información de contacto</h2>
                        <a
                            v-for="item in contactInfo"
                            :key="item.label"
                            :href="item.href ?? '#'"
                            :target="item.href?.startsWith('http') ? '_blank' : undefined"
                            class="flex items-start gap-4 rounded-xl border border-gray-200 bg-white p-5 transition hover:shadow-md"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                                :class="item.color === 'emerald' ? 'bg-emerald-50 text-emerald-600' : item.color === 'amber' ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600'"
                            >
                                <component :is="item.icon" class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">{{ item.label }}</p>
                                <p class="text-sm text-gray-900 font-medium mt-0.5">{{ item.value }}</p>
                            </div>
                        </a>
                    </div>

                    <!-- Form -->
                    <div class="lg:col-span-3">
                        <div v-if="sent" class="flex flex-col items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 p-12 text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/25 mb-4">
                                <CheckCircle class="h-8 w-8 text-white" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">¡Mensaje enviado!</h3>
                            <p class="mt-2 text-sm text-gray-500 max-w-sm">
                                Gracias por contactarnos. Te responderemos a la brevedad posible.
                            </p>
                        </div>

                        <form v-else @submit.prevent="handleSubmit" class="space-y-5">
                            <h2 class="text-lg font-semibold text-gray-900">Envíanos un mensaje</h2>

                            <div v-if="form.errors.nombre || form.errors.email || form.errors.mensaje" class="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                <AlertCircle class="h-4 w-4 shrink-0" />
                                {{ form.errors.nombre || form.errors.email || form.errors.mensaje }}
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre *</label>
                                    <input
                                        v-model="form.nombre"
                                        type="text"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                                        :class="{ 'border-red-400': form.errors.nombre }"
                                        placeholder="Tu nombre"
                                    />
                                    <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Teléfono</label>
                                    <input
                                        v-model="form.telefono"
                                        type="tel"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                                        placeholder="55 1234 5678"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Empresa</label>
                                    <input
                                        v-model="form.empresa"
                                        type="text"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                                        placeholder="Tu empresa (opcional)"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email *</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                                    :class="{ 'border-red-400': form.errors.email }"
                                    placeholder="tu@email.com"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Mensaje *</label>
                                <textarea
                                    v-model="form.mensaje"
                                    rows="4"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition resize-none"
                                    :class="{ 'border-red-400': form.errors.mensaje }"
                                    placeholder="¿En qué podemos ayudarte?"
                                />
                                <p v-if="form.errors.mensaje" class="mt-1 text-xs text-red-500">{{ form.errors.mensaje }}</p>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/25 transition-all hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-500/40 disabled:opacity-50 disabled:hover:scale-100"
                            >
                                <Send class="h-4 w-4" />
                                {{ form.processing ? 'Enviando...' : 'Enviar mensaje' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Google Maps -->
        <section class="border-t border-gray-200 bg-white py-10">
            <div class="mx-auto w-full max-w-[1400px] px-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ubicación</h2>
                <div class="rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3798.0!2d-65.0!3d-17.0!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTfCsDAwJzAwLjAiUyA2NsKwMDAnMDAuMCJX!5e0!3m2!1ses!2sbo!4v1"
                        width="100%"
                        height="350"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Ubicación de ControlCatálogo"
                    />
                </div>
            </div>
        </section>

        <!-- WhatsApp CTA -->
        <section class="bg-gradient-to-r from-blue-600 to-indigo-700 py-16">
            <div class="mx-auto max-w-3xl px-6 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm mb-4">
                    <MessageCircle class="h-8 w-8 text-white" />
                </div>
                <h2 class="text-2xl font-bold text-white">Respuesta inmediata por WhatsApp</h2>
                <p class="mt-3 text-base text-blue-200 max-w-lg mx-auto">
                    ¿Necesitas una cotización rápida? Escríbenos directamente y te atenderemos al instante.
                </p>
                <a
                    :href="`https://wa.me/${whatsappNumber}`"
                    target="_blank"
                    class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-8 py-3.5 text-sm font-bold text-gray-900 transition hover:shadow-xl hover:scale-105"
                >
                    <MessageCircle class="h-5 w-5" />
                    Abrir WhatsApp
                </a>
            </div>
        </section>
    </div>
</template>
