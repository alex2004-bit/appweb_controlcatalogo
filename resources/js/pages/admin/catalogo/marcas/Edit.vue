<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Tag } from '@lucide/vue';
import MarcaController from '@/actions/App/Http/Controllers/Admin/Catalogo/MarcaController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type Props = {
    marca: {
        id: number;
        nombre: string;
        slug: string;
        logo: string | null;
        descripcion: string | null;
        activo: boolean;
    };
};

const props = defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;
</script>

<template>
    <Head title="Editar Marca" />

    <div class="flex flex-col space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                <Tag class="h-5 w-5 text-white" />
            </div>
            <div>
                <h1 class="text-lg font-semibold text-foreground">Editar Marca</h1>
                <p class="text-sm text-muted-foreground">Modificar datos de la marca</p>
            </div>
        </div>

        <Card class="border-border/50 shadow-xl">
            <CardHeader>
                <div class="flex items-center gap-2">
                    <div class="h-1 w-8 rounded-full bg-gradient-to-r from-cyan-500 to-blue-600" />
                    <CardTitle class="text-base font-semibold">Datos de la marca</CardTitle>
                </div>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="MarcaController.update.form([currentTeam?.slug ?? '', props.marca.id])"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="nombre">Nombre</Label>
                        <Input
                            id="nombre"
                            name="nombre"
                            :default-value="marca.nombre"
                            placeholder="Nombre de la marca"
                            class="rounded-xl border-border bg-card"
                            required
                        />
                        <InputError :message="errors.nombre" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="logo">URL del logo (opcional)</Label>
                        <Input
                            id="logo"
                            name="logo"
                            :default-value="marca.logo ?? ''"
                            placeholder="https://ejemplo.com/logo.png"
                            class="rounded-xl border-border bg-card"
                        />
                        <InputError :message="errors.logo" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="descripcion">Descripción</Label>
                        <Textarea
                            id="descripcion"
                            name="descripcion"
                            :default-value="marca.descripcion ?? ''"
                            placeholder="Descripción de la marca"
                        />
                        <InputError :message="errors.descripcion" />
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox id="activo" name="activo" value="1" :checked="marca.activo" />
                        <Label for="activo">Activo</Label>
                        <InputError :message="errors.activo" />
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <Button
                            type="submit"
                            :disabled="processing"
                            class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 hover:from-cyan-400 hover:to-blue-500"
                        >
                            Actualizar
                        </Button>
                        <Link :href="route('admin.catalogo.marcas.index')">
                            <Button variant="outline" type="button" class="rounded-xl border-border bg-card hover:bg-accent/50">
                                Cancelar
                            </Button>
                        </Link>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
