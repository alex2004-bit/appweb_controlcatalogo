<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Package, Plus, Trash2, Save, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import ProductoController from '@/actions/App/Http/Controllers/Admin/Catalogo/ProductoController';
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

type Especificacion = { nombre: string; valor: string; imagen?: File | string | null; imagenPreview?: string | null };

const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;
const teamParams = () => ({ current_team: currentTeam?.slug ?? '' });
defineProps<{
    categorias: { id: number; nombre: string }[];
    marcas: { id: number; nombre: string }[];
}>();

const precioCompra = ref<string | number>('');
const precioVentaManual = ref<string | number>('');
const precioVenta = computed(() => {
    const compra = Number(precioCompra.value);

    if (String(precioCompra.value).trim() !== '' && Number.isFinite(compra)) {
        return (compra * 1.4).toFixed(2);
    }

    return precioVentaManual.value;
});

const especificacionesList = ref<Especificacion[]>([]);
const primaryImagePreview = ref<string | null>(null);
const galleryImagePreviews = ref<(string | null)[]>([null, null, null, null]);

const especificacionesJson = computed(() => {
    const filtered = especificacionesList.value.filter(
        (e) => e.nombre.trim() !== '' || e.valor.trim() !== '' || e.imagen
    );

    if (filtered.length === 0) {
        return '';
    }

    const specsArray: Array<{ nombre: string; valor: string; imagen?: string | null }> = [];
    filtered.forEach((e) => {
        if (e.nombre.trim()) {
            specsArray.push({
                nombre: e.nombre.trim(),
                valor: e.valor.trim(),
                imagen: typeof e.imagen === 'string' ? e.imagen : null,
            });
        }
    });

    return JSON.stringify(specsArray);
});

function handleEspecImageChange(event: Event, index: number) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            especificacionesList.value[index].imagen = file;
            especificacionesList.value[index].imagenPreview = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
}

function removeEspecImage(index: number) {
    especificacionesList.value[index].imagen = null;
    especificacionesList.value[index].imagenPreview = null;
}

function agregarEspecificacion() {
    especificacionesList.value.push({ nombre: '', valor: '' });
}

function quitarEspecificacion(index: number) {
    especificacionesList.value.splice(index, 1);
}

function previewPrimaryImage(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0];
    primaryImagePreview.value = file ? URL.createObjectURL(file) : null;
}

function previewGalleryImage(event: Event, index: number): void {
    const file = (event.target as HTMLInputElement).files?.[0];
    galleryImagePreviews.value[index] = file ? URL.createObjectURL(file) : null;
}
</script>

