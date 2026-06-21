<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
  Eye, 
  EyeOff, 
  Mail, 
  Lock, 
  ArrowRight 
} from '@lucide/vue';
import InputError from '@/Components/InputError.vue';

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

    <div class="min-h-screen bg-slate-100 flex items-center justify-center p-2 sm:p-6 md:p-8 font-sans">
        <div class="bg-white rounded-3xl w-full max-w-7xl shadow-[0_25px_60px_rgba(0,0,0,0.08)] border border-slate-200/50 p-2.5 md:p-3 flex flex-col lg:flex-row gap-4 md:gap-10 min-h-[720px] transition-all duration-300 animate-card">
            
            <!-- Left Side Panel (Blue Gradient Banner) -->
            <div class="lg:w-[55%] bg-gradient-to-br from-[#1e3c72] via-[#2a5298] to-[#1e4d8c] rounded-2xl p-8 md:p-12 text-white flex flex-col justify-end relative overflow-hidden min-h-[350px] lg:min-h-0 animate-left-panel">
                <!-- Decorative Glow Blobs -->
                <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-white/5 blur-2xl"></div>
                <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-blue-500/10 blur-3xl"></div>
                
                <!-- Brand Content -->
                <div class="relative z-10 space-y-4">
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">Carnival Technology</h2>
                    <h3 class="text-xl md:text-2xl font-bold text-[#eab308]">Wujudkan Ide, Ciptakan Inovasi</h3>
                    <p class="text-white/80 text-xs md:text-sm leading-relaxed font-medium">
                        Masuk ke akun Anda untuk mengakses dashboard, membentuk tim, dan mengikuti rangkaian kompetisi Carnival Technology UNEJ 2026, ajang Web Design tingkat nasional bagi generasi muda kreatif dan inovatif.
                    </p>
                </div>
            </div>

            <!-- Right Side Panel (Form) -->
            <div class="lg:w-[45%] flex flex-col justify-center px-4 md:px-8 py-6 animate-right-panel">
                <div class="w-full max-w-md mx-auto space-y-8">
                    
                    <!-- Header Text -->
                    <div class="space-y-2 animate-fade-in-up">
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Masuk ke Akun</h1>
                        <p class="text-slate-400 text-xs md:text-sm font-medium">Silakan masukkan email dan password Anda.</p>
                    </div>

                    <!-- Session Status Alert -->
                    <div v-if="status" class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl text-sm font-semibold animate-fade-in-up delay-100">
                        {{ status }}
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="space-y-5">
                        
                        <!-- Email Input -->
                        <div class="space-y-2 animate-fade-in-up delay-100">
                            <label for="email" class="text-sm font-bold text-slate-800 block">Email</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                    <Mail class="w-5 h-5" />
                                </span>
                                <input 
                                    id="email" 
                                    type="email" 
                                    v-model="form.email" 
                                    placeholder="Masukkan email kamu" 
                                    required 
                                    autofocus 
                                    class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/25 focus:border-[#1e4d8c] transition duration-300 text-sm font-medium"
                                />
                            </div>
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password Input -->
                        <div class="space-y-2 animate-fade-in-up delay-200">
                            <div class="flex items-center justify-between">
                                <label for="password" class="text-sm font-bold text-slate-800 block">Password</label>
                                <Link 
                                    v-if="canResetPassword" 
                                    :href="route('password.request')" 
                                    class="text-xs font-bold text-blue-600 hover:text-blue-800 transition"
                                >
                                    Lupa Password?
                                </Link>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                    <Lock class="w-5 h-5" />
                                </span>
                                <input 
                                    id="password" 
                                    :type="showPassword ? 'text' : 'password'" 
                                    v-model="form.password" 
                                    placeholder="Masukkan password kamu" 
                                    required 
                                    class="w-full pl-11 pr-11 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/25 focus:border-[#1e4d8c] transition duration-300 text-sm font-medium"
                                />
                                <button 
                                    type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                                >
                                    <EyeOff v-if="showPassword" class="w-5 h-5" />
                                    <Eye v-else class="w-5 h-5" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center animate-fade-in-up delay-300">
                            <input 
                                id="remember_me" 
                                type="checkbox" 
                                v-model="form.remember" 
                                class="w-4 h-4 text-[#1e4d8c] border-slate-300 rounded focus:ring-[#1e4d8c]"
                            />
                            <label for="remember_me" class="ml-2 text-xs font-bold text-slate-600 cursor-pointer select-none">
                                Ingat saya di perangkat ini
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full bg-[#1e4d8c] hover:bg-[#153a6b] text-white font-bold text-sm py-3.5 rounded-xl transition duration-300 shadow-md hover:shadow-lg hover:shadow-blue-500/20 active:scale-[0.98] disabled:opacity-50 flex items-center justify-center space-x-2 animate-fade-in-up delay-400"
                        >
                            <span>Masuk</span>
                            <ArrowRight class="w-4 h-4" />
                        </button>
                    </form>

                    <!-- Links & Separators -->
                    <div class="space-y-6 pt-4 text-center animate-fade-in-up delay-500">
                        <p class="text-xs font-bold text-slate-500">
                            Belum punya akun? 
                            <Link :href="route('register')" class="text-blue-600 hover:text-blue-800 hover:underline">
                                Daftar Sekarang
                            </Link>
                        </p>

                        <div class="relative flex py-2 items-center">
                            <div class="flex-grow border-t border-slate-100"></div>
                            <span class="flex-shrink mx-4 text-[10px] font-black tracking-wider text-slate-400 uppercase">ATAU MASUK DENGAN</span>
                            <div class="flex-grow border-t border-slate-100"></div>
                        </div>

                        <!-- Google Sign In -->
                        <a 
                            :href="route('auth.google')"
                            class="w-full flex items-center justify-center space-x-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs py-3.5 px-4 rounded-xl transition duration-300 shadow-sm active:scale-[0.98]"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" />
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" />
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
    from { opacity: 0; transform: scale(0.97) translateY(16px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-32px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes slideInRight {
    from { opacity: 0; transform: translateX(32px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
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
