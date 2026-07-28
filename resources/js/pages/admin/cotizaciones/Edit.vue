<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { Plus, Trash2, FileText } from '@lucide/vue';
import { computed } from 'vue';
import { FormSection } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    cotizacion: {
        id: number;
        cliente_id: number;
        estado: string;
        validez_dias: number;
        notas: string | null;
        condiciones: string | null;
        observaciones: string | null;
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
    productos: { id: number; nombre: string; precio: string; sku: string | null }[];
    servicios: { id: number; nombre: string; precio: string }[];
}>();

const form = useForm({
    cliente_id: props.cotizacion.cliente_id,
    estado: props.cotizacion.estado,
    validez_dias: props.cotizacion.validez_dias,
    notas: props.cotizacion.notas ?? '',
    condiciones: props.cotizacion.condiciones ?? '',
    observaciones: props.cotizacion.observaciones ?? '',
    items: props.cotizacion.detalles.map(d => ({
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
const impuesto = computed(() => subtotal.value * 0.18);
const total = computed(() => subtotal.value + impuesto.value);

function submit() {
    form.put(route('admin.cotizaciones.update', props.cotizacion.id), { preserveScroll: true });
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Clientes' }, { title: 'Cotizaciones', href: route('admin.cotizaciones.index') }, { title: 'Editar', href: '' }]">
        <div class="flex flex-col gap-6 p-6 max-w-4xl">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                    <FileText class="h-5 w-5 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Editar Cotización</h1>
                    <p class="text-sm text-muted-foreground">Modifica los datos de la cotización</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-border bg-card p-6">
                        <FormSection title="Cliente">
                            <select v-model="form.cliente_id" class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm" required>
                                <option value="">Seleccionar...</option>
                                <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                            </select>
                        </FormSection>
                    </div>
                    <div class="rounded-xl border border-border bg-card p-6">
                        <FormSection title="Estado">
                            <select v-model="form.estado" class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm">
                                <option value="borrador">Borrador</option>
                                <option value="enviada">Enviada</option>
                                <option value="aprobada">Aprobada</option>
                                <option value="rechazada">Rechazada</option>
                            </select>
                        </FormSection>
                    </div>
                </div>

                <div class="rounded-xl border border-border bg-card p-6">
                    <FormSection title="Items">
                        <div v-for="(item, index) in form.items" :key="index" class="mb-4 rounded-xl border border-border bg-background/50 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500/20 to-blue-600/20 text-xs font-bold text-cyan-400">{{ index + 1 }}</span>
                                    Item #{{ index + 1 }}
                                </span>
                                <Button variant="destructive" size="sm" class="rounded-lg h-9 w-9" @click="removeItem(index)"><Trash2 class="h-4 w-4" /></Button>
                            </div>
                            <div class="grid gap-3 md:grid-cols-5">
                                <div>
                                    <Label class="text-xs">Tipo</Label>
                                    <select v-model="item.item_type" class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm">
                                        <option value="producto">Producto</option>
                                        <option value="servicio">Servicio</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <Label class="text-xs">Item</Label>
                                    <select :value="item.item_id" @change="onItemSelect(index, item.item_type, ($event.target as HTMLSelectElement).value)" class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm">
                                        <option value="">Seleccionar...</option>
                                        <option v-if="item.item_type === 'producto'" v-for="p in productos" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                                        <option v-if="item.item_type === 'servicio'" v-for="s in servicios" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                                    </select>
                                </div>
                                <div>
                                    <Label class="text-xs">Cant.</Label>
                                    <Input type="number" min="1" v-model="item.cantidad" class="h-11 rounded-xl border-input bg-background" />
                                </div>
                                <div>
                                    <Label class="text-xs">Desc.</Label>
                                    <Input type="number" step="0.01" min="0" v-model="item.descuento" class="h-11 rounded-xl border-input bg-background" />
                                </div>
                            </div>
                        </div>
                        <Button variant="outline" @click="addItem" class="w-full rounded-xl h-11 border-dashed"><Plus class="mr-2 h-4 w-4" /> Agregar Item</Button>
                    </FormSection>
                </div>

                <div class="rounded-xl border border-border bg-card p-6">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-muted-foreground">Subtotal:</span><span>${{ subtotal.toFixed(2) }}</span></div>
                        <div class="flex justify-between"><span class="text-muted-foreground">Impuesto (18%):</span><span>${{ impuesto.toFixed(2) }}</span></div>
                        <div class="flex justify-between text-lg font-bold border-t border-border pt-2 mt-2"><span>Total:</span><span class="text-cyan-400">${{ total.toFixed(2) }}</span></div>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-border bg-card p-6">
                        <FormSection title="Notas"><Textarea v-model="form.notas" rows="3" class="rounded-xl border-input bg-background" /></FormSection>
                    </div>
                    <div class="rounded-xl border border-border bg-card p-6">
                        <FormSection title="Condiciones"><Textarea v-model="form.condiciones" rows="3" class="rounded-xl border-input bg-background" /></FormSection>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 rounded-xl h-11 px-6 hover:from-cyan-600 hover:to-blue-700">
                        Actualizar Cotización
                    </Button>
                    <Link :href="route('admin.cotizaciones.index')"><Button variant="outline" class="rounded-xl h-11 px-6">Cancelar</Button></Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
