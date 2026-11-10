<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { FolderTree, Save, X } from '@lucide/vue';
import CategoriaController from '@/actions/App/Http/Controllers/Admin/Catalogo/CategoriaController';
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
    categoriasPadre: { id: number; nombre: string }[];
};

defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;
</script>

<template>
    <Head title="Nueva Categoría" />

    <div class="flex flex-col gap-6">
        <ModuleHeader title="Nueva Categoría" description="Crear una nueva categoría en el catálogo" :icon="FolderTree">
            <template #actions>
                <Link :href="route('admin.catalogo.categorias.index')">
                    <Button variant="outline" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800">
                        <X class="mr-2 h-4 w-4" /> Cancelar
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <Form
            v-bind="CategoriaController.store.form(currentTeam?.slug ?? '')"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <FormSection title="Datos de la categoría">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="grid gap-1.5">
                        <Label for="nombre">Nombre</Label>
                        <Input id="nombre" name="nombre" placeholder="Nombre de la categoría" class="h-10 rounded-lg border-gray-300" required />
                        <InputError :message="errors.nombre" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="parent_id">Categoría Padre (opcional)</Label>
                        <Select name="parent_id">
                            <SelectTrigger class="h-10 rounded-lg border-gray-300">
                                <SelectValue placeholder="Ninguna (categoría raíz)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="cat in categoriasPadre" :key="cat.id" :value="String(cat.id)">
                                    {{ cat.nombre }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.parent_id" />
                    </div>
                </div>

                <div class="grid gap-1.5">
                    <Label for="descripcion">Descripción</Label>
                    <Textarea id="descripcion" name="descripcion" placeholder="Descripción de la categoría" class="rounded-lg border-gray-300" />
                    <InputError :message="errors.descripcion" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="imagen">Imagen (opcional)</Label>
                    <Input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp" class="h-10 rounded-lg border-gray-300 file:mr-3 file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white" />
                    <InputError :message="errors.imagen" />
                </div>
            </FormSection>

            <div class="flex items-center gap-2 mb-4">
                <Checkbox id="activo" name="activo" value="1" />
                <Label for="activo">Activo</Label>
                <InputError :message="errors.activo" />
            </div>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="processing" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg px-6 h-10">
                    <Save class="mr-2 h-4 w-4" /> Guardar
                </Button>
                <Link :href="route('admin.catalogo.categorias.index')">
                    <Button variant="outline" type="button" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800 h-10">
                        Cancelar
                    </Button>
                </Link>
            </div>
        </Form>
    </div>
</template>
