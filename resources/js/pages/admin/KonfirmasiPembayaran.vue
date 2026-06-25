<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    CreditCard,
    Check,
    X,
    ExternalLink,
    FileText,
    Search,
    Users,
    AlertCircle,
    Info,
} from '@lucide/vue';
import { ref, computed } from 'vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';

const props = defineProps({
    teams: Array,
});

const searchQuery = ref('');
const statusFilter = ref('all');
const isRejectModalOpen = ref(false);
const selectedPembayaranId = ref(null);

const rejectionForm = useForm({
    status: 'ditolak',
    catatan: '',
});

// Filter and sort teams by status and date
const filteredTeams = computed(() => {
    if (!props.teams) {
        return [];
    }

    const result = props.teams.filter((team) => {
        const query = searchQuery.value.toLowerCase();
        const matchesQuery = team.nama_tim.toLowerCase().includes(query) ||
                             team.universitas.toLowerCase().includes(query);

        if (!matchesQuery) return false;

        if (statusFilter.value !== 'all') {
            if (statusFilter.value === 'belum') {
                return !team.pembayaran;
            }
            return team.pembayaran && team.pembayaran.status_pembayaran === statusFilter.value;
        }

        return true;
    });

    result.sort((a, b) => {
        const statusA = a.pembayaran ? a.pembayaran.status_pembayaran : 'pending_upload';
        const statusB = b.pembayaran ? b.pembayaran.status_pembayaran : 'pending_upload';

        const weight = {
            'pending': 1,
            'pending_upload': 2,
            'ditolak': 3,
            'berhasil': 4
        };

        const diff = (weight[statusA] || 5) - (weight[statusB] || 5);
        if (diff !== 0) return diff;

        // Secondary sort: upload date (uploaded_at) descending
        const dateA = a.pembayaran ? new Date(a.pembayaran.uploaded_at).getTime() : 0;
        const dateB = b.pembayaran ? new Date(b.pembayaran.uploaded_at).getTime() : 0;
        return dateB - dateA;
    });

    return result;
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

const formatPrice = (batchNum) => {
    if (batchNum === 1) {
return 'Batch 1 (Rp 60.000)';
}

    if (batchNum === 2) {
return 'Batch 2 (Rp 80.000)';
}

    return 'Belum Ditentukan';
};

const isConfirmApproveModalOpen = ref(false);
const selectedApproveId = ref(null);

const openConfirmApproveModal = (id_pembayaran) => {
    selectedApproveId.value = id_pembayaran;
    isConfirmApproveModalOpen.value = true;
};

const closeConfirmApproveModal = () => {
    isConfirmApproveModalOpen.value = false;
};

const submitApproval = () => {
    router.post(
        route('admin.pembayaran.update', selectedApproveId.value),
        {
            status: 'berhasil',
        },
        {
            onSuccess: () => {
                isConfirmApproveModalOpen.value = false;
            },
        },
    );
};

const openRejectModal = (id_pembayaran) => {
    selectedPembayaranId.value = id_pembayaran;
    rejectionForm.catatan = '';
    rejectionForm.clearErrors();
    isRejectModalOpen.value = true;
};

const closeRejectModal = () => {
    isRejectModalOpen.value = false;
};

const submitRejection = () => {
    rejectionForm.post(
        route('admin.pembayaran.update', selectedPembayaranId.value),
        {
            onSuccess: () => {
                isRejectModalOpen.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Konfirmasi Pembayaran" />

    <CitechDashboardLayout activeMenu="admin.pembayaran" role="admin">
        <template #header-title>
            <h2
                class="font-sans text-lg font-black tracking-wide text-slate-800"
            >
                Konfirmasi Pembayaran
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
                        <CreditCard class="h-6 w-6 text-blue-900" />
                        <span>Verifikasi Bukti Pembayaran</span>
                    </h3>
                    <p
                        class="max-w-2xl text-xs leading-relaxed font-bold text-slate-500"
                    >
                        Halaman ini digunakan oleh administrator untuk meninjau,
                        menyetujui, atau menolak bukti transaksi pembayaran
                        biaya pendaftaran yang diunggah oleh peserta.
                    </p>
                </div>

                <!-- Search & Filter Status -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center w-full flex-shrink-0 md:w-auto">
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
                            placeholder="Cari tim atau universitas..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pr-4 pl-10 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-900 focus:outline-none"
                        />
                    </div>

                    <!-- Status Filter Dropdown -->
                    <div class="relative w-full sm:w-44">
                        <select
                            v-model="statusFilter"
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-3 pr-8 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-900 focus:outline-none cursor-pointer shadow-sm hover:border-slate-300 transition"
                        >
                            <option value="all">Semua Status</option>
                            <option value="belum">Belum Mengunggah</option>
                            <option value="pending">Pending</option>
                            <option value="ditolak">Ditolak / Gagal</option>
                            <option value="berhasil">Berhasil</option>
                        </select>
                    </div>
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
                                <th class="px-6 py-4">Tahap / Nominal</th>
                                <th class="px-6 py-4">Tanggal Upload</th>
                                <th class="px-6 py-4 text-center">
                                    Status Pembayaran
                                </th>
                                <th class="px-6 py-4 text-center">
                                    Tinjau Bukti
                                </th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 text-xs font-bold text-slate-700"
                        >
                            <tr v-if="filteredTeams.length === 0">
                                <td
                                    colspan="6"
                                    class="py-12 text-center font-bold text-slate-400"
                                >
                                    Tidak ada data pembayaran tim yang cocok.
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

                                <!-- Batch details -->
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-600">{{
                                        formatPrice(team.batch || 1)
                                    }}</span>
                                </td>

                                <!-- Upload Date -->
                                <td class="px-6 py-4 text-slate-500">
                                    {{
                                        team.pembayaran
                                            ? formatDate(
                                                  team.pembayaran.uploaded_at,
                                              )
                                            : '-'
                                    }}
                                </td>

                                <!-- Status Badge -->
                                <td class="px-6 py-4 text-center">
                                    <div
                                        class="inline-flex flex-col items-center space-y-1"
                                    >
                                        <span
                                            class="inline-block rounded-full border px-3 py-1 text-[9px] font-black tracking-wider uppercase shadow-sm"
                                            :class="[
                                                !team.pembayaran
                                                    ? 'border-slate-200 bg-slate-50 text-slate-500'
                                                    : team.pembayaran
                                                            .status_pembayaran ===
                                                        'pending'
                                                      ? 'border-amber-200 bg-amber-50 text-amber-600'
                                                      : team.pembayaran
                                                              .status_pembayaran ===
                                                          'berhasil'
                                                        ? 'border-green-200 bg-green-50 text-green-600'
                                                        : 'border-red-200 bg-red-50 text-red-600',
                                            ]"
                                        >
                                            {{
                                                !team.pembayaran
                                                    ? 'Belum Mengunggah'
                                                    : team.pembayaran
                                                          .status_pembayaran
                                            }}
                                        </span>

                                        <!-- Catatan Penolakan (Jika ada) -->
                                        <span
                                            v-if="
                                                team.pembayaran &&
                                                team.pembayaran
                                                    .status_pembayaran ===
                                                    'ditolak'
                                            "
                                            class="block max-w-[150px] truncate text-[9px] text-red-500"
                                            :title="
                                                team.pembayaran
                                                    .catatan_pembayaran
                                            "
                                        >
                                            Ket:
                                            {{
                                                team.pembayaran
                                                    .catatan_pembayaran || '-'
                                            }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Berkas File link -->
                                <td class="px-6 py-4 text-center">
                                    <a
                                        v-if="team.pembayaran"
                                        :href="
                                            '/storage/' +
                                            team.pembayaran.bukti_pembayaran
                                        "
                                        target="_blank"
                                        class="inline-flex items-center space-x-1 rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-[10px] font-black tracking-wider text-blue-600 uppercase transition hover:bg-blue-100"
                                    >
                                        <FileText class="h-3.5 w-3.5" />
                                        <span>Buka Bukti</span>
                                        <ExternalLink class="h-2.5 w-2.5" />
                                    </a>
                                    <span
                                        v-else
                                        class="font-bold text-slate-400"
                                        >-</span
                                    >
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-center">
                                    <div
                                        v-if="
                                            team.pembayaran &&
                                            team.pembayaran
                                                .status_pembayaran === 'pending'
                                        "
                                        class="flex items-center justify-center space-x-2"
                                    >
                                        <button
                                            @click="
                                                openConfirmApproveModal(
                                                    team.pembayaran
                                                        .id_pembayaran,
                                                )
                                            "
                                            class="animate-scale-up rounded-xl border border-green-100 bg-green-50 p-2 text-green-600 shadow-sm transition hover:bg-green-100"
                                            title="Setujui Pembayaran"
                                        >
                                            <Check class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="
                                                openRejectModal(
                                                    team.pembayaran
                                                        .id_pembayaran,
                                                )
                                            "
                                            class="animate-scale-up rounded-xl border border-red-100 bg-red-50 p-2 text-red-600 shadow-sm transition hover:bg-red-100"
                                            title="Tolak Pembayaran"
                                        >
                                            <X class="h-4 w-4" />
                                        </button>
                                    </div>
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

        <!-- Custom Confirm Approve Modal -->
        <div
            v-if="isConfirmApproveModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div
                class="flex min-h-full items-center justify-center p-4 text-center"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-slate-900/5 backdrop-blur-md transition-opacity"
                    @click="closeConfirmApproveModal"
                ></div>

                <!-- Modal Content Wrapper -->
                <div
                    class="animate-fade-in-up relative z-10 my-8 inline-block w-full max-w-md transform overflow-hidden rounded-3xl border border-slate-100 bg-white text-left align-middle shadow-[0_25px_50px_-12px_rgba(0,0,0,0.15)] transition-all"
                >
                    <div class="space-y-6 p-6">
                        <!-- Icon & Title -->
                        <div class="flex items-start space-x-4">
                            <div
                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-green-50 text-green-600"
                            >
                                <Check class="h-5 w-5" />
                            </div>
                            <div class="min-w-0 space-y-1">
                                <h3 class="text-base font-black text-slate-900">
                                    Setujui Pembayaran Peserta?
                                </h3>
                                <p
                                    class="text-xs leading-relaxed font-bold text-slate-500"
                                >
                                    Apakah Anda yakin ingin menyetujui bukti
                                    pembayaran pendaftaran tim ini? Setelah
                                    disetujui, status seleksi tim ini otomatis
                                    akan berubah menjadi babak penyisihan.
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3">
                            <button
                                type="button"
                                @click="closeConfirmApproveModal"
                                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                @click="submitApproval"
                                class="rounded-xl bg-green-600 px-6 py-2.5 text-xs font-black text-white shadow-md transition hover:bg-green-700"
                            >
                                Ya, Setujui
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Reject Modal -->
        <div
            v-if="isRejectModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div
                class="flex min-h-full items-center justify-center p-4 text-center"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-slate-900/5 backdrop-blur-md transition-opacity"
                    @click="closeRejectModal"
                ></div>

                <!-- Modal Content Wrapper -->
                <div
                    class="animate-fade-in-up relative z-10 my-8 inline-block w-full max-w-lg transform overflow-hidden rounded-3xl border border-slate-100 bg-white text-left align-middle shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] transition-all"
                >
                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-slate-100 p-6"
                    >
                        <div class="space-y-1">
                            <h3
                                class="text-base leading-tight font-black text-slate-900"
                            >
                                Tolak Bukti Pembayaran
                            </h3>
                            <p
                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Silakan tuliskan alasan penolakan secara jelas.
                            </p>
                        </div>
                        <button
                            @click="closeRejectModal"
                            class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Body / Form -->
                    <form
                        @submit.prevent="submitRejection"
                        class="space-y-5 p-6 text-left"
                    >
                        <!-- Textarea -->
                        <div class="space-y-2">
                            <label
                                class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                >Catatan / Alasan Penolakan</label
                            >
                            <textarea
                                v-model="rejectionForm.catatan"
                                rows="4"
                                placeholder="Contoh: Bukti transfer terpotong atau nominal tidak sesuai..."
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-900 focus:outline-none"
                            ></textarea>
                            <div
                                v-if="rejectionForm.errors.catatan"
                                class="mt-1 flex items-center space-x-1 text-red-500"
                            >
                                <AlertCircle
                                    class="h-3.5 w-3.5 flex-shrink-0"
                                />
                                <span class="text-[10px] font-bold">{{
                                    rejectionForm.errors.catatan
                                }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div
                            class="flex items-center justify-end space-x-3 pt-2"
                        >
                            <button
                                type="button"
                                @click="closeRejectModal"
                                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="
                                    rejectionForm.processing ||
                                    !rejectionForm.catatan.trim()
                                "
                                class="rounded-xl bg-red-600 px-6 py-2.5 text-xs font-black text-white shadow-md transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{
                                    rejectionForm.processing
                                        ? 'Memproses...'
                                        : 'Tolak Bukti Pembayaran'
                                }}
                            </button>
                        </div>
                    </form>
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
