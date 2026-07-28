<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, CreditCard } from '@lucide/vue';
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
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    pago: {
        id: number;
        monto: string;
        metodo_pago: string;
        referencia: string | null;
        estado: string;
        fecha_pago: string;
        notas: string | null;
        cotizacion: {
            id: number;
            codigo: string;
            total: string;
            cliente: { id: number; nombre: string } | null;
        };
    };
}>();

const form = useForm({
    monto: props.pago.monto,
    metodo_pago: props.pago.metodo_pago,
    referencia: props.pago.referencia ?? '',
    fecha_pago: props.pago.fecha_pago,
    comprobante: null as File | null,
    notas: props.pago.notas ?? '',
});

function submit() {
    form.put(route('admin.finanzas.pagos.update', props.pago.id), {
        preserveScroll: true,
    });
}

function handleComprobante(e: Event) {
    const target = e.target as HTMLInputElement;

    if (target.files?.length) {
        form.comprobante = target.files[0];
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Finanzas' }, { title: 'Pagos', href: route('admin.finanzas.pagos.index') }, { title: 'Editar Pago #' + pago.id, href: '' }]">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                    <CreditCard class="h-5 w-5 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Editar Pago #{{ pago.id }}</h1>
                    <p class="text-sm text-muted-foreground">Modifica los datos del pago registrado</p>
                </div>
                <div class="ml-auto">
                    <Link :href="route('admin.finanzas.pagos.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
                        <ArrowLeft class="h-4 w-4" /> Volver
                    </Link>
                </div>
            </div>

            <form @submit.prevent="submit" class="mx-auto w-full max-w-2xl space-y-6">
                <div class="rounded-xl border border-border bg-card p-6">
                    <div class="rounded-xl bg-background/50 p-4 text-sm mb-6 space-y-1">
                        <p class="flex justify-between"><span class="text-muted-foreground">Cotización:</span> <span>{{ pago.cotizacion.codigo }} — {{ pago.cotizacion.cliente?.nombre ?? '—' }}</span></p>
                        <p class="flex justify-between"><span class="text-muted-foreground">Total:</span> <span>${{ parseFloat(pago.cotizacion.total).toFixed(2) }}</span></p>
                    </div>

                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="monto">Monto *</Label>
                            <Input id="monto" v-model="form.monto" type="number" step="0.01" min="0.01" placeholder="0.00" class="h-11 rounded-xl border-input bg-background" />
                            <p v-if="form.errors.monto" class="text-sm text-red-500">{{ form.errors.monto }}</p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="metodo_pago">Método de Pago *</Label>
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
                            <p v-if="form.errors.metodo_pago" class="text-sm text-red-500">{{ form.errors.metodo_pago }}</p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="referencia">Referencia</Label>
                            <Input id="referencia" v-model="form.referencia" placeholder="N° de operación, voucher, etc." class="h-11 rounded-xl border-input bg-background" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="fecha_pago">Fecha de Pago *</Label>
                            <Input id="fecha_pago" v-model="form.fecha_pago" type="date" class="h-11 rounded-xl border-input bg-background" />
                            <p v-if="form.errors.fecha_pago" class="text-sm text-red-500">{{ form.errors.fecha_pago }}</p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="comprobante">Comprobante (dejar vacío para mantener el actual)</Label>
                            <Input id="comprobante" type="file" accept=".pdf,.jpg,.jpeg,.png" @input="handleComprobante" class="h-11 rounded-xl border-input bg-background file:rounded-lg file:border-0 file:bg-gradient-to-r file:from-cyan-500 file:to-blue-600 file:text-white file:text-sm file:px-3 file:py-1" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="notas">Notas</Label>
                            <Textarea id="notas" v-model="form.notas" placeholder="Notas adicionales..." rows="3" class="rounded-xl border-input bg-background" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="route('admin.finanzas.pagos.index')"><Button variant="outline" type="button" class="rounded-xl h-11 px-6">Cancelar</Button></Link>
                    <Button type="submit" :disabled="form.processing" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 rounded-xl h-11 px-6 hover:from-cyan-600 hover:to-blue-700">
                        <Save class="mr-2 h-4 w-4" /> Guardar Cambios
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
