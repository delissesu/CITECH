<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Mail, Lock, ArrowRight } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk Ke Akun - CITECH 2026" />

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
                        Masuk ke akun Anda untuk mengakses dashboard, membentuk
                        tim, dan mengikuti rangkaian kompetisi Carnival
                        Technology UNEJ 2026, ajang Web Design tingkat nasional
                        bagi generasi muda kreatif dan inovatif.
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
                            Masuk ke Akun
                        </h1>
                        <p
                            class="text-xs font-medium text-slate-400 md:text-sm"
                        >
                            Silakan masukkan email dan password Anda.
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
                            <Label
                                for="email"
                                class="block text-sm font-bold text-slate-800"
                                >Email</Label
                            >
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3.5 z-10 text-slate-400"
                                >
                                    <Mail class="h-5 w-5" />
                                </span>
                                <Input
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    placeholder="Masukkan email kamu"
                                    required
                                    autofocus
                                    class="w-full h-12 rounded-xl border border-slate-200 py-3 pr-4 pl-11 text-sm font-medium transition duration-300 focus:border-[#1e4d8c] focus:ring-2 focus:ring-blue-500/25 focus:outline-none"
                                />
                            </div>
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password Input -->
                        <div class="animate-fade-in-up space-y-2 delay-200">
                            <div class="flex items-center justify-between">
                                <Label
                                    for="password"
                                    class="block text-sm font-bold text-slate-800"
                                    >Password</Label
                                >
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-xs font-bold text-blue-600 transition hover:text-blue-800"
                                >
                                    Lupa Password?
                                </Link>
                            </div>
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3.5 z-10 text-slate-400"
                                >
                                    <Lock class="h-5 w-5" />
                                </span>
                                <Input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password"
                                    placeholder="Masukkan password kamu"
                                    required
                                    class="w-full h-12 rounded-xl border border-slate-200 py-3 pr-11 pl-11 text-sm font-medium transition duration-300 focus:border-[#1e4d8c] focus:ring-2 focus:ring-blue-500/25 focus:outline-none"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 z-10 text-slate-400 hover:text-slate-600 focus:outline-none"
                                >
                                    <EyeOff
                                        v-if="showPassword"
                                        class="h-5 w-5"
                                    />
                                    <Eye v-else class="h-5 w-5" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Remember Me -->
                        <div
                            class="animate-fade-in-up flex items-center gap-2 delay-300"
                        >
                            <Checkbox
                                id="remember_me"
                                v-model:checked="form.remember"
                            />
                            <Label
                                for="remember_me"
                                class="cursor-pointer text-xs font-bold text-slate-600 select-none"
                            >
                                Ingat saya di perangkat ini
                            </Label>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="animate-fade-in-up flex w-full h-12 items-center justify-center space-x-2 rounded-xl bg-[#1e4d8c] py-3.5 text-sm font-bold text-white shadow-md transition delay-400 duration-300 hover:bg-[#153a6b] hover:shadow-lg hover:shadow-blue-500/20 active:scale-[0.98] disabled:opacity-50"
                        >
                            <span>Masuk</span>
                            <ArrowRight class="h-4 w-4" />
                        </Button>
                    </form>

                    <!-- Links & Separators -->
                    <div
                        class="animate-fade-in-up space-y-6 pt-4 text-center delay-500"
                    >
                        <p class="text-xs font-bold text-slate-500">
                            Belum punya akun?
                            <Link
                                :href="route('register')"
                                class="text-blue-600 hover:text-blue-800 hover:underline"
                            >
                                Daftar Sekarang
                            </Link>
                        </p>

                        <div class="relative flex items-center py-2">
                            <div
                                class="flex-grow border-t border-slate-100"
                            ></div>
                            <span
                                class="mx-4 flex-shrink text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                >ATAU MASUK DENGAN</span
                            >
                            <div
                                class="flex-grow border-t border-slate-100"
                            ></div>
                        </div>

                        <!-- Google Sign In -->
                        <a
                            :href="route('auth.google')"
                            class="flex w-full items-center justify-center space-x-3 rounded-xl border border-slate-200 bg-white px-4 py-3.5 text-xs font-bold text-slate-700 shadow-sm transition duration-300 hover:bg-slate-50 active:scale-[0.98]"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24">
                                <path
                                    fill="#4285F4"
                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                />
                                <path
                                    fill="#34A853"
                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                />
                                <path
                                    fill="#FBBC05"
                                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"
                                />
                                <path
                                    fill="#EA4335"
                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"
                                />
                            </svg>
                            <span>Masuk dengan Google</span>
                        </a>
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
.delay-400 {
    animation-delay: 0.4s;
}
.delay-500 {
    animation-delay: 0.5s;
}
</style>
