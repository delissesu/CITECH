<script setup>
import { Head } from '@inertiajs/vue3';
import {
    Search,
    Users,
    Award,
    ShieldCheck,
    Info,
    FileText,
    CheckCircle2,
    X,
    Download,
} from '@lucide/vue';
import { ref, computed } from 'vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const props = defineProps({
    teams: Array,
});

const searchQuery = ref('');
const selectedTeamDetails = ref(null);
const isDetailModalOpen = ref(false);

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

const mapStatusSeleksi = (status) => {
    const mapping = {
        penyisihan: 'Babak Penyisihan',
        tidak_lolos_final: 'Tidak Lolos Final',
        final: 'Babak Final',
    };

    return mapping[status] || status;
};

const getKetuaName = (members) => {
    if (!members) {
        return '-';
    }

    const ketua = members.find((m) => m.role === 'ketua');

    return ketua ? ketua.nama_peserta : '-';
};

const openTeamDetails = (team) => {
    // Sort members: leader first
    const membersList = team.members
        ? [...team.members].sort((a, b) => {
              if (a.role === 'ketua') {
                  return -1;
              }

              if (b.role === 'ketua') {
                  return 1;
              }

              return a.id_member - b.id_member;
          })
        : [];

    selectedTeamDetails.value = {
        ...team,
        sortedMembers: membersList,
    };
    isDetailModalOpen.value = true;
};

const closeTeamDetails = () => {
    isDetailModalOpen.value = false;
};
</script>

