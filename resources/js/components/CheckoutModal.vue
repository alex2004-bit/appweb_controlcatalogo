<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { CreditCard, X, MessageCircle, Loader2, MapPin, FileText, AlertCircle, CheckCircle2, Truck } from '@lucide/vue';
import { ref, computed, onMounted, watch } from 'vue';
import { useCart } from '@/composables/useCart';
import { useMoney } from '@/composables/useMoney';
import { cotizar } from '@/routes';

const props = defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits<{
    close: [];
    backToCart: [];
}>();

const cart = useCart();
const money = useMoney();
const page = usePage();

const cartItems = computed(() => cart.items.value);
const cartTotal = computed(() => cart.total.value);
const configuracion = computed(() => page.props.configuracion as { impuesto?: number } | undefined);
const impuestoRate = computed(() => Number(configuracion.value?.impuesto ?? 18));
const totalConImpuesto = computed(() => cartTotal.value * (1 + impuestoRate.value / 100));

type Step = 'datos' | 'entrega' | 'confirmacion' | 'exito';
const deliveryOptions = [
    { value: 'provincia', label: 'Envío a provincia', icon: Truck },
    { value: 'domicilio', label: 'Envío a domicilio', icon: MapPin },
    { value: 'recoger', label: 'Recoger en tienda', icon: FileText },
] as const;
type DeliveryType = (typeof deliveryOptions)[number]['value'];

const step = ref<Step>('datos');
const loading = ref(false);
const error = ref('');
const success = ref<{ codigo: string; total: number; qr_pago: string | null } | null>(null);
const qrImageError = ref(false);
const paymentReceiptUrl = computed(() => {
    if (!success.value) {
        return '';
    }

    const message = `Hola, ya realicé el pago de la solicitud ${success.value.codigo} por ${money.format(success.value.total)}. Adjunto el comprobante.`;

    return `https://wa.me/59164828148?text=${encodeURIComponent(message)}`;
});

const STORAGE_KEY = 'controlcatalogo_checkout_form';

function loadFormFromStorage() {
    if (typeof window === 'undefined') {
return;
}

    try {
        const raw = window.localStorage.getItem(STORAGE_KEY);

        if (raw) {
            const parsed = JSON.parse(raw);
            form.value = { ...form.value, ...parsed };
        }
    } catch {
    }
}

function persistForm() {
    if (typeof window === 'undefined') {
return;
}

    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(form.value));
    } catch {
    }
}

const form = ref({
    nombre: '',
    email: '',
    telefono: '',
    documento: '',
    tipo_entrega: 'provincia' as DeliveryType,
    direccion: '',
    ciudad: '',
    provincia: '',
    notas: '',
});

function open() {
    if (cartItems.value.length === 0) {
return;
}

    loadFormFromStorage();
    step.value = 'datos';
    error.value = '';
    success.value = null;
    loading.value = false;
}

function close() {
    emit('close');
    reset();
}

function reset() {
    step.value = 'datos';
    loading.value = false;
    error.value = '';
    success.value = null;
    form.value = {
        nombre: '',
        email: '',
        telefono: '',
        documento: '',
        tipo_entrega: 'provincia',
        direccion: '',
        ciudad: '',
        provincia: '',
        notas: '',
    };

    if (typeof window !== 'undefined') {
        window.localStorage.removeItem(STORAGE_KEY);
    }
}

function cotizarWhatsApp() {
    if (cartItems.value.length === 0) {
return;
}

    const lineas = cartItems.value
        .map((item) => `- ${item.nombre} x${item.cantidad} (${money.format(item.precio)})`)
        .join('%0A');

    const mensaje = `Hola, me interesa cotizar los siguientes productos:%0A${lineas}%0A%0A¿Podrían darme más información?`;

    window.open(`https://wa.me/59164828148?text=${mensaje}`, '_blank');
}

