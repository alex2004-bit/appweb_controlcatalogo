<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { UserCog, Save, X } from '@lucide/vue';
import UserController from '@/actions/App/Http/Controllers/Admin/Seguridad/UserController';
import InputError from '@/components/InputError.vue';
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
import type { User } from '@/types';

type Props = {
    user: User;
    roles: string[];
    userRole: string | null;
};

const props = defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;
</script>

<template>
    <Head title="Editar Usuario" />

    <div class="flex flex-col gap-6">
        <ModuleHeader title="Editar Usuario" description="Modificar datos del usuario" :icon="UserCog">
            <template #actions>
                <Link :href="route('admin.seguridad.users.index')">
                    <Button variant="outline" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800">
                        <X class="mr-2 h-4 w-4" /> Cancelar
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <Form
            v-bind="UserController.update.form([currentTeam?.slug ?? '', props.user.id])"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <FormSection title="Datos del usuario">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="grid gap-1.5">
                        <Label for="name">Nombre</Label>
                        <Input id="name" name="name" :default-value="user.name" placeholder="Nombre completo" required autocomplete="name" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" name="email" :default-value="user.email" placeholder="correo@ejemplo.com" required autocomplete="email" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.email" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="grid gap-1.5">
                        <Label for="password">Nueva contraseña (dejar vacío para mantener)</Label>
                        <Input id="password" type="password" name="password" placeholder="Nueva contraseña" autocomplete="new-password" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.password" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="password_confirmation">Confirmar contraseña</Label>
                        <Input id="password_confirmation" type="password" name="password_confirmation" placeholder="Repetir contraseña" autocomplete="new-password" class="h-10 rounded-lg border-gray-300" />
                        <InputError :message="errors.password_confirmation" />
                    </div>
                </div>
                <div class="grid gap-1.5">
                    <Label for="role">Rol</Label>
                    <Select name="role" :default-value="userRole ?? undefined">
                        <SelectTrigger class="h-10 rounded-lg border-gray-300">
                            <SelectValue placeholder="Seleccionar rol" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="role in roles" :key="role" :value="role">
                                {{ role }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.role" />
                </div>
            </FormSection>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="processing" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg px-6 h-10">
                    <Save class="mr-2 h-4 w-4" /> Actualizar
                </Button>
                <Link :href="route('admin.seguridad.users.index')">
                    <Button variant="outline" type="button" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800 h-10">
                        Cancelar
                    </Button>
                </Link>
            </div>
        </Form>
    </div>
</template>
