<script setup>
import { Head } from '@inertiajs/vue3';
import {
    Search,
    FileText,
    ExternalLink,
    Calendar,
    CheckCircle2,
} from '@lucide/vue';
import { ref, computed } from 'vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';

const props = defineProps({
    teams: Array,
});

const searchQuery = ref('');

// Filter teams by search query
const filteredTeams = computed(() => {
    if (!props.teams) {
return [];
}

    return props.teams.filter((team) => {
        const query = searchQuery.value.toLowerCase();

        return (
            team.nama_tim.toLowerCase().includes(query) ||
            team.universitas.toLowerCase().includes(query)
        );
    });
});

const formatDate = (dateStr) => {
    if (!dateStr) {
return '-';
}

    const options = {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    };

    return new Date(dateStr).toLocaleDateString('id-ID', options);
};

const getKetuaName = (members) => {
    if (!members) {
return '-';
}

    const ketua = members.find((m) => m.role === 'ketua');

    return ketua ? ketua.nama_peserta : '-';
};
</script>

<template>
    <Head title="Submission Karya - CITECH 2026" />

    <CitechDashboardLayout activeMenu="admin.submission" role="admin">
        <template #header-title>
            <h2
                class="font-sans text-lg font-black tracking-wide text-slate-800"
            >
                Submission
            </h2>
        </template>

        <div class="animate-fade-in-up space-y-6">
            <!-- Header Card -->
            <div
                class="flex flex-col justify-between gap-6 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:flex-row md:items-center md:p-8"
            >
                <div class="space-y-2">
                    <h3
                        class="flex items-center space-x-2 text-xl font-black tracking-tight text-slate-800"
                    >
                        <FileText class="h-6 w-6 text-blue-900" />
                        <span>Proposal & Surat Orisinalitas</span>
                    </h3>
                    <p
                        class="max-w-2xl text-xs leading-relaxed font-bold text-slate-500"
                    >
                        Tinjau link Google Drive pengumpulan proposal dan surat
                        orisinalitas dari tim-tim peserta yang terdaftar resmi.
                    </p>
                </div>

                <!-- Search Input -->
                <div class="relative w-full flex-shrink-0 md:w-80">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"
                    >
                        <Search class="h-4 w-4" />
                    </span>
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Cari nama tim atau universitas..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pr-4 pl-10 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-900 focus:outline-none"
                    />
                </div>
            </div>

            <!-- Table Card -->
            <div
                class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-[0_10px_35px_rgba(0,0,0,0.03)]"
            >
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-slate-100 bg-slate-50/70 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                            >
                                <th class="px-6 py-4">Nama Tim / Instansi</th>
                                <th class="px-6 py-4">Ketua Tim</th>
                                <th class="px-6 py-4">Tanggal Pengumpulan</th>
                                <th class="px-6 py-4 text-center">
                                    Tautan Proposal
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 text-xs font-bold text-slate-700"
                        >
                            <tr v-if="filteredTeams.length === 0">
                                <td
                                    colspan="4"
                                    class="py-12 text-center font-bold text-slate-400"
                                >
                                    Tidak ada data submission yang ditemukan.
                                </td>
                            </tr>
                            <tr
                                v-for="team in filteredTeams"
                                :key="team.id_tim"
                                class="transition-colors hover:bg-slate-50/30"
                            >
                                <!-- Team / Univ -->
                                <td class="space-y-1 px-6 py-4">
                                    <div
                                        class="text-sm font-extrabold text-blue-900"
                                    >
                                        {{ team.nama_tim }}
                                    </div>
                                    <div
                                        class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                    >
                                        {{ team.universitas }}
                                    </div>
                                </td>

                                <!-- Ketua Tim -->
                                <td
                                    class="px-6 py-4 font-semibold text-slate-600"
                                >
                                    {{ getKetuaName(team.members) }}
                                </td>

                                <!-- Upload Date -->
                                <td
                                    class="px-6 py-4 font-semibold text-slate-500"
                                >
                                    <div class="flex items-center space-x-1.5">
                                        <Calendar
                                            class="h-3.5 w-3.5 text-slate-400"
                                        />
                                        <span>{{
                                            team.submission
                                                ? formatDate(
                                                      team.submission
                                                          .uploaded_at,
                                                  )
                                                : '-'
                                        }}</span>
                                    </div>
                                </td>

                                <!-- Link Google Drive -->
                                <td class="px-6 py-4 text-center">
                                    <a
                                        v-if="team.submission"
                                        :href="
                                            team.submission.link_file_submission
                                        "
                                        target="_blank"
                                        class="inline-flex items-center space-x-1 rounded-xl border border-blue-100/50 bg-blue-50 px-3.5 py-2 text-[10px] font-black tracking-wider text-blue-600 uppercase shadow-sm transition hover:bg-blue-100"
                                    >
                                        <FileText class="h-3.5 w-3.5" />
                                        <span>Buka Link Drive</span>
                                        <ExternalLink class="h-2.5 w-2.5" />
                                    </a>
                                    <span
                                        v-else
                                        class="font-bold text-slate-400"
                                        >-</span
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
