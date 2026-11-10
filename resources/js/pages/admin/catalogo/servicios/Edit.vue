<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Wrench, Save, X } from '@lucide/vue';
import ServicioController from '@/actions/App/Http/Controllers/Admin/Catalogo/ServicioController';
import InputError from '@/components/InputError.vue';
import { FormSection, ModuleHeader } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

type Props = {
    servicio: {
        id: number;
        nombre: string;
        descripcion: string | null;
        imagen: string | null;
        precio: string | null;
        duracion_estimada: string | null;
        caracteristicas: string[] | null;
        categoria_id: number | null;
        activo: boolean;
    };
    categorias: { id: number; nombre: string }[];
};

const props = defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;
</script>

<template>
    <Head title="Editar Servicio" />

    <div class="flex flex-col gap-6">
        <ModuleHeader title="Editar Servicio" description="Modificar datos del servicio" :icon="Wrench">
            <template #actions>
                <Link :href="route('admin.catalogo.servicios.index')">
                    <Button variant="outline" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800">
                        <X class="mr-2 h-4 w-4" /> Cancelar
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <Form
            v-bind="ServicioController.update.form([currentTeam?.slug ?? '', props.servicio.id])"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <FormSection title="Datos del servicio">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="grid gap-1.5">
                        <Label for="nombre">Nombre</Label>
                        <Input id="nombre" name="nombre" :default-value="servicio.nombre" placeholder="Nombre del servicio" class="h-10 rounded-lg border-gray-300" required />
                        <InputError :message="errors.nombre" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="duracion_estimada">Duración estimada</Label>
                        <Input id="duracion_estimada" name="duracion_estimada" :default-value="servicio.duracion_estimada ?? ''" placeholder="Ej: 2 horas" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.duracion_estimada" />
                    </div>
                </div>

                <div class="grid gap-1.5">
                    <Label for="descripcion">Descripción</Label>
                    <Textarea id="descripcion" name="descripcion" :default-value="servicio.descripcion ?? ''" placeholder="Descripción del servicio" class="rounded-lg border-gray-300" />
                    <InputError :message="errors.descripcion" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="grid gap-1.5">
                        <Label for="categoria_id">Categoría</Label>
                        <Select name="categoria_id" :default-value="String(servicio.categoria_id ?? '')">
                            <SelectTrigger class="h-10 rounded-lg border-gray-300">
                                <SelectValue placeholder="Seleccionar categoría" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="cat in categorias" :key="cat.id" :value="String(cat.id)">
                                    {{ cat.nombre }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.categoria_id" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="precio">Precio referencial</Label>
                        <Input id="precio" name="precio" type="number" step="0.01" :default-value="servicio.precio ?? ''" placeholder="0.00" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.precio" />
                    </div>
                </div>

                <div class="grid gap-1.5">
                    <Label for="caracteristicas">Características (JSON)</Label>
                    <Textarea id="caracteristicas" name="caracteristicas" :default-value="servicio.caracteristicas ? JSON.stringify(servicio.caracteristicas, null, 2) : ''" placeholder='["Instalación profesional", "Garantía 1 año"]' class="rounded-lg border-gray-300 font-mono" />
                    <InputError :message="errors.caracteristicas" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="imagen">Reemplazar imagen</Label>
                    <Input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp" class="h-10 rounded-lg border-gray-300 file:mr-3 file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white" />
                    <InputError :message="errors.imagen" />
                </div>
            </FormSection>

            <div class="flex items-center gap-2 mb-4">
                <Checkbox id="activo" name="activo" value="1" :checked="servicio.activo" />
                <Label for="activo">Activo</Label>
                <InputError :message="errors.activo" />
            </div>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="processing" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg px-6 h-10">
                    <Save class="mr-2 h-4 w-4" /> Actualizar
                </Button>
                <Link :href="route('admin.catalogo.servicios.index')">
                    <Button variant="outline" type="button" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800 h-10">
                        Cancelar
                    </Button>
                </Link>
            </div>
        </Form>
    </div>
</template>
