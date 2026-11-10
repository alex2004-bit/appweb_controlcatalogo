<script setup lang="ts">
import { Link, router, Head } from '@inertiajs/vue3';
import { ArrowLeft, MessageSquare, Calendar } from '@lucide/vue';
import { StatusBadge, ModuleHeader } from '@/components/shared';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const props = defineProps<{
    consulta: { id: number; nombre: string; email: string; telefono: string | null; mensaje: string; tipo: string; estado: string; leido: boolean; created_at: string; notas: string | null; };
}>();

function updateEstado(estado: unknown) {
    if (estado === null || estado === undefined) {
        return;
    }

    router.put(route('admin.consultas.update', props.consulta.id), { estado: String(estado) }, { preserveScroll: true });
}

const estadoList = ['pendiente', 'en_proceso', 'respondido', 'cerrado'];
</script>

<template>
    <Head :title="consulta.nombre" />
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <ModuleHeader :title="consulta.nombre" description="Consulta recibida" :icon="MessageSquare" />
            <div class="flex items-center gap-2">
                <Select @update:model-value="updateEstado">
                    <SelectTrigger class="h-10 rounded-lg border-gray-300 w-44">
                        <SelectValue :placeholder="'Estado: ' + consulta.estado" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="e in estadoList" :key="e" :value="e">{{ e }}</SelectItem>
                    </SelectContent>
                </Select>
                <Link :href="route('admin.consultas.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <ArrowLeft class="h-4 w-4" /> Volver
                </Link>
            </div>
        </div>

        <div class="mx-auto w-full max-w-4xl">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-xl font-bold text-gray-500">
                                {{ consulta.nombre.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">{{ consulta.nombre }}</h2>
                                <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-gray-500">
                                    <span class="flex items-center gap-1.5"><Mail class="h-3.5 w-3.5" />{{ consulta.email }}</span>
                                    <span class="flex items-center gap-1.5"><Calendar class="h-3.5 w-3.5" />{{ consulta.created_at }}</span>
                                </div>
                            </div>
                        </div>
                        <StatusBadge :value="consulta.estado" />
                    </div>

                    <div class="rounded-lg bg-gray-50 p-6">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500 flex items-center gap-2">
                            <MessageSquare class="h-4 w-4 text-gray-400" /> Mensaje
                        </h3>
                        <p class="text-sm leading-relaxed text-gray-900 whitespace-pre-wrap">{{ consulta.mensaje }}</p>
                    </div>

                    <div v-if="consulta.notas" class="mt-6 rounded-lg bg-gray-50 p-6">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500 flex items-center gap-2">
                            Notas internas
                        </h3>
                        <p class="text-sm leading-relaxed text-gray-900 whitespace-pre-wrap">{{ consulta.notas }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
