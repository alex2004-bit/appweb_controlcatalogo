<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Mail, Lock } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Iniciar Sesión',
        description: 'Ingresa tus credenciales para acceder al panel de control',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Iniciar Sesión" />

    <div
        v-if="status"
        class="mb-4 w-full max-w-sm text-center text-sm font-medium text-emerald-400"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="w-full max-w-sm space-y-6"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <label for="email" class="text-sm font-medium text-foreground">Correo electrónico</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <Mail class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        tabindex="1"
                        autocomplete="email"
                        placeholder="admin@seguridadcatalogo.com"
                        class="flex h-11 w-full rounded-xl border border-input bg-card pl-10 pr-3 py-2 text-sm text-foreground placeholder:text-muted-foreground/60 transition-all focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/20"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <label for="password" class="text-sm font-medium text-foreground">Contraseña</label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors"
                        :tabindex="5"
                    >
                        ¿Olvidaste tu contraseña?
                    </TextLink>
                </div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3">
                        <Lock class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="pl-10"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center gap-2">
                <Checkbox id="remember" name="remember" :tabindex="3" />
                <label for="remember" class="text-sm text-muted-foreground">Recordar sesión</label>
            </div>

            <Button
                type="submit"
                class="w-full h-11 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/25 transition-all hover:from-cyan-400 hover:to-blue-500 hover:shadow-cyan-500/40 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
                :tabindex="4"
                :disabled="processing"
            >
                <Spinner v-if="processing" class="mr-2" />
                Ingresar
            </Button>
        </div>

        <p class="text-center text-xs text-muted-foreground/60">
            Al ingresar aceptas los términos y condiciones del sistema
        </p>
    </Form>
</template>
