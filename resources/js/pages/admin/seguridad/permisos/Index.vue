<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Shield, ShieldCheck, ShieldOff } from '@lucide/vue';
import { computed, ref } from 'vue';
import { ModuleHeader } from '@/components/shared';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

defineProps({ permissions: Object });
const page = usePage();
const roles = computed<Array<Record<string, any>>>(() => (page.props.roles as Array<Record<string, any>> | undefined) ?? []);
const selectedRole = ref(roles.value[0]?.id ?? null);

const rolePermissions = computed(() => {
    if (!selectedRole.value) {
return [];
}

    const role = roles.value.find((r) => r.id === selectedRole.value);

    return role?.permissions?.map((p: Record<string, any>) => p.name) ?? [];
});

function togglePermission(permission: string) {
    const current = [...rolePermissions.value];
    const idx = current.indexOf(permission);

    if (idx >= 0) {
current.splice(idx, 1);
} else {
current.push(permission);
}

    router.put(route('admin.seguridad.permisos.update'), { role_id: selectedRole.value, permissions: current }, { preserveScroll: true });
}

function isChecked(permission: string) {
 return rolePermissions.value.includes(permission);
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <ModuleHeader title="Permisos" description="Gestiona los permisos por rol" :icon="ShieldCheck">
            <template #actions>
                <div class="w-48">
                    <Select v-model="selectedRole">
                        <SelectTrigger class="h-10 rounded-lg border-gray-300 bg-white"><SelectValue placeholder="Seleccionar Rol" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </template>
        </ModuleHeader>

        <div class="space-y-6">
            <div v-for="(perms, group) in permissions" :key="String(group)" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-gray-500">
                    <Shield class="h-4 w-4 text-blue-500" /> {{ group }}
                </h3>
                <div class="flex flex-wrap gap-2">
                    <label v-for="perm in (perms as any[])" :key="perm.id" class="group flex cursor-pointer items-center gap-2 rounded-lg border px-3.5 py-2 text-sm transition-all duration-200" :class="isChecked(perm.name) ? 'border-blue-500/50 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50'">
                        <input type="checkbox" :checked="isChecked(perm.name)" class="sr-only" @change="togglePermission(perm.name)" />
                        <ShieldCheck v-if="isChecked(perm.name)" class="h-4 w-4 text-blue-500" />
                        <ShieldOff v-else class="h-4 w-4 text-gray-400 group-hover:text-gray-500" />
                        <span>{{ (perm.name as string).replace(/_/g, ' ') }}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>
