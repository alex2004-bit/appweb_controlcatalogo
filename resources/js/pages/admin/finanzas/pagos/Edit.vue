<script setup lang="ts">
import { Link, useForm, Head } from '@inertiajs/vue3';
import { CreditCard, X } from '@lucide/vue';
import { ModuleHeader } from '@/components/shared';
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

const { format } = useMoney();
</script>

<template>
    <Head title="Editar Pago" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Editar Pago" description="Modifica los datos del pago registrado" :icon="CreditCard">
            <template #actions>
                <Link :href="route('admin.finanzas.pagos.index')">
                    <Button variant="outline" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800">
                        <X class="mr-2 h-4 w-4" /> Cancelar
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <form @submit.prevent="submit" class="mx-auto w-full max-w-2xl space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="rounded-lg bg-gray-50 p-4 text-sm mb-6 space-y-1">
                    <p class="flex justify-between"><span class="text-gray-500">Cotización:</span> <span class="text-gray-900">{{ pago.cotizacion.codigo }} — {{ pago.cotizacion.cliente?.nombre ?? '—' }}</span></p>
                    <p class="flex justify-between"><span class="text-gray-500">Total:</span> <span class="text-gray-900">{{ format(pago.cotizacion.total) }}</span></p>
                </div>

                <div class="grid gap-4">
                    <div class="grid gap-1.5">
                        <Label for="monto">Monto *</Label>
                        <Input id="monto" v-model="form.monto" type="number" step="0.01" min="0.01" placeholder="0.00" class="h-10 rounded-lg border-gray-300" />
                        <p v-if="form.errors.monto" class="text-sm text-red-500">{{ form.errors.monto }}</p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="metodo_pago">Método de Pago *</Label>
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
                        <p v-if="form.errors.metodo_pago" class="text-sm text-red-500">{{ form.errors.metodo_pago }}</p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="referencia">Referencia</Label>
                        <Input id="referencia" v-model="form.referencia" placeholder="N° de operación, voucher, etc." class="h-10 rounded-lg border-gray-300" />
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="fecha_pago">Fecha de Pago *</Label>
                        <Input id="fecha_pago" v-model="form.fecha_pago" type="date" class="h-10 rounded-lg border-gray-300" />
                        <p v-if="form.errors.fecha_pago" class="text-sm text-red-500">{{ form.errors.fecha_pago }}</p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="comprobante">Comprobante (dejar vacío para mantener el actual)</Label>
                        <Input id="comprobante" type="file" accept=".pdf,.jpg,.jpeg,.png" @input="handleComprobante" class="h-10 rounded-lg border-gray-300 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white file:text-sm file:px-3 file:py-1" />
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="notas">Notas</Label>
                        <Textarea id="notas" v-model="form.notas" placeholder="Notas adicionales..." rows="3" class="rounded-lg border-gray-300" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <Link :href="route('admin.finanzas.pagos.index')"><Button variant="outline" type="button" class="rounded-lg border-gray-200 text-gray-600 h-10">Cancelar</Button></Link>
                <Button type="submit" :disabled="form.processing" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg px-6 h-10">
                    <Save class="mr-2 h-4 w-4" /> Guardar Cambios
                </Button>
            </div>
        </form>
    </div>
</template>