async function submitCheckout() {
    if (cartItems.value.length === 0) {
return;
}

    error.value = '';
    loading.value = true;

    try {
        const res = await fetch(cotizar.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '',
            },
            body: JSON.stringify({
                ...form.value,
                items: cartItems.value.map(i => ({ id: i.id, cantidad: i.cantidad })),
            }),
        });

        const data = await res.json();

        if (!res.ok) {
            const firstError = data.errors ? Object.values(data.errors)[0] : data.message;
            error.value = Array.isArray(firstError) ? firstError[0] : String(firstError ?? 'No se pudo procesar el pedido.');

            return;
        }

        success.value = {
            codigo: data.codigo,
            total: Number(data.total),
            qr_pago: typeof data.qr_pago === 'string' ? data.qr_pago : null,
        };
        qrImageError.value = false;
        cart.clear();
        step.value = 'exito';

        if (typeof window !== 'undefined') {
            window.localStorage.removeItem(STORAGE_KEY);
        }
    } catch {
        error.value = 'Error de conexión. Intenta nuevamente.';
    } finally {
        loading.value = false;
    }
}

function handleBackdropClick(e: MouseEvent) {
    if ((e.target as HTMLElement).classList.contains('checkout-backdrop')) {
        if (!loading.value && step.value !== 'exito') {
            close();
        }
    }
}

defineExpose({ open, close });

const isValidDatos = computed(() => 
    form.value.nombre.trim() && 
    form.value.telefono.trim()
);

const requiresDeliveryAddress = computed(() => form.value.tipo_entrega !== 'recoger');
const requiresProvince = computed(() => form.value.tipo_entrega === 'provincia');
const isValidEntrega = computed(() => {
    if (!requiresDeliveryAddress.value) {
        return true;
    }

    return Boolean(
        form.value.direccion.trim() &&
        form.value.ciudad.trim() &&
        (!requiresProvince.value || form.value.provincia.trim()),
    );
});

const isValidForm = computed(() => isValidDatos.value && isValidEntrega.value);

watch(form, persistForm, { deep: true });
watch(() => form.value.tipo_entrega, (deliveryType) => {
    if (deliveryType === 'recoger') {
        form.value.direccion = '';
        form.value.ciudad = '';
        form.value.provincia = '';

        return;
    }

    if (deliveryType === 'domicilio') {
        form.value.provincia = '';
    }
});

