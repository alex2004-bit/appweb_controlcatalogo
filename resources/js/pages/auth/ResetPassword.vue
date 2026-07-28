<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Mail, Lock, KeyRound } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Restablecer contraseña',
        description: 'Ingresa tu nueva contraseña',
    },
});

const props = defineProps<{
    token: string;
    email: string;
    passwordRules: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <Head title="Restablecer contraseña" />

    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 shadow-lg shadow-cyan-500/10 mb-6">
        <KeyRound class="h-7 w-7 text-cyan-400" />
    </div>

    <Form
        v-bind="update.form()"
        :transform="(data) => ({ ...data, token, email })"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email" class="text-sm font-medium text-foreground">Correo electrónico</Label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <Mail class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="email"
                        v-model="inputEmail"
                        class="pl-10 h-11 rounded-xl border-input bg-card"
                        readonly
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password" class="text-sm font-medium text-foreground">Nueva contraseña</Label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3">
                        <Lock class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        class="pl-10 h-11"
                        autofocus
                        placeholder="••••••••"
                        :passwordrules="passwordRules"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation" class="text-sm font-medium text-foreground">Confirmar contraseña</Label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3">
                        <Lock class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        class="pl-10 h-11"
                        placeholder="••••••••"
                        :passwordrules="passwordRules"
                    />
                </div>
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="w-full h-11 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold shadow-lg shadow-cyan-500/25 transition-all hover:from-cyan-400 hover:to-blue-500 hover:shadow-cyan-500/40 hover:scale-[1.02] active:scale-[0.98]"
                :disabled="processing"
                data-test="reset-password-button"
            >
                <Spinner v-if="processing" class="mr-2" />
                Restablecer contraseña
            </Button>
        </div>
    </Form>
</template>
