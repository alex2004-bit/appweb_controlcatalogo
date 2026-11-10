<script setup lang="ts">
import { useForm, Link, Head } from '@inertiajs/vue3';
import { UserPen, Save, X } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { FormSection, ModuleHeader } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    cliente: {
        id: number;
        tipo_persona: string;
        nombre: string;
        email: string | null;
        telefono: string | null;
        direccion: string | null;
        documento: string | null;
        empresa: string | null;
        notas: string | null;
    };
}>();

const form = useForm({
    tipo_persona: props.cliente.tipo_persona,
    nombre: props.cliente.nombre,
    email: props.cliente.email ?? '',
    telefono: props.cliente.telefono ?? '',
    direccion: props.cliente.direccion ?? '',
    documento: props.cliente.documento ?? '',
    empresa: props.cliente.empresa ?? '',
    notas: props.cliente.notas ?? '',
});

function submit() {
    form.put(route('admin.clientes.update', props.cliente.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Editar Cliente" />

    <div class="flex flex-col gap-6">
        <ModuleHeader title="Editar Cliente" description="Modifica los datos del cliente" :icon="UserPen">
            <template #actions>
                <Link :href="route('admin.clientes.index')">
                    <Button variant="outline" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800">
                        <X class="mr-2 h-4 w-4" /> Cancelar
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <form @submit.prevent="submit" class="space-y-6">
            <FormSection title="Tipo de Persona">
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer rounded-lg border border-gray-200 bg-white p-4 transition-all hover:border-blue-300" :class="form.tipo_persona === 'natural' ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500/30' : ''">
                        <input type="radio" v-model="form.tipo_persona" value="natural" class="sr-only" />
                        <div class="flex h-5 w-5 items-center justify-center rounded-full border-2" :class="form.tipo_persona === 'natural' ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                            <div v-if="form.tipo_persona === 'natural'" class="h-2 w-2 rounded-full bg-white"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">Persona Natural</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer rounded-lg border border-gray-200 bg-white p-4 transition-all hover:border-blue-300" :class="form.tipo_persona === 'juridica' ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500/30' : ''">
                        <input type="radio" v-model="form.tipo_persona" value="juridica" class="sr-only" />
                        <div class="flex h-5 w-5 items-center justify-center rounded-full border-2" :class="form.tipo_persona === 'juridica' ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                            <div v-if="form.tipo_persona === 'juridica'" class="h-2 w-2 rounded-full bg-white"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">Persona Jurídica</span>
                    </label>
                </div>
            </FormSection>

            <FormSection title="Datos del Cliente">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="grid gap-1.5">
                        <Label for="nombre">Nombre *</Label>
                        <Input id="nombre" v-model="form.nombre" required class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="form.errors.nombre" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="documento">{{ form.tipo_persona === 'natural' ? 'DNI' : 'RUC' }}</Label>
                        <Input id="documento" v-model="form.documento" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="form.errors.documento" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="grid gap-1.5">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="telefono">Teléfono</Label>
                        <Input id="telefono" v-model="form.telefono" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="form.errors.telefono" />
                    </div>
                </div>
                <div class="grid gap-1.5">
                    <Label for="direccion">Dirección</Label>
                    <Textarea id="direccion" v-model="form.direccion" rows="2" class="rounded-lg border-gray-300" />
                    <InputError :message="form.errors.direccion" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="empresa">Empresa</Label>
                    <Input id="empresa" v-model="form.empresa" class="h-10 rounded-lg border-gray-300" />
                    <InputError :message="form.errors.empresa" />
                </div>
            </FormSection>

            <FormSection title="Notas (opcional)">
                <Textarea v-model="form.notas" rows="3" class="rounded-lg border-gray-300" />
                <InputError :message="form.errors.notas" />
            </FormSection>

            <div class="flex items-center gap-3">
                <Button :disabled="form.processing" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg px-6 h-10">
                    <Save class="mr-2 h-4 w-4" /> Actualizar Cliente
                </Button>
                <Link :href="route('admin.clientes.index')"><Button variant="outline" type="button" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800 h-10">Cancelar</Button></Link>
            </div>
        </form>
    </div>
</template>
