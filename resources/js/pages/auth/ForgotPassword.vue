<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, ArrowRight } from '@lucide/vue';
import InputError from '@/components/InputError.vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Lupa Password - CITECH 2026" />

    <div
        class="flex min-h-screen items-center justify-center bg-slate-100 p-2 font-sans sm:p-6 md:p-8"
    >
        <div
            class="animate-card flex min-h-[720px] w-full max-w-7xl flex-col gap-4 rounded-3xl border border-slate-200/50 bg-white p-2.5 shadow-[0_25px_60px_rgba(0,0,0,0.08)] transition-all duration-300 md:gap-10 md:p-3 lg:flex-row"
        >
            <!-- Left Side Panel (Blue Gradient Banner) -->
            <div
                class="animate-left-panel relative flex min-h-[350px] flex-col justify-end overflow-hidden rounded-2xl bg-gradient-to-br from-[#1e3c72] via-[#2a5298] to-[#1e4d8c] p-8 text-white md:p-12 lg:min-h-0 lg:w-[55%]"
            >
                <!-- Decorative Glow Blobs -->
                <div
                    class="absolute -top-20 -right-20 h-80 w-80 rounded-full bg-white/5 blur-2xl"
                ></div>
                <div
                    class="absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-blue-500/10 blur-3xl"
                ></div>

                <!-- Brand Content -->
                <div class="relative z-10 space-y-4">
                    <h2
                        class="text-3xl font-extrabold tracking-tight md:text-4xl"
                    >
                        Carnival Technology
                    </h2>
                    <h3 class="text-xl font-bold text-[#eab308] md:text-2xl">
                        Wujudkan Ide, Ciptakan Inovasi
                    </h3>
                    <p
                        class="text-xs leading-relaxed font-medium text-white/80 md:text-sm"
                    >
                        Lupa kata sandi Anda? Tidak masalah. Beritahu kami
                        alamat email Anda dan kami akan mengirimkan email berisi
                        tautan penyetelan ulang kata sandi yang memungkinkan
                        Anda memilih kata sandi baru.
                    </p>
                </div>
            </div>

            <!-- Right Side Panel (Form) -->
            <div
                class="animate-right-panel flex flex-col justify-center px-4 py-6 md:px-8 lg:w-[45%]"
            >
                <div class="mx-auto w-full max-w-md space-y-8">
                    <!-- Header Text -->
                    <div class="animate-fade-in-up space-y-2">
                        <h1
                            class="text-2xl font-extrabold tracking-tight text-slate-900 md:text-3xl"
                        >
                            Lupa Kata Sandi
                        </h1>
                        <p
                            class="text-xs font-medium text-slate-400 md:text-sm"
                        >
                            Masukkan email terdaftar untuk menerima link setel
                            ulang kata sandi.
                        </p>
                    </div>

                    <!-- Session Status Alert -->
                    <div
                        v-if="status"
                        class="animate-fade-in-up rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-700 delay-100"
                    >
                        {{ status }}
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email Input -->
                        <div class="animate-fade-in-up space-y-2 delay-100">
                            <label
                                for="email"
                                class="block text-sm font-bold text-slate-800"
                                >Email</label
                            >
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"
                                >
                                    <Mail class="h-5 w-5" />
                                </span>
                                <input
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    placeholder="Masukkan email kamu"
                                    required
                                    autofocus
                                    class="w-full rounded-xl border border-slate-200 py-3 pr-4 pl-11 text-sm font-medium transition duration-300 focus:border-[#1e4d8c] focus:ring-2 focus:ring-blue-500/25 focus:outline-none"
                                />
                            </div>
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="animate-fade-in-up flex w-full items-center justify-center space-x-2 rounded-xl bg-[#1e4d8c] py-3.5 text-sm font-bold text-white shadow-md transition delay-200 duration-300 hover:bg-[#153a6b] hover:shadow-lg hover:shadow-blue-500/20 active:scale-[0.98] disabled:opacity-50"
                        >
                            <span>Kirim Link Setel Ulang</span>
                            <ArrowRight class="h-4 w-4" />
                        </button>
                    </form>

                    <!-- Links -->
                    <div class="animate-fade-in-up pt-4 text-center delay-300">
                        <p class="text-xs font-bold text-slate-500">
                            Sudah ingat password?
                            <Link
                                :href="route('login')"
                                class="font-bold text-blue-600 hover:text-blue-800 hover:underline"
                            >
                                Masuk Sekarang
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes cardPop {
    from {
        opacity: 0;
        transform: scale(0.97) translateY(16px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-32px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(32px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-card {
    animation: cardPop 1.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.animate-left-panel {
    animation: slideInLeft 1.4s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards;
    opacity: 0;
}
.animate-right-panel {
    animation: slideInRight 1.4s cubic-bezier(0.16, 1, 0.3, 1) 0.25s forwards;
    opacity: 0;
}
.animate-fade-in-up {
    animation: fadeInUp 1.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}
.delay-100 {
    animation-delay: 0.1s;
}
.delay-200 {
    animation-delay: 0.2s;
}
.delay-300 {
    animation-delay: 0.3s;
}
</style>