<template>
    <Head title="Nuevo Producto" />

    <div class="flex flex-col gap-6">
        <ModuleHeader title="Nuevo Producto" description="Crear un nuevo producto en el catálogo" :icon="Package">
            <template #actions>
                <Link :href="route('admin.catalogo.productos.index', teamParams())">
                    <Button variant="outline" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800">
                        <X class="mr-2 h-4 w-4" /> Cancelar
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <Form
            v-bind="ProductoController.store.form(currentTeam?.slug ?? '')"
            class="space-y-6"
            v-slot="{ errors, processing, progress }"
        >
            <FormSection title="Datos básicos" description="Información principal del producto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="grid gap-1.5">
                        <Label for="nombre">Nombre del producto</Label>
                        <Input id="nombre" name="nombre" placeholder="Nombre del producto" class="h-10 rounded-lg border-gray-300" required />
                        <InputError :message="errors.nombre" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="sku">Código (SKU)</Label>
                        <Input id="sku" name="sku" placeholder="PRO-001" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.sku" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="slug">Slug</Label>
                        <Input id="slug" name="slug" placeholder="nombre-del-producto" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.slug" />
                    </div>
                </div>
                <div class="grid gap-1.5">
                    <Label for="descripcion">Descripción</Label>
                    <Textarea id="descripcion" name="descripcion" placeholder="Descripción del producto" class="rounded-lg border-gray-300 min-h-[100px]" />
                    <InputError :message="errors.descripcion" />
                </div>
            </FormSection>

            <FormSection title="Clasificación" description="Categoría y marca del producto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="grid gap-1.5">
                        <Label for="categoria_id">Categoría</Label>
                        <Select name="categoria_id">
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
                        <Label for="marca_id">Marca</Label>
                        <Select name="marca_id">
                            <SelectTrigger class="h-10 rounded-lg border-gray-300">
                                <SelectValue placeholder="Seleccionar marca" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="marca in marcas" :key="marca.id" :value="String(marca.id)">
                                    {{ marca.nombre }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.marca_id" />
                    </div>
                </div>
            </FormSection>

            <FormSection title="Precios" description="Precios del producto">
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                    <div class="grid gap-1.5">
                        <Label for="precio_compra">Precio compra</Label>
                        <Input id="precio_compra" name="precio_compra" v-model="precioCompra" type="number" min="0" step="0.01" placeholder="0.00" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.precio_compra" />
                        <p class="text-xs text-gray-500">Al ingresar el costo, el precio de venta se calcula con 40% de ganancia.</p>
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="precio">Precio venta</Label>
                        <Input id="precio" name="precio" type="number" min="0" step="0.01" :model-value="precioVenta" @update:model-value="precioVentaManual = $event" :readonly="String(precioCompra).trim() !== ''" placeholder="0.00" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.precio" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="precio_anterior">Precio oferta</Label>
                        <Input id="precio_anterior" name="precio_anterior" type="number" step="0.01" placeholder="0.00" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.precio_anterior" />
                    </div>
                </div>
            </FormSection>

            <FormSection title="Inventario" description="Control de stock">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="grid gap-1.5">
                        <Label for="stock">Stock</Label>
                        <Input id="stock" name="stock" type="number" placeholder="0" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.stock" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="stock_minimo">Stock mínimo</Label>
                        <Input id="stock_minimo" name="stock_minimo" type="number" placeholder="0" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.stock_minimo" />
                    </div>
                </div>
            </FormSection>

            <FormSection title="Especificaciones técnicas" description="Características del producto (texto o imagen)">
                <input type="hidden" name="especificaciones" :value="especificacionesJson" />

                <div v-for="(esp, index) in especificacionesList" :key="index" class="space-y-3 mb-4 p-3 border border-gray-200 rounded-lg">
                    <div class="flex gap-3 items-start">
                        <div class="flex-1">
                            <Label :for="`espec_nombre_${index}`" class="block text-sm font-medium text-gray-700 mb-1">Nombre</Label>
                            <Input :id="`espec_nombre_${index}`" v-model="esp.nombre" placeholder="Nombre (ej: Marca)" class="h-10 rounded-lg border-gray-300" />
                        </div>
                        <div class="flex-1">
                            <Label :for="`espec_valor_${index}`" class="block text-sm font-medium text-gray-700 mb-1">Valor</Label>
                            <Input :id="`espec_valor_${index}`" v-model="esp.valor" placeholder="Valor (ej: Hikvision)" class="h-10 rounded-lg border-gray-300" />
                        </div>
                        <Button type="button" variant="outline" size="icon" class="h-10 w-10 rounded-lg border-gray-200 text-red-500 hover:text-red-700 mt-6" @click="quitarEspecificacion(index)">
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>

                    <div class="flex gap-3 items-center">
                        <Label :for="`espec_imagen_${index}`" class="block text-sm font-medium text-gray-700 w-24 shrink-0">Imagen</Label>
                        <div class="flex-1">
                            <input
                                type="file"
                                :id="`espec_imagen_${index}`"
                                accept="image/*"
                                @change="handleEspecImageChange($event, index)"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                            <p class="mt-1 text-xs text-gray-500">Opcional: sube una foto para esta característica</p>
                        </div>
                        <div v-if="esp.imagenPreview" class="relative w-20 h-20 flex-shrink-0">
                            <img :src="esp.imagenPreview" alt="Preview" class="w-full h-full object-cover rounded-lg border border-gray-200" />
                            <button type="button" @click="removeEspecImage(index)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">×</button>
                        </div>
                    </div>
                </div>

                <Button type="button" variant="outline" size="sm" class="rounded-lg border-gray-200 text-gray-600" @click="agregarEspecificacion">
                    <Plus class="h-4 w-4 mr-1" /> Agregar característica
                </Button>

                <InputError :message="errors.especificaciones" />
            </FormSection>

            <FormSection title="Galería del producto" description="Sube una imagen principal y hasta 12 imágenes adicionales">
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="imagen">Imagen principal</Label>
                        <Input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp" class="h-10 rounded-lg border-gray-300 file:mr-3 file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white" @change="previewPrimaryImage" />
                        <img v-if="primaryImagePreview" :src="primaryImagePreview" alt="Vista previa principal" class="h-40 w-full rounded-lg border border-gray-200 object-contain bg-gray-50 p-2" />
                        <InputError :message="errors.imagen" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Imágenes adicionales (hasta 4)</Label>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="(_, index) in galleryImagePreviews" :key="index" class="grid gap-1.5">
                                <Label :for="`imagenes_${index}`" class="text-xs text-gray-500">Imagen {{ index + 1 }}</Label>
                                <input :id="`imagenes_${index}`" name="imagenes[]" type="file" accept="image/jpeg,image/png,image/webp" class="block h-10 w-full rounded-lg border border-gray-300 text-xs file:mr-2 file:border-0 file:bg-blue-600 file:px-2 file:py-2 file:text-xs file:font-medium file:text-white" @change="previewGalleryImage($event, index)" />
                                <img v-if="galleryImagePreviews[index]" :src="galleryImagePreviews[index] ?? undefined" :alt="`Vista previa ${index + 1}`" class="h-20 w-full rounded-md border border-gray-200 object-cover" />
                            </div>
                        </div>
                        <InputError :message="errors.imagenes" />
                        <p class="text-xs text-gray-500">JPG, PNG o WEBP. Máximo 5 MB por imagen.</p>
                    </div>
                </div>
                <progress v-if="progress" :value="progress.percentage" max="100" class="h-2 w-full rounded-full overflow-hidden [&::-webkit-progress-bar]:bg-gray-100 [&::-webkit-progress-value]:bg-blue-600" />
            </FormSection>

            <div class="flex items-center gap-2 mb-4">
                <Checkbox id="activo" name="activo" value="1" />
                <Label for="activo">Producto activo</Label>
                <InputError :message="errors.activo" />
            </div>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="processing" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg px-6 h-10">
                    <Save class="mr-2 h-4 w-4" /> Guardar Producto
                </Button>
                <Link :href="route('admin.catalogo.productos.index', teamParams())">
                    <Button variant="outline" type="button" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800 h-10">
                        Cancelar
                    </Button>
                </Link>
            </div>
        </Form>
    </div>
</template>
