<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, ClipboardList, Plus, Save, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import CompraController from '@/actions/App/Http/Controllers/Admin/Abastecimiento/CompraController';
import InputError from '@/components/InputError.vue';
import { FormSection, ModuleHeader } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useMoney } from '@/composables/useMoney';

type Supplier = { id: number; nombre: string };
type Product = { id: number; nombre: string; sku: string | null; precio_compra: string | null };

const props = defineProps<{
    proveedores: Supplier[];
    productos: Product[];
    impuestoRate: number;
    moneda: string;
}>();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');
const { format } = useMoney();
const form = useForm({
    proveedor_id: '',
    fecha_compra: new Date().toISOString().slice(0, 10),
    numero_factura: '',
    notas: '',
    items: [{ producto_id: '', cantidad: 1, precio_unitario: 0 }],
});
const subtotal = computed(() => Math.round(form.items.reduce(
    (sum, item) => sum + Math.round(Number(item.cantidad || 0) * Number(item.precio_unitario || 0) * 100) / 100,
    0,
) * 100) / 100);
const impuesto = computed(() => Math.round(subtotal.value * props.impuestoRate * 100) / 100);
const total = computed(() => subtotal.value + impuesto.value);

function selectProduct(index: number, value: string) {
    const item = form.items[index];

    if (!item) {
        return;
    }

    item.producto_id = value;
    const product = props.productos.find((candidate) => candidate.id === Number(value));
    item.precio_unitario = Number(product?.precio_compra ?? 0);
}

function handleProductChange(index: number, event: Event) {
    if (event.target instanceof HTMLSelectElement) {
        selectProduct(index, event.target.value);
    }
}

function addItem() {
    form.items.push({ producto_id: '', cantidad: 1, precio_unitario: 0 });
}

function removeItem(index: number) {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
}

function submit() {
    form.post(CompraController.store.url(teamSlug.value));
}
</script>

<template>
    <Head title="Registrar compra" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Registrar compra" description="La compra quedará pendiente; el stock se actualizará al confirmar la recepción" :icon="ClipboardList">
            <template #actions>
                <Link :href="CompraController.index.url(teamSlug)" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800">
                    <ArrowLeft class="h-4 w-4" /> Volver a compras
                </Link>
            </template>
        </ModuleHeader>

        <div v-if="!proveedores.length || !productos.length" class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            {{ !proveedores.length ? 'Primero registra y activa al menos un proveedor.' : 'Primero registra y activa al menos un producto.' }}
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Datos de la compra">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <div class="grid gap-1.5">
                        <Label for="proveedor_id">Proveedor *</Label>
                        <select id="proveedor_id" v-model="form.proveedor_id" required class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm">
                            <option value="" disabled>Selecciona un proveedor</option>
                            <option v-for="proveedor in proveedores" :key="proveedor.id" :value="String(proveedor.id)">{{ proveedor.nombre }}</option>
                        </select>
                        <InputError :message="form.errors.proveedor_id" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="fecha_compra">Fecha *</Label>
                        <Input id="fecha_compra" v-model="form.fecha_compra" type="date" required />
                        <InputError :message="form.errors.fecha_compra" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="numero_factura">N.º de factura</Label>
                        <Input id="numero_factura" v-model="form.numero_factura" maxlength="80" />
                        <InputError :message="form.errors.numero_factura" />
                    </div>
                </div>
            </FormSection>

            <FormSection title="Productos comprados">
                <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-1 items-end gap-4 rounded-lg border border-gray-200 p-4 md:grid-cols-[minmax(0,2fr)_minmax(100px,0.7fr)_minmax(130px,1fr)_auto]">
                    <div class="grid gap-1.5">
                        <Label :for="`producto-${index}`">Producto *</Label>
                        <select :id="`producto-${index}`" :value="item.producto_id" required class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm" @change="handleProductChange(index, $event)">
                            <option value="" disabled>Selecciona un producto</option>
                            <option v-for="producto in productos" :key="producto.id" :value="String(producto.id)">{{ producto.nombre }}{{ producto.sku ? ` · ${producto.sku}` : '' }}</option>
                        </select>
                        <InputError :message="form.errors[`items.${index}.producto_id`]" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label :for="`cantidad-${index}`">Cantidad *</Label>
                        <Input :id="`cantidad-${index}`" v-model.number="item.cantidad" type="number" min="1" step="1" required />
                        <InputError :message="form.errors[`items.${index}.cantidad`]" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label :for="`precio-${index}`">Costo unitario ({{ moneda }}) *</Label>
                        <Input :id="`precio-${index}`" v-model.number="item.precio_unitario" type="number" min="0" step="0.01" required />
                        <InputError :message="form.errors[`items.${index}.precio_unitario`]" />
                    </div>
                    <Button type="button" variant="outline" class="h-10 rounded-lg text-red-600 hover:border-red-300" :disabled="form.items.length === 1" :aria-label="`Quitar producto ${index + 1}`" @click="removeItem(index)">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
                <InputError :message="form.errors.items" />
                <Button type="button" variant="outline" class="rounded-lg" @click="addItem">
                    <Plus class="mr-2 h-4 w-4" /> Agregar producto
                </Button>
            </FormSection>

            <FormSection title="Notas">
                <Textarea id="notas" v-model="form.notas" rows="3" maxlength="2000" placeholder="Observaciones de la compra o entrega" />
                <InputError :message="form.errors.notas" />
            </FormSection>

            <div class="ml-auto w-full max-w-sm rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex justify-between py-1 text-sm text-gray-600"><span>Subtotal</span><span>{{ format(subtotal) }}</span></div>
                <div class="flex justify-between py-1 text-sm text-gray-600"><span>Impuesto</span><span>{{ format(impuesto) }}</span></div>
                <div class="mt-2 flex justify-between border-t border-gray-200 pt-3 font-semibold text-gray-900"><span>Total estimado</span><span>{{ format(total) }}</span></div>
            </div>

            <div class="flex flex-wrap gap-3">
                <Button type="submit" :disabled="form.processing || !proveedores.length || !productos.length" class="h-10 rounded-lg bg-blue-600 px-5 text-white hover:bg-blue-700">
                    <Save class="mr-2 h-4 w-4" /> {{ form.processing ? 'Guardando...' : 'Registrar compra pendiente' }}
                </Button>
                <Link :href="CompraController.index.url(teamSlug)">
                    <Button type="button" variant="outline" class="h-10 rounded-lg">Cancelar</Button>
                </Link>
            </div>
        </form>
    </div>
</template>
