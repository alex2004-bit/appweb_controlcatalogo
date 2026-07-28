<script setup lang="ts">
import { ref } from 'vue';
import { Form, Head, router, usePage } from '@inertiajs/vue3';
import { Images, Plus, Trash2, Eye } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ConfirmDeleteDialog, PageTransition, StaggerTransition, ModuleHeader } from '@/components/shared';
import GaleriaController from '@/actions/App/Http/Controllers/Admin/Catalogo/GaleriaController';
import type { PaginatedData } from '@/types';

type Galeria = { id: number; titulo: string | null; imagen: string; tipo: string; activo: boolean; created_at: string; };
type Props = { galeria: PaginatedData<Galeria> };
const props = defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;

const deleteId = ref<number | null>(null);
const showDelete = ref(false);
function confirmDelete(id: number) { deleteId.value = id; showDelete.value = true; }
function executeDelete() {
    if (deleteId.value) router.delete(route('admin.catalogo.galeria.destroy', deleteId.value), { preserveScroll: true, onSuccess: () => { showDelete.value = false; deleteId.value = null; } });
}

const previewImg = ref<string | null>(null);
const showPreview = ref(false);
function openPreview(img: string) { previewImg.value = img; showPreview.value = true; }

const showUpload = ref(false);
</script>

<template>
    <Head title="Galería" />
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Galería" description="Imágenes y recursos multimedia del catálogo" :icon="Images" gradient="from-purple-500 to-pink-600">
                <template #actions>
                    <Button class="bg-gradient-to-r from-purple-500 to-pink-600 text-white shadow-lg shadow-purple-500/25 hover:from-purple-600 hover:to-pink-700 rounded-xl h-10" @click="showUpload = true">
                        <Plus class="mr-2 h-4 w-4" /> Subir Imagen
                    </Button>
                </template>
            </ModuleHeader>

            <StaggerTransition tag="div" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div v-for="item in galeria.data" :key="item.id" class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="relative aspect-[4/3] overflow-hidden bg-muted cursor-pointer" @click="openPreview(item.imagen)">
                        <img :src="'/storage/' + item.imagen" :alt="item.titulo ?? ''" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <Eye class="h-8 w-8 text-white drop-shadow-lg" />
                        </div>
                        <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <Button variant="secondary" size="icon" class="h-8 w-8 rounded-xl bg-white/90 hover:bg-white text-red-500 shadow-sm" @click.stop="confirmDelete(item.id)">
                                <Trash2 class="h-3.5 w-3.5" />
                            </Button>
                        </div>
                        <div class="absolute bottom-2 left-2">
                            <span class="rounded-lg bg-black/60 px-2 py-0.5 text-[10px] font-medium text-white backdrop-blur-sm">{{ item.tipo }}</span>
                        </div>
                    </div>
                    <div class="p-3">
                        <p class="text-sm font-medium text-foreground truncate">{{ item.titulo ?? 'Sin título' }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5">{{ item.created_at }}</p>
                    </div>
                </div>
            </StaggerTransition>

            <div v-if="!galeria.data.length" class="flex flex-col items-center justify-center py-20 text-center">
                <Images class="h-16 w-16 text-muted-foreground/20 mb-4" />
                <h3 class="text-lg font-semibold text-foreground">Galería vacía</h3>
                <p class="mt-1 text-sm text-muted-foreground">Sube tu primera imagen para comenzar.</p>
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
                        <DialogTitle>Subir Imagen</DialogTitle>
                    </DialogHeader>
                    <Form
                        v-bind="GaleriaController.store.form(currentTeam?.slug ?? '')"
                        class="space-y-4"
                        reset-on-success
                        v-slot="{ errors, processing, progress }"
                    >
                        <div class="grid gap-2">
                            <Label for="titulo">Título</Label>
                            <Input id="titulo" name="titulo" placeholder="Nombre de la imagen" class="rounded-xl border-border bg-card" required />
                            <InputError :message="errors.titulo" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="descripcion">Descripción (opcional)</Label>
                            <Textarea id="descripcion" name="descripcion" placeholder="Descripción de la imagen" class="rounded-xl border-border bg-card" />
                            <InputError :message="errors.descripcion" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="tipo">Tipo (opcional)</Label>
                            <Input id="tipo" name="tipo" placeholder="Ej: fotografia, diagrama, logo" class="rounded-xl border-border bg-card" />
                            <InputError :message="errors.tipo" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="imagen">Archivo de imagen</Label>
                            <Input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp" class="rounded-xl border-border bg-card file:rounded-lg file:border-0 file:bg-gradient-to-r file:from-purple-500 file:to-pink-600 file:text-white file:px-4 file:py-2 file:text-sm file:font-medium file:cursor-pointer" required />
                            <InputError :message="errors.imagen" />
                            <progress v-if="progress" :value="progress.percentage" max="100" class="w-full h-2 rounded-full overflow-hidden [&::-webkit-progress-bar]:bg-muted [&::-webkit-progress-value]:bg-gradient-to-r [&::-webkit-progress-value]:from-purple-500 [&::-webkit-progress-value]:to-pink-600" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button variant="outline" type="button" class="rounded-xl border-border bg-card">Cancelar</Button>
                            </DialogClose>
                            <Button type="submit" :disabled="processing" class="bg-gradient-to-r from-purple-500 to-pink-600 text-white shadow-lg shadow-purple-500/25 hover:from-purple-600 hover:to-pink-700 rounded-xl">
                                {{ processing ? 'Subiendo...' : 'Subir' }}
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </PageTransition>
</template>