<template>
    <Head title="Tim Terdaftar" />

    <CitechDashboardLayout activeMenu="admin.tim-terdaftar" role="admin">
        <template #header-title>
            <h2
                class="font-sans text-lg font-black tracking-wide text-slate-800"
            >
                Tim Terdaftar
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
                        <CheckCircle2 class="h-6 w-6 text-green-600" />
                        <span>Daftar Tim Terverifikasi</span>
                    </h3>
                    <p
                        class="max-w-2xl text-xs leading-relaxed font-bold text-slate-500"
                    >
                        Menampilkan daftar seluruh tim peserta CITECH 2026 yang
                        telah berhasil melewati verifikasi berkas persyaratan
                        administratif dan penyelesaian pembayaran pendaftaran.
                    </p>
                </div>

                <!-- Search & Export -->
                <div
                    class="flex w-full flex-shrink-0 flex-col gap-3 sm:flex-row sm:items-center md:w-auto"
                >
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64 md:w-80">
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

                    <!-- Export Button -->
                    <a
                        :href="route('admin.tim-terdaftar.export')"
                        target="_blank"
                        class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700"
                    >
                        <Download class="h-4 w-4" />
                        <span>Export Excel</span>
                    </a>
                </div>
            </div>

            <!-- Table Card -->
            <div
                class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-[0_10px_35px_rgba(0,0,0,0.03)]"
            >
                <div class="overflow-x-auto">
                    <Table class="w-full border-collapse text-left">
                        <TableHeader>
                            <TableRow
                                class="border-b border-slate-100 bg-slate-50/70 text-[10px] font-black tracking-widest text-slate-400 uppercase hover:bg-slate-50/70"
                            >
                                <TableHead class="px-6 py-4 text-slate-400"
                                    >Nama Tim / Instansi</TableHead
                                >
                                <TableHead class="px-6 py-4 text-slate-400"
                                    >Ketua Tim</TableHead
                                >
                                <TableHead class="px-6 py-4 text-slate-400"
                                    >Batch Lomba</TableHead
                                >
                                <TableHead class="px-6 py-4 text-slate-400"
                                    >Tanggal Verifikasi</TableHead
                                >
                                <TableHead
                                    class="px-6 py-4 text-center text-slate-400"
                                >
                                    Status Lomba
                                </TableHead>
                                <TableHead
                                    class="px-6 py-4 text-center text-slate-400"
                                    >Aksi</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody
                            class="divide-y divide-slate-100 text-xs font-bold text-slate-700"
                        >
                            <TableRow
                                v-if="filteredTeams.length === 0"
                                class="hover:bg-transparent"
                            >
                                <TableCell
                                    colspan="6"
                                    class="py-12 text-center font-bold text-slate-400"
                                >
                                    Tidak ada data tim terdaftar yang ditemukan.
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-for="team in filteredTeams"
                                :key="team.id_tim"
                                class="border-b border-slate-100 transition-colors hover:bg-slate-50/30"
                            >
                                <!-- Team / Univ -->
                                <TableCell class="space-y-1 px-6 py-4">
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
                                </TableCell>

                                <!-- Ketua Tim -->
                                <TableCell
                                    class="px-6 py-4 font-semibold text-slate-600"
                                >
                                    {{ getKetuaName(team.members) }}
                                </TableCell>

                                <!-- Batch -->
                                <TableCell class="px-6 py-4">
                                    <span
                                        class="inline-block rounded-lg bg-slate-100 px-2.5 py-1 text-[10px] font-black tracking-wider text-slate-600 uppercase"
                                    >
                                        Batch {{ team.batch || 1 }}
                                    </span>
                                </TableCell>

                                <!-- Verification Date -->
                                <TableCell
                                    class="px-6 py-4 font-semibold text-slate-500"
                                >
                                    {{
                                        team.pembayaran
                                            ? formatDate(
                                                  team.pembayaran.uploaded_at,
                                              )
                                            : '-'
                                    }}
                                </TableCell>

                                <!-- Selection Status -->
                                <TableCell class="px-6 py-4 text-center">
                                    <Badge
                                        variant="outline"
                                        class="inline-block rounded-full border px-3 py-1 text-[9px] font-black tracking-wider uppercase shadow-sm"
                                        :class="[
                                            team.status_seleksi === 'final'
                                                ? 'border-green-200 bg-green-50 text-green-600 hover:bg-green-50'
                                                : team.status_seleksi ===
                                                    'tidak_lolos_final'
                                                  ? 'border-red-200 bg-red-50 text-red-600 hover:bg-red-50'
                                                  : 'border-blue-200 bg-blue-50 text-blue-600 hover:bg-blue-50',
                                        ]"
                                    >
                                        {{
                                            mapStatusSeleksi(
                                                team.status_seleksi,
                                            )
                                        }}
                                    </Badge>
                                </TableCell>

                                <!-- Action (Details Modal) -->
                                <TableCell class="px-6 py-4 text-center">
                                    <Button
                                        size="sm"
                                        @click="openTeamDetails(team)"
                                        class="inline-flex items-center space-x-1 rounded-lg bg-blue-950 px-3 py-1.5 text-[10px] font-black tracking-wider text-white uppercase shadow-sm transition hover:bg-blue-900"
                                    >
                                        <Info class="h-3.5 w-3.5" />
                                        <span>Detail Anggota</span>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>

        <!-- Custom Details Modal (Centralized glassmorphic look) -->
        <Dialog
            v-model:open="isDetailModalOpen"
            @update:open="(val) => !val && closeTeamDetails()"
        >
            <DialogContent
                v-if="selectedTeamDetails"
                class="max-w-2xl overflow-hidden rounded-3xl border-none p-0 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)]"
            >
                <!-- Header -->
                <DialogHeader
                    class="flex flex-row items-center justify-between space-y-0 border-b border-slate-100 p-6"
                >
                    <div class="space-y-1">
                        <DialogTitle
                            class="text-lg leading-tight font-black text-slate-900"
                        >
                            Detail Anggota Tim
                        </DialogTitle>
                        <p
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            {{ selectedTeamDetails.nama_tim }} -
                            {{ selectedTeamDetails.universitas }}
                        </p>
                    </div>
                </DialogHeader>

                <!-- Body -->
                <div class="space-y-6 p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div
                            v-for="(
                                member, index
                            ) in selectedTeamDetails.sortedMembers"
                            :key="member.id_member"
                            class="space-y-3 rounded-2xl border border-slate-100 bg-slate-50 p-5"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[9px] font-black tracking-wider text-slate-400 uppercase"
                                >
                                    {{
                                        member.role === 'ketua'
                                            ? 'Ketua Kelompok'
                                            : `Anggota ${index}`
                                    }}
                                </span>
                                <span
                                    class="rounded border px-2 py-0.5 text-[8px] font-black tracking-wider uppercase"
                                    :class="
                                        member.role === 'ketua'
                                            ? 'border-blue-900 bg-blue-900 text-white'
                                            : 'border-slate-200 bg-slate-100 text-slate-600'
                                    "
                                >
                                    {{ member.role }}
                                </span>
                            </div>
                            <div class="space-y-1.5">
                                <div class="space-y-0.5">
                                    <span
                                        class="text-[9px] font-bold tracking-wider text-slate-400 uppercase"
                                        >Nama Lengkap</span
                                    >
                                    <p
                                        class="text-xs font-extrabold text-slate-800"
                                    >
                                        {{ member.nama_peserta }}
                                    </p>
                                </div>
                                <div class="space-y-0.5">
                                    <span
                                        class="text-[9px] font-bold tracking-wider text-slate-400 uppercase"
                                        >NIM / Identitas</span
                                    >
                                    <p
                                        class="text-xs font-extrabold text-slate-800"
                                    >
                                        {{ member.nim_peserta }}
                                    </p>
                                </div>
                                <div class="space-y-0.5">
                                    <span
                                        class="text-[9px] font-bold tracking-wider text-slate-400 uppercase"
                                        >Jurusan</span
                                    >
                                    <p
                                        class="text-xs font-extrabold text-slate-800"
                                    >
                                        {{ member.jurusan || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end border-t border-slate-100 p-6">
                    <Button
                        @click="closeTeamDetails"
                        class="rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800"
                    >
                        Tutup Detail
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
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
