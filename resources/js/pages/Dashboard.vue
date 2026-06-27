<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Download, User, Users, Calendar, FileDown } from '@lucide/vue';
import { computed } from 'vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';

const props = defineProps({
    activeTimeline: Object,
    allTimelines: Array,
    userTeam: Object,
    teamMembers: Array,
});

const getFormattedDate = () => {
    const options = {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    };

    return new Date().toLocaleDateString('id-ID', options);
};

const mapTahap = (tahap) => {
    const mapping = {
        pendaftaran_b1: 'Pendaftaran Batch 1',
        pendaftaran_b2: 'Pendaftaran Batch 2',
        penyisihan: 'Pengumpulan Berkas (Babak Penyisihan)',
        final: 'Pengumpulan Berkas (Babak Final)',
        awarding: 'Final & Awarding',
    };

    return mapping[tahap] || tahap;
};

const formatTimelineDate = (start, end) => {
    if (!start || !end) {
        return '';
    }

    const startD = new Date(start);
    const endD = new Date(end);

    // If end date is at exactly midnight (00:00:00), display as the previous day
    // e.g. "2026-07-19 00:00:00" should display as "18 Juli" (deadline is 18 Juli 23:59)
    if (endD.getHours() === 0 && endD.getMinutes() === 0 && endD.getSeconds() === 0) {
        endD.setDate(endD.getDate() - 1);
    }

    const options = { day: 'numeric', month: 'long' };
    const yearOptions = { year: 'numeric' };

    const startStr = startD.toLocaleDateString('id-ID', options);
    const endStr = endD.toLocaleDateString('id-ID', {
        ...options,
        ...yearOptions,
    });

    return `${startStr} - ${endStr}`;
};

const getTimelineStatus = (start, end) => {
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

    const startD = new Date(start);
    const startMidnight = new Date(
        startD.getFullYear(),
        startD.getMonth(),
        startD.getDate(),
    );

    const endD = new Date(end);
    const endMidnight = new Date(
        endD.getFullYear(),
        endD.getMonth(),
        endD.getDate(),
        23,
        59,
        59,
        999,
    );

    if (today > endMidnight) {
        return {
            label: 'Selesai',
            class: 'bg-green-100 text-green-700 border-green-200',
        };
    } else if (today >= startMidnight && today <= endMidnight) {
        return {
            label: 'Sedang Berlangsung',
            class: 'bg-amber-100 text-amber-700 border-amber-200',
        };
    } else {
        const diffMs = startMidnight - today;
        const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));

        if (diffDays <= 0) {
            return {
                label: 'Dimulai hari ini',
                class: 'bg-blue-100 text-blue-700 border-blue-200',
            };
        } else if (diffDays === 1) {
            return {
                label: 'Dimulai besok',
                class: 'bg-blue-100 text-blue-700 border-blue-200',
            };
        } else if (diffDays <= 7) {
            return {
                label: `Dimulai dalam ${diffDays} hari`,
                class: 'bg-blue-100 text-blue-700 border-blue-200',
            };
        } else {
            return {
                label: 'Akan Datang',
                class: 'bg-blue-50 text-blue-600 border-blue-100',
            };
        }
    }
};

const getTimelineCircleClass = (start, end) => {
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

    const startD = new Date(start);
    const startMidnight = new Date(
        startD.getFullYear(),
        startD.getMonth(),
        startD.getDate(),
    );

    const endD = new Date(end);
    const endMidnight = new Date(
        endD.getFullYear(),
        endD.getMonth(),
        endD.getDate(),
        23,
        59,
        59,
        999,
    );

    if (today > endMidnight) {
        return 'bg-amber-500 border-amber-500';
    } else if (today >= startMidnight && today <= endMidnight) {
        return 'bg-amber-500 border-amber-500 ring-4 ring-amber-500/25';
    } else {
        return 'bg-white border-slate-300';
    }
};
</script>

