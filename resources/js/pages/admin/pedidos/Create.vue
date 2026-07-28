<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { Plus, Trash2, ShoppingCart, Minus, Search, Package, Wrench, CreditCard } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { FormSection } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    clientes: { id: number; nombre: string; documento: string | null; email: string | null }[];
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
    metodo_pago: '',
    pagado: false,
    notas: '',
    items: [] as Array<{
        item_type: string;
        item_id: number | string;
        item_nombre: string;
        cantidad: number;
        precio_unitario: number;
        descuento: number;
    }>,
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

const impuesto = computed(() => subtotal.value * 0.18);
const total = computed(() => subtotal.value + impuesto.value);

const itemCount = computed(() => form.items.reduce((sum, i) => sum + i.cantidad, 0));

function submit(pagado: boolean) {
    form.pagado = pagado;
    form.post(route('admin.pedidos.store'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Ventas' }, { title: 'Pedidos', href: route('admin.pedidos.index') }, { title: 'Nuevo Pedido', href: '' }]">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                    <ShoppingCart class="h-5 w-5 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Nuevo Pedido</h1>
                    <p class="text-sm text-muted-foreground">Selecciona productos o servicios para crear un pedido</p>
                </div>
            </div>

            <form @submit.prevent="submit(false)" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="rounded-xl border border-border bg-card p-6">
                            <div class="mb-4 flex items-center justify-between">
                                <FormSection title="Catálogo">
                                    <template #description>Busca y agrega productos o servicios al carrito</template>
                                </FormSection>
                                <Badge class="rounded-full px-3 py-1 text-xs" variant="secondary">
                                    {{ itemCount }} items
                                </Badge>
                            </div>

                            <div class="relative mb-4">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input v-model="searchQuery" placeholder="Buscar productos o servicios..." class="h-11 rounded-xl border-input bg-background pl-10" />
                            </div>

                            <div class="mb-4 flex gap-2">
                                <Button
                                    type="button"
                                    :variant="tab === 'productos' ? 'default' : 'outline'"
                                    size="sm"
                                    class="rounded-xl h-9"
                                    @click="tab = 'productos'"
                                >
                                    <Package class="mr-1.5 h-4 w-4" /> Productos
                                </Button>
                                <Button
                                    type="button"
                                    :variant="tab === 'servicios' ? 'default' : 'outline'"
                                    size="sm"
                                    class="rounded-xl h-9"
                                    @click="tab = 'servicios'"
                                >
                                    <Wrench class="mr-1.5 h-4 w-4" /> Servicios
                                </Button>
                            </div>

                            <div v-if="tab === 'productos'" class="grid gap-3 sm:grid-cols-2">
                                <div
                                    v-for="p in filteredProductos"
                                    :key="p.id"
                                    class="group relative flex items-center gap-3 rounded-xl border border-border bg-background/50 p-3 transition-all hover:border-cyan-500/50 hover:bg-cyan-500/5"
                                >
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500/20 to-blue-600/20 text-lg font-bold text-cyan-400">
                                        {{ p.nombre.charAt(0) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium truncate">{{ p.nombre }}</p>
                                        <p class="text-xs text-muted-foreground">${{ parseFloat(p.precio).toFixed(2) }}</p>
                                        <p v-if="p.sku" class="text-xs text-muted-foreground">SKU: {{ p.sku }}</p>
                                    </div>
                                    <Button
                                        type="button"
                                        size="sm"
                                        class="shrink-0 rounded-lg h-9 w-9 p-0 bg-gradient-to-r from-cyan-500 to-blue-600 text-white"
                                        @click="addToCart('producto', p.id, p.nombre, p.precio)"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                                <div v-if="filteredProductos.length === 0" class="col-span-full py-8 text-center text-sm text-muted-foreground">
                                    No se encontraron productos
                                </div>
                            </div>

                            <div v-if="tab === 'servicios'" class="grid gap-3 sm:grid-cols-2">
                                <div
                                    v-for="s in filteredServicios"
                                    :key="s.id"
                                    class="group relative flex items-center gap-3 rounded-xl border border-border bg-background/50 p-3 transition-all hover:border-cyan-500/50 hover:bg-cyan-500/5"
                                >
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500/20 to-pink-600/20 text-lg font-bold text-purple-400">
                                        {{ s.nombre.charAt(0) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium truncate">{{ s.nombre }}</p>
                                        <p class="text-xs text-muted-foreground">${{ parseFloat(s.precio).toFixed(2) }}</p>
                                        <p v-if="s.duracion_estimada" class="text-xs text-muted-foreground">{{ s.duracion_estimada }}</p>
                                    </div>
                                    <Button
                                        type="button"
                                        size="sm"
                                        class="shrink-0 rounded-lg h-9 w-9 p-0 bg-gradient-to-r from-purple-500 to-pink-600 text-white"
                                        @click="addToCart('servicio', s.id, s.nombre, s.precio)"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                                <div v-if="filteredServicios.length === 0" class="col-span-full py-8 text-center text-sm text-muted-foreground">
                                    No se encontraron servicios
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-xl border border-border bg-card p-6">
                            <FormSection title="Carrito">
                                <template #description>{{ itemCount }} items seleccionados</template>
                            </FormSection>

                            <div v-if="form.items.length === 0" class="py-8 text-center">
                                <ShoppingCart class="mx-auto h-10 w-10 text-muted-foreground/50 mb-2" />
                                <p class="text-sm text-muted-foreground">El carrito está vacío</p>
                                <p class="text-xs text-muted-foreground">Agrega productos o servicios desde el catálogo</p>
                            </div>

                            <div v-for="(item, index) in form.items" :key="index" class="mb-3 rounded-xl border border-border bg-background/50 p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium truncate flex-1">{{ item.item_nombre }}</span>
                                    <Button type="button" variant="ghost" size="sm" class="h-7 w-7 p-0 rounded-lg text-red-500 shrink-0 ml-2" @click="removeItem(index)">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <Button type="button" variant="outline" size="sm" class="h-7 w-7 p-0 rounded-lg" @click="updateCantidad(index, -1)">
                                            <Minus class="h-3 w-3" />
                                        </Button>
                                        <span class="w-8 text-center text-sm font-medium">{{ item.cantidad }}</span>
                                        <Button type="button" variant="outline" size="sm" class="h-7 w-7 p-0 rounded-lg" @click="updateCantidad(index, 1)">
                                            <Plus class="h-3 w-3" />
                                        </Button>
                                    </div>
                                    <span class="text-sm font-medium text-cyan-400">
                                        ${{ (item.cantidad * item.precio_unitario - item.descuento * item.cantidad).toFixed(2) }}
                                    </span>
                                </div>
                                <div class="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                                    <span>${{ parseFloat(String(item.precio_unitario)).toFixed(2) }} c/u</span>
                                    <span v-if="item.descuento">· Desc: ${{ (item.descuento * item.cantidad).toFixed(2) }}</span>
                                </div>
                            </div>

                            <div class="mt-4 space-y-1.5 border-t border-border pt-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Subtotal:</span>
                                    <span>${{ subtotal.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Impuesto (18%):</span>
                                    <span>${{ impuesto.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold border-t border-border pt-2 mt-2">
                                    <span>Total:</span>
                                    <span class="text-cyan-400">${{ total.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl border border-border bg-card p-6">
                        <FormSection title="Cliente">
                            <div class="grid gap-2">
                                <Label for="cliente_id">Cliente *</Label>
                                <Select v-model="form.cliente_id">
                                    <SelectTrigger id="cliente_id" class="h-11 rounded-xl">
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

                    <div class="rounded-xl border border-border bg-card p-6">
                        <FormSection title="Pago">
                            <div class="grid gap-3">
                                <div class="grid gap-2">
                                    <Label for="metodo_pago">Método de Pago</Label>
                                    <Select v-model="form.metodo_pago">
                                        <SelectTrigger id="metodo_pago" class="h-11 rounded-xl">
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
                                <div class="rounded-xl bg-background/50 p-3 text-sm">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" v-model="form.pagado" class="h-4 w-4 rounded border-border accent-cyan-500" />
                                        <span class="font-medium">Marcar como pagado</span>
                                    </label>
                                </div>
                            </div>
                        </FormSection>
                    </div>
                </div>

                <div class="rounded-xl border border-border bg-card p-6">
                    <FormSection title="Notas">
                        <Textarea v-model="form.notas" rows="3" placeholder="Notas adicionales para este pedido..." class="rounded-xl border-input bg-background" />
                        <InputError :message="form.errors.notas" />
                    </FormSection>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        type="submit"
                        :disabled="form.processing || form.items.length === 0"
                        class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 rounded-xl h-11 px-6 hover:from-cyan-600 hover:to-blue-700"
                    >
                        <ShoppingCart class="mr-2 h-4 w-4" /> Crear Pedido
                    </Button>
                    <Button
                        type="button"
                        :disabled="form.processing || form.items.length === 0"
                        class="bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-lg shadow-green-500/25 rounded-xl h-11 px-6 hover:from-green-600 hover:to-emerald-700"
                        @click="submit(true)"
                    >
                        <CreditCard class="mr-2 h-4 w-4" /> Crear y Cobrar
                    </Button>
                    <Link :href="route('admin.pedidos.index')">
                        <Button variant="outline" type="button" class="rounded-xl h-11 px-6">Cancelar</Button>
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
