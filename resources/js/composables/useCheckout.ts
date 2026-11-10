import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import type { CartItem } from '@/composables/useCart';
import { useMoney } from '@/composables/useMoney';

type CheckoutLineItem = {
    item_type: 'producto' | 'servicio';
    item_id: string | number;
    item_nombre: string;
    cantidad: number;
    precio_unitario: number;
    descuento: number;
};

type CheckoutForm = {
    nombre: string;
    email: string;
    telefono: string;
    documento: string;
    direccion: string;
    ciudad: string;
    notas: string;
    metodo_pago: string;
    items: CheckoutLineItem[];
};

export function useCheckout() {
    const isOpen = ref(false);
    const step = ref<'items' | 'datos' | 'pago' | 'confirmacion'>('items');
    const cartItems = ref<CartItem[]>([]);
    const loading = ref(false);
    const submitMethod = ref<'whatsapp' | 'pedido' | 'cotizacion'>('whatsapp');

    const { format, symbol } = useMoney();

    const form = useForm<CheckoutForm>({
        nombre: '',
        email: '',
        telefono: '',
        documento: '',
        direccion: '',
        ciudad: '',
        notas: '',
        metodo_pago: 'efectivo',
        items: [],
    });

    const subtotal = computed(() =>
        cartItems.value.reduce((acc, item) => acc + item.precio * item.cantidad, 0)
    );

    const impuesto = computed(() => subtotal.value * 0.18);
    const total = computed(() => subtotal.value + impuesto.value);

    function openCheckout(items: CartItem[] = [], method: 'whatsapp' | 'pedido' | 'cotizacion' = 'whatsapp') {
        cartItems.value = items.length ? items : cartItems.value;
        submitMethod.value = method;
        form.items = cartItems.value.map(i => ({
            item_type: 'producto',
            item_id: i.id,
            item_nombre: i.nombre,
            cantidad: i.cantidad,
            precio_unitario: i.precio,
            descuento: 0,
        }));
        step.value = items.length ? 'datos' : 'items';
        isOpen.value = true;
    }

    function closeCheckout() {
        isOpen.value = false;
        step.value = 'items';
        form.reset();
    }

    function nextStep() {
        const steps: Array<'items' | 'datos' | 'pago' | 'confirmacion'> = ['items', 'datos', 'pago', 'confirmacion'];
        const currentIndex = steps.indexOf(step.value);

        if (currentIndex < steps.length - 1) {
            step.value = steps[currentIndex + 1];
        }
    }

    function prevStep() {
        const steps: Array<'items' | 'datos' | 'pago' | 'confirmacion'> = ['items', 'datos', 'pago', 'confirmacion'];
        const currentIndex = steps.indexOf(step.value);

        if (currentIndex > 0) {
            step.value = steps[currentIndex - 1];
        }
    }

    function setStep(s: 'items' | 'datos' | 'pago' | 'confirmacion') {
        step.value = s;
    }

    async function submit() {
        loading.value = true;

        try {
            if (submitMethod.value === 'pedido') {
                await form.post(route('admin.pedidos.store'), {
                    onSuccess: () => {
                        closeCheckout();
                    },
                    onError: () => {
                        loading.value = false;
                    },
                });
            } else if (submitMethod.value === 'cotizacion') {
                await form.post(route('admin.cotizaciones.store'), {
                    onSuccess: () => {
                        closeCheckout();
                    },
                    onError: () => {
                        loading.value = false;
                    },
                });
            } else {
                const mensaje = cartItems.value
                    .map(item => `- ${item.nombre} x${item.cantidad} (${format(item.precio)})`)
                    .join('\n');
                const message = `Hola, quiero cotizar:\n${mensaje}\n\nDatos:\nNombre: ${form.nombre}\nTeléfono: ${form.telefono}\nEmail: ${form.email}\nDirección: ${form.direccion}\nCiudad: ${form.ciudad}\nNotas: ${form.notas}`;
                const url = `https://wa.me/59164828148?text=${encodeURIComponent(message)}`;
                window.open(url, '_blank');
                closeCheckout();
                loading.value = false;
            }
        } catch {
            loading.value = false;
        }
    }

    function addItem(item: CartItem) {
        const existing = cartItems.value.find(i => i.id === item.id);

        if (existing) {
            existing.cantidad += item.cantidad;
        } else {
            cartItems.value.push({ ...item, cantidad: item.cantidad || 1 });
        }
    }

    function removeItem(index: number) {
        cartItems.value.splice(index, 1);
    }

    function updateQuantity(index: number, quantity: number) {
        if (quantity > 0) {
            cartItems.value[index].cantidad = quantity;
        } else {
            cartItems.value.splice(index, 1);
        }
    }

    function clearCart() {
        cartItems.value = [];
    }

    return {
        isOpen,
        step,
        cartItems,
        loading,
        submitMethod,
        form,
        subtotal,
        impuesto,
        total,
        format,
        symbol,
        openCheckout,
        closeCheckout,
        nextStep,
        prevStep,
        setStep,
        submit,
        addItem,
        removeItem,
        updateQuantity,
        clearCart,
    };
}