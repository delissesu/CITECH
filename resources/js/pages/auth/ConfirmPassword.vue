<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import Layout from '@/layouts/settings/Layout.vue';

defineOptions({
    layout: Layout,
});

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Konfirmasi Password" />

    <div class="space-y-6">
        <div class="border-b border-slate-100 pb-4 mb-2">
            <h3 class="text-lg font-extrabold text-slate-800">
                Konfirmasi Password Anda
            </h3>
            <p class="text-xs font-bold text-slate-400 mt-1">
                Ini adalah area aman aplikasi. Silakan konfirmasi password Anda sebelum melanjutkan.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6 max-w-xl">
            <div class="grid gap-2">
                <label for="password" class="block text-[10px] font-black tracking-wider text-slate-400 uppercase">Password</label>
                <input
                    id="password"
                    type="password"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan password Anda"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-end">
                <button
                    type="submit"
                    class="flex items-center space-x-2 rounded-xl bg-[#1e4d8c] hover:bg-[#153a6b] px-8 py-3 text-xs font-black tracking-wider text-white shadow-md transition"
                    :disabled="form.processing"
                >
                    <span>Konfirmasi</span>
                </button>
            </div>
        </form>
    </div>
</template>
