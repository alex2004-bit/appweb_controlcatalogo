<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Shield } from '@lucide/vue';
import RoleController from '@/actions/App/Http/Controllers/Admin/Seguridad/RoleController';
import Heading from '@/components/Heading.vue';
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
import { Separator } from '@/components/ui/separator';

type PermissionGroup = Record<string, { name: string; group: string }[]>;

type Props = {
    permissionGroups: PermissionGroup;
};

const props = defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;
</script>

<template>
    <Head title="Nuevo Rol" />

    <div class="flex flex-col space-y-6 p-6">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 shadow-lg shadow-cyan-500/10">
                <Shield class="h-6 w-6 text-cyan-400" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-foreground">Nuevo Rol</h1>
                <p class="text-sm text-muted-foreground">Crear un nuevo rol con permisos</p>
            </div>
        </div>

        <Card class="overflow-hidden border-border/50 shadow-lg shadow-black/5">
            <div class="h-1 bg-gradient-to-r from-cyan-500 to-blue-600" />
            <CardHeader>
                <CardTitle class="text-lg font-semibold text-foreground">
                    <span class="bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">Datos del rol</span>
                </CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="RoleController.store.form(currentTeam?.slug ?? '')"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="name" class="text-sm font-medium text-foreground">Nombre del rol</Label>
                        <Input
                            id="name"
                            name="name"
                            placeholder="Ej: Editor"
                            required
                            class="h-11 rounded-xl border-input bg-card focus-visible:ring-cyan-500/30"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <Separator class="bg-border/50" />

                    <div class="space-y-4">
                        <Label class="text-sm font-medium text-foreground">Permisos</Label>

                        <div
                            v-for="(perms, group) in permissionGroups"
                            :key="group"
                            class="space-y-2"
                        >
                            <h3 class="text-sm font-semibold text-muted-foreground">
                                {{ group }}
                            </h3>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                <div
                                    v-for="perm in perms"
                                    :key="perm.name"
                                    class="flex items-center space-x-2"
                                >
                                    <Checkbox
                                        :id="'perm-' + perm.name"
                                        :name="'permissions[]'"
                                        :value="perm.name"
                                        class="border-border data-[state=checked]:bg-cyan-500 data-[state=checked]:border-cyan-500"
                                    />
                                    <Label
                                        :for="'perm-' + perm.name"
                                        class="text-sm font-normal cursor-pointer text-muted-foreground hover:text-foreground"
                                    >
                                        {{ perm.name }}
                                    </Label>
                                </div>
                            </div>
                            <Separator class="mt-2 bg-border/30" />
                        </div>

                        <InputError :message="errors.permissions" />
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <Button
                            type="submit"
                            :disabled="processing"
                            class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 hover:from-cyan-600 hover:to-blue-700"
                        >
                            Guardar
                        </Button>
                        <Link :href="route('admin.seguridad.roles.index')">
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
