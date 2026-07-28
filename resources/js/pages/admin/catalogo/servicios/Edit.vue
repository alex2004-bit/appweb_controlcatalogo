<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Wrench } from '@lucide/vue';
import ServicioController from '@/actions/App/Http/Controllers/Admin/Catalogo/ServicioController';
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

    <div class="flex flex-col space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                <Wrench class="h-5 w-5 text-white" />
            </div>
            <div>
                <h1 class="text-lg font-semibold text-foreground">Editar Servicio</h1>
                <p class="text-sm text-muted-foreground">Modificar datos del servicio</p>
            </div>
        </div>

        <Card class="border-border/50 shadow-xl">
            <CardHeader>
                <div class="flex items-center gap-2">
                    <div class="h-1 w-8 rounded-full bg-gradient-to-r from-cyan-500 to-blue-600" />
                    <CardTitle class="text-base font-semibold">Datos del servicio</CardTitle>
                </div>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="ServicioController.update.form([currentTeam?.slug ?? '', props.servicio.id])"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="nombre">Nombre</Label>
                            <Input
                                id="nombre"
                                name="nombre"
                                :default-value="servicio.nombre"
                                placeholder="Nombre del servicio"
                                class="rounded-xl border-border bg-card"
                                required
                            />
                            <InputError :message="errors.nombre" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="duracion_estimada">Duración estimada</Label>
                            <Input
                                id="duracion_estimada"
                                name="duracion_estimada"
                                :default-value="servicio.duracion_estimada ?? ''"
                                placeholder="Ej: 2 horas"
                                class="rounded-xl border-border bg-card"
                            />
                            <InputError :message="errors.duracion_estimada" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="descripcion">Descripción</Label>
                        <Textarea
                            id="descripcion"
                            name="descripcion"
                            :default-value="servicio.descripcion ?? ''"
                            placeholder="Descripción del servicio"
                        />
                        <InputError :message="errors.descripcion" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="categoria_id">Categoría</Label>
                        <Select name="categoria_id" :default-value="String(servicio.categoria_id ?? '')">
                            <SelectTrigger class="rounded-xl border-border bg-card">
                                <SelectValue placeholder="Seleccionar categoría" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="cat in categorias"
                                    :key="cat.id"
                                    :value="String(cat.id)"
                                >
                                    {{ cat.nombre }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.categoria_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="caracteristicas">Características (JSON)</Label>
                        <Textarea
                            id="caracteristicas"
                            name="caracteristicas"
                            :default-value="servicio.caracteristicas ? JSON.stringify(servicio.caracteristicas, null, 2) : ''"
                            placeholder='["Instalación profesional", "Garantía 1 año"]'
                            class="font-mono"
                        />
                        <InputError :message="errors.caracteristicas" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="precio">Precio referencial</Label>
                            <Input
                                id="precio"
                                name="precio"
                                type="number"
                                step="0.01"
                                :default-value="servicio.precio ?? ''"
                                placeholder="0.00"
                                class="rounded-xl border-border bg-card"
                            />
                            <InputError :message="errors.precio" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="imagen">URL de imagen</Label>
                            <Input
                                id="imagen"
                                name="imagen"
                                :default-value="servicio.imagen ?? ''"
                                placeholder="https://ejemplo.com/imagen.jpg"
                                class="rounded-xl border-border bg-card"
                            />
                            <InputError :message="errors.imagen" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox id="activo" name="activo" value="1" :checked="servicio.activo" />
                        <Label for="activo">Activo</Label>
                        <InputError :message="errors.activo" />
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <Button
                            type="submit"
                            :disabled="processing"
                            class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 hover:from-cyan-400 hover:to-blue-500"
                        >
                            Actualizar
                        </Button>
                        <Link :href="route('admin.catalogo.servicios.index')">
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