onMounted(() => {
    if (props.isOpen) {
        open();
    }
});

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        open();
    } else {
        reset();
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition name="checkout-modal">
            <div v-if="props.isOpen" class="fixed inset-0 z-[80]" @click="handleBackdropClick">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm checkout-backdrop" />
                
                <!-- Modal Container - centered with space around -->
                <div class="flex min-h-[calc(100vh-2rem)] items-center justify-center p-2 sm:p-4">
                    <div class="relative w-full max-w-[650px] max-h-[90vh] bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col animate-slide-up">
                        
                        <!-- Header (Sticky) -->
                        <header class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 bg-white/95 backdrop-blur-sm flex-shrink-0 sticky top-0 z-10">
                            <h3 class="flex items-center gap-2 text-lg font-bold text-gray-900">
                                <CreditCard class="h-5 w-5 text-blue-600" />
                                <span v-if="step === 'datos'">Finalizar pedido</span>
                                <span v-else>Pago por QR</span>
                                <span v-if="cartItems.length > 0 && step === 'datos'" class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-600">{{ cartItems.length }}</span>
                            </h3>
                            <button 
                                @click="close" 
                                :disabled="loading || Boolean(success)"
                                class="text-gray-400 hover:text-gray-600 transition-colors p-1 -ml-1 -mr-1 disabled:opacity-50" 
                                aria-label="Cerrar"
                            >
                                <X class="h-6 w-6" />
                            </button>
                        </header>

                        <!-- Content (Scrollable) -->
                        <main class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                            
                            <!-- Step: Datos del cliente -->
                            <div v-if="step === 'datos' && !success" class="space-y-4 animate-fade-in">
                                
                                <!-- Cart Items Summary -->
                                <div v-if="cartItems.length > 0" class="space-y-3 max-h-[250px] overflow-y-auto pr-1">
                                    <div v-for="item in cartItems" :key="String(item.id)" class="flex gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                            <img :src="item.imagen || 'https://images.unsplash.com/photo-1557324232-b8917d3c3dcb?q=80&w=1974&auto=format&fit=clip'" :alt="item.nombre" class="h-full w-full object-cover" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ item.nombre }}</p>
                                            <p class="mt-0.5 text-sm font-bold text-blue-600">
                                                {{ money.format(item.precio) }} x{{ item.cantidad }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Totals -->
                                <div v-if="cartItems.length" class="border-t border-gray-100 pt-3 space-y-2 bg-gray-50 rounded-lg p-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Subtotal ({{ cartItems.length }} productos)</span>
                                        <span class="font-medium text-gray-900">{{ money.format(cartTotal) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Impuesto ({{ impuestoRate }}%)</span>
                                        <span class="font-medium text-gray-900">{{ money.format(cartTotal * impuestoRate / 100) }}</span>
                                    </div>
                                    <div class="flex justify-between text-base font-bold border-t border-gray-200 pt-2">
                                        <span>Total</span>
                                        <span class="text-blue-600">{{ money.format(totalConImpuesto) }}</span>
                                    </div>
                                </div>

                                <!-- Form -->
                                <form @submit.prevent="submitCheckout" class="space-y-4">
                                    <!-- Nombre + Documento -->
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div class="grid gap-1.5">
                                            <label for="nombre" class="text-sm font-medium text-gray-700">Nombre completo *</label>
                                            <input 
                                                id="nombre"
                                                v-model="form.nombre"
                                                type="text"
                                                placeholder="Juan Pérez"
                                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                                required
                                                autocomplete="name"
                                            />
                                        </div>
                                        <div class="grid gap-1.5">
                                            <label for="documento" class="text-sm font-medium text-gray-700">CI / NIT (opcional)</label>
                                            <input 
                                                id="documento"
                                                v-model="form.documento"
                                                type="text"
                                                placeholder="12345678"
                                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                                autocomplete="off"
                                            />
                                        </div>
                                    </div>

                                    <!-- Email + Teléfono -->
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="grid gap-1.5">
                                            <label for="email" class="text-sm font-medium text-gray-700">Email (opcional)</label>
                                            <input 
                                                id="email"
                                                v-model="form.email"
                                                type="email"
                                                placeholder="juan@email.com"
                                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                                autocomplete="email"
                                            />
                                        </div>
                                        <div class="grid gap-1.5">
                                            <label for="telefono" class="text-sm font-medium text-gray-700">Teléfono *</label>
                                            <input 
                                                id="telefono"
                                                v-model="form.telefono"
                                                type="tel"
                                                placeholder="+591 7XXXXXXX"
                                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                                required
                                                autocomplete="tel"
                                            />
                                        </div>
                                    </div>

                                    <!-- Tipo de entrega -->
                                    <fieldset class="grid gap-2">
                                        <legend class="text-sm font-medium text-gray-700">Tipo de entrega</legend>
                                        <div class="flex flex-wrap gap-3">
                                            <label
                                                v-for="opt in deliveryOptions"
                                                :key="opt.value"
                                                class="flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 transition"
                                                :class="form.tipo_entrega === opt.value
                                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                    : 'border-gray-200 text-gray-700 hover:border-gray-300'"
                                            >
                                                <input 
                                                    type="radio" 
                                                    v-model="form.tipo_entrega" 
                                                    :value="opt.value" 
                                                    class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" 
                                                />
                                                <component :is="opt.icon" class="h-4 w-4 text-gray-500" />
                                                <span class="text-sm text-gray-700">{{ opt.label }}</span>
                                            </label>
                                        </div>
                                    </fieldset>

                                    <p v-if="form.tipo_entrega === 'recoger'" class="rounded-lg bg-blue-50 p-3 text-sm text-blue-800">
                                        Para recoger en tienda solo necesitamos tu nombre y teléfono para coordinar la entrega.
                                    </p>

                                    <div v-else class="grid gap-4">
                                        <div class="grid gap-1.5">
                                            <label for="direccion" class="text-sm font-medium text-gray-700">Dirección de entrega *</label>
                                            <input 
                                                id="direccion"
                                                v-model="form.direccion"
                                                type="text"
                                                placeholder="Calle, número, zona, referencias..."
                                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                                required
                                                autocomplete="street-address"
                                            />
                                        </div>

                                        <div :class="requiresProvince ? 'grid gap-4 md:grid-cols-2' : 'grid gap-1.5'">
                                            <div class="grid gap-1.5">
                                                <label for="ciudad" class="text-sm font-medium text-gray-700">Ciudad *</label>
                                                <input 
                                                    id="ciudad"
                                                    v-model="form.ciudad"
                                                    type="text"
                                                    placeholder="Santa Cruz, La Paz, etc."
                                                    class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                                    required
                                                    autocomplete="address-level2"
                                                />
                                            </div>
                                            <div v-if="requiresProvince" class="grid gap-1.5">
                                                <label for="provincia" class="text-sm font-medium text-gray-700">Provincia / Departamento *</label>
                                                <input 
                                                    id="provincia"
                                                    v-model="form.provincia"
                                                    type="text"
                                                    placeholder="Andrés Ibáñez, Pedro Domingo Murillo..."
                                                    class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                                    required
                                                    autocomplete="address-level1"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notas -->
                                    <div class="grid gap-1.5">
                                        <label for="notas" class="text-sm font-medium text-gray-700">Notas adicionales</label>
                                        <textarea 
                                            id="notas"
                                            v-model="form.notas"
                                            rows="3"
                                            placeholder="Referencias, instrucciones de entrega, etc."
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none resize-y"
                                        />
                                    </div>

                                    <!-- Error -->
                                    <div v-if="error" class="rounded-lg bg-red-50 p-3 text-sm text-red-600 flex items-start gap-2">
                                        <AlertCircle class="h-5 w-5 shrink-0 mt-0.5" />
                                        {{ error }}
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                                        <button
                                            type="button"
                                            @click="cotizarWhatsApp"
                                            class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500 hover:shadow-lg hover:shadow-emerald-500/25"
                                        >
                                            <MessageCircle class="h-4 w-4" />
                                            Cotizar por WhatsApp
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="loading || !isValidForm"
                                            class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500 hover:shadow-lg hover:shadow-blue-500/25 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span v-if="loading" class="flex h-4 w-4 animate-spin">
                                                <Loader2 class="h-4 w-4" />
                                            </span>
                                            {{ loading ? 'Enviando...' : 'Continuar al pago' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Step: Confirmación -->
                            <div v-if="success" class="space-y-4 animate-fade-in text-center">
                                <CheckCircle2 class="mx-auto h-16 w-16 text-emerald-500" />
                                <h4 class="text-xl font-bold text-gray-900">Solicitud registrada. Completa el pago</h4>
                                <p class="text-sm text-gray-600">Escanea el QR con tu aplicación bancaria y realiza el pago. Después, envía el comprobante para confirmar tu compra.</p>

                                <div v-if="success.qr_pago && !qrImageError" class="mx-auto flex max-w-sm flex-col items-center gap-3 rounded-xl border border-gray-200 bg-white p-4">
                                    <img
                                        :src="success.qr_pago"
                                        alt="Código QR para realizar el pago"
                                        class="max-h-72 w-full max-w-72 object-contain"
                                        @error="qrImageError = true"
                                    />
                                    <p class="text-xs text-gray-500">Escanea este código QR desde la aplicación de tu banco.</p>
                                </div>

                                <div v-else role="status" class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                    {{ success.qr_pago ? 'No se pudo cargar el QR de pago.' : 'Todavía no se ha configurado un QR de pago.' }}
                                    Contáctanos por WhatsApp para coordinar el pago.
                                </div>

                                <div class="rounded-lg bg-gray-50 p-4 space-y-2 text-left">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Código de solicitud:</span>
                                        <span class="font-bold text-gray-900">{{ success.codigo }}</span>
                                    </div>
                                    <div class="flex justify-between text-base font-bold border-t border-gray-200 pt-2">
                                        <span>Total a pagar</span>
                                        <span class="text-blue-600">{{ money.format(success.total) }}</span>
                                    </div>
                                </div>

                                <a
                                    :href="paymentReceiptUrl"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500"
                                >
                                    <MessageCircle class="h-4 w-4" />
                                    Enviar comprobante por WhatsApp
                                </a>

                                <button
                                    type="button"
                                    @click="close"
                                    class="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
                                >
                                    Continuar comprando
                                </button>
                            </div>
                        </main>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
    .checkout-modal-enter-active,
    .checkout-modal-leave-active {
        transition: all 0.3s ease-out;
    }
    .checkout-modal-enter-from,
    .checkout-modal-leave-to {
        opacity: 0;
    }
    .checkout-modal-enter-from > div > div,
    .checkout-modal-leave-to > div > div {
        transform: translateX(100%) scale(0.95);
    }
    
    .animate-fade-in {
        animation: fadeIn 0.2s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateX(100%) scale(0.95); }
        to { opacity: 1; transform: translateX(0) scale(1); }
    }
    
    .animate-slide-up {
        animation: slideUp 0.3s ease-out;
    }

    /* Hide scrollbar for WebKit */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    </style>