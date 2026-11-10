<script setup lang="ts">
import { useForm, Link, Head } from '@inertiajs/vue3';
import { Plus, Trash2, FileText, Save, X, Search, ChevronDown, Package, Wrench, Truck, Calendar } from '@lucide/vue';
import { ref, computed } from 'vue';
import { FormSection, ModuleHeader } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useMoney } from '@/composables/useMoney';
import { useTaxRate } from '@/composables/useTaxRate';

const props = defineProps<{
    clientes: { id: number; nombre: string; documento: string | null; email: string | null; telefono: string | null }[];
    productos: { id: number; nombre: string; precio: string; sku: string | null }[];
    servicios: { id: number; nombre: string; precio: string }[];
}>();

const form = useForm({
    cliente_id: '',
    validez_dias: 30,
    notas: '',
    condiciones: '',
    observaciones: '',
    items: [] as Array<{
        item_type: string;
        item_id: number | string;
        item_nombre: string;
        cantidad: number;
        precio_unitario: number;
        descuento: number;
    }>,
});

const clienteSearch = ref('');
const showClienteDropdown = ref(false);

const filteredClientes = computed(() => {
    if (!clienteSearch.value) {
return props.clientes.slice(0, 10);
}

    const search = clienteSearch.value.toLowerCase();

    return props.clientes.filter(c =>
        c.nombre.toLowerCase().includes(search) ||
        c.documento?.toLowerCase().includes(search) ||
        c.email?.toLowerCase().includes(search)
    ).slice(0, 10);
});

function addItem() {
    form.items.push({
        item_type: 'producto',
        item_id: '',
        item_nombre: '',
        cantidad: 1,
        precio_unitario: 0,
        descuento: 0,
    });
}

function removeItem(index: number) {
    form.items.splice(index, 1);
}

function onItemSelect(index: number, type: string, id: string) {
    const item = form.items[index];
    item.item_type = type;
    item.item_id = id;

    const catalog = type === 'producto' ? props.productos : props.servicios;
    const found = catalog.find((p: any) => String(p.id) === id);

    if (found) {
        item.item_nombre = found.nombre;
        item.precio_unitario = parseFloat(found.precio);
    }
}

function onItemTypeChange(index: number, type: string) {
    const item = form.items[index];

    if (item.item_type !== type) {
        item.item_type = type;
        item.item_id = '';
        item.item_nombre = '';
        item.precio_unitario = 0;
    }
}

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + (item.cantidad * item.precio_unitario) - (item.descuento * item.cantidad);
    }, 0);
});

const totalDescuento = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.descuento * item.cantidad), 0);
});

const taxRate = useTaxRate();
const impuesto = computed(() => subtotal.value * taxRate.value / 100);
const total = computed(() => subtotal.value + impuesto.value);

function submit() {
    form.post(route('admin.cotizaciones.store'), {
        preserveScroll: true,
    });
}

function selectCliente(cliente: typeof props.clientes[0]) {
    form.cliente_id = String(cliente.id);
    clienteSearch.value = `${cliente.nombre}${cliente.documento ? ` (${cliente.documento})` : ''}`;
    showClienteDropdown.value = false;
}

function closeClienteDropdown() {
    window.setTimeout(() => {
        showClienteDropdown.value = false;
    }, 150);
}

function handleItemTypeChange(index: number, value: unknown) {
    if (value === 'producto' || value === 'servicio') {
        onItemTypeChange(index, value);
    }
}

function handleItemSelect(index: number, type: string, value: unknown) {
    if (typeof value === 'string' || typeof value === 'number') {
        onItemSelect(index, type, String(value));
    }
}

const { format } = useMoney();
</script>

