<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { FolderTree } from '@lucide/vue';
import CategoriaController from '@/actions/App/Http/Controllers/Admin/Catalogo/CategoriaController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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

const props = defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;
</script>

<template>
    <Head title="Nueva Categoría" />

    <div class="flex flex-col space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                <FolderTree class="h-5 w-5 text-white" />
            </div>
            <div>
                <h1 class="text-lg font-semibold text-foreground">Nueva Categoría</h1>
                <p class="text-sm text-muted-foreground">Crear una nueva categoría en el catálogo</p>
            </div>
        </div>

        <Card class="border-border/50 shadow-xl">
            <CardHeader>
                <div class="flex items-center gap-2">
                    <div class="h-1 w-8 rounded-full bg-gradient-to-r from-cyan-500 to-blue-600" />
                    <CardTitle class="text-base font-semibold">Datos de la categoría</CardTitle>
                </div>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="CategoriaController.store.form(currentTeam?.slug ?? '')"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="nombre">Nombre</Label>
                        <Input
                            id="nombre"
                            name="nombre"
                            placeholder="Nombre de la categoría"
                            class="rounded-xl border-border bg-card"
                            required
                        />
                        <InputError :message="errors.nombre" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="descripcion">Descripción</Label>
                        <Textarea
                            id="descripcion"
                            name="descripcion"
                            placeholder="Descripción de la categoría"
                        />
                        <InputError :message="errors.descripcion" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="parent_id">Categoría Padre (opcional)</Label>
                        <Select name="parent_id">
                            <SelectTrigger class="rounded-xl border-border bg-card">
                                <SelectValue placeholder="Ninguna (categoría raíz)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">Ninguna</SelectItem>
                                <SelectItem
                                    v-for="cat in categoriasPadre"
                                    :key="cat.id"
                                    :value="String(cat.id)"
                                >
                                    {{ cat.nombre }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.parent_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="imagen">URL de imagen (opcional)</Label>
                        <Input
                            id="imagen"
                            name="imagen"
                            placeholder="https://ejemplo.com/imagen.jpg"
                            class="rounded-xl border-border bg-card"
                        />
                        <InputError :message="errors.imagen" />
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox id="activo" name="activo" value="1" />
                        <Label for="activo">Activo</Label>
                        <InputError :message="errors.activo" />
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <Button
                            type="submit"
                            :disabled="processing"
                            class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 hover:from-cyan-400 hover:to-blue-500"
                        >
                            Guardar
                        </Button>
                        <Link :href="route('admin.catalogo.categorias.index')">
                            <Button variant="outline" type="button" class="rounded-xl border-border bg-card hover:bg-accent/50">
                                Cancelar
                            </Button>
                        </Link>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
