<script setup lang="ts">
import { Link, useForm, Head } from '@inertiajs/vue3';
import { CreditCard, X } from '@lucide/vue';
import { computed, watch } from 'vue';
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

const props = defineProps<{
    cotizaciones: Array<{
        id: number;
        codigo: string;
        cliente_nombre: string;
        total: string;
        saldo_pendiente: number;
    }>;
}>();

const form = useForm({
    cotizacion_id: '',
    monto: '',
    metodo_pago: '',
    referencia: '',
    fecha_pago: new Date().toISOString().split('T')[0],
    comprobante: null as File | null,
    notas: '',
});

const cotizacionSeleccionada = computed(() =>
    props.cotizaciones.find(c => c.id === Number(form.cotizacion_id)),
);

watch(() => form.cotizacion_id, () => {
    if (cotizacionSeleccionada.value) {
        form.monto = cotizacionSeleccionada.value.saldo_pendiente.toString();
    } else {
        form.monto = '';
    }
});

function submit() {
    form.post(route('admin.finanzas.pagos.store'), {
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
    <Head title="Nuevo Pago" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Registrar Pago" description="Ingresa los datos del pago recibido" :icon="CreditCard">
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
                <FormSection title="Información del Pago" description="Ingresa los datos del pago recibido.">
                    <div class="grid gap-4">
                        <div class="grid gap-1.5">
                            <Label for="cotizacion_id">Cotización *</Label>
                            <Select v-model="form.cotizacion_id">
                                <SelectTrigger id="cotizacion_id" class="h-10 rounded-lg border-gray-300">
                                    <SelectValue placeholder="Seleccionar cotización..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="c in cotizaciones" :key="c.id" :value="c.id.toString()">
                                        {{ c.codigo }} — {{ c.cliente_nombre }} (Saldo: {{ format(c.saldo_pendiente) }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.cotizacion_id" />
                        </div>

                        <div v-if="cotizacionSeleccionada" class="rounded-lg bg-gray-50 p-4 text-sm space-y-1">
                            <p class="flex justify-between"><span class="text-gray-500">Total cotización:</span> <span class="text-gray-900">{{ format(cotizacionSeleccionada.total) }}</span></p>
                            <p class="flex justify-between"><span class="text-gray-500">Saldo pendiente:</span> <span class="text-blue-600 font-medium">{{ format(cotizacionSeleccionada.saldo_pendiente) }}</span></p>
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="monto">Monto *</Label>
                            <Input id="monto" v-model="form.monto" type="number" step="0.01" min="0.01" placeholder="0.00" class="h-10 rounded-lg border-gray-300" />
                            <InputError :message="form.errors.monto" />
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
                            <InputError :message="form.errors.metodo_pago" />
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="referencia">Referencia</Label>
                            <Input id="referencia" v-model="form.referencia" placeholder="N° de operación, voucher, etc." class="h-10 rounded-lg border-gray-300" />
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="fecha_pago">Fecha de Pago *</Label>
                            <Input id="fecha_pago" v-model="form.fecha_pago" type="date" class="h-10 rounded-lg border-gray-300" />
                            <InputError :message="form.errors.fecha_pago" />
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="comprobante">Comprobante (PDF o imagen)</Label>
                            <Input id="comprobante" type="file" accept=".pdf,.jpg,.jpeg,.png" @input="handleComprobante" class="h-10 rounded-lg border-gray-300 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white file:text-sm file:px-3 file:py-1" />
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="notas">Notas</Label>
                            <Textarea id="notas" v-model="form.notas" placeholder="Notas adicionales..." rows="3" class="rounded-lg border-gray-300" />
                        </div>
                    </div>
                </FormSection>
            </div>

            <div class="flex justify-end gap-3">
                <Link :href="route('admin.finanzas.pagos.index')"><Button variant="outline" type="button" class="rounded-lg border-gray-200 text-gray-600 h-10">Cancelar</Button></Link>
                <Button type="submit" :disabled="form.processing" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg px-6 h-10">
                    <CreditCard class="mr-2 h-4 w-4" /> Registrar Pago
                </Button>
            </div>
        </form>
    </div>
</template>
