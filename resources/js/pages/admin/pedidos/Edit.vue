<script setup lang="ts">
import { useForm, Link, Head } from '@inertiajs/vue3';
import { Plus, Trash2, ShoppingCart, Save, X } from '@lucide/vue';
import { computed } from 'vue';
import { FormSection, ModuleHeader } from '@/components/shared';
import { Button } from '@/components/ui/button';
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
import { useMoney } from '@/composables/useMoney';
import { useTaxRate } from '@/composables/useTaxRate';

const props = defineProps<{
    pedido: {
        id: number;
        cliente_id: number;
        vendedor_id: number | null;
        referido_por: string | null;
        estado: string;
        pagado: boolean;
        metodo_pago: string | null;
        notas: string | null;
        direccion_entrega: string | null;
        ciudad: string | null;
        provincia: string | null;
        tipo_entrega: string | null;
        fecha_entrega: string | null;
        telefono_contacto: string | null;
        anticipo: string;
        numero_cuotas: number;
        plazo_dias: number | null;
        detalles: Array<{
            id: number;
            item_type: string;
            item_id: number;
            item_nombre: string;
            cantidad: number;
            precio_unitario: string;
            descuento: string;
        }>;
    };
    clientes: { id: number; nombre: string; documento: string | null; email: string | null }[];
    vendedores: { id: number; name: string }[];
    productos: { id: number; nombre: string; precio: string; sku: string | null }[];
    servicios: { id: number; nombre: string; precio: string }[];
}>();

const form = useForm({
    cliente_id: props.pedido.cliente_id,
    vendedor_id: props.pedido.vendedor_id ? String(props.pedido.vendedor_id) : '',
    referido_por: props.pedido.referido_por ?? '',
    estado: props.pedido.estado,
    metodo_pago: props.pedido.metodo_pago ?? '',
    pagado: props.pedido.pagado,
    notas: props.pedido.notas ?? '',
    direccion_entrega: props.pedido.direccion_entrega ?? '',
    ciudad: props.pedido.ciudad ?? '',
    provincia: props.pedido.provincia ?? '',
    tipo_entrega: props.pedido.tipo_entrega ?? 'tienda',
    fecha_entrega: props.pedido.fecha_entrega ?? '',
    telefono_contacto: props.pedido.telefono_contacto ?? '',
    anticipo: props.pedido.anticipo ?? '0',
    numero_cuotas: String(props.pedido.numero_cuotas ?? 1),
    plazo_dias: props.pedido.plazo_dias ? String(props.pedido.plazo_dias) : '',
    items: props.pedido.detalles.map(d => ({
        item_type: d.item_type.includes('Producto') ? 'producto' : 'servicio',
        item_id: d.item_id,
        item_nombre: d.item_nombre,
        cantidad: d.cantidad,
        precio_unitario: parseFloat(d.precio_unitario),
        descuento: parseFloat(d.descuento) / d.cantidad,
    })),
});

function addItem() {
    form.items.push({ item_type: 'producto', item_id: 0, item_nombre: '', cantidad: 1, precio_unitario: 0, descuento: 0 });
}

function removeItem(index: number) {
    form.items.splice(index, 1);
}

function onItemSelect(index: number, type: string, id: string) {
    const item = form.items[index];
    item.item_type = type;
    item.item_id = parseInt(id);
    const catalog = type === 'producto' ? props.productos : props.servicios;
    const found = catalog.find((p: any) => String(p.id) === id);

    if (found) {
        item.item_nombre = found.nombre;
        item.precio_unitario = parseFloat(found.precio);
    }
}

const subtotal = computed(() => form.items.reduce((s, i) => s + (i.cantidad * i.precio_unitario) - (i.descuento * i.cantidad), 0));
const taxRate = useTaxRate();
const impuesto = computed(() => subtotal.value * taxRate.value / 100);
const total = computed(() => subtotal.value + impuesto.value);
const anticipoNum = computed(() => parseFloat(form.anticipo) || 0);
const saldo = computed(() => Math.max(total.value - anticipoNum.value, 0));