<template>
    <Head title="Dashboard Peserta" />

    <CitechDashboardLayout activeMenu="dashboard" role="peserta">
        <template #header-title>
            <div class="hidden sm:block">
                <h2 class="text-lg font-black tracking-wide text-slate-800">
                    Beranda
                </h2>
            </div>
        </template>

        <!-- Welcome section -->
        <div class="animate-fade-in-up mb-8 space-y-1">
            <h1
                class="text-2xl font-black tracking-tight text-slate-900 md:text-3xl"
            >
                Selamat Datang, {{ $page.props.auth.user.name }}
            </h1>
            <p
                class="text-xs font-bold tracking-wide text-slate-400 md:text-sm"
            >
                {{ getFormattedDate() }}
            </p>
        </div>

        <!-- Main Dashboard Cards Grid -->
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
            <!-- Left Side (Downloads & Team Members) -->
            <div class="space-y-8 lg:col-span-5">
                <!-- Downloads Card -->
                <div
                    class="space-y-6 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8"
                >
                    <h3
                        class="flex items-center space-x-2 text-lg font-extrabold tracking-tight text-slate-800"
                    >
                        <FileDown class="h-5 w-5 text-[#1e4d8c]" />
                        <span>Unduh Dokumen</span>
                    </h3>

                    <div class="space-y-4">
                        <!-- Guidebook Download -->
                        <div
                            class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 p-4 transition hover:bg-slate-100/50"
                        >
                            <div class="space-y-0.5">
                                <h4 class="text-sm font-bold text-slate-800">
                                    Guidebook Citech
                                </h4>
                                <p class="text-[11px] font-bold text-slate-400">
                                    PDF
                                </p>
                            </div>
                            <a
                                href="#"
                                class="flex items-center space-x-1 rounded-xl border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-black text-blue-600 transition hover:bg-blue-100"
                            >
                                <span>Download</span>
                                <Download class="h-3.5 w-3.5" />
                            </a>
                        </div>

                        <!-- Surat Orisinalitas Download -->
                        <div
                            class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 p-4 transition hover:bg-slate-100/50"
                        >
                            <div class="space-y-0.5">
                                <h4 class="text-sm font-bold text-slate-800">
                                    Surat Orisinalitas
                                </h4>
                                <p class="text-[11px] font-bold text-slate-400">
                                    DOCX
                                </p>
                            </div>
                            <a
                                href="#"
                                class="flex items-center space-x-1 rounded-xl border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-black text-blue-600 transition hover:bg-blue-100"
                            >
                                <span>Download</span>
                                <Download class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Anggota Tim Card -->
                <div
                    class="space-y-6 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8"
                >
                    <h3
                        class="flex items-center space-x-2 text-lg font-extrabold tracking-tight text-slate-800"
                    >
                        <Users class="h-5 w-5 text-[#1e4d8c]" />
                        <span>Anggota Tim</span>
                    </h3>

                    <div v-if="userTeam" class="space-y-3">
                        <div
                            class="mb-4 rounded-2xl border border-blue-100/50 bg-blue-50/50 p-2 text-center"
                        >
                            <span
                                class="text-xs font-black tracking-wider text-blue-800 uppercase"
                                >TIM: {{ userTeam.nama_tim }}</span
                            >
                        </div>

                        <div
                            v-for="member in teamMembers"
                            :key="member.id_member"
                            class="flex items-center space-x-4 rounded-xl border border-slate-100/80 bg-slate-50 p-3.5 transition hover:bg-slate-100/50"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-600"
                            >
                                {{
                                    member.nama_peserta.charAt(0).toUpperCase()
                                }}
                            </div>
                            <div class="flex-grow">
                                <h4
                                    class="text-sm font-extrabold text-slate-800"
                                >
                                    {{ member.nama_peserta }}
                                </h4>
                                <p
                                    class="mt-0.5 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    NIM. {{ member.nim_peserta }}
                                </p>
                            </div>
                            <span
                                class="rounded-full border px-2.5 py-1 text-[9px] font-black tracking-wider uppercase"
                                :class="[
                                    member.role === 'ketua'
                                        ? 'border-blue-900 bg-blue-900 text-white'
                                        : 'border-slate-200 bg-slate-100 text-slate-600',
                                ]"
                            >
                                {{ member.role }}
                            </span>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="space-y-4 py-8 text-center">
                        <div
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400"
                        >
                            <Users class="h-6 w-6" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-bold text-slate-700">
                                Kamu belum terdaftar di tim mana pun
                            </p>
                            <p class="text-[11px] font-bold text-slate-400">
                                Silakan buat tim baru untuk mulai
                                berpartisipasi.
                            </p>
                        </div>
                        <Link
                            :href="route('peserta.tim')"
                            class="inline-flex items-center space-x-2 rounded-xl bg-[#1e4d8c] px-5 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-[#153a6b]"
                        >
                            <span>Buat Tim Sekarang</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Right Side (Timeline) -->
            <div class="lg:col-span-7">
                <div
                    class="space-y-8 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8"
                >
                    <h3
                        class="flex items-center space-x-2 text-lg font-extrabold tracking-tight text-slate-800"
                    >
                        <Calendar class="h-5 w-5 text-[#1e4d8c]" />
                        <span>Timeline Perlombaan</span>
                    </h3>

                    <!-- Timeline flow -->
                    <div class="relative space-y-8 pl-8">
                        <!-- Vertical line -->
                        <div
                            class="absolute top-2 bottom-2 left-[11px] w-0.5 bg-slate-200"
                        ></div>

                        <!-- Timeline Item -->
                        <div
                            v-for="item in allTimelines"
                            :key="item.id_timeline"
                            class="relative flex flex-col justify-between gap-2 sm:flex-row sm:items-center"
                        >
                            <!-- Circle marker -->
                            <div
                                class="absolute top-1.5 -left-[28px] flex h-6 w-6 items-center justify-center rounded-full border-4 transition-all duration-300"
                                :class="
                                    getTimelineCircleClass(
                                        item.tanggal_mulai,
                                        item.tanggal_selesai,
                                    )
                                "
                            ></div>

                            <!-- Content -->
                            <div class="space-y-1">
                                <h4
                                    class="text-sm font-extrabold tracking-tight text-slate-800"
                                >
                                    {{ mapTahap(item.tahap) }}
                                </h4>
                                <p class="text-xs font-semibold text-slate-400">
                                    {{
                                        formatTimelineDate(
                                            item.tanggal_mulai,
                                            item.tanggal_selesai,
                                        )
                                    }}
                                </p>
                            </div>

                            <!-- Status Pill -->
                            <div>
                                <span
                                    class="inline-block rounded-xl border px-3 py-1.5 text-[10px] font-black tracking-wider uppercase"
                                    :class="
                                        getTimelineStatus(
                                            item.tanggal_mulai,
                                            item.tanggal_selesai,
                                        ).class
                                    "
                                >
                                    {{
                                        getTimelineStatus(
                                            item.tanggal_mulai,
                                            item.tanggal_selesai,
                                        ).label
                                    }}
                                </span>
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
    animation: fadeInUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
