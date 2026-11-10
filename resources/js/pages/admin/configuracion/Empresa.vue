<script setup lang="ts">
import { useForm, Link, Head, usePage } from '@inertiajs/vue3';
import { Building2, Share2, SlidersHorizontal, Save } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { ModuleHeader } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index as empresaIndex } from '@/routes/admin/configuracion/empresa';
import { index as generalesIndex } from '@/routes/admin/configuracion/generales';
import { index as socialIndex } from '@/routes/admin/configuracion/social';

const props = defineProps<{ config: { nombre_empresa: string; ruc: string; direccion: string; telefono: string; email: string; website: string } | null }>();

const page = usePage();
const teamSlug = page.props.currentTeam?.slug ?? '';

const tabs = [
    { key: 'empresa', label: 'Empresa', icon: Building2, route: teamSlug ? empresaIndex(teamSlug).url : '' },
    { key: 'social', label: 'Redes Sociales', icon: Share2, route: teamSlug ? socialIndex(teamSlug).url : '' },
    { key: 'generales', label: 'Datos Generales', icon: SlidersHorizontal, route: teamSlug ? generalesIndex(teamSlug).url : '' },
];

const form = useForm({
    nombre_empresa: props.config?.nombre_empresa ?? '',
    ruc: props.config?.ruc ?? '',
    direccion: props.config?.direccion ?? '',
    telefono: props.config?.telefono ?? '',
    email: props.config?.email ?? '',
    website: props.config?.website ?? '',
});

const currentPath = teamSlug ? empresaIndex(teamSlug).url : '';

function submit() {
 form.put(currentPath, { preserveScroll: true });
}
</script>

<template>
    <Head title="Configuración - Empresa" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Configuración" description="Personaliza los datos de tu empresa" :icon="Building2" />

        <div class="flex gap-1.5 rounded-xl border border-gray-200 bg-white p-1.5 shadow-sm overflow-x-auto">
            <Link v-for="t in tabs" :key="t.key" :href="t.route || '#'" :class="[
                'flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200',
                !t.route ? 'bg-blue-100 text-blue-700 shadow-sm ring-1 ring-blue-200' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-100'
            ]">
                <component :is="t.icon" class="h-4 w-4" />
                <span class="hidden sm:inline">{{ t.label }}</span>
            </Link>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="p-6 sm:p-8">
                <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                            <Building2 class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Datos de la Empresa</h3>
                            <p class="text-xs text-gray-500">Información principal del negocio</p>
                        </div>
                    </div>
                    <div class="grid gap-1.5"><Label for="nombre_empresa">Nombre de la Empresa</Label><Input id="nombre_empresa" v-model="form.nombre_empresa" class="h-10 rounded-lg border-gray-300" /><InputError :message="form.errors.nombre_empresa" /></div>
                    <div class="grid gap-1.5"><Label for="ruc">RUC / Documento</Label><Input id="ruc" v-model="form.ruc" class="h-10 rounded-lg border-gray-300" /></div>
                    <div class="grid gap-1.5"><Label for="direccion">Dirección</Label><Textarea id="direccion" v-model="form.direccion" rows="2" class="rounded-lg border-gray-300 resize-none" /></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="grid gap-1.5"><Label for="telefono">Teléfono</Label><Input id="telefono" v-model="form.telefono" class="h-10 rounded-lg border-gray-300" /></div>
                        <div class="grid gap-1.5"><Label for="email">Email</Label><Input id="email" v-model="form.email" type="email" class="h-10 rounded-lg border-gray-300" /></div>
                    </div>
                    <div class="grid gap-1.5"><Label for="website">Sitio Web</Label><Input id="website" v-model="form.website" type="url" placeholder="https://" class="h-10 rounded-lg border-gray-300" /></div>
                    <div class="flex gap-3 pt-2">
                        <Button type="submit" :disabled="form.processing" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg px-6 h-10">
                            <Save class="mr-2 h-4 w-4" /> Guardar Cambios
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
