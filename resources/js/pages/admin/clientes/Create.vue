<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { UserPlus } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { FormSection } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';

const form = useForm({
    tipo_persona: 'natural',
    nombre: '',
    email: '',
    telefono: '',
    direccion: '',
    documento: '',
    empresa: '',
    notas: '',
});

function submit() {
    form.post(route('admin.clientes.store'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Clientes' }, { title: 'Clientes', href: route('admin.clientes.index') }, { title: 'Nuevo', href: '' }]">
        <div class="flex flex-col gap-6 p-6 max-w-3xl">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                    <UserPlus class="h-5 w-5 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Nuevo Cliente</h1>
                    <p class="text-sm text-muted-foreground">Registra un nuevo cliente en el sistema</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-xl border border-border bg-card p-6">
                    <FormSection title="Tipo de Persona">
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer rounded-xl border border-border bg-background p-4 transition-all hover:border-cyan-500/50" :class="form.tipo_persona === 'natural' ? 'border-cyan-500 bg-cyan-500/5 ring-1 ring-cyan-500/30' : ''">
                                <input type="radio" v-model="form.tipo_persona" value="natural" class="sr-only" />
                                <div class="flex h-5 w-5 items-center justify-center rounded-full border-2" :class="form.tipo_persona === 'natural' ? 'border-cyan-500 bg-cyan-500' : 'border-muted-foreground'">
                                    <div v-if="form.tipo_persona === 'natural'" class="h-2 w-2 rounded-full bg-white"></div>
                                </div>
                                <span class="text-sm font-medium">Persona Natural</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer rounded-xl border border-border bg-background p-4 transition-all hover:border-cyan-500/50" :class="form.tipo_persona === 'juridica' ? 'border-cyan-500 bg-cyan-500/5 ring-1 ring-cyan-500/30' : ''">
                                <input type="radio" v-model="form.tipo_persona" value="juridica" class="sr-only" />
                                <div class="flex h-5 w-5 items-center justify-center rounded-full border-2" :class="form.tipo_persona === 'juridica' ? 'border-cyan-500 bg-cyan-500' : 'border-muted-foreground'">
                                    <div v-if="form.tipo_persona === 'juridica'" class="h-2 w-2 rounded-full bg-white"></div>
                                </div>
                                <span class="text-sm font-medium">Persona Jurídica</span>
                            </label>
                        </div>
                    </FormSection>
                </div>

                <div class="rounded-xl border border-border bg-card p-6">
                    <FormSection title="Datos del Cliente">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="nombre">Nombre *</Label>
                                <Input id="nombre" v-model="form.nombre" placeholder="Nombre completo" required class="h-11 rounded-xl border-input bg-background" />
                                <InputError :message="form.errors.nombre" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="documento">{{ form.tipo_persona === 'natural' ? 'DNI' : 'RUC' }}</Label>
                                <Input id="documento" v-model="form.documento" :placeholder="form.tipo_persona === 'natural' ? '12345678' : '20123456789'" class="h-11 rounded-xl border-input bg-background" />
                                <InputError :message="form.errors.documento" />
                            </div>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2 mt-4">
                            <div class="grid gap-2">
                                <Label for="email">Email</Label>
                                <Input id="email" v-model="form.email" type="email" placeholder="cliente@correo.com" class="h-11 rounded-xl border-input bg-background" />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="telefono">Teléfono</Label>
                                <Input id="telefono" v-model="form.telefono" placeholder="999 888 777" class="h-11 rounded-xl border-input bg-background" />
                                <InputError :message="form.errors.telefono" />
                            </div>
                        </div>
                        <div class="grid gap-2 mt-4">
                            <Label for="direccion">Dirección</Label>
                            <Textarea id="direccion" v-model="form.direccion" placeholder="Av. Principal 123" rows="2" class="rounded-xl border-input bg-background" />
                            <InputError :message="form.errors.direccion" />
                        </div>
                        <div class="grid gap-4 md:grid-cols-2 mt-4">
                            <div class="grid gap-2">
                                <Label for="empresa">Empresa</Label>
                                <Input id="empresa" v-model="form.empresa" placeholder="Nombre de la empresa" class="h-11 rounded-xl border-input bg-background" />
                                <InputError :message="form.errors.empresa" />
                            </div>
                        </div>
                    </FormSection>
                </div>

                <div class="rounded-xl border border-border bg-card p-6">
                    <FormSection title="Notas (opcional)">
                        <Textarea v-model="form.notas" rows="3" placeholder="Notas adicionales..." class="rounded-xl border-input bg-background" />
                        <InputError :message="form.errors.notas" />
                    </FormSection>
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 rounded-xl h-11 px-6 hover:from-cyan-600 hover:to-blue-700">
                        Guardar Cliente
                    </Button>
                    <Link :href="route('admin.clientes.index')"><Button variant="outline" type="button" class="rounded-xl h-11 px-6">Cancelar</Button></Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
