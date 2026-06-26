<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Users,
    FileCheck,
    CreditCard,
    Trophy,
    Upload,
    ArrowRight,
    ExternalLink,
    Handshake,
} from '@lucide/vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';

interface Statistics {
    totalTim: number;
    totalTimVerified: number;
    totalSubmission: number;
    persyaratanPending: number;
    pembayaranPending: number;
    totalSponsor: number;
}

interface Tim {
    nama_tim: string;
    universitas: string;
    batch: number;
}

interface Pembayaran {
    id_pembayaran: number;
    tim?: Tim;
    uploaded_at?: string;
}

interface Persyaratan {
    id_registrasi: number;
    tim?: Tim;
    uploaded_at?: string;
}

defineProps<{
    statistics: Statistics;
    latestPembayaran: Pembayaran[];
    latestPersyaratan: Persyaratan[];
}>();

const formatDate = (dateStr: string | undefined) => {
    if (!dateStr) {
        return '-';
    }

    const options: Intl.DateTimeFormatOptions = {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    };

    return new Date(dateStr).toLocaleDateString('id-ID', options);
};
</script>

<template>
    <Head title="Dashboard Admin" />

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

                <Link
                    :href="route('admin.kelola-sponsor')"
                    class="rounded-2xl border border-slate-100 bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] transition hover:shadow-md"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eef2ff]"
                        >
                            <Handshake class="h-5 w-5 text-indigo-600" />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Total Sponsor
                            </p>
                            <p class="text-2xl font-black text-slate-800">
                                {{ statistics.totalSponsor }}
                            </p>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Quick Verifications Panel -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Latest Payments Verification Card -->
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)]"
                >
                    <div
                        class="mb-4 flex items-center justify-between border-b border-slate-50 pb-4"
                    >
                        <div class="flex items-center gap-2.5">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50"
                            >
                                <CreditCard class="h-4.5 w-4.5 text-rose-600" />
                            </div>
                            <div>
                                <h4
                                    class="text-sm font-extrabold text-slate-800"
                                >
                                    Konfirmasi Pembayaran Terbaru
                                </h4>
                                <p
                                    class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    5 pendaftaran yang baru masuk
                                </p>
                            </div>
                        </div>
                        <Link
                            :href="route('admin.pembayaran')"
                            class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-900 transition hover:text-blue-700"
                        >
                            <span>Lihat Semua</span>
                            <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>

                    <!-- Payments List -->
                    <div class="divide-y divide-slate-100">
                        <div
                            v-if="
                                !latestPembayaran ||
                                latestPembayaran.length === 0
                            "
                            class="py-8 text-center text-xs font-bold text-slate-400"
                        >
                            Tidak ada pembayaran pending baru.
                        </div>
                        <div
                            v-else
                            v-for="pembayaran in latestPembayaran"
                            :key="pembayaran.id_pembayaran"
                            class="flex items-center justify-between rounded-xl px-2 py-3 transition hover:bg-slate-50/50"
                        >
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-extrabold text-slate-800"
                                >
                                    {{
                                        pembayaran.tim?.nama_tim ||
                                        'Tanpa Nama Tim'
                                    }}
                                </div>
                                <div
                                    class="text-[10px] font-bold tracking-wide text-slate-400 uppercase"
                                >
                                    {{ pembayaran.tim?.universitas || '-' }}
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <div
                                        class="text-[10px] font-extrabold text-slate-600"
                                    >
                                        Batch {{ pembayaran.tim?.batch || 1 }}
                                    </div>
                                    <div
                                        class="text-[9px] font-bold text-slate-400"
                                    >
                                        {{ formatDate(pembayaran.uploaded_at) }}
                                    </div>
                                </div>
                                <Link
                                    :href="route('admin.pembayaran')"
                                    class="rounded-lg border border-slate-100 bg-slate-50/50 p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600"
                                    title="Verifikasi"
                                >
                                    <ExternalLink class="h-3.5 w-3.5" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Latest Requirements Verification Card -->
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)]"
                >
                    <div
                        class="mb-4 flex items-center justify-between border-b border-slate-50 pb-4"
                    >
                        <div class="flex items-center gap-2.5">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50"
                            >
                                <FileCheck class="h-4.5 w-4.5 text-amber-600" />
                            </div>
                            <div>
                                <h4
                                    class="text-sm font-extrabold text-slate-800"
                                >
                                    Konfirmasi Persyaratan Terbaru
                                </h4>
                                <p
                                    class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    5 dokumen yang baru masuk
                                </p>
                            </div>
                        </div>
                        <Link
                            :href="route('admin.persyaratan')"
                            class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-900 transition hover:text-blue-700"
                        >
                            <span>Lihat Semua</span>
                            <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>

                    <!-- Requirements List -->
                    <div class="divide-y divide-slate-100">
                        <div
                            v-if="
                                !latestPersyaratan ||
                                latestPersyaratan.length === 0
                            "
                            class="py-8 text-center text-xs font-bold text-slate-400"
                        >
                            Tidak ada persyaratan pending baru.
                        </div>
                        <div
                            v-else
                            v-for="persyaratan in latestPersyaratan"
                            :key="persyaratan.id_registrasi"
                            class="flex items-center justify-between rounded-xl px-2 py-3 transition hover:bg-slate-50/50"
                        >
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-extrabold text-slate-800"
                                >
                                    {{
                                        persyaratan.tim?.nama_tim ||
                                        'Tanpa Nama Tim'
                                    }}
                                </div>
                                <div
                                    class="text-[10px] font-bold tracking-wide text-slate-400 uppercase"
                                >
                                    {{ persyaratan.tim?.universitas || '-' }}
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <span
                                        class="inline-block rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[8px] font-black tracking-wider text-amber-600 uppercase"
                                    >
                                        Pending
                                    </span>
                                    <div
                                        class="text-[9px] font-bold text-slate-400"
                                    >
                                        {{
                                            formatDate(persyaratan.uploaded_at)
                                        }}
                                    </div>
                                </div>
                                <Link
                                    :href="route('admin.persyaratan')"
                                    class="rounded-lg border border-slate-100 bg-slate-50/50 p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600"
                                    title="Verifikasi"
                                >
                                    <ExternalLink class="h-3.5 w-3.5" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
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
