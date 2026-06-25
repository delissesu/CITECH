<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import type { Props as ManagePasskeysProps } from '@/components/ManagePasskeys.vue';
import ManagePasskeys from '@/components/ManagePasskeys.vue';
import type { Props as ManageTwoFactorProps } from '@/components/ManageTwoFactor.vue';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import Layout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/security';

type Props = {
    passwordRules: string;
} & ManagePasskeysProps &
    ManageTwoFactorProps;

const props = defineProps<Props>();

defineOptions({
    layout: Layout,
});
</script>

<template>
    <Head title="Keamanan Akun" />

    <h1 class="sr-only">Keamanan Akun</h1>

    <div class="space-y-6">
        <div class="border-b border-slate-100 pb-4 mb-2">
            <h3 class="text-lg font-extrabold text-slate-800">
                Ubah Password
            </h3>
            <p class="text-xs font-bold text-slate-400 mt-1">
                Pastikan akun Anda menggunakan kata sandi yang panjang dan acak demi menjaga keamanan akun.
            </p>
        </div>

        <Form
            v-bind="SecurityController.update.form()"
            :options="{
                preserveScroll: true,
            }"
            reset-on-success
            :reset-on-error="[
                'password',
                'password_confirmation',
                'current_password',
            ]"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="current_password" class="block text-[10px] font-black tracking-wider text-slate-400 uppercase">Kata Sandi Saat Ini</Label>
                <PasswordInput
                    id="current_password"
                    name="current_password"
                    class="mt-1 block w-full rounded-xl! border-slate-200! px-4! py-3! text-sm! font-semibold! focus:ring-2! focus:ring-[#1e4d8c]! focus:outline-none! h-11!"
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi saat ini"
                />
                <InputError :message="errors.current_password" />
            </div>

            <div class="grid gap-2">
                <Label for="password" class="block text-[10px] font-black tracking-wider text-slate-400 uppercase">Kata Sandi Baru</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    class="mt-1 block w-full rounded-xl! border-slate-200! px-4! py-3! text-sm! font-semibold! focus:ring-2! focus:ring-[#1e4d8c]! focus:outline-none! h-11!"
                    autocomplete="new-password"
                    placeholder="Masukkan kata sandi baru"
                    :passwordrules="props.passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation" class="block text-[10px] font-black tracking-wider text-slate-400 uppercase">Konfirmasi Kata Sandi Baru</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    class="mt-1 block w-full rounded-xl! border-slate-200! px-4! py-3! text-sm! font-semibold! focus:ring-2! focus:ring-[#1e4d8c]! focus:outline-none! h-11!"
                    autocomplete="new-password"
                    placeholder="Konfirmasi kata sandi baru"
                    :passwordrules="props.passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <div class="flex items-center gap-4 pt-2">
                <Button
                    :disabled="processing"
                    class="flex items-center space-x-2 rounded-xl bg-[#1e4d8c] hover:bg-[#153a6b] px-8! py-3! h-11! text-xs font-black tracking-wider text-white shadow-md transition"
                    data-test="update-password-button"
                >
                    Ubah Password
                </Button>
            </div>
        </Form>
    </div>

</template>
