<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <Head title="Verifikasi Email" />

    <div
        class="flex min-h-screen items-center justify-center bg-slate-100 p-2 font-sans sm:p-6 md:p-8"
    >
        <div
            class="animate-card flex min-h-[600px] w-full max-w-5xl flex-col gap-4 rounded-3xl border border-slate-200/50 bg-white p-2.5 shadow-[0_25px_60px_rgba(0,0,0,0.08)] transition-all duration-300 md:gap-10 md:p-3 lg:flex-row"
        >
            <!-- Left Side Panel (Blue Gradient Banner) -->
            <div
                class="animate-left-panel relative flex min-h-[250px] flex-col justify-end overflow-hidden rounded-2xl bg-gradient-to-br from-[#1e3c72] via-[#2a5298] to-[#1e4d8c] p-8 text-white md:p-12 lg:min-h-0 lg:w-[50%]"
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
                        class="text-2xl font-extrabold tracking-tight md:text-3xl"
                    >
                        Carnival Technology
                    </h2>
                    <h3 class="text-lg font-bold text-[#eab308]">
                        Verifikasi Akun Anda
                    </h3>
                    <p
                        class="text-xs leading-relaxed font-medium text-white/80"
                    >
                        Satu langkah lagi untuk bergabung dalam ajang kompetisi Web Design tingkat nasional bagi generasi muda kreatif dan inovatif di UNEJ.
                    </p>
                </div>
            </div>

            <!-- Right Side Panel (Verification message & action) -->
            <div
                class="animate-right-panel flex flex-col justify-center px-4 py-6 md:px-8 lg:w-[50%]"
            >
                <div class="mx-auto w-full max-w-md space-y-8">
                    <!-- Header Text -->
                    <div class="animate-fade-in-up space-y-2">
                        <h1
                            class="text-2xl font-extrabold tracking-tight text-slate-900 md:text-3xl"
                        >
                            Verifikasi Email
                        </h1>
                        <p
                            class="text-xs font-bold leading-relaxed text-slate-400"
                        >
                            Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan ke email Anda. Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan ulang.
                        </p>
                    </div>

                    <!-- Link Sent Alert -->
                    <div
                        v-if="verificationLinkSent"
                        class="animate-fade-in-up rounded-xl border border-green-200 bg-green-50 p-4 text-xs font-semibold text-green-700"
                    >
                        Tautan verifikasi baru telah dikirimkan ke alamat email yang Anda berikan saat pendaftaran.
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="flex h-12 items-center justify-center space-x-2 rounded-xl bg-[#1e4d8c] px-6 py-3.5 text-xs font-black tracking-wider text-white shadow-md transition duration-300 hover:bg-[#153a6b]"
                            >
                                Kirim Ulang Email Verifikasi
                            </Button>

                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="text-xs font-black text-slate-400 underline decoration-slate-200 underline-offset-4 transition hover:text-slate-600 hover:decoration-slate-400"
                            >
                                Keluar (Logout)
                            </Link>
                        </div>
                    </form>
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
</style>

