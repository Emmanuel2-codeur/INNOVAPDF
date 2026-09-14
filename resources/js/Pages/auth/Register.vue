<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Create an account',
        description: 'Enter your details below to create your account',
    },
});

// Si on vient de l'éditeur (bouton "Sauvegarder" côté invité), un brouillon
// attend en sessionStorage : on le signale ici pour rassurer l'utilisateur.
const hasPendingDraft = ref(false);
onMounted(() => {
    hasPendingDraft.value = new URLSearchParams(window.location.search).get('intent') === 'save_document'
        && sessionStorage.getItem('innovapdf_pending_document') !== null;
});
</script>

<template>
    <Head title="Register" />

    <div v-if="hasPendingDraft" class="rounded-md bg-green-50 border border-green-200 text-green-700 text-sm px-3 py-2 mb-4">
        Ton document est prêt — il sera enregistré automatiquement une fois ton compte créé.
    </div>

    <a
        href="/auth/google/redirect"
        class="flex items-center justify-center gap-2 w-full border rounded-md py-2 text-sm font-medium hover:bg-gray-50 transition mb-4"
    >
        <svg class="w-4 h-4" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.9 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l6-6C34.5 5.1 29.5 3 24 3 12.4 3 3 12.4 3 24s9.4 21 21 21 21-9.4 21-21c0-1.2-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.6 15.9 18.9 13 24 13c3.1 0 5.8 1.1 8 3l6-6C34.5 5.1 29.5 3 24 3 16 3 9 7.5 6.3 14.7z"/><path fill="#4CAF50" d="M24 45c5.4 0 10.3-2.1 14-5.5l-6.5-5.5c-2 1.5-4.6 2.5-7.5 2.5-5.3 0-9.7-3.1-11.3-7.9l-6.6 5.1C9 40.4 16 45 24 45z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.3-4.2 5.7l6.5 5.5C40.9 36.8 45 31 45 24c0-1.2-.1-2.4-.4-3.5z"/></svg>
        Continuer avec Google
    </a>

    <div class="relative text-center text-xs text-gray-400 mb-4">
        <span class="bg-white px-2 relative z-10">ou avec ton e-mail</span>
        <div class="absolute top-1/2 left-0 right-0 border-t border-gray-200"></div>
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    placeholder="Full name"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Password"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm password</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Confirm password"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                tabindex="5"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Create account
            </Button>
        </div>

        <div class="text-muted-foreground text-center text-sm">
            Already have an account?
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
                :tabindex="6"
                >Log in</TextLink
            >
        </div>
    </Form>
</template>
