<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Shield, ShieldCheck, ShieldOff } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PageTransition, ModuleHeader } from '@/components/shared';

const props = defineProps({ permissions: Object });
const page = usePage();
const roles: any[] = computed(() => page.props.roles ?? []);
const selectedRole = ref(roles.value[0]?.id ?? null);

const rolePermissions = computed(() => {
    if (!selectedRole.value) return [];
    const role = roles.value.find((r: any) => r.id === selectedRole.value);
    return role?.permissions?.map((p: any) => p.name) ?? [];
});

function togglePermission(permission: string) {
    const current = [...rolePermissions.value];
    const idx = current.indexOf(permission);
    if (idx >= 0) current.splice(idx, 1);
    else current.push(permission);
    router.put(route('admin.seguridad.permisos.update'), { role_id: selectedRole.value, permissions: current }, { preserveScroll: true });
}

function isChecked(permission: string) { return rolePermissions.value.includes(permission); }
</script>

<template>
    <PageTransition>
        <div class="flex flex-col gap-6 p-4 sm:p-6">
            <ModuleHeader title="Permisos" description="Gestiona los permisos por rol" :icon="ShieldCheck" gradient="from-cyan-500 to-blue-600">
                <template #actions>
                    <div class="w-48">
                        <Select v-model="selectedRole">
                            <SelectTrigger class="h-10 rounded-xl border-input bg-card"><SelectValue placeholder="Seleccionar Rol" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>
            </ModuleHeader>

            <div class="space-y-6">
                <div v-for="(perms, group) in permissions" :key="String(group)" class="rounded-2xl border border-border/50 bg-card p-6 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-muted-foreground">
                        <Shield class="h-4 w-4 text-cyan-400" /> {{ group }}
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <label v-for="perm in (perms as any[])" :key="perm.id" class="group flex cursor-pointer items-center gap-2 rounded-xl border px-3.5 py-2 text-sm transition-all duration-200" :class="isChecked(perm.name) ? 'border-cyan-500/50 bg-cyan-500/10 text-cyan-400 shadow-sm shadow-cyan-500/10' : 'border-border/50 bg-card text-muted-foreground hover:border-border hover:bg-accent/30 hover:text-foreground'">
                            <input type="checkbox" :checked="isChecked(perm.name)" class="sr-only" @change="togglePermission(perm.name)" />
                            <ShieldCheck v-if="isChecked(perm.name)" class="h-4 w-4 text-cyan-400" />
                            <ShieldOff v-else class="h-4 w-4 text-muted-foreground/50 group-hover:text-muted-foreground" />
                            <span>{{ (perm.name as string).replace(/_/g, ' ') }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </PageTransition>
</template>