<template>
    <Head title="Nueva Cotización" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Nueva Cotización" description="Crea un nuevo presupuesto para un cliente" :icon="FileText">
            <template #actions>
                <Link :href="route('admin.cotizaciones.index')">
                    <Button variant="outline" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800">
                        <X class="mr-2 h-4 w-4" /> Cancelar
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <form @submit.prevent="submit" class="flex flex-col lg:flex-row gap-6">
            <div class="flex-1 min-w-0 space-y-5">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <Truck class="h-4 w-4" />
                        </div>
                        <h3 class="text-base font-semibold text-gray-900">Información del Cliente</h3>
                    </div>

                    <div class="relative">
                        <Label for="cliente_search" class="text-sm font-medium text-gray-700">Cliente *</Label>
                        <div class="relative mt-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                            <Input
                                id="cliente_search"
                                ref="clienteInput"
                                v-model="clienteSearch"
                                placeholder="Buscar cliente por nombre, documento o email..."
                                class="h-11 pl-10 pr-10 rounded-lg border-gray-300 bg-white"
                                @focus="showClienteDropdown = true"
                                @click="showClienteDropdown = true"
                                @blur="closeClienteDropdown"
                                autocomplete="off"
                                required
                            />
                            <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" :class="{ 'rotate-180': showClienteDropdown }" />
                        </div>

                        <Transition enter-active-class="transition-opacity duration-150" leave-active-class="transition-opacity duration-150">
                            <div v-show="showClienteDropdown && (clienteSearch || filteredClientes.length)" class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg max-h-60 overflow-auto">
                                <div v-if="filteredClientes.length === 0" class="px-3 py-3 text-center text-sm text-gray-500">
                                    No se encontraron clientes
                                </div>
                                <div v-for="cliente in filteredClientes" :key="cliente.id" class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors"
                                    @click="selectCliente(cliente)">
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 truncate">{{ cliente.nombre }}</p>
                                            <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500">
                                                <span v-if="cliente.documento" class="font-mono bg-gray-100 px-1.5 py-0.5 rounded">{{ cliente.documento }}</span>
                                                <span v-if="cliente.email" class="truncate max-w-xs">{{ cliente.email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Transition>

                        <InputError :message="form.errors.cliente_id" class="mt-1" />
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                            <Package class="h-4 w-4" />
                        </div>
                        <h3 class="text-base font-semibold text-gray-900">Items de la Cotización</h3>
                    </div>

                    <div v-if="form.items.length === 0" class="text-center py-10 rounded-lg border-2 border-dashed border-gray-200">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 mx-auto mb-3">
                            <Package class="h-6 w-6 text-gray-400" />
                        </div>
                        <p class="text-gray-500 mb-3">No hay items agregados</p>
                        <Button variant="outline" @click="addItem" class="gap-2">
                            <Plus class="h-4 w-4" /> Agregar primer item
                        </Button>
                    </div>

                    <div v-else class="space-y-3">
                        <div v-for="(item, index) in form.items" :key="index" class="rounded-lg border border-gray-200 bg-white p-4 transition-all hover:border-gray-300">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-sm font-bold text-blue-600">{{ index + 1 }}</span>
                                    <div>
                                        <p class="font-medium text-gray-900">Item #{{ index + 1 }}</p>
                                        <p class="text-xs text-gray-500">{{ item.item_type === 'producto' ? 'Producto' : 'Servicio' }}</p>
                                    </div>
                                </div>
                                <Button variant="ghost" size="icon" class="text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg" @click="removeItem(index)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>

                            <div class="grid gap-3 md:grid-cols-12">
                                <div class="md:col-span-3">
                                    <Label class="text-xs font-medium text-gray-600">Tipo</Label>
                                    <Select v-model="item.item_type" @update:model-value="val => handleItemTypeChange(index, val)">
                                        <SelectTrigger class="h-10"><SelectValue placeholder="Tipo" /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="producto"><Package class="mr-2 h-3 w-3" /> Producto</SelectItem>
                                            <SelectItem value="servicio"><Wrench class="mr-2 h-3 w-3" /> Servicio</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="md:col-span-5">
                                    <Label class="text-xs font-medium text-gray-600">Item</Label>
                                    <Select :value="item.item_id" @update:model-value="val => handleItemSelect(index, item.item_type, val)">
                                        <SelectTrigger class="h-10"><SelectValue placeholder="Seleccionar..." /></SelectTrigger>
                                        <SelectContent>
                                            <template v-if="item.item_type === 'producto'">
                                                <SelectItem v-for="p in productos" :key="p.id" :value="String(p.id)">
                                                    <div class="flex items-center justify-between w-full">
                                                        <span>{{ p.nombre }}</span>
                                                        <span class="text-sm font-medium text-blue-600">{{ format(parseFloat(p.precio)) }}</span>
                                                    </div>
                                                </SelectItem>
                                            </template>
                                            <template v-else>
                                                <SelectItem v-for="s in servicios" :key="s.id" :value="String(s.id)">
                                                    <div class="flex items-center justify-between w-full">
                                                        <span>{{ s.nombre }}</span>
                                                        <span class="text-sm font-medium text-emerald-600">{{ format(parseFloat(s.precio)) }}</span>
                                                    </div>
                                                </SelectItem>
                                            </template>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="md:col-span-2">
                                    <Label class="text-xs font-medium text-gray-600">Cant.</Label>
                                    <Input type="number" min="1" step="1" v-model="item.cantidad" class="h-10 rounded-lg border-gray-300 text-center" />
                                </div>

                                <div class="md:col-span-2">
                                    <Label class="text-xs font-medium text-gray-600">Precio Unit.</Label>
                                    <Input type="number" step="0.01" min="0" v-model="item.precio_unitario" class="h-10 rounded-lg border-gray-300 text-right" />
                                </div>

                                <div class="md:col-span-2">
                                    <Label class="text-xs font-medium text-gray-600">Desc. Unit.</Label>
                                    <Input type="number" step="0.01" min="0" v-model="item.descuento" class="h-10 rounded-lg border-gray-300 text-right" />
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span class="text-gray-500">Subtotal del item:</span>
                                <span class="font-semibold text-gray-900">{{ format(item.cantidad * item.precio_unitario - item.descuento * item.cantidad) }}</span>
                            </div>
                        </div>

                        <Button variant="outline" @click="addItem" class="w-full rounded-lg h-11 border-dashed border-gray-200 hover:border-blue-300 hover:bg-blue-50 text-blue-600 transition-all">
                            <Plus class="mr-2 h-4 w-4" /> Agregar otro item
                        </Button>
                        <InputError :message="form.errors.items" class="mt-1" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <FormSection title="Notas">
                            <Textarea v-model="form.notas" rows="3" placeholder="Notas internas para el equipo..." class="rounded-lg border-gray-300" />
                        </FormSection>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <FormSection title="Condiciones">
                            <Textarea v-model="form.condiciones" rows="3" placeholder="Términos y condiciones de la cotización..." class="rounded-lg border-gray-300" />
                        </FormSection>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <FormSection title="Observaciones">
                        <Textarea v-model="form.observaciones" rows="2" placeholder="Observaciones adicionales para el cliente..." class="rounded-lg border-gray-300" />
                    </FormSection>
                </div>
            </div>

            <aside class="lg:w-72 shrink-0">
                <div class="sticky top-24 space-y-4">
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                                <Calendar class="h-4 w-4" />
                            </div>
                            <h3 class="text-base font-semibold text-gray-900">Validez</h3>
                        </div>
                        <Label class="text-sm font-medium text-gray-700">Días de validez</Label>
                        <Input type="number" min="1" max="365" v-model="form.validez_dias" class="h-10 mt-1 rounded-lg border-gray-300 text-center" />
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                <FileText class="h-4 w-4" />
                            </div>
                            <h3 class="text-base font-semibold text-gray-900">Resumen</h3>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal ({{
                                    form.items.reduce((sum, i) => sum + i.cantidad, 0)
                                }} items):</span>
                                <span class="font-medium text-gray-900">{{ format(subtotal + totalDescuento) }}</span>
                            </div>
                            <div v-if="totalDescuento > 0" class="flex justify-between text-emerald-600">
                                <span>Descuentos:</span>
                                <span class="font-medium">- {{ format(totalDescuento) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal neto:</span>
                                <span class="font-medium text-gray-900">{{ format(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Impuesto ({{ taxRate }}%):</span>
                                <span class="font-medium text-gray-900">{{ format(impuesto) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-3 mt-3">
                                <span class="text-gray-900">Total:</span>
                                <span class="text-blue-600">{{ format(total) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-gradient-to-br from-blue-600 to-blue-700 p-5 shadow-lg shadow-blue-600/25 text-white">
                        <div class="flex items-center gap-2 mb-3">
                            <Save class="h-5 w-5" />
                            <span class="font-semibold">Crear Cotización</span>
                        </div>
                        <p class="text-xs text-blue-100 mb-4">Se generará el PDF automáticamente</p>
                        <Button :disabled="form.processing || form.items.length === 0 || !form.cliente_id" class="w-full bg-white text-blue-600 hover:bg-blue-50 shadow-lg rounded-lg h-11 font-medium">
                            <Save class="mr-2 h-4 w-4" /> {{ form.processing ? 'Creando...' : 'Crear Cotización' }}
                        </Button>
                    </div>
                </div>
            </aside>
        </form>
    </div>
</template>