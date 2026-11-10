import { computed, ref } from 'vue';
import type { Ref, ComputedRef } from 'vue';

export type CartItem = {
    id: number | string;
    nombre: string;
    slug: string;
    precio: number;
    imagen: string | null;
    cantidad: number;
    stock?: number | null;
};

export type CartProduct = {
    id: number | string;
    nombre: string;
    slug: string;
    precio: number | string;
    imagen: string | null;
    stock?: number | null;
};

const STORAGE_KEY = 'controlcatalogo_cart';

function loadFromStorage(): CartItem[] {
    if (typeof window === 'undefined') {
        return [];
    }

    try {
        const raw = window.localStorage.getItem(STORAGE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function persist(itemsArray: CartItem[]): void {
    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(itemsArray));
    } catch {
        // almacenamiento no disponible, ignorar
    }
}

const items = ref<CartItem[]>(loadFromStorage());
const isOpen = ref(false);

const count = computed(() => items.value.reduce((acc: number, item: CartItem) => acc + item.cantidad, 0));
const total = computed(() => items.value.reduce((acc: number, item: CartItem) => acc + item.precio * item.cantidad, 0));

function add(product: CartProduct, cantidad = 1): void {
    const existing = items.value.find((item) => item.id === product.id);

    if (existing) {
        existing.cantidad = maxQuantity(existing.cantidad + cantidad, product.stock ?? existing.stock);
    } else {
        items.value.push({
            id: product.id,
            nombre: product.nombre,
            slug: product.slug,
            precio: typeof product.precio === 'string' ? parseFloat(product.precio) : product.precio,
            imagen: product.imagen,
            stock: product.stock ?? null,
            cantidad: maxQuantity(cantidad, product.stock ?? null),
        });
    }

    persist(items.value);
}

function remove(id: number | string): void {
    items.value = items.value.filter((item) => item.id !== id);
    persist(items.value);
}

function maxQuantity(cantidad: number, stock: number | null | undefined): number {
    if (typeof stock === 'number' && stock > 0) {
        return Math.min(Math.max(1, cantidad), stock);
    }

    return Math.max(1, cantidad);
}

function setQuantity(id: number | string, cantidad: number): void {
    const item = items.value.find((i) => i.id === id);

    if (!item) {
        return;
    }

    item.cantidad = maxQuantity(cantidad, item.stock);
    persist(items.value);
}

function clear(): void {
    items.value = [];
    persist(items.value);
}

function open(): void {
    isOpen.value = true;
}

function close(): void {
    isOpen.value = false;
}

function toggle(): void {
    isOpen.value = !isOpen.value;
}

export function useCart(): {
    items: Ref<CartItem[]>;
    isOpen: Ref<boolean>;
    count: ComputedRef<number>;
    total: ComputedRef<number>;
    add: (product: CartProduct, cantidad?: number) => void;
    remove: (id: number | string) => void;
    setQuantity: (id: number | string, cantidad: number) => void;
    clear: () => void;
    open: () => void;
    close: () => void;
    toggle: () => void;
} {
    return {
        items,
        isOpen,
        count,
        total,
        add,
        remove,
        setQuantity,
        clear,
        open,
        close,
        toggle,
    };
}
