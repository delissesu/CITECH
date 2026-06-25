<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Layout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: Layout,
});

const page = usePage<any>();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Pengaturan Profil" />

    <h1 class="sr-only">Pengaturan Profil</h1>

    <div class="flex flex-col space-y-6">
        <div class="border-b border-slate-100 pb-4 mb-2">
            <h3 class="text-lg font-extrabold text-slate-800">
                Informasi Profil
            </h3>
            <p class="text-xs font-bold text-slate-400 mt-1">
                Perbarui nama lengkap dan alamat email akun Anda.
            </p>
        </div>

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name" class="block text-[10px] font-black tracking-wider text-slate-400 uppercase">Nama Lengkap</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full rounded-xl! border-slate-200! px-4! py-3! text-sm! font-semibold! focus:ring-2! focus:ring-[#1e4d8c]! focus:outline-none! h-11!"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Masukkan nama lengkap Anda"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email" class="block text-[10px] font-black tracking-wider text-slate-400 uppercase">Alamat Email</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-xl! border-slate-200! px-4! py-3! text-sm! font-semibold! focus:ring-2! focus:ring-[#1e4d8c]! focus:outline-none! h-11! bg-slate-50! text-slate-500! cursor-not-allowed! select-none!"
                    name="email"
                    :default-value="user.email"
                    required
                    autocomplete="username"
                    placeholder="Masukkan alamat email Anda"
                    readonly
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div v-if="page.props.mustVerifyEmail && !user.email_verified_at">
                <p class="text-slate-500 -mt-2 text-xs font-bold">
                    Alamat email Anda belum terverifikasi.
                    <Link
                        :href="send()"
                        as="button"
                        class="text-blue-600 underline decoration-blue-300 underline-offset-4 transition-colors duration-300 ease-out hover:text-blue-800"
                    >
                        Klik di sini untuk mengirim ulang email verifikasi.
                    </Link>
                </p>

                <div
                    v-if="page.props.status === 'verification-link-sent'"
                    class="mt-2 text-xs font-bold text-green-600"
                >
                    Tautan verifikasi baru telah dikirimkan ke alamat email Anda.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <Button
                    :disabled="processing"
                    class="flex items-center space-x-2 rounded-xl bg-[#1e4d8c] hover:bg-[#153a6b] px-8! py-3! h-11! text-xs font-black tracking-wider text-white shadow-md transition"
                    data-test="update-profile-button"
                >
                    Simpan Perubahan
                </Button>
            </div>
        </Form>
    </div>

    <div class="border-t border-slate-100 pt-8 mt-8">
        <DeleteUser />
    </div>
</template>
