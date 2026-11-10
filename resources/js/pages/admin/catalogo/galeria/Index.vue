<script setup lang="ts">
import { Form, Head, router, usePage } from '@inertiajs/vue3';
import { Images, Plus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import GaleriaController from '@/actions/App/Http/Controllers/Admin/Catalogo/GaleriaController';
import InputError from '@/components/InputError.vue';
import { ConfirmDeleteDialog, ModuleHeader } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { PaginatedData } from '@/types';

type Galeria = { id: number; titulo: string | null; imagen: string; tipo: string; activo: boolean; created_at: string; };
type Props = { galeria: PaginatedData<Galeria> };
defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;

const deleteId = ref<number | null>(null);
const showDelete = ref(false);
function confirmDelete(id: number) {
 deleteId.value = id; showDelete.value = true;
}
function executeDelete() {
    if (deleteId.value) {
router.delete(route('admin.catalogo.galeria.destroy', deleteId.value), { preserveScroll: true, onSuccess: () => {
 showDelete.value = false; deleteId.value = null;
} });
}
}

const previewImg = ref<string | null>(null);
const showPreview = ref(false);
function openPreview(img: string) {
 previewImg.value = img; showPreview.value = true;
}

const showUpload = ref(false);
</script>

<template>
    <Head title="Galería" />
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Galería" description="Imágenes y recursos multimedia del catálogo" :icon="Images">
            <template #actions>
                <Button class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg h-10" @click="showUpload = true">
                    <Plus class="mr-2 h-4 w-4" /> Subir Imagen
                </Button>
            </template>
        </ModuleHeader>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div v-for="item in galeria.data" :key="item.id" class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg">
                <div class="relative aspect-[4/3] overflow-hidden bg-gray-100 cursor-pointer" @click="openPreview(item.imagen)">
                    <img :src="'/storage/' + item.imagen" :alt="item.titulo ?? ''" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                    <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-all duration-300">
                        <Button variant="secondary" size="icon" class="h-8 w-8 rounded-lg bg-white/90 hover:bg-white text-red-500 shadow-sm" @click.stop="confirmDelete(item.id)">
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                    <div class="absolute bottom-2 left-2">
                        <span class="rounded-md bg-black/60 px-2 py-0.5 text-[10px] font-medium text-white">{{ item.tipo }}</span>
                    </div>
                </div>
                <div class="p-3">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ item.titulo ?? 'Sin título' }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ item.created_at }}</p>
                </div>
            </div>
        </div>

        <div v-if="!galeria.data.length" class="flex flex-col items-center justify-center py-20 text-center">
            <Images class="mx-auto h-12 w-12 text-gray-300 mb-3" />
            <p class="font-medium text-gray-900">Galería vacía</p>
            <p class="text-sm text-gray-500 mt-1">Sube tu primera imagen para comenzar.</p>
        </div>

        <ConfirmDeleteDialog :open="showDelete" title="Eliminar Imagen" description="¿Estás seguro de eliminar esta imagen?" @confirm="executeDelete" @cancel="showDelete = false" />

        <Dialog :open="showPreview" @update:open="showPreview = $event">
            <DialogContent class="sm:max-w-3xl p-1 bg-black/95 border-0">
                <img v-if="previewImg" :src="'/storage/' + previewImg" class="w-full rounded-lg" />
            </DialogContent>
        </Dialog>

        <Dialog :open="showUpload" @update:open="showUpload = $event">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle class="text-gray-900">Subir Imagen</DialogTitle>
                </DialogHeader>
                <Form
                    v-bind="GaleriaController.store.form(currentTeam?.slug ?? '')"
                    class="space-y-4"
                    reset-on-success
                    v-slot="{ errors, processing, progress }"
                >
                    <div class="grid gap-1.5">
                        <Label for="titulo">Título</Label>
                        <Input id="titulo" name="titulo" placeholder="Nombre de la imagen" class="h-10 rounded-lg border-gray-300" required />
                        <InputError :message="errors.titulo" />
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="descripcion">Descripción (opcional)</Label>
                        <Textarea id="descripcion" name="descripcion" placeholder="Descripción de la imagen" class="rounded-lg border-gray-300" />
                        <InputError :message="errors.descripcion" />
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="tipo">Tipo (opcional)</Label>
                        <Input id="tipo" name="tipo" placeholder="Ej: fotografia, diagrama, logo" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.tipo" />
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="imagen">Archivo de imagen</Label>
                        <Input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp" class="h-10 rounded-lg border-gray-300 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white file:px-4 file:py-2 file:text-sm file:font-medium file:cursor-pointer" required />
                        <InputError :message="errors.imagen" />
                        <progress v-if="progress" :value="progress.percentage" max="100" class="w-full h-2 rounded-full overflow-hidden [&::-webkit-progress-bar]:bg-gray-100 [&::-webkit-progress-value]:bg-blue-600" />
                    </div>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="outline" type="button" class="rounded-lg border-gray-200 text-gray-600">Cancelar</Button>
                        </DialogClose>
                        <Button type="submit" :disabled="processing" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg">
                            {{ processing ? 'Subiendo...' : 'Subir' }}
                        </Button>
                    </DialogFooter>
                </Form>
            </DialogContent>
        </Dialog>
    </div>
</template>
