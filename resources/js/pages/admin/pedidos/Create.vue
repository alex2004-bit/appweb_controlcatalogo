<script setup lang="ts">
import { useForm, Link, Head } from '@inertiajs/vue3';
import { Plus, Trash2, ShoppingCart, Minus, Search, Package, Wrench, CreditCard, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { ModuleHeader, FormSection } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
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
    clientes: { id: number; nombre: string; documento: string | null; email: string | null; telefono: string | null; direccion: string | null }[];
    vendedores: { id: number; name: string }[];
    productos: { id: number; nombre: string; precio: string; sku: string | null; imagen: string | null }[];
    servicios: { id: number; nombre: string; precio: string; imagen: string | null; duracion_estimada: string | null }[];
}>();

const searchQuery = ref('');
const tab = ref<'productos' | 'servicios'>('productos');

const filteredProductos = computed(() =>
    props.productos.filter(p =>
        p.nombre.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (p.sku && p.sku.toLowerCase().includes(searchQuery.value.toLowerCase()))
    )
);

const filteredServicios = computed(() =>
    props.servicios.filter(s =>
        s.nombre.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
);

const form = useForm({
    cliente_id: '',
    vendedor_id: '',
    referido_por: '',
    metodo_pago: '',
    pagado: false,
    notas: '',
    direccion_entrega: '',
    ciudad: '',
    provincia: '',
    tipo_entrega: 'tienda',
    fecha_entrega: '',
    telefono_contacto: '',
    anticipo: '',
    numero_cuotas: '1',
    plazo_dias: '',
    items: [] as Array<{
        item_type: string;
        item_id: number | string;
        item_nombre: string;
        cantidad: number;
        precio_unitario: number;
        descuento: number;
    }>,
});

const selectedCliente = computed(() => props.clientes.find(c => c.id === Number(form.cliente_id)));
watch(selectedCliente, c => {
    if (c && !form.direccion_entrega && c.direccion) {
form.direccion_entrega = c.direccion;
}

    if (c && !form.telefono_contacto && c.telefono) {
form.telefono_contacto = c.telefono;
}
});

function addToCart(type: string, id: number, nombre: string, precio: string) {
    const existing = form.items.find(i => i.item_type === type && i.item_id === id);

    if (existing) {
        existing.cantidad++;
    } else {
        form.items.push({
            item_type: type,
            item_id: id,
            item_nombre: nombre,
            cantidad: 1,
            precio_unitario: parseFloat(precio),
            descuento: 0,
        });
    }
}

function removeItem(index: number) {
    form.items.splice(index, 1);
}

function updateCantidad(index: number, delta: number) {
    const item = form.items[index];
    const newCant = item.cantidad + delta;

    if (newCant >= 1) {
        item.cantidad = newCant;
    }
}

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + (item.cantidad * item.precio_unitario) - (item.descuento * item.cantidad);
    }, 0);
});

const taxRate = useTaxRate();
const impuesto = computed(() => subtotal.value * taxRate.value / 100);
const total = computed(() => subtotal.value + impuesto.value);
const anticipoNum = computed(() => parseFloat(form.anticipo) || 0);
const saldo = computed(() => Math.max(total.value - anticipoNum.value, 0));

const itemCount = computed(() => form.items.reduce((sum, i) => sum + i.cantidad, 0));

const { format, symbol } = useMoney();

