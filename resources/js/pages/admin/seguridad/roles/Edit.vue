<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Shield, Save, X } from '@lucide/vue';
import RoleController from '@/actions/App/Http/Controllers/Admin/Seguridad/RoleController';
import InputError from '@/components/InputError.vue';
import { FormSection, ModuleHeader } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type PermissionGroup = Record<string, { name: string; group: string }[]>;

type Props = {
    role: { id: number; name: string };
    permissionGroups: PermissionGroup;
    rolePermissions: string[];
};

const props = defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as { slug: string } | null;
</script>

<template>
    <Head title="Editar Rol" />

    <div class="flex flex-col gap-6">
        <ModuleHeader title="Editar Rol" description="Modificar permisos del rol" :icon="Shield">
            <template #actions>
                <Link :href="route('admin.seguridad.roles.index')">
                    <Button variant="outline" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800">
                        <X class="mr-2 h-4 w-4" /> Cancelar
                    </Button>
                </Link>
            </template>
        </ModuleHeader>

        <Form
            v-bind="RoleController.update.form([currentTeam?.slug ?? '', props.role.id])"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <FormSection title="Datos del rol">
                <div class="grid gap-1.5">
                    <Label for="name">Nombre del rol</Label>
                    <Input id="name" name="name" :default-value="role.name" placeholder="Nombre del rol" required class="h-10 rounded-lg border-gray-300" />
                    <InputError :message="errors.name" />
                </div>
            </FormSection>

            <FormSection title="Permisos">
                <div v-for="(perms, group) in permissionGroups" :key="group" class="space-y-2 mb-4">
                    <h3 class="text-sm font-semibold text-gray-500">{{ group }}</h3>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        <div v-for="perm in perms" :key="perm.name" class="flex items-center space-x-2">
                            <Checkbox :id="'perm-' + perm.name" :name="'permissions[]'" :value="perm.name" :checked="rolePermissions.includes(perm.name)" class="border-gray-300 data-[state=checked]:bg-blue-600 data-[state=checked]:border-blue-600" />
                            <Label :for="'perm-' + perm.name" class="text-sm font-normal cursor-pointer text-gray-600 hover:text-gray-900">
                                {{ perm.name }}
                            </Label>
                        </div>
                    </div>
                </div>
                <InputError :message="errors.permissions" />
            </FormSection>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="processing" class="bg-blue-600 text-white hover:bg-blue-700 shadow-sm rounded-lg px-6 h-10">
                    <Save class="mr-2 h-4 w-4" /> Actualizar
                </Button>
                <Link :href="route('admin.seguridad.roles.index')">
                    <Button variant="outline" type="button" class="rounded-lg border-gray-200 text-gray-600 hover:text-gray-800 h-10">
                        Cancelar
                    </Button>
                </Link>
            </div>
        </Form>
    </div>
</template>