const { format, symbol } = useMoney();

function submit() {
    form.put(route('admin.pedidos.update', props.pedido.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Editar Pedido" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Editar Pedido" description="Modifica los datos del pedido" :icon="ShoppingCart">
            <template #actions>
                <Link :href="route('admin.pedidos.index')">
                    <Button variant="outline" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800">
                        <X class="mr-2 h-4 w-4" /> Cancelar
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <FormSection title="Cliente">
                        <Select v-model="form.cliente_id">
                            <SelectTrigger class="h-10 rounded-lg border-gray-300">
                                <SelectValue placeholder="Seleccionar..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in clientes" :key="c.id" :value="c.id.toString()">
                                    {{ c.nombre }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </FormSection>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <FormSection title="Estado">
                        <Select v-model="form.estado">
                            <SelectTrigger class="h-10 rounded-lg border-gray-300">
                                <SelectValue placeholder="Seleccionar..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pendiente">Pendiente</SelectItem>
                                <SelectItem value="procesando">Procesando</SelectItem>
                                <SelectItem value="completado">Completado</SelectItem>
                                <SelectItem value="cancelado">Cancelado</SelectItem>
                            </SelectContent>
                        </Select>
                    </FormSection>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <FormSection title="Items">
                    <div v-for="(item, index) in form.items" :key="index" class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-blue-100 text-xs font-bold text-blue-600">{{ index + 1 }}</span>
                                Item #{{ index + 1 }}
                            </span>
                            <Button variant="destructive" size="sm" class="rounded-lg h-9 w-9" @click="removeItem(index)"><Trash2 class="h-4 w-4" /></Button>
                        </div>
                        <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                            <div>
                                <Label class="text-xs text-gray-500">Tipo</Label>
                                <select v-model="item.item_type" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm">
                                    <option value="producto">Producto</option>
                                    <option value="servicio">Servicio</option>
                                </select>
                            </div>
                            <div class="col-span-2 lg:col-span-2">
                                <Label class="text-xs text-gray-500">Item</Label>
                                <select :value="item.item_id" @change="onItemSelect(index, item.item_type, ($event.target as HTMLSelectElement).value)" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm">
                                    <option value="">Seleccionar...</option>
                                    <template v-if="item.item_type === 'producto'">
                                        <option v-for="p in productos" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                                    </template>
                                    <template v-if="item.item_type === 'servicio'">
                                        <option v-for="s in servicios" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <Label class="text-xs text-gray-500">Cant.</Label>
                                <Input type="number" min="1" v-model="item.cantidad" class="h-10 rounded-lg border-gray-300" />
                            </div>
                            <div>
                                <Label class="text-xs text-gray-500">Desc.</Label>
                                <Input type="number" step="0.01" min="0" v-model="item.descuento" class="h-10 rounded-lg border-gray-300" />
                            </div>
                        </div>
                    </div>
                    <Button variant="outline" @click="addItem" class="w-full rounded-lg h-10 border-dashed border-gray-200"><Plus class="mr-2 h-4 w-4" /> Agregar Item</Button>
                </FormSection>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal:</span><span class="text-gray-900">{{ format(subtotal) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Impuesto ({{ taxRate }}%):</span><span class="text-gray-900">{{ format(impuesto) }}</span></div>
                    <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2 mt-2"><span class="text-gray-900">Total:</span><span class="text-blue-600">{{ format(total) }}</span></div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <FormSection title="Pago">
                        <div class="grid gap-3">
                            <div>
                                <Label class="text-xs text-gray-500">Método de Pago</Label>
                                <Select v-model="form.metodo_pago">
                                    <SelectTrigger class="h-10 rounded-lg border-gray-300">
                                        <SelectValue placeholder="Seleccionar..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="efectivo">Efectivo</SelectItem>
                                        <SelectItem value="transferencia">Transferencia</SelectItem>
                                        <SelectItem value="tarjeta">Tarjeta</SelectItem>
                                        <SelectItem value="cheque">Cheque</SelectItem>
                                        <SelectItem value="deposito">Depósito</SelectItem>
                                        <SelectItem value="otro">Otro</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3 text-sm">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" v-model="form.pagado" class="h-4 w-4 rounded border-gray-300 accent-blue-600" />
                                    <span class="font-medium text-gray-900">Pagado</span>
                                </label>
                            </div>
                        </div>
                    </FormSection>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <FormSection title="Notas">
                        <Textarea v-model="form.notas" rows="3" class="rounded-lg border-gray-300" />
                    </FormSection>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <FormSection title="Vendedor">
                        <div class="grid gap-3">
                            <div>
                                <Label class="text-xs text-gray-500">Vendedor</Label>
                                <Select v-model="form.vendedor_id">
                                    <SelectTrigger class="h-10 rounded-lg border-gray-300">
                                        <SelectValue placeholder="Seleccionar..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="v in vendedores" :key="v.id" :value="v.id.toString()">{{ v.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label class="text-xs text-gray-500">Referido por</Label>
                                <Input v-model="form.referido_por" class="h-10 rounded-lg border-gray-300" />
                            </div>
                        </div>
                    </FormSection>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <FormSection title="Condiciones de pago">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <Label class="text-xs text-gray-500">Anticipo ({{ symbol }})</Label>
                                <Input type="number" min="0" step="0.01" v-model="form.anticipo" class="h-10 rounded-lg border-gray-300" />
                            </div>
                            <div>
                                <Label class="text-xs text-gray-500">N° de cuotas</Label>
                                <Input type="number" min="1" max="24" v-model="form.numero_cuotas" class="h-10 rounded-lg border-gray-300" />
                            </div>
                        </div>
                        <div class="mt-3">
                            <Label class="text-xs text-gray-500">Plazo de crédito (días)</Label>
                            <Input type="number" min="0" v-model="form.plazo_dias" class="h-10 rounded-lg border-gray-300" />
                        </div>
                        <div class="mt-3 rounded-lg bg-gray-50 p-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Saldo a pagar:</span>
                                <span class="font-medium text-blue-600">{{ format(saldo) }}</span>
                            </div>
                        </div>
                    </FormSection>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <FormSection title="Entrega">
                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <Label class="text-xs text-gray-500">Tipo de entrega</Label>
                            <select v-model="form.tipo_entrega" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm">
                                <option value="tienda">Recoger en tienda</option>
                                <option value="provincia">Envío a provincia</option>
                            </select>
                        </div>
                        <div>
                            <Label class="text-xs text-gray-500">Dirección de entrega</Label>
                            <Input v-model="form.direccion_entrega" class="h-10 rounded-lg border-gray-300" />
                        </div>
                        <div>
                            <Label class="text-xs text-gray-500">Ciudad</Label>
                            <Input v-model="form.ciudad" class="h-10 rounded-lg border-gray-300" />
                        </div>
                        <div>
                            <Label class="text-xs text-gray-500">Provincia / Departamento</Label>
                            <Input v-model="form.provincia" class="h-10 rounded-lg border-gray-300" />
                        </div>
                        <div>
                            <Label class="text-xs text-gray-500">Fecha de entrega</Label>
                            <Input type="datetime-local" v-model="form.fecha_entrega" class="h-10 rounded-lg border-gray-300" />
                        </div>
                        <div>
                            <Label class="text-xs text-gray-500">Teléfono de contacto</Label>
                            <Input v-model="form.telefono_contacto" class="h-10 rounded-lg border-gray-300" />
                        </div>
                    </div>
                </FormSection>
            </div>

            <div class="flex items-center gap-3">
                <Button :disabled="form.processing" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg px-6 h-10">
                    <Save class="mr-2 h-4 w-4" /> Actualizar Pedido
                </Button>
                <Link :href="route('admin.pedidos.index')"><Button variant="outline" type="button" class="rounded-lg border-gray-200 text-gray-600 h-10">Cancelar</Button></Link>
            </div>
        </form>
    </div>
</template>
