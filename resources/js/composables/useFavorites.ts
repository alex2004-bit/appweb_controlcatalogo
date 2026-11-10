import { computed, ref } from 'vue';
import type { Ref, ComputedRef } from 'vue';

export type FavoriteItem = {
    id: number | string;
    nombre: string;
    slug: string;
    precio: number;
    imagen: string | null;
};

export type FavoriteProduct = {
    id: number | string;
    nombre: string;
    slug: string;
    precio: number | string;
    imagen: string | null;
};

const STORAGE_KEY = 'controlcatalogo_favorites';

function loadFromStorage(): FavoriteItem[] {
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

function persist(itemsArray: FavoriteItem[]): void {
    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(itemsArray));
    } catch {
    }
}

const items = ref<FavoriteItem[]>(loadFromStorage());
const isOpen = ref(false);

export function useFavorites(): {
    items: Ref<FavoriteItem[]>;
    isOpen: Ref<boolean>;
    count: ComputedRef<number>;
    add: (product: FavoriteProduct) => void;
    remove: (id: number | string) => void;
    toggle: (product: FavoriteProduct) => void;
    isFavorite: (id: number | string) => boolean;
    clear: () => void;
    open: () => void;
    close: () => void;
    toggleOpen: () => void;
    reset: () => void;
} {

    const count = computed(() => items.value.length);

    function add(product: FavoriteProduct): void {
        const existing = items.value.find((item) => item.id === product.id);

        if (!existing) {
            items.value.push({
                id: product.id,
                nombre: product.nombre,
                slug: product.slug,
                precio: typeof product.precio === 'string' ? parseFloat(product.precio) : product.precio,
                imagen: product.imagen,
            });
            persist(items.value);
        }
    }

    function remove(id: number | string): void {
        items.value = items.value.filter((item) => item.id !== id);
        persist(items.value);
    }

    function toggle(product: FavoriteProduct): void {
        const existing = items.value.find((item) => item.id === product.id);
        
        if (existing) {
            remove(product.id);
        } else {
            add(product);
        }
    }

    function isFavorite(id: number | string): boolean {
        return items.value.some((item) => item.id === id);
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

    function toggleOpen(): void {
        isOpen.value = !isOpen.value;
    }

    function reset(): void {
        isOpen.value = false;
    }

    return {
        items,
        isOpen,
        count,
        add,
        remove,
        toggle,
        isFavorite,
        clear,
        open,
        close,
        toggleOpen,
        reset,
    };
}