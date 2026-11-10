<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Building2, Share2, SlidersHorizontal, Save, QrCode } from '@lucide/vue';
import { ref } from 'vue';
import GeneralesController from '@/actions/App/Http/Controllers/Admin/Configuracion/GeneralesController';
import InputError from '@/components/InputError.vue';
import { ModuleHeader } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as empresaIndex } from '@/routes/admin/configuracion/empresa';
import { index as generalesIndex } from '@/routes/admin/configuracion/generales';
import { index as socialIndex } from '@/routes/admin/configuracion/social';

const props = defineProps<{
    config: {
        moneda: string;
        impuesto: number;
        logo: string | null;
        qr_pago: string | null;
        banner_imagen_1: string | null;
        banner_imagen_2: string | null;
        banner_imagen_3: string | null;
    } | null;
}>();

const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;
const teamSlug = currentTeam?.slug ?? '';

const tabs = [
    { key: 'empresa', label: 'Empresa', icon: Building2, route: teamSlug ? empresaIndex(teamSlug).url : '' },
    { key: 'social', label: 'Redes Sociales', icon: Share2, route: teamSlug ? socialIndex(teamSlug).url : '' },
    { key: 'generales', label: 'Datos Generales', icon: SlidersHorizontal, route: teamSlug ? generalesIndex(teamSlug).url : '' },
];

const monedaDefault = props.config?.moneda ?? 'Bs';
const impuestoDefault = props.config?.impuesto ?? 13;

const qrPreview = ref(props.config?.qr_pago ? `/storage/${props.config.qr_pago}` : '');
const bannerPreviews = ref([
    props.config?.banner_imagen_1 ? `/storage/${props.config.banner_imagen_1}` : null,
    props.config?.banner_imagen_2 ? `/storage/${props.config.banner_imagen_2}` : null,
    props.config?.banner_imagen_3 ? `/storage/${props.config.banner_imagen_3}` : null,
]);
const bannerFields = [
    { key: 'banner_imagen_1', label: 'Banner 1 — Cámaras de seguridad' },
    { key: 'banner_imagen_2', label: 'Banner 2 — Sistemas de alarma' },
    { key: 'banner_imagen_3', label: 'Banner 3 — Control de acceso' },
] as const;

function onQrChange(event: Event) {
    const el = event.target as HTMLInputElement;
    const file = el.files?.[0] ?? null;
    qrPreview.value = file ? URL.createObjectURL(file) : '';
}

function onBannerChange(event: Event, index: number) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    const previousPreview = bannerPreviews.value[index];

    if (previousPreview?.startsWith('blob:')) {
        URL.revokeObjectURL(previousPreview);
    }

    bannerPreviews.value[index] = URL.createObjectURL(file);
}
</script>

<template>
    <Head title="Configuración - Datos Generales" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Configuración" description="Preferencias generales del sistema" :icon="SlidersHorizontal" />

        <div class="flex gap-1.5 rounded-xl border border-gray-200 bg-white p-1.5 shadow-sm overflow-x-auto">
            <Link v-for="t in tabs" :key="t.key" :href="t.route || '#'" :class="[
                'flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200',
                !t.route ? 'bg-blue-100 text-blue-700 shadow-sm ring-1 ring-blue-200' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-100'
            ]">
                <component :is="t.icon" class="h-4 w-4" />
                <span class="hidden sm:inline">{{ t.label }}</span>
            </Link>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="p-6 sm:p-8">
                <Form
                    v-bind="GeneralesController.update.form(currentTeam?.slug ?? '')"
                    class="max-w-2xl space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="flex items-center gap-3 mb-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                            <SlidersHorizontal class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Datos Generales</h3>
                            <p class="text-xs text-gray-500">Configuración de moneda e impuestos</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="grid gap-1.5">
                            <Label for="moneda">Moneda</Label>
                            <select id="moneda" name="moneda" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm appearance-none cursor-pointer">
                                <option value="Bs" :selected="monedaDefault === 'Bs'">Bolivianos (Bs)</option>
                                <option value="USD" :selected="monedaDefault === 'USD'">Dólares (USD)</option>
                                <option value="PEN" :selected="monedaDefault === 'PEN'">Soles (PEN)</option>
                            </select>
                            <InputError :message="errors.moneda" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="impuesto">Impuesto (%)</Label>
                            <Input id="impuesto" name="impuesto" type="number" step="0.01" min="0" max="100" :default-value="impuestoDefault" class="h-10 rounded-lg border-gray-300" />
                            <InputError :message="errors.impuesto" />
                            <p class="text-xs text-gray-500">
                                Porcentaje que se suma al subtotal de los productos para calcular el total. El IVA en Bolivia es 13%. Pon <span class="font-semibold">13</span> si tus precios NO incluyen impuesto, o <span class="font-semibold">0</span> si ya lo incluyen.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-1.5 pt-2 border-t border-gray-100">
                        <Label>QR de Pago</Label>
                        <p class="text-xs text-gray-500">Sube la imagen QR de pago (banco, billetera digital, etc.) que el cliente verá al pagar desde la web.</p>
                        <div class="flex items-start gap-4 mt-1">
                            <div class="flex h-28 w-28 shrink-0 items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50 overflow-hidden">
                                <img v-if="qrPreview" :src="qrPreview" alt="QR de pago" class="h-full w-full object-contain" />
                                <QrCode v-else class="h-8 w-8 text-gray-300" />
                            </div>
                            <div class="space-y-2">
                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                                    {{ qrPreview ? 'Cambiar QR' : 'Subir QR' }}
                                    <input type="file" name="qr_pago" accept="image/*" class="hidden" @change="onQrChange" />
                                </label>
                                <InputError :message="errors.qr_pago" />
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 border-t border-gray-100 pt-5">
                        <div>
                            <Label>Imágenes del carrusel de inicio</Label>
                            <p class="mt-1 text-xs text-gray-500">Sube imágenes panorámicas para los tres banners de la página principal. Si dejas un campo vacío, se conserva la imagen actual.</p>
                        </div>
                        <div class="grid gap-5 lg:grid-cols-3">
                            <div v-for="(banner, index) in bannerFields" :key="banner.key" class="grid content-start gap-2">
                                <Label :for="banner.key">{{ banner.label }}</Label>
                                <div class="flex aspect-video items-center justify-center overflow-hidden rounded-lg border border-dashed border-gray-300 bg-gray-50">
                                    <img
                                        v-if="bannerPreviews[index]"
                                        :src="bannerPreviews[index] ?? undefined"
                                        :alt="banner.label"
                                        class="h-full w-full object-cover"
                                    />
                                    <span v-else class="px-3 text-center text-xs text-gray-400">Se usará la imagen predeterminada</span>
                                </div>
                                <label :for="banner.key" class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                                    Cambiar imagen
                                    <input
                                        :id="banner.key"
                                        :name="banner.key"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="sr-only"
                                        @change="onBannerChange($event, index)"
                                    />
                                </label>
                                <InputError :message="errors[banner.key]" />
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <Button type="submit" :disabled="processing" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg px-6 h-10">
                            <Save class="mr-2 h-4 w-4" /> Guardar Cambios
                        </Button>
                    </div>
                </Form>
            </div>
        </div>
    </div>
</template>