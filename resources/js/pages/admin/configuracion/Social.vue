<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { Building2, Share2, SlidersHorizontal, Save, MessageCircle, Globe, Camera, Music2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { PageTransition, ModuleHeader } from '@/components/shared';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{ config: { whatsapp: string; facebook: string; instagram: string } | null }>();

const form = useForm({
    whatsapp: props.config?.whatsapp ?? '',
    facebook: props.config?.facebook ?? '',
    instagram: props.config?.instagram ?? '',
});

function submit() { form.put(route('admin.configuracion.social.update'), { preserveScroll: true }); }

const tabs = [
    { key: 'empresa', label: 'Empresa', icon: Building2, route: 'admin.configuracion.empresa.index' },
    { key: 'social', label: 'Redes Sociales', icon: Share2, route: '' },
    { key: 'generales', label: 'Datos Generales', icon: SlidersHorizontal, route: 'admin.configuracion.generales.index' },
];
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Configuración' }, { title: 'Redes Sociales', href: '' }]">
        <PageTransition>
            <div class="flex flex-col gap-6 p-4 sm:p-6">
                <ModuleHeader title="Configuración" description="Conecta tus redes sociales" :icon="Share2" gradient="from-amber-500 to-orange-600" />

                <div class="flex gap-1.5 rounded-2xl border border-border/50 bg-card p-1.5 shadow-sm overflow-x-auto">
                    <Link v-for="t in tabs" :key="t.key" :href="t.route ? route(t.route) : '#'" :class="[
                        'flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-200',
                        !t.route ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-md' : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                    ]">
                        <component :is="t.icon" class="h-4 w-4" />
                        <span class="hidden sm:inline">{{ t.label }}</span>
                    </Link>
                </div>

                <div class="rounded-2xl border border-border/50 bg-card shadow-sm overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-amber-500 to-orange-600" />
                    <div class="p-6 sm:p-8">
                        <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500/20 to-orange-600/20">
                                    <Share2 class="h-5 w-5 text-amber-400" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-foreground">Redes Sociales</h3>
                                    <p class="text-xs text-muted-foreground">Enlaces a tus perfiles sociales</p>
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="whatsapp">WhatsApp</Label>
                                <div class="relative">
                                    <MessageCircle class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-emerald-400" />
                                    <Input id="whatsapp" v-model="form.whatsapp" placeholder="+51999888777" class="h-11 rounded-xl border-input bg-background pl-10" />
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="facebook">Facebook</Label>
                                <div class="relative">
                                    <Globe class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-blue-400" />
                                    <Input id="facebook" v-model="form.facebook" placeholder="https://facebook.com/miempresa" class="h-11 rounded-xl border-input bg-background pl-10" />
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="instagram">Instagram</Label>
                                <div class="relative">
                                    <Camera class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-pink-400" />
                                    <Input id="instagram" v-model="form.instagram" placeholder="@miempresa" class="h-11 rounded-xl border-input bg-background pl-10" />
                                </div>
                            </div>
                            <div class="flex gap-3 pt-2">
                                <Button type="submit" :disabled="form.processing" class="bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/25 rounded-xl h-11 px-6 hover:from-amber-600 hover:to-orange-700 transition-all hover:shadow-xl active:scale-95">
                                    <Save class="mr-2 h-4 w-4" /> Guardar Cambios
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </PageTransition>
    </AppLayout>
</template>
