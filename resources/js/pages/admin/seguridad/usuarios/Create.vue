<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { UserPlus } from '@lucide/vue';
import UserController from '@/actions/App/Http/Controllers/Admin/Seguridad/UserController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type Props = {
    roles: string[];
};

const props = defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;
</script>

<template>
    <Head title="Nuevo Usuario" />

    <div class="flex flex-col space-y-6 p-6">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 shadow-lg shadow-cyan-500/10">
                <UserPlus class="h-6 w-6 text-cyan-400" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-foreground">Nuevo Usuario</h1>
                <p class="text-sm text-muted-foreground">Crear un nuevo usuario en el sistema</p>
            </div>
        </div>

        <Card class="overflow-hidden border-border/50 shadow-lg shadow-black/5">
            <div class="h-1 bg-gradient-to-r from-cyan-500 to-blue-600" />
            <CardHeader>
                <CardTitle class="text-lg font-semibold text-foreground">
                    <span class="bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">Datos del usuario</span>
                </CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="UserController.store.form(currentTeam?.slug ?? '')"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="name" class="text-sm font-medium text-foreground">Nombre</Label>
                        <Input
                            id="name"
                            name="name"
                            placeholder="Nombre completo"
                            required
                            autocomplete="name"
                            class="h-11 rounded-xl border-input bg-card focus-visible:ring-cyan-500/30"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email" class="text-sm font-medium text-foreground">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            placeholder="correo@ejemplo.com"
                            required
                            autocomplete="email"
                            class="h-11 rounded-xl border-input bg-card focus-visible:ring-cyan-500/30"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password" class="text-sm font-medium text-foreground">Contraseña</Label>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Contraseña"
                            required
                            autocomplete="new-password"
                            class="h-11 rounded-xl border-input bg-card focus-visible:ring-cyan-500/30"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation" class="text-sm font-medium text-foreground">Confirmar contraseña</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            placeholder="Repetir contraseña"
                            required
                            autocomplete="new-password"
                            class="h-11 rounded-xl border-input bg-card focus-visible:ring-cyan-500/30"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role" class="text-sm font-medium text-foreground">Rol</Label>
                        <Select name="role">
                            <SelectTrigger class="h-11 rounded-xl border-input bg-card focus:ring-cyan-500/30">
                                <SelectValue placeholder="Seleccionar rol" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="role in roles"
                                    :key="role"
                                    :value="role"
                                >
                                    {{ role }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.role" />
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <Button
                            type="submit"
                            :disabled="processing"
                            class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 hover:from-cyan-600 hover:to-blue-700"
                        >
                            Guardar
                        </Button>
                        <Link :href="route('admin.seguridad.users.index')">
                            <Button variant="outline" type="button" class="border-border/50 hover:bg-accent hover:text-accent-foreground">
                                Cancelar
                            </Button>
                        </Link>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
