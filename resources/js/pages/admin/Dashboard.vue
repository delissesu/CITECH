<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Users, FileCheck, CreditCard, Trophy, Upload } from '@lucide/vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';

defineProps({
    statistics: Object,
});
</script>

<template>
    <Head title="Dashboard Admin - CITECH 2026" />

    <CitechDashboardLayout activeMenu="admin.dashboard" role="admin">
        <template #header-title>
            <h2 class="text-lg font-black tracking-wide text-slate-800">
                Beranda Admin
            </h2>
        </template>

        <div class="animate-fade-in-up space-y-6">
            <div
                class="rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8"
            >
                <h3 class="text-xl font-extrabold text-slate-800">
                    Selamat Datang di Panel Admin CITECH
                </h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Gunakan sidebar di sebelah kiri untuk menavigasi menu
                    pengelolaan berkas persyaratan, verifikasi pembayaran, data
                    tim, karya submission, dan penjadwalan tanggal timeline
                    lomba.
                </p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.03)]"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50"
                        >
                            <Users class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Total Tim
                            </p>
                            <p class="text-2xl font-black text-slate-800">
                                {{ statistics.totalTim }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-100 bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.03)]"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50"
                        >
                            <Trophy class="h-5 w-5 text-emerald-600" />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Tim Terverifikasi
                            </p>
                            <p class="text-2xl font-black text-slate-800">
                                {{ statistics.totalTimVerified }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-100 bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.03)]"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50"
                        >
                            <Upload class="h-5 w-5 text-purple-600" />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Total Submission
                            </p>
                            <p class="text-2xl font-black text-slate-800">
                                {{ statistics.totalSubmission }}
                            </p>
                        </div>
                    </div>
                </div>

                <Link
                    :href="route('admin.persyaratan')"
                    class="rounded-2xl border border-slate-100 bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] transition hover:shadow-md"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50"
                        >
                            <FileCheck class="h-5 w-5 text-amber-600" />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Persyaratan Pending
                            </p>
                            <p class="text-2xl font-black text-slate-800">
                                {{ statistics.persyaratanPending }}
                            </p>
                        </div>
                    </div>
                </Link>

                <Link
                    :href="route('admin.pembayaran')"
                    class="rounded-2xl border border-slate-100 bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] transition hover:shadow-md"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50"
                        >
                            <CreditCard class="h-5 w-5 text-rose-600" />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Pembayaran Pending
                            </p>
                            <p class="text-2xl font-black text-slate-800">
                                {{ statistics.pembayaranPending }}
                            </p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </CitechDashboardLayout>
</template>

<style scoped>
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
.animate-fade-in-up {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
