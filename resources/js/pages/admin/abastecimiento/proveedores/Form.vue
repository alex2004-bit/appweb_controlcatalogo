<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Building2, Save } from '@lucide/vue';
import { computed } from 'vue';
import ProveedorController from '@/actions/App/Http/Controllers/Admin/Abastecimiento/ProveedorController';
import InputError from '@/components/InputError.vue';
import { FormSection, ModuleHeader } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type Supplier = {
    id: number;
    nombre: string;
    documento: string | null;
    contacto: string | null;
    email: string | null;
    telefono: string | null;
    direccion: string | null;
    notas: string | null;
    activo: boolean;
};

const props = defineProps<{ proveedor: Supplier | null }>();
const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');
const form = useForm({
    nombre: props.proveedor?.nombre ?? '',
    documento: props.proveedor?.documento ?? '',
    contacto: props.proveedor?.contacto ?? '',
    email: props.proveedor?.email ?? '',
    telefono: props.proveedor?.telefono ?? '',
    direccion: props.proveedor?.direccion ?? '',
    notas: props.proveedor?.notas ?? '',
    activo: props.proveedor?.activo ?? true,
});

function submit() {
    if (props.proveedor) {
        form.put(ProveedorController.update.url({ current_team: teamSlug.value, proveedor: props.proveedor.id }));

        return;
    }

    form.post(ProveedorController.store.url(teamSlug.value));
}
</script>

<template>
    <Head :title="proveedor ? 'Editar proveedor' : 'Nuevo proveedor'" />
    <div class="flex flex-col gap-6">
        <ModuleHeader :title="proveedor ? 'Editar proveedor' : 'Nuevo proveedor'" description="Registra los datos de contacto y facturación" :icon="Building2">
            <template #actions>
                <Link :href="ProveedorController.index.url(teamSlug)" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800">
                    <ArrowLeft class="h-4 w-4" /> Volver a proveedores
                </Link>
            </template>
        </ModuleHeader>

        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Datos del proveedor">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="grid gap-1.5">
                        <Label for="nombre">Nombre / razón social *</Label>
                        <Input id="nombre" v-model="form.nombre" required maxlength="180" />
                        <InputError :message="form.errors.nombre" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="documento">NIT / documento</Label>
                        <Input id="documento" v-model="form.documento" maxlength="30" />
                        <InputError :message="form.errors.documento" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="contacto">Persona de contacto</Label>
                        <Input id="contacto" v-model="form.contacto" maxlength="150" />
                        <InputError :message="form.errors.contacto" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="telefono">Teléfono</Label>
                        <Input id="telefono" v-model="form.telefono" maxlength="30" />
                        <InputError :message="form.errors.telefono" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="email">Correo electrónico</Label>
                        <Input id="email" v-model="form.email" type="email" maxlength="255" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="direccion">Dirección</Label>
                        <Input id="direccion" v-model="form.direccion" maxlength="255" />
                        <InputError :message="form.errors.direccion" />
                    </div>
                </div>
                <div class="grid gap-1.5">
                    <Label for="notas">Notas</Label>
                    <Textarea id="notas" v-model="form.notas" rows="3" maxlength="2000" />
                    <InputError :message="form.errors.notas" />
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input v-model="form.activo" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600" />
                    Proveedor activo
                </label>
                <InputError :message="form.errors.activo" />
            </FormSection>

            <div class="flex flex-wrap gap-3">
                <Button type="submit" :disabled="form.processing" class="h-10 rounded-lg bg-blue-600 px-5 text-white hover:bg-blue-700">
                    <Save class="mr-2 h-4 w-4" /> {{ form.processing ? 'Guardando...' : 'Guardar proveedor' }}
                </Button>
                <Link :href="ProveedorController.index.url(teamSlug)">
                    <Button type="button" variant="outline" class="h-10 rounded-lg">Cancelar</Button>
                </Link>
            </div>
        </form>
    </div>
</template>
