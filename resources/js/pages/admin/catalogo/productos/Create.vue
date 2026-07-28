<script setup lang="ts">
import { computed, ref } from 'vue';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Package, Plus, Trash2 } from '@lucide/vue';
import ProductoController from '@/actions/App/Http/Controllers/Admin/Catalogo/ProductoController';
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

type Especificacion = { nombre: string; valor: string };

type Props = {
    categorias: { id: number; nombre: string }[];
};

const props = defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;

const especificacionesList = ref<Especificacion[]>([]);

const especificacionesJson = computed(() => {
    const filtered = especificacionesList.value.filter(
        (e) => e.nombre.trim() !== '' || e.valor.trim() !== ''
    );
    return filtered.length > 0 ? JSON.stringify(filtered) : '';
});

function agregarEspecificacion() {
    especificacionesList.value.push({ nombre: '', valor: '' });
}

function quitarEspecificacion(index: number) {
    especificacionesList.value.splice(index, 1);
}
</script>

<template>
    <Head title="Nuevo Producto" />

    <div class="flex flex-col space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                <Package class="h-5 w-5 text-white" />
            </div>
            <div>
                <h1 class="text-lg font-semibold text-foreground">Nuevo Producto</h1>
                <p class="text-sm text-muted-foreground">Crear un nuevo producto en el catálogo</p>
            </div>
        </div>

        <Card class="border-border/50 shadow-xl">
            <CardHeader>
                <div class="flex items-center gap-2">
                    <div class="h-1 w-8 rounded-full bg-gradient-to-r from-cyan-500 to-blue-600" />
                    <CardTitle class="text-base font-semibold">Datos del producto</CardTitle>
                </div>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="ProductoController.store.form(currentTeam?.slug ?? '')"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="nombre">Nombre</Label>
                            <Input id="nombre" name="nombre" placeholder="Nombre del producto" class="rounded-xl border-border bg-card" required />
                            <InputError :message="errors.nombre" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="sku">SKU (opcional)</Label>
                            <Input id="sku" name="sku" placeholder="PRO-001" class="rounded-xl border-border bg-card" />
                            <InputError :message="errors.sku" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="descripcion">Descripción</Label>
                        <Textarea
                            id="descripcion"
                            name="descripcion"
                            placeholder="Descripción del producto"
                        />
                        <InputError :message="errors.descripcion" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="categoria_id">Categoría</Label>
                        <Select name="categoria_id">
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
                        <Label>Especificaciones técnicas</Label>
                        <input type="hidden" name="especificaciones" :value="especificacionesJson" />

                        <div v-for="(esp, index) in especificacionesList" :key="index" class="flex gap-2 items-start">
                            <Input
                                v-model="esp.nombre"
                                placeholder="Nombre (ej: Marca)"
                                class="rounded-xl border-border bg-card flex-1"
                            />
                            <Input
                                v-model="esp.valor"
                                placeholder="Valor (ej: Hikvision)"
                                class="rounded-xl border-border bg-card flex-1"
                            />
                            <Button
                                type="button"
                                variant="outline"
                                size="icon-sm"
                                class="shrink-0"
                                @click="quitarEspecificacion(index)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>

                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="w-fit"
                            @click="agregarEspecificacion"
                        >
                            <Plus class="h-4 w-4" />
                            Agregar
                        </Button>

                        <InputError :message="errors.especificaciones" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="precio">Precio referencial</Label>
                            <Input id="precio" name="precio" type="number" step="0.01" placeholder="0.00" class="rounded-xl border-border bg-card" />
                            <InputError :message="errors.precio" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="imagen">URL de imagen</Label>
                            <Input id="imagen" name="imagen" placeholder="https://ejemplo.com/imagen.jpg" class="rounded-xl border-border bg-card" />
                            <InputError :message="errors.imagen" />
                        </div>
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
                        <Link :href="route('admin.catalogo.productos.index')">
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