function submit(pagado: boolean) {
    form.pagado = pagado;
    form.post(route('admin.pedidos.store'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Nuevo Pedido" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Nuevo Pedido" description="Selecciona productos o servicios para crear un pedido" :icon="ShoppingCart">
            <template #actions>
                <Link :href="route('admin.pedidos.index')">
                    <Button variant="outline" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800">
                        <X class="mr-2 h-4 w-4" /> Cancelar
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <form @submit.prevent="submit(false)" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <FormSection title="Catálogo">
                                <template #description>Busca y agrega productos o servicios al carrito</template>
                            </FormSection>
                            <Badge class="rounded-full px-3 py-1 text-xs" variant="secondary">
                                {{ itemCount }} items
                            </Badge>
                        </div>

                        <div class="relative mb-4">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input v-model="searchQuery" placeholder="Buscar productos o servicios..." class="h-10 rounded-lg border-gray-300 bg-white pl-10" />
                        </div>

                        <div class="mb-4 flex gap-2">
                            <Button type="button" :variant="tab === 'productos' ? 'default' : 'outline'" size="sm" class="rounded-lg h-9" @click="tab = 'productos'">
                                <Package class="mr-1.5 h-4 w-4" /> Productos
                            </Button>
                            <Button type="button" :variant="tab === 'servicios' ? 'default' : 'outline'" size="sm" class="rounded-lg h-9" @click="tab = 'servicios'">
                                <Wrench class="mr-1.5 h-4 w-4" /> Servicios
                            </Button>
                        </div>

                        <div v-if="tab === 'productos'" class="grid gap-3 sm:grid-cols-2">
                            <div v-for="p in filteredProductos" :key="p.id" class="group relative flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 transition-all hover:border-blue-300 hover:bg-blue-50">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-lg font-bold text-blue-600">
                                    {{ p.nombre.charAt(0) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ p.nombre }}</p>
                                    <p class="text-xs text-gray-500">{{ format(p.precio) }}</p>
                                    <p v-if="p.sku" class="text-xs text-gray-400">SKU: {{ p.sku }}</p>
                                </div>
                                <Button type="button" size="sm" class="shrink-0 rounded-lg h-9 w-9 p-0 bg-blue-600 text-white" @click="addToCart('producto', p.id, p.nombre, p.precio)">
                                    <Plus class="h-4 w-4" />
                                </Button>
                            </div>
                            <div v-if="filteredProductos.length === 0" class="col-span-full py-8 text-center text-sm text-gray-500">
                                No se encontraron productos
                            </div>
                        </div>

                        <div v-if="tab === 'servicios'" class="grid gap-3 sm:grid-cols-2">
                            <div v-for="s in filteredServicios" :key="s.id" class="group relative flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 transition-all hover:border-blue-300 hover:bg-blue-50">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-purple-100 text-lg font-bold text-purple-600">
                                    {{ s.nombre.charAt(0) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ s.nombre }}</p>
                                    <p class="text-xs text-gray-500">{{ format(s.precio) }}</p>
                                    <p v-if="s.duracion_estimada" class="text-xs text-gray-400">{{ s.duracion_estimada }}</p>
                                </div>
                                <Button type="button" size="sm" class="shrink-0 rounded-lg h-9 w-9 p-0 bg-blue-600 text-white" @click="addToCart('servicio', s.id, s.nombre, s.precio)">
                                    <Plus class="h-4 w-4" />
                                </Button>
                            </div>
                            <div v-if="filteredServicios.length === 0" class="col-span-full py-8 text-center text-sm text-gray-500">
                                No se encontraron servicios
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <FormSection title="Carrito">
                            <template #description>{{ itemCount }} items seleccionados</template>
                        </FormSection>

                        <div v-if="form.items.length === 0" class="py-8 text-center">
                            <ShoppingCart class="mx-auto h-10 w-10 text-gray-300 mb-2" />
                            <p class="text-sm text-gray-500">El carrito está vacío</p>
                            <p class="text-xs text-gray-400">Agrega productos o servicios desde el catálogo</p>
                        </div>

                        <div v-for="(item, index) in form.items" :key="index" class="mb-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900 truncate flex-1">{{ item.item_nombre }}</span>
                                <Button type="button" variant="ghost" size="sm" class="h-7 w-7 p-0 rounded-lg text-red-500 shrink-0 ml-2" @click="removeItem(index)">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </Button>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <Button type="button" variant="outline" size="sm" class="h-7 w-7 p-0 rounded-lg border-gray-200" @click="updateCantidad(index, -1)">
                                        <Minus class="h-3 w-3" />
                                    </Button>
                                    <span class="w-8 text-center text-sm font-medium text-gray-900">{{ item.cantidad }}</span>
                                    <Button type="button" variant="outline" size="sm" class="h-7 w-7 p-0 rounded-lg border-gray-200" @click="updateCantidad(index, 1)">
                                        <Plus class="h-3 w-3" />
                                    </Button>
                                </div>
                                <span class="text-sm font-medium text-blue-600">
                                    {{ format(item.cantidad * item.precio_unitario - item.descuento * item.cantidad) }}
                                </span>
                            </div>
                            <div class="mt-1 flex items-center gap-2 text-xs text-gray-500">
                                <span>{{ format(item.precio_unitario) }} c/u</span>
                                <span v-if="item.descuento">&middot; Desc: {{ format(item.descuento * item.cantidad) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 space-y-1.5 border-t border-gray-200 pt-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Subtotal:</span>
                                <span class="text-gray-900">{{ format(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Impuesto ({{ taxRate }}%):</span>
                                <span class="text-gray-900">{{ format(impuesto) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2 mt-2">
                                <span class="text-gray-900">Total:</span>
                                <span class="text-blue-600">{{ format(total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <FormSection title="Cliente">
                        <div class="grid gap-1.5">
                            <Label for="cliente_id">Cliente *</Label>
                            <Select v-model="form.cliente_id">
                                <SelectTrigger id="cliente_id" class="h-10 rounded-lg border-gray-300">
                                    <SelectValue placeholder="Seleccionar cliente..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="c in clientes" :key="c.id" :value="c.id.toString()">
                                        {{ c.nombre }} {{ c.documento ? `(${c.documento})` : '' }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.cliente_id" />
                        </div>
                    </FormSection>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <FormSection title="Pago">
                        <div class="grid gap-3">
                            <div class="grid gap-1.5">
                                <Label for="metodo_pago">Método de Pago</Label>
                                <Select v-model="form.metodo_pago">
                                    <SelectTrigger id="metodo_pago" class="h-10 rounded-lg border-gray-300">
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
                                    <span class="font-medium text-gray-900">Marcar como pagado</span>
                                </label>
                            </div>
                        </div>
                    </FormSection>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <FormSection title="Vendedor">
                        <div class="grid gap-3">
                            <div class="grid gap-1.5">
                                <Label for="vendedor_id">Vendedor</Label>
                                <Select v-model="form.vendedor_id">
                                    <SelectTrigger id="vendedor_id" class="h-10 rounded-lg border-gray-300">
                                        <SelectValue placeholder="Seleccionar vendedor..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="v in vendedores" :key="v.id" :value="v.id.toString()">
                                            {{ v.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="grid gap-1.5">
                                <Label for="referido_por">Referido por</Label>
                                <Input id="referido_por" v-model="form.referido_por" placeholder="Nombre de quién refirió el pedido..." class="h-10 rounded-lg border-gray-300" />
                                <InputError :message="form.errors.referido_por" />
                            </div>
                        </div>
                    </FormSection>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <FormSection title="Condiciones de pago">
                        <div class="grid gap-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="grid gap-1.5">
                                    <Label for="anticipo">Anticipo ({{ symbol }})</Label>
                                    <Input id="anticipo" type="number" min="0" step="0.01" v-model="form.anticipo" placeholder="0.00" class="h-10 rounded-lg border-gray-300" />
                                    <InputError :message="form.errors.anticipo" />
                                </div>
                                <div class="grid gap-1.5">
                                    <Label for="numero_cuotas">N° de cuotas</Label>
                                    <Input id="numero_cuotas" type="number" min="1" max="24" v-model="form.numero_cuotas" class="h-10 rounded-lg border-gray-300" />
                                    <InputError :message="form.errors.numero_cuotas" />
                                </div>
                            </div>
                            <div class="grid gap-1.5">
                                <Label for="plazo_dias">Plazo de crédito (días)</Label>
                                <Input id="plazo_dias" type="number" min="0" v-model="form.plazo_dias" placeholder="Ej: 30" class="h-10 rounded-lg border-gray-300" />
                                <InputError :message="form.errors.plazo_dias" />
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Saldo a pagar:</span>
                                    <span class="font-medium text-blue-600">{{ format(saldo) }}</span>
                                </div>
                            </div>
                        </div>
                    </FormSection>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <FormSection title="Entrega">
                    <template #description>Datos de entrega o instalación del pedido</template>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="tipo_entrega">Tipo de entrega</Label>
                            <select id="tipo_entrega" v-model="form.tipo_entrega" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm">
                                <option value="tienda">Recoger en tienda</option>
                                <option value="provincia">Envío a provincia</option>
                            </select>
                            <InputError :message="form.errors.tipo_entrega" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="direccion_entrega">Dirección de entrega</Label>
                            <Input id="direccion_entrega" v-model="form.direccion_entrega" placeholder="Dirección..." class="h-10 rounded-lg border-gray-300" />
                            <InputError :message="form.errors.direccion_entrega" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="ciudad">Ciudad</Label>
                            <Input id="ciudad" v-model="form.ciudad" placeholder="Ciudad..." class="h-10 rounded-lg border-gray-300" />
                            <InputError :message="form.errors.ciudad" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="provincia">Provincia / Departamento</Label>
                            <Input id="provincia" v-model="form.provincia" placeholder="Provincia..." class="h-10 rounded-lg border-gray-300" />
                            <InputError :message="form.errors.provincia" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="fecha_entrega">Fecha de entrega</Label>
                            <Input id="fecha_entrega" type="datetime-local" v-model="form.fecha_entrega" class="h-10 rounded-lg border-gray-300" />
                            <InputError :message="form.errors.fecha_entrega" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="telefono_contacto">Teléfono de contacto</Label>
                            <Input id="telefono_contacto" v-model="form.telefono_contacto" placeholder="Teléfono..." class="h-10 rounded-lg border-gray-300" />
                            <InputError :message="form.errors.telefono_contacto" />
                        </div>
                    </div>
                </FormSection>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <FormSection title="Notas">
                    <Textarea v-model="form.notas" rows="3" placeholder="Notas adicionales para este pedido..." class="rounded-lg border-gray-300" />
                    <InputError :message="form.errors.notas" />
                </FormSection>
            </div>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="form.processing || form.items.length === 0" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg px-6 h-10">
                    <ShoppingCart class="mr-2 h-4 w-4" /> Crear Pedido
                </Button>
                <Button type="button" :disabled="form.processing || form.items.length === 0" class="bg-green-600 text-white hover:bg-green-700 shadow-sm rounded-lg px-6 h-10" @click="submit(true)">
                    <CreditCard class="mr-2 h-4 w-4" /> Crear y Cobrar
                </Button>
                <Link :href="route('admin.pedidos.index')">
                    <Button variant="outline" type="button" class="rounded-lg border-gray-200 text-gray-600 h-10">Cancelar</Button>
                </Link>
            </div>
        </form>
    </div>
</template>
