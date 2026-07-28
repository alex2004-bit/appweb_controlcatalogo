<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, MessageSquare, User, Mail, Calendar, Clock } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { PageTransition, StatusBadge } from '@/components/shared';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    consulta: { id: number; nombre: string; email: string; telefono: string | null; mensaje: string; tipo: string; estado: string; leido: boolean; created_at: string; notas: string | null; };
}>();

function updateEstado(estado: string) {
    router.put(route('admin.consultas.update', props.consulta.id), { estado }, { preserveScroll: true });
}

const estadoList = ['pendiente', 'en_proceso', 'respondido', 'cerrado'];
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Clientes' }, { title: 'Consultas', href: route('admin.consultas.index') }, { title: consulta.nombre, href: '' }]">
        <PageTransition>
            <div class="flex flex-col gap-6 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg shadow-purple-500/25">
                            <MessageSquare class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ consulta.nombre }}</h1>
                            <p class="text-sm text-muted-foreground">Consulta recibida</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Select @update:model-value="updateEstado">
                            <SelectTrigger class="h-10 rounded-xl w-44">
                                <SelectValue :placeholder="'Estado: ' + consulta.estado" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="e in estadoList" :key="e" :value="e">{{ e }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <Link :href="route('admin.consultas.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
                            <ArrowLeft class="h-4 w-4" /> Volver
                        </Link>
                    </div>
                </div>

                <div class="mx-auto w-full max-w-4xl">
                    <div class="rounded-2xl border border-border/50 bg-card shadow-sm overflow-hidden">
                        <div class="h-1 bg-gradient-to-r from-purple-500 to-pink-600" />
                        <div class="p-6 sm:p-8">
                            <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-pink-600 text-xl font-bold text-white shadow-sm">
                                        {{ consulta.nombre.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-foreground">{{ consulta.nombre }}</h2>
                                        <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                            <span class="flex items-center gap-1.5"><Mail class="h-3.5 w-3.5" />{{ consulta.email }}</span>
                                            <span class="flex items-center gap-1.5"><Calendar class="h-3.5 w-3.5" />{{ consulta.created_at }}</span>
                                        </div>
                                    </div>
                                </div>
                                <StatusBadge :value="consulta.estado" />
                            </div>

                            <div class="rounded-xl bg-muted/30 p-6">
                                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                                    <MessageSquare class="h-4 w-4 text-purple-400" /> Mensaje
                                </h3>
                                <p class="text-sm leading-relaxed text-foreground whitespace-pre-wrap">{{ consulta.mensaje }}</p>
                            </div>

                            <div v-if="consulta.notas" class="mt-6 rounded-xl bg-muted/30 p-6">
                                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                                    <Clock class="h-4 w-4 text-purple-400" /> Notas internas
                                </h3>
                                <p class="text-sm leading-relaxed text-foreground whitespace-pre-wrap">{{ consulta.notas }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </PageTransition>
    </AppLayout>
</template>
