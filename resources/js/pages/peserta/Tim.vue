<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import {
    Users,
    Info,
    ShieldAlert,
    Pencil,
    Save,
    ArrowLeft,
    Trash2,
    UploadCloud,
    X,
    FileText,
    AlertCircle,
    CheckCircle2,
} from '@lucide/vue';
import { ref, computed } from 'vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
    userTeam: Object,
    teamMembers: Array,
    isSubmissionOpen: Boolean,
});

const isUploadModalOpen = ref(false);
const fileInput = ref(null);
const selectedFileName = ref('');

const uploadForm = useForm({
    file_dokumen: null,
});

const openUploadModal = () => {
    selectedFileName.value = '';
    uploadForm.clearErrors();
    uploadForm.reset();
    isUploadModalOpen.value = true;
};

const closeUploadModal = () => {
    isUploadModalOpen.value = false;
};

const handleFileChange = (e) => {
    const file = e.target.files[0];

    if (file) {
        uploadForm.file_dokumen = file;
        selectedFileName.value = file.name;
    }
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const submitDocument = () => {
    uploadForm.post(route('peserta.tim.dokumen.store'), {
        onSuccess: () => {
            isUploadModalOpen.value = false;
        },
    });
};

const isConfirmCancelModalOpen = ref(false);

const openConfirmCancelModal = () => {
    isConfirmCancelModalOpen.value = true;
};

const closeConfirmCancelModal = () => {
    isConfirmCancelModalOpen.value = false;
};

const confirmCancelDocument = () => {
    router.delete(route('peserta.tim.dokumen.destroy'), {
        onSuccess: () => {
            isConfirmCancelModalOpen.value = false;
        },
    });
};

// Payment refs and methods
const isPaymentUploadModalOpen = ref(false);
const paymentFileInput = ref(null);
const selectedPaymentFileName = ref('');

const paymentForm = useForm({
    bukti_pembayaran: null,
});

const openPaymentUploadModal = () => {
    selectedPaymentFileName.value = '';
    paymentForm.clearErrors();
    paymentForm.reset();
    isPaymentUploadModalOpen.value = true;
};

const closePaymentUploadModal = () => {
    isPaymentUploadModalOpen.value = false;
};

const handlePaymentFileChange = (e) => {
    const file = e.target.files[0];

    if (file) {
        paymentForm.bukti_pembayaran = file;
        selectedPaymentFileName.value = file.name;
    }
};

const triggerPaymentFileInput = () => {
    paymentFileInput.value.click();
};

const submitPayment = () => {
    paymentForm.post(route('peserta.tim.pembayaran.store'), {
        onSuccess: () => {
            isPaymentUploadModalOpen.value = false;
            // Open WA redirect automatically
            const waNumber = '6282264890113'; // WhatsApp Panitia
            const teamName = props.userTeam?.nama_tim || '';
            const university = props.userTeam?.universitas || '';
            const waMessage = `Halo Panitia CITECH 2026, saya perwakilan dari tim ${teamName} dari ${university} ingin mengonfirmasi bahwa kami telah melakukan pembayaran pendaftaran dan mengunggah bukti pembayaran di website. Mohon untuk diverifikasi. Terima kasih!`;
            window.open(
                `https://wa.me/${waNumber}?text=${encodeURIComponent(waMessage)}`,
                '_blank',
            );
        },
    });
};

const isConfirmCancelPaymentModalOpen = ref(false);

const openConfirmCancelPaymentModal = () => {
    isConfirmCancelPaymentModalOpen.value = true;
};

const closeConfirmCancelPaymentModal = () => {
    isConfirmCancelPaymentModalOpen.value = false;
};

const confirmCancelPayment = () => {
    router.delete(route('peserta.tim.pembayaran.destroy'), {
        onSuccess: () => {
            isConfirmCancelPaymentModalOpen.value = false;
        },
    });
};

// Proposal submission refs and methods
const isSubmissionModalOpen = ref(false);
const isConfirmSubmissionModalOpen = ref(false);

const submissionForm = useForm({
    link_file_submission: '',
});

const openSubmissionModal = () => {
    submissionForm.clearErrors();
    submissionForm.reset();
    isSubmissionModalOpen.value = true;
};

const closeSubmissionModal = () => {
    isSubmissionModalOpen.value = false;
};

const triggerConfirmSubmission = () => {
    submissionForm.clearErrors();

    if (!submissionForm.link_file_submission) {
        submissionForm.setError(
            'link_file_submission',
            'Link Google Drive wajib diisi.',
        );

        return;
    }

    if (
        !submissionForm.link_file_submission.startsWith('http://') &&
        !submissionForm.link_file_submission.startsWith('https://')
    ) {
        submissionForm.setError(
            'link_file_submission',
            'Format link harus berupa URL valid (harus diawali http:// atau https://).',
        );

        return;
    }

    isConfirmSubmissionModalOpen.value = true;
};

const closeConfirmSubmissionModal = () => {
    isConfirmSubmissionModalOpen.value = false;
};

const submitProposal = () => {
    submissionForm.post(route('peserta.tim.submission.store'), {
        onSuccess: () => {
            isConfirmSubmissionModalOpen.value = false;
            isSubmissionModalOpen.value = false;
        },
    });
};

const page = usePage();
const user = computed(() => page.props.auth.user);

const isCreatingTeam = ref(false);
const isEditing = ref(false);

const form = useForm({
    nama_tim: '',
    universitas: '',
    nim_ketua: '',
    jurusan_ketua: '',
    nama_anggota1: '',
    nim_anggota1: '',
    jurusan_anggota1: '',
    nama_anggota2: '',
    nim_anggota2: '',
    jurusan_anggota2: '',
});

// Sort members to put 'ketua' first
const sortedMembers = computed(() => {
    if (!props.teamMembers) {
return [];
}

    return [...props.teamMembers].sort((a, b) => {
        if (a.role === 'ketua') {
return -1;
}

        if (b.role === 'ketua') {
return 1;
}

        return a.id_member - b.id_member;
    });
});

const mapStatus = (status) => {
    const mapping = {
        belum_seleksi: 'Belum Seleksi',
        penyisihan: 'Babak Penyisihan',
        tidak_lolos_final: 'Tidak Lolos Final',
        final: 'Babak Final',
    };

    return mapping[status] || status;
};

const startCreate = () => {
    form.reset();
    isEditing.value = false;
    isCreatingTeam.value = true;
};

const startEdit = () => {
    form.reset();
    form.nama_tim = props.userTeam.nama_tim;
    form.universitas = props.userTeam.universitas;

    const ketua = props.teamMembers.find((m) => m.role === 'ketua');

    if (ketua) {
        form.nim_ketua = ketua.nim_peserta;
        form.jurusan_ketua = ketua.jurusan || '';
    }

    const anggota = props.teamMembers.filter((m) => m.role === 'anggota');

    if (anggota[0]) {
        form.nama_anggota1 = anggota[0].nama_peserta;
        form.nim_anggota1 = anggota[0].nim_peserta;
        form.jurusan_anggota1 = anggota[0].jurusan || '';
    }

    if (anggota[1]) {
        form.nama_anggota2 = anggota[1].nama_peserta;
        form.nim_anggota2 = anggota[1].nim_peserta;
        form.jurusan_anggota2 = anggota[1].jurusan || '';
    }

    isEditing.value = true;
    isCreatingTeam.value = true;
};

const cancelAction = () => {
    form.reset();
    isCreatingTeam.value = false;
    isEditing.value = false;
};

const submit = () => {
    form.post(route('peserta.tim.store'), {
        onSuccess: () => {
            isCreatingTeam.value = false;
            isEditing.value = false;
        },
    });
};

const clearKetua = () => {
    form.nim_ketua = '';
    form.jurusan_ketua = '';
};

const clearAnggota1 = () => {
    form.nama_anggota1 = '';
    form.nim_anggota1 = '';
    form.jurusan_anggota1 = '';
};

const clearAnggota2 = () => {
    form.nama_anggota2 = '';
    form.nim_anggota2 = '';
    form.jurusan_anggota2 = '';
};
</script>

<template>
    <Head title="Manajemen Tim - CITECH 2026" />

    <CitechDashboardLayout activeMenu="peserta.tim" role="peserta">
        <template #header-title>
            <h2 class="text-lg font-black tracking-wide text-slate-800">
                Manajemen Tim
            </h2>
        </template>

        <div class="animate-fade-in-up space-y-8">
            <!-- 1. FORM CREATE / EDIT TIM -->
            <div v-if="isCreatingTeam || !userTeam" class="space-y-6">
                <!-- If not currently in form view and userTeam is empty, show empty state first -->
                <div
                    v-if="!isCreatingTeam && !userTeam"
                    class="mx-auto max-w-md space-y-6 rounded-3xl border border-slate-100 bg-white p-8 py-16 text-center shadow-[0_10px_35px_rgba(0,0,0,0.03)]"
                >
                    <div
                        class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-blue-50 text-blue-600"
                    >
                        <Users class="h-10 w-10" />
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-xl font-black text-slate-900">
                            Belum Ada Data Tim
                        </h4>
                        <p
                            class="text-xs leading-relaxed font-bold text-slate-400"
                        >
                            Anda belum mendaftarkan kelompok tim Anda. Silakan
                            isi formulir pendaftaran tim baru untuk dapat
                            berpartisipasi.
                        </p>
                    </div>
                    <button
                        @click="startCreate"
                        class="rounded-xl bg-[#1e4d8c] px-8 py-3.5 text-xs font-extrabold tracking-wider text-white uppercase shadow-md transition hover:bg-[#153a6b] hover:shadow-lg hover:shadow-blue-500/10"
                    >
                        Buat Tim Baru
                    </button>
                </div>

                <!-- Create/Edit Form UI -->
                <div v-else class="mx-auto max-w-3xl space-y-6">
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-2xl font-black tracking-tight text-slate-900"
                        >
                            {{ isEditing ? 'Edit Data Tim' : 'Buat Data Tim' }}
                        </h3>
                        <button
                            @click="cancelAction"
                            class="inline-flex items-center space-x-1 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-slate-200"
                        >
                            <ArrowLeft class="h-3.5 w-3.5" />
                            <span>Kembali</span>
                        </button>
                    </div>

                    <div
                        class="space-y-8 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8"
                    >
                        <!-- Data Tim Section -->
                        <div class="space-y-4">
                            <h4
                                class="border-b border-slate-100 pb-2 text-sm font-black tracking-wider text-slate-800 uppercase"
                            >
                                Data Tim
                            </h4>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                        >Nama Tim</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.nama_tim"
                                        placeholder="Masukkan nama tim"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                                    />
                                    <div
                                        v-if="form.errors.nama_tim"
                                        class="mt-1 text-xs font-bold text-red-500"
                                    >
                                        {{ form.errors.nama_tim }}
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                        >Universitas Asal</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.universitas"
                                        placeholder="Masukkan universitas asal tim"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                                    />
                                    <div
                                        v-if="form.errors.universitas"
                                        class="mt-1 text-xs font-bold text-red-500"
                                    >
                                        {{ form.errors.universitas }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Ketua Section (Automatically creator, no Name edit) -->
                        <div
                            class="space-y-4 border-t border-slate-100/80 pt-4"
                        >
                            <div class="flex items-center justify-between">
                                <h4
                                    class="text-sm font-black tracking-wider text-slate-800 uppercase"
                                >
                                    Data Ketua (Otomatis Anda)
                                </h4>
                                <button
                                    type="button"
                                    @click="clearKetua"
                                    class="inline-flex items-center space-x-1.5 rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-[10px] font-extrabold tracking-wide text-red-600 uppercase transition hover:bg-red-100"
                                >
                                    <Trash2 class="h-3 w-3" />
                                    <span>Hapus Data</span>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                        >Nama Ketua</label
                                    >
                                    <input
                                        type="text"
                                        :value="user.name"
                                        disabled
                                        class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-500"
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                        >NIM</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.nim_ketua"
                                        placeholder="Masukkan NIM ketua"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                                    />
                                    <div
                                        v-if="form.errors.nim_ketua"
                                        class="mt-1 text-xs font-bold text-red-500"
                                    >
                                        {{ form.errors.nim_ketua }}
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                        >Jurusan</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.jurusan_ketua"
                                        placeholder="Masukkan jurusan ketua"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                                    />
                                    <div
                                        v-if="form.errors.jurusan_ketua"
                                        class="mt-1 text-xs font-bold text-red-500"
                                    >
                                        {{ form.errors.jurusan_ketua }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Anggota 1 Section (Required) -->
                        <div
                            class="space-y-4 border-t border-slate-100/80 pt-4"
                        >
                            <div class="flex items-center justify-between">
                                <h4
                                    class="text-sm font-black tracking-wider text-slate-800 uppercase"
                                >
                                    Data Anggota 1 (Wajib)
                                </h4>
                                <button
                                    type="button"
                                    @click="clearAnggota1"
                                    class="inline-flex items-center space-x-1.5 rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-[10px] font-extrabold tracking-wide text-red-600 uppercase transition hover:bg-red-100"
                                >
                                    <Trash2 class="h-3 w-3" />
                                    <span>Hapus Data</span>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                        >Nama Anggota 1</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.nama_anggota1"
                                        placeholder="Masukkan nama anggota 1"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                                    />
                                    <div
                                        v-if="form.errors.nama_anggota1"
                                        class="mt-1 text-xs font-bold text-red-500"
                                    >
                                        {{ form.errors.nama_anggota1 }}
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                        >NIM</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.nim_anggota1"
                                        placeholder="Masukkan NIM anggota 1"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                                    />
                                    <div
                                        v-if="form.errors.nim_anggota1"
                                        class="mt-1 text-xs font-bold text-red-500"
                                    >
                                        {{ form.errors.nim_anggota1 }}
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                        >Jurusan</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.jurusan_anggota1"
                                        placeholder="Masukkan jurusan anggota 1"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                                    />
                                    <div
                                        v-if="form.errors.jurusan_anggota1"
                                        class="mt-1 text-xs font-bold text-red-500"
                                    >
                                        {{ form.errors.jurusan_anggota1 }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Anggota 2 Section (Optional) -->
                        <div
                            class="space-y-4 border-t border-slate-100/80 pt-4"
                        >
                            <div class="flex items-center justify-between">
                                <h4
                                    class="text-sm font-black tracking-wider text-slate-800 uppercase"
                                >
                                    Data Anggota 2 (Opsional)
                                </h4>
                                <button
                                    type="button"
                                    @click="clearAnggota2"
                                    class="inline-flex items-center space-x-1.5 rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-[10px] font-extrabold tracking-wide text-red-600 uppercase transition hover:bg-red-100"
                                >
                                    <Trash2 class="h-3 w-3" />
                                    <span>Hapus Data</span>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                        >Nama Anggota 2</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.nama_anggota2"
                                        placeholder="Masukkan nama anggota 2"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                                    />
                                    <div
                                        v-if="form.errors.nama_anggota2"
                                        class="mt-1 text-xs font-bold text-red-500"
                                    >
                                        {{ form.errors.nama_anggota2 }}
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                        >NIM</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.nim_anggota2"
                                        placeholder="Masukkan NIM anggota 2"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                                    />
                                    <div
                                        v-if="form.errors.nim_anggota2"
                                        class="mt-1 text-xs font-bold text-red-500"
                                    >
                                        {{ form.errors.nim_anggota2 }}
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label
                                        class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                        >Jurusan</label
                                    >
                                    <input
                                        type="text"
                                        v-model="form.jurusan_anggota2"
                                        placeholder="Masukkan jurusan anggota 2"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                                    />
                                    <div
                                        v-if="form.errors.jurusan_anggota2"
                                        class="mt-1 text-xs font-bold text-red-500"
                                    >
                                        {{ form.errors.jurusan_anggota2 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-3">
                        <button
                            type="button"
                            @click="cancelAction"
                            class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="submit"
                            :disabled="form.processing"
                            class="flex items-center space-x-2 rounded-xl bg-[#1b2a4a] px-8 py-3 text-xs font-bold text-white shadow-md transition hover:bg-[#15233d]"
                        >
                            <Save class="h-3.5 w-3.5" />
                            <span>{{
                                form.processing ? 'Menyimpan...' : 'Simpan'
                            }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. DETAIL TIM VIEW (WHEN REGISTERED) -->
            <div v-else class="space-y-6">
                <!-- Header Title & Selection Status -->
                <div
                    class="flex flex-col gap-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="min-w-0 flex-1">
                        <h1
                            class="text-xl font-black tracking-tight break-words text-blue-900 md:text-2xl"
                        >
                            {{ userTeam.nama_tim }}
                        </h1>
                        <p
                            class="mt-1 block text-xs font-bold tracking-wider break-words text-slate-400 uppercase"
                        >
                            {{ userTeam.universitas }}
                        </p>
                    </div>
                    <div class="flex flex-shrink-0 items-center space-x-2.5">
                        <span
                            class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                            >Status Kelayakan:</span
                        >
                        <span
                            class="inline-block rounded-full border px-4 py-1.5 text-xs font-black tracking-wider uppercase shadow-sm"
                            :class="[
                                userTeam.status_seleksi === 'final'
                                    ? 'border-green-200 bg-green-50 text-green-700'
                                    : userTeam.status_seleksi ===
                                        'tidak_lolos_final'
                                      ? 'border-red-200 bg-red-50 text-red-700'
                                      : 'border-blue-100 bg-blue-50 text-blue-700',
                            ]"
                        >
                            {{ mapStatus(userTeam.status_seleksi) }}
                        </span>
                    </div>
                </div>

                <!-- Grid Details layout -->
                <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                    <!-- Left Column (Members Details) -->
                    <div class="space-y-6 lg:col-span-7">
                        <!-- Edit Button -->
                        <div class="flex">
                            <button
                                @click="
                                    userTeam.dokumen_registrasi
                                        ?.status_registrasi !== 'berhasil'
                                        ? startEdit()
                                        : null
                                "
                                :disabled="
                                    userTeam.dokumen_registrasi
                                        ?.status_registrasi === 'berhasil'
                                "
                                :class="[
                                    'inline-flex items-center space-x-2 rounded-xl px-5 py-2.5 text-xs font-black shadow-sm transition',
                                    userTeam.dokumen_registrasi
                                        ?.status_registrasi === 'berhasil'
                                        ? 'cursor-not-allowed border border-slate-300/50 bg-slate-200 text-slate-400 shadow-none'
                                        : 'bg-blue-900 text-white hover:scale-[1.02] hover:bg-blue-800',
                                ]"
                                :title="
                                    userTeam.dokumen_registrasi
                                        ?.status_registrasi === 'berhasil'
                                        ? 'Data tim tidak dapat diubah karena berkas persyaratan pendaftaran sudah disetujui'
                                        : ''
                                "
                            >
                                <Pencil class="h-3.5 w-3.5" />
                                <span>Edit Data Tim</span>
                            </button>
                        </div>

                        <!-- Card Members details -->
                        <div
                            class="space-y-6 divide-y divide-slate-100 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8"
                        >
                            <div
                                v-for="(member, idx) in sortedMembers"
                                :key="member.id_member"
                                class="space-y-3 pt-6 first:pt-0"
                            >
                                <div class="flex items-center justify-between">
                                    <h4
                                        class="text-xs font-black tracking-wider text-slate-400 uppercase"
                                    >
                                        {{
                                            member.role === 'ketua'
                                                ? 'Nama Ketua'
                                                : `Nama Anggota ${idx}`
                                        }}
                                    </h4>
                                    <span
                                        class="rounded border px-2 py-0.5 text-[9px] font-black tracking-wider uppercase shadow-sm"
                                        :class="[
                                            member.role === 'ketua'
                                                ? 'border-amber-200 bg-amber-50 text-amber-700'
                                                : 'border-slate-200 bg-slate-100 text-slate-600',
                                        ]"
                                    >
                                        {{ member.role }}
                                    </span>
                                </div>
                                <div
                                    class="grid grid-cols-1 gap-2 rounded-2xl border border-slate-100 bg-slate-50/50 p-4 sm:grid-cols-3"
                                >
                                    <div class="space-y-0.5">
                                        <span
                                            class="text-[9px] font-bold tracking-wider text-slate-400 uppercase"
                                            >Nama Lengkap</span
                                        >
                                        <p
                                            class="text-sm font-extrabold text-slate-800"
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
                                            class="text-sm font-extrabold text-slate-800"
                                        >
                                            {{ member.nim_peserta }}
                                        </p>
                                    </div>
                                    <div class="space-y-0.5">
                                        <span
                                            class="text-[9px] font-bold tracking-wider text-slate-400 uppercase"
                                            >Program Studi / Jurusan</span
                                        >
                                        <p
                                            class="text-sm font-extrabold text-slate-800"
                                        >
                                            {{ member.jurusan || '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (Requirements & Payment) -->
                    <div class="space-y-6 lg:col-span-5">
                        <!-- Card: Dokumen Persyaratan -->
                        <div
                            class="space-y-5 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8"
                        >
                            <h4
                                class="border-b border-slate-100 pb-3 text-center text-sm font-black tracking-wider text-slate-800 uppercase"
                            >
                                Persyaratan Pendaftaran
                            </h4>

                            <div
                                class="space-y-4 rounded-2xl border border-slate-100 bg-slate-50 p-5"
                            >
                                <ol
                                    class="list-inside list-decimal space-y-2 text-xs font-bold text-slate-600"
                                >
                                    <li>KTM atau Surat Aktif</li>
                                    <li>Twibbon</li>
                                    <li>Bukti Follow</li>
                                </ol>
                                <p
                                    class="block pt-2 text-center text-[10px] font-extrabold text-blue-600"
                                >
                                    * Kumpulkan menjadi 1 file dalam format PDF
                                </p>
                            </div>

                            <!-- State 1: No Upload / Null -->
                            <div
                                v-if="!userTeam.dokumen_registrasi"
                                class="space-y-3"
                            >
                                <button
                                    @click="openUploadModal"
                                    class="w-full rounded-xl bg-[#3769a6] py-3 text-xs font-black text-white shadow-sm transition hover:scale-[1.01] hover:bg-[#2b5487]"
                                >
                                    Upload Persyaratan
                                </button>
                            </div>

                            <!-- State 2: Pending -->
                            <div
                                v-else-if="
                                    userTeam.dokumen_registrasi
                                        .status_registrasi === 'pending'
                                "
                                class="space-y-4 pt-2 text-center"
                            >
                                <div class="space-y-1">
                                    <span
                                        class="block text-xs font-black tracking-widest text-slate-400 uppercase"
                                        >Status Berkas:</span
                                    >
                                    <h4
                                        class="text-2xl font-black tracking-wider text-amber-500 uppercase"
                                    >
                                        PENDING
                                    </h4>
                                </div>
                                <button
                                    @click="openConfirmCancelModal"
                                    class="w-full rounded-xl bg-red-600 py-3 text-xs font-black text-white shadow-sm transition hover:scale-[1.01] hover:bg-red-700"
                                >
                                    Batalkan
                                </button>
                            </div>

                            <!-- State 3: Approved / Berhasil -->
                            <div
                                v-else-if="
                                    userTeam.dokumen_registrasi
                                        .status_registrasi === 'berhasil'
                                "
                                class="space-y-4 pt-2 text-center"
                            >
                                <div class="space-y-1">
                                    <span
                                        class="block text-xs font-black tracking-widest text-slate-400 uppercase"
                                        >Status Berkas:</span
                                    >
                                    <h4
                                        class="text-2xl font-black tracking-wider text-green-600 uppercase"
                                    >
                                        BERHASIL
                                    </h4>
                                </div>
                                <p
                                    class="mx-auto max-w-xs text-xs leading-relaxed font-bold text-slate-500"
                                >
                                    Bukti berhasil terkirim. Lakukan yang
                                    terbaik untuk di tahap selanjutnya, semoga
                                    berhasil!
                                </p>
                            </div>

                            <!-- State 4: Rejected / Ditolak -->
                            <div
                                v-else-if="
                                    userTeam.dokumen_registrasi
                                        .status_registrasi === 'ditolak'
                                "
                                class="space-y-4 pt-2 text-center"
                            >
                                <div class="space-y-1">
                                    <span
                                        class="block text-xs font-black tracking-widest text-slate-400 uppercase"
                                        >Status Berkas:</span
                                    >
                                    <h4
                                        class="text-2xl font-black tracking-wider text-red-600 uppercase"
                                    >
                                        DITOLAK
                                    </h4>
                                </div>

                                <!-- Catatan Admin -->
                                <div
                                    class="rounded-2xl border border-red-200/60 bg-red-50/80 p-4 text-left"
                                >
                                    <span
                                        class="block text-[9px] font-black tracking-wider text-red-600 uppercase"
                                        >Catatan Admin:</span
                                    >
                                    <p
                                        class="mt-1 text-xs leading-relaxed font-bold break-words text-red-800"
                                    >
                                        {{
                                            userTeam.dokumen_registrasi
                                                .catatan_registrasi ||
                                            'Tidak ada catatan alasan penolakan.'
                                        }}
                                    </p>
                                </div>

                                <button
                                    @click="openUploadModal"
                                    class="w-full rounded-xl bg-[#3769a6] py-3 text-xs font-black text-white shadow-sm transition hover:scale-[1.01] hover:bg-[#2b5487]"
                                >
                                    Ganti File
                                </button>
                            </div>
                        </div>

                        <!-- Card: Bukti Pembayaran -->
                        <div
                            class="space-y-5 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8"
                        >
                            <h4
                                class="border-b border-slate-100 pb-3 text-center text-sm font-black tracking-wider text-slate-800 uppercase"
                            >
                                Bukti Pembayaran
                            </h4>

                            <!-- State 1: No Payment Record Uploaded -->
                            <div v-if="!userTeam.pembayaran" class="space-y-5">
                                <div class="flex justify-center py-2">
                                    <img
                                        src="/assets/qris-citech.jpeg"
                                        alt="QRIS CITECH"
                                        class="h-auto w-72 rounded-2xl border border-slate-200 bg-white object-contain p-2 shadow-sm"
                                    />
                                </div>

                                <!-- Panduan Pembayaran (Sesuai Desain Foto) -->
                                <div
                                    class="space-y-3.5 rounded-2xl border border-slate-100/80 bg-slate-50/80 p-5 text-left"
                                >
                                    <h5
                                        class="flex items-center space-x-2 text-xs font-black text-slate-800"
                                    >
                                        <Info
                                            class="h-4 w-4 flex-shrink-0 text-blue-600"
                                        />
                                        <span>Panduan Alur Pembayaran:</span>
                                    </h5>
                                    <ol
                                        class="list-inside list-decimal space-y-2 text-[11px] font-bold text-slate-600"
                                    >
                                        <li>
                                            Peserta Membayar melalui QRIS yang
                                            telah disediakan
                                        </li>
                                        <li class="pl-0">
                                            Biaya Pendaftaran Peserta:
                                            <ul
                                                class="mt-1 list-inside list-disc space-y-0.5 pl-4 font-semibold text-slate-500"
                                            >
                                                <li>Batch 1: Rp 60.000</li>
                                                <li>Batch 2: Rp 80.000</li>
                                            </ul>
                                        </li>
                                        <li>
                                            Peserta Mengupload Bukti Pembayaran
                                            Melalui Button yang sudah disediakan
                                        </li>
                                        <li>
                                            Peserta Otomatis Mengirimkan Pesan
                                            Konfirmasi Ke Nomor Whatsapp Panitia
                                        </li>
                                        <li>
                                            Panitia Mengonfirmasi Pembayaran
                                            Peserta
                                        </li>
                                        <li>Pembayaran Berhasil</li>
                                    </ol>
                                    <p
                                        class="mt-2 text-center text-[10px] font-extrabold text-blue-600"
                                    >
                                        * Mohon pastikan nominal pembayaran
                                        sesuai dengan batch yang sedang
                                        berjalan.
                                    </p>
                                </div>

                                <button
                                    @click="openPaymentUploadModal"
                                    class="w-full rounded-xl bg-[#3769a6] py-3 text-xs font-black text-white shadow-sm transition hover:scale-[1.01] hover:bg-[#2b5487]"
                                >
                                    Upload Bukti Pembayaran
                                </button>
                            </div>

                            <!-- State 2: Payment Pending -->
                            <div
                                v-else-if="
                                    userTeam.pembayaran.status_pembayaran ===
                                    'pending'
                                "
                                class="space-y-4 pt-2 text-center"
                            >
                                <div class="space-y-1">
                                    <span
                                        class="block text-xs font-black tracking-widest text-slate-400 uppercase"
                                        >Status Pembayaran:</span
                                    >
                                    <h4
                                        class="text-2xl font-black tracking-wider text-amber-500 uppercase"
                                    >
                                        PENDING
                                    </h4>
                                </div>
                                <p
                                    class="px-4 text-xs leading-relaxed font-bold text-slate-500"
                                >
                                    Bukti pembayaran Anda sedang ditinjau oleh
                                    panitia. Mohon tunggu proses konfirmasi.
                                </p>
                                <button
                                    @click="openConfirmCancelPaymentModal"
                                    class="w-full rounded-xl bg-red-600 py-3 text-xs font-black text-white shadow-sm transition hover:scale-[1.01] hover:bg-red-700"
                                >
                                    Batalkan Unggahan
                                </button>
                            </div>

                            <!-- State 3: Payment Approved / Berhasil -->
                            <div
                                v-else-if="
                                    userTeam.pembayaran.status_pembayaran ===
                                    'berhasil'
                                "
                                class="space-y-4 pt-2 text-center"
                            >
                                <div class="space-y-1">
                                    <span
                                        class="block text-xs font-black tracking-widest text-slate-400 uppercase"
                                        >Status Pembayaran:</span
                                    >
                                    <h4
                                        class="text-2xl font-black tracking-wider text-green-600 uppercase"
                                    >
                                        BERHASIL
                                    </h4>
                                </div>
                                <p
                                    class="px-4 text-xs leading-relaxed font-bold text-slate-500"
                                >
                                    Selamat! Pembayaran pendaftaran tim Anda
                                    telah berhasil diverifikasi oleh panitia.
                                    Status tim Anda sekarang terdaftar untuk
                                    babak penyisihan.
                                </p>
                            </div>

                            <!-- State 4: Payment Rejected / Ditolak -->
                            <div
                                v-else-if="
                                    userTeam.pembayaran.status_pembayaran ===
                                    'ditolak'
                                "
                                class="space-y-4 pt-2 text-center"
                            >
                                <div class="space-y-1">
                                    <span
                                        class="block text-xs font-black tracking-widest text-slate-400 uppercase"
                                        >Status Pembayaran:</span
                                    >
                                    <h4
                                        class="text-2xl font-black tracking-wider text-red-600 uppercase"
                                    >
                                        DITOLAK
                                    </h4>
                                </div>

                                <!-- Catatan Admin -->
                                <div
                                    class="rounded-2xl border border-red-200/60 bg-red-50/80 p-4 text-left"
                                >
                                    <span
                                        class="block text-[9px] font-black tracking-wider text-red-600 uppercase"
                                        >Catatan Admin:</span
                                    >
                                    <p
                                        class="mt-1 text-xs leading-relaxed font-bold break-words text-red-800"
                                    >
                                        {{
                                            userTeam.pembayaran
                                                .catatan_pembayaran ||
                                            'Tidak ada catatan alasan penolakan.'
                                        }}
                                    </p>
                                </div>

                                <button
                                    @click="openPaymentUploadModal"
                                    class="w-full rounded-xl bg-[#3769a6] py-3 text-xs font-black text-white shadow-sm transition hover:scale-[1.01] hover:bg-[#2b5487]"
                                >
                                    Ganti Bukti Pembayaran
                                </button>
                            </div>
                        </div>

                        <!-- Card: Gabung Grup WhatsApp -->
                        <div
                            class="space-y-5 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8"
                        >
                            <h4
                                class="border-b border-slate-100 pb-3 text-center text-sm font-black tracking-wider text-slate-800 uppercase"
                            >
                                Grup Peserta
                            </h4>
                            <p
                                class="text-center text-xs leading-relaxed text-slate-500"
                            >
                                Silakan bergabung ke grup koordinasi resmi
                                peserta CITECH untuk mendapatkan pembaruan
                                informasi terkini.
                            </p>
                            <a
                                href="#"
                                class="block w-full rounded-xl bg-[#3769a6] py-3 text-center text-xs font-black text-white shadow-sm transition hover:scale-[1.01] hover:bg-[#2b5487]"
                            >
                                Gabung Grup Peserta
                            </a>
                        </div>

                        <!-- Card: Upload Proposal -->
                        <div
                            class="space-y-5 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8"
                        >
                            <h4
                                class="border-b border-slate-100 pb-3 text-center text-sm font-black tracking-wider text-slate-800 uppercase"
                            >
                                Proposal & Orisinalitas
                            </h4>

                            <!-- State 1: Already Submitted -->
                            <div
                                v-if="userTeam.submission"
                                class="space-y-4 pt-2 text-center"
                            >
                                <div class="space-y-1">
                                    <span
                                        class="block text-xs font-black tracking-widest text-slate-400 uppercase"
                                        >Status Pengumpulan:</span
                                    >
                                    <h4
                                        class="text-2xl font-black tracking-wider text-green-600 uppercase"
                                    >
                                        SUDAH DIKUMPULKAN
                                    </h4>
                                </div>
                                <p
                                    class="px-4 text-xs leading-relaxed font-bold text-slate-500"
                                >
                                    Proposal dan Surat Orisinalitas tim Anda
                                    telah berhasil dikumpulkan. Pengumpulan
                                    hanya dilakukan sekali dan tidak dapat
                                    dibatalkan atau diubah.
                                </p>
                                <!-- Display link drive -->
                                <div
                                    class="flex flex-col items-center space-y-2 rounded-2xl border border-slate-100 bg-slate-50 p-4"
                                >
                                    <span
                                        class="text-[9px] font-black tracking-wider text-slate-400 uppercase"
                                        >Link Google Drive Anda:</span
                                    >
                                    <a
                                        :href="
                                            userTeam.submission
                                                .link_file_submission
                                        "
                                        target="_blank"
                                        class="inline-flex items-center space-x-1.5 rounded-xl border border-blue-100/50 bg-blue-50/50 px-3 py-1.5 text-xs font-extrabold break-all text-blue-600 transition hover:bg-blue-50 hover:text-blue-800"
                                    >
                                        <FileText class="h-4 w-4" />
                                        <span>Buka Link Drive</span>
                                        <ExternalLink class="h-3 w-3" />
                                    </a>
                                </div>
                            </div>

                            <!-- State 2: Not Submitted & Deadline Not Over -->
                            <div v-else-if="isSubmissionOpen" class="space-y-4">
                                <p
                                    class="text-center text-xs leading-relaxed text-slate-500"
                                >
                                    Silakan kumpulkan proposal dan surat
                                    orisinalitas tim Anda dalam satu Link Google
                                    Drive. Harap pastikan link dapat diakses
                                    oleh panitia (Public).
                                </p>
                                <button
                                    @click="
                                        userTeam.status_seleksi === 'penyisihan'
                                            ? openSubmissionModal()
                                            : null
                                    "
                                    :disabled="
                                        userTeam.status_seleksi !== 'penyisihan'
                                    "
                                    :class="[
                                        'w-full rounded-xl py-3 text-xs font-black shadow-sm transition',
                                        userTeam.status_seleksi === 'penyisihan'
                                            ? 'bg-[#3769a6] text-white hover:scale-[1.01] hover:bg-[#2b5487]'
                                            : 'cursor-not-allowed border border-slate-300/50 bg-slate-200 text-slate-400 shadow-none',
                                    ]"
                                    :title="
                                        userTeam.status_seleksi !== 'penyisihan'
                                            ? 'Anda hanya dapat mengumpulkan proposal setelah verifikasi berkas dan pembayaran disetujui'
                                            : ''
                                    "
                                >
                                    Kumpulkan Link Proposal
                                </button>
                            </div>

                            <!-- State 3: Not Submitted & Deadline Over -->
                            <div v-else class="space-y-4 pt-2 text-center">
                                <div class="space-y-1">
                                    <span
                                        class="block text-xs font-black tracking-widest text-slate-400 uppercase"
                                        >Status Pengumpulan:</span
                                    >
                                    <h4
                                        class="text-2xl font-black tracking-wider text-red-600 uppercase"
                                    >
                                        BATAS WAKTU HABIS
                                    </h4>
                                </div>
                                <p
                                    class="px-4 text-xs leading-relaxed font-bold text-slate-500"
                                >
                                    Mohon maaf, batas waktu pengumpulan proposal
                                    dan surat orisinalitas (Batch 2) telah
                                    berakhir.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal Dialog -->
        <Dialog v-model:open="isUploadModalOpen" @update:open="(val) => !val && closeUploadModal()">
            <DialogContent class="rounded-3xl max-w-lg p-0 overflow-hidden border-none shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)]">
                <!-- Header -->
                <DialogHeader class="border-b border-slate-100 p-6 flex flex-row items-center justify-between space-y-0">
                    <div class="space-y-1 text-left">
                        <DialogTitle class="text-lg leading-tight font-black text-slate-900">
                            Unggah Berkas Persyaratan
                        </DialogTitle>
                        <p
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Format Berkas wajib PDF (Maksimal 5MB)
                        </p>
                    </div>
                </DialogHeader>

                <!-- Body / Upload Form -->
                <form
                    @submit.prevent="submitDocument"
                    class="space-y-6 p-6 text-left"
                >
                    <!-- Info Box -->
                    <div
                        class="flex items-start space-x-3 rounded-2xl border border-slate-100 bg-slate-50 p-4"
                    >
                        <Info
                            class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-600"
                        />
                        <div class="space-y-1">
                            <h5 class="text-xs font-black text-slate-800">
                                Berkas yang Harus Dikumpulkan:
                            </h5>
                            <ul
                                class="list-inside list-disc space-y-0.5 text-[11px] font-bold text-slate-500"
                            >
                                <li>
                                    KTM (Kartu Tanda Mahasiswa) / Surat
                                    Keterangan Aktif
                                </li>
                                <li>Screenshot Twibbon di media sosial</li>
                                <li>
                                    Screenshot Bukti Follow akun
                                    penyelenggara
                                </li>
                            </ul>
                            <p
                                class="mt-1.5 text-[10px] font-extrabold text-[#1e4d8c]"
                            >
                                * Ketiga berkas di atas wajib digabung
                                menjadi 1 file PDF.
                            </p>
                        </div>
                    </div>

                    <!-- Drop / Select Area -->
                    <div
                        @click="triggerFileInput"
                        class="flex cursor-pointer flex-col items-center justify-center space-y-3 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-8 transition hover:border-[#3769a6] hover:bg-blue-50/10"
                    >
                        <input
                            type="file"
                            ref="fileInput"
                            @change="handleFileChange"
                            accept=".pdf"
                            class="hidden"
                        />

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-[#3769a6]"
                        >
                            <UploadCloud class="h-6 w-6" />
                        </div>

                        <div class="space-y-1 text-center">
                            <p
                                class="text-xs font-extrabold text-slate-800"
                            >
                                {{
                                    selectedFileName
                                        ? 'Ganti File PDF terpilih'
                                        : 'Pilih Berkas PDF'
                                }}
                            </p>
                            <p class="text-[10px] font-bold text-slate-400">
                                Klik di sini untuk menjelajahi file
                            </p>
                        </div>
                    </div>

                    <!-- Display selected file -->
                    <div
                        v-if="selectedFileName"
                        class="flex items-center justify-between rounded-xl border border-blue-100/50 bg-blue-50/30 p-3.5"
                    >
                        <div class="flex min-w-0 items-center space-x-3">
                            <FileText
                                class="h-5 w-5 flex-shrink-0 text-[#3769a6]"
                            />
                            <span
                                class="truncate text-xs font-bold text-slate-700"
                                >{{ selectedFileName }}</span
                            >
                        </div>
                        <span
                            class="rounded border border-blue-100 bg-blue-50 px-2 py-0.5 text-[10px] font-extrabold text-blue-600"
                            >Terpilih</span
                        >
                    </div>

                    <!-- Errors -->
                    <div
                        v-if="uploadForm.errors.file_dokumen"
                        class="flex items-center space-x-2 rounded-xl border border-red-100 bg-red-50 p-3 text-red-600"
                    >
                        <AlertCircle class="h-4 w-4 flex-shrink-0" />
                        <span class="text-xs font-bold">{{
                            uploadForm.errors.file_dokumen
                        }}</span>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-end space-x-3 pt-2"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            @click="closeUploadModal"
                            class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            :disabled="
                                uploadForm.processing ||
                                !uploadForm.file_dokumen
                            "
                            class="flex items-center space-x-2 rounded-xl bg-[#1b2a4a] px-6 py-2.5 text-xs font-black text-white shadow-md transition hover:bg-[#15233d] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <UploadCloud class="h-3.5 w-3.5" />
                            <span>{{
                                uploadForm.processing
                                    ? 'Mengunggah...'
                                    : 'Kirim Persyaratan'
                            }}</span>
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Confirm Cancel Modal -->
        <AlertDialog v-model:open="isConfirmCancelModalOpen" @update:open="(val) => !val && closeConfirmCancelModal()">
            <AlertDialogContent class="rounded-3xl max-w-md p-6 border-none shadow-[0_25px_50px_-12px_rgba(0,0,0,0.15)]">
                <div class="flex items-start space-x-4">
                    <div
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600"
                    >
                        <AlertCircle class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 space-y-1">
                        <AlertDialogTitle class="text-base font-black text-slate-900">
                            Batalkan Unggahan Berkas?
                        </AlertDialogTitle>
                        <AlertDialogDescription class="text-xs leading-relaxed font-bold text-slate-500">
                            Apakah Anda yakin ingin membatalkan
                            pengiriman dokumen persyaratan ini? Tindakan
                            ini akan menghapus berkas PDF yang telah
                            diunggah secara permanen.
                        </AlertDialogDescription>
                    </div>
                </div>

                <AlertDialogFooter class="flex items-center justify-end space-x-3 mt-4">
                    <AlertDialogCancel
                        @click="closeConfirmCancelModal"
                        as-child
                    >
                        <Button variant="outline" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                            Tidak, Kembali
                        </Button>
                    </AlertDialogCancel>
                    <AlertDialogAction
                        @click="confirmCancelDocument"
                        as-child
                    >
                        <Button class="flex items-center space-x-1.5 rounded-xl bg-red-600 px-6 py-2.5 text-xs font-black text-white hover:bg-red-700">
                            <Trash2 class="h-3.5 w-3.5" />
                            <span>Ya, Batalkan</span>
                        </Button>
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Payment Upload Modal Dialog -->
        <Dialog v-model:open="isPaymentUploadModalOpen" @update:open="(val) => !val && closePaymentUploadModal()">
            <DialogContent class="rounded-3xl max-w-lg p-0 overflow-hidden border-none shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)]">
                <!-- Header -->
                <DialogHeader class="border-b border-slate-100 p-6 flex flex-row items-center justify-between space-y-0">
                    <div class="space-y-1 text-left">
                        <DialogTitle class="text-lg leading-tight font-black text-slate-900">
                            Unggah Bukti Pembayaran
                        </DialogTitle>
                        <p
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Format Berkas: JPG, PNG, atau PDF (Maksimal 5MB)
                        </p>
                    </div>
                </DialogHeader>

                <!-- Body / Upload Form -->
                <form
                    @submit.prevent="submitPayment"
                    class="space-y-6 p-6 text-left"
                >
                    <!-- Drop / Select Area -->
                    <div
                        @click="triggerPaymentFileInput"
                        class="flex cursor-pointer flex-col items-center justify-center space-y-3 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-8 transition hover:border-[#3769a6] hover:bg-blue-50/10"
                    >
                        <input
                            type="file"
                            ref="paymentFileInput"
                            @change="handlePaymentFileChange"
                            accept="image/*,.pdf"
                            class="hidden"
                        />

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-[#3769a6]"
                        >
                            <UploadCloud class="h-6 w-6" />
                        </div>

                        <div class="space-y-1 text-center">
                            <p
                                class="text-xs font-extrabold text-slate-800"
                            >
                                {{
                                    selectedPaymentFileName
                                        ? 'Ganti Bukti Pembayaran terpilih'
                                        : 'Pilih Bukti Pembayaran'
                                }}
                            </p>
                            <p class="text-[10px] font-bold text-slate-400">
                                Klik di sini untuk menjelajahi file
                            </p>
                        </div>
                    </div>

                    <!-- Display selected file -->
                    <div
                        v-if="selectedPaymentFileName"
                        class="flex items-center justify-between rounded-xl border border-blue-100/50 bg-blue-50/30 p-3.5"
                    >
                        <div class="flex min-w-0 items-center space-x-3">
                            <FileText
                                class="h-5 w-5 flex-shrink-0 text-[#3769a6]"
                            />
                            <span
                                class="truncate text-xs font-bold text-slate-700"
                                >{{ selectedPaymentFileName }}</span
                            >
                        </div>
                        <span
                            class="rounded border border-blue-100 bg-blue-50 px-2 py-0.5 text-[10px] font-extrabold text-blue-600"
                            >Terpilih</span
                        >
                    </div>

                    <!-- Errors -->
                    <div
                        v-if="paymentForm.errors.bukti_pembayaran"
                        class="flex items-center space-x-2 rounded-xl border border-red-100 bg-red-50 p-3 text-red-600"
                    >
                        <AlertCircle class="h-4 w-4 flex-shrink-0" />
                        <span class="text-xs font-bold">{{
                            paymentForm.errors.bukti_pembayaran
                        }}</span>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-end space-x-3 pt-2"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            @click="closePaymentUploadModal"
                            class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            :disabled="
                                paymentForm.processing ||
                                !paymentForm.bukti_pembayaran
                            "
                            class="flex items-center space-x-2 rounded-xl bg-[#1b2a4a] px-6 py-2.5 text-xs font-black text-white shadow-md transition hover:bg-[#15233d] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <UploadCloud class="h-3.5 w-3.5" />
                            <span>{{
                                paymentForm.processing
                                    ? 'Mengunggah...'
                                    : 'Kirim Bukti'
                            }}</span>
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Confirm Cancel Payment Modal -->
        <AlertDialog v-model:open="isConfirmCancelPaymentModalOpen" @update:open="(val) => !val && closeConfirmCancelPaymentModal()">
            <AlertDialogContent class="rounded-3xl max-w-md p-6 border-none shadow-[0_25px_50px_-12px_rgba(0,0,0,0.15)]">
                <div class="flex items-start space-x-4">
                    <div
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600"
                    >
                        <AlertCircle class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 space-y-1">
                        <AlertDialogTitle class="text-base font-black text-slate-900">
                            Batalkan Unggahan Pembayaran?
                        </AlertDialogTitle>
                        <AlertDialogDescription class="text-xs leading-relaxed font-bold text-slate-500">
                            Apakah Anda yakin ingin membatalkan
                            pengiriman bukti pembayaran ini? Tindakan
                            ini akan menghapus bukti transaksi yang
                            telah diunggah secara permanen.
                        </AlertDialogDescription>
                    </div>
                </div>

                <AlertDialogFooter class="flex items-center justify-end space-x-3 mt-4">
                    <AlertDialogCancel
                        @click="closeConfirmCancelPaymentModal"
                        as-child
                    >
                        <Button variant="outline" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                            Tidak, Kembali
                        </Button>
                    </AlertDialogCancel>
                    <AlertDialogAction
                        @click="confirmCancelPayment"
                        as-child
                    >
                        <Button class="flex items-center space-x-1.5 rounded-xl bg-red-600 px-6 py-2.5 text-xs font-black text-white hover:bg-red-700">
                            <Trash2 class="h-3.5 w-3.5" />
                            <span>Ya, Batalkan</span>
                        </Button>
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Proposal Submission Link drive Modal Dialog -->
        <Dialog v-model:open="isSubmissionModalOpen" @update:open="(val) => !val && closeSubmissionModal()">
            <DialogContent class="rounded-3xl max-w-lg p-0 overflow-hidden border-none shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)]">
                <!-- Header -->
                <DialogHeader class="border-b border-slate-100 p-6 flex flex-row items-center justify-between space-y-0">
                    <div class="space-y-1 text-left">
                        <DialogTitle class="text-lg leading-tight font-black text-slate-900">
                            Kumpulkan Link Proposal
                        </DialogTitle>
                        <p
                            class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Kumpulkan Proposal & Surat Orisinalitas (Google Drive Link)
                        </p>
                    </div>
                </DialogHeader>

                <!-- Body / Form -->
                <form
                    @submit.prevent="triggerConfirmSubmission"
                    class="space-y-5 p-6 text-left"
                >
                    <!-- Info Box -->
                    <div
                        class="flex items-start space-x-3 rounded-2xl border border-slate-100 bg-slate-50 p-4"
                    >
                        <Info
                            class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-600"
                        />
                        <div class="space-y-1">
                            <h5 class="text-xs font-black text-slate-800">
                                Petunjuk Pengumpulan Link:
                            </h5>
                            <ul
                                class="list-inside list-disc space-y-0.5 text-[11px] font-bold text-slate-500"
                            >
                                <li>
                                    Masukkan Proposal dan Surat Orisinalitas
                                    dalam satu folder Google Drive
                                </li>
                                <li>
                                    Atur hak akses sharing folder ke:
                                    <strong
                                        >"Siapa saja yang memiliki link
                                        dapat melihat"</strong
                                    >
                                    (Public)
                                </li>
                                <li>
                                    Tempel (Paste) alamat URL folder Google
                                    Drive tersebut pada kolom input di bawah
                                    ini
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Link Input -->
                    <div class="space-y-2">
                        <Label
                            class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                            >URL Google Drive Folder</Label
                        >
                        <Input
                            type="text"
                            v-model="submissionForm.link_file_submission"
                            placeholder="Contoh: https://drive.google.com/drive/folders/..."
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-900 focus:outline-none animate-none"
                        />
                        <div
                            v-if="
                                submissionForm.errors.link_file_submission
                            "
                            class="mt-1 flex items-center space-x-1.5 text-red-500"
                        >
                            <AlertCircle
                                class="h-3.5 w-3.5 flex-shrink-0"
                            />
                            <span class="text-[10px] font-bold">{{
                                submissionForm.errors.link_file_submission
                            }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-end space-x-3 pt-2"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            @click="closeSubmissionModal"
                            class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            class="flex items-center space-x-2 rounded-xl bg-[#1b2a4a] px-6 py-2.5 text-xs font-black text-white shadow-md transition hover:bg-[#15233d]"
                        >
                            <UploadCloud class="h-3.5 w-3.5" />
                            <span>Kumpulkan Link</span>
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Double Confirm Submit Proposal Modal -->
        <AlertDialog v-model:open="isConfirmSubmissionModalOpen" @update:open="(val) => !val && closeConfirmSubmissionModal()">
            <AlertDialogContent class="rounded-3xl max-w-md p-6 border-none shadow-[0_25px_50px_-12px_rgba(0,0,0,0.15)]">
                <div class="flex items-start space-x-4">
                    <div
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600"
                    >
                        <AlertCircle class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 space-y-1">
                        <AlertDialogTitle class="text-base font-black text-slate-900">
                            Apakah Anda Yakin Mengumpulkan?
                        </AlertDialogTitle>
                        <AlertDialogDescription class="text-xs leading-relaxed font-bold text-slate-500">
                            Pengumpulan proposal dan surat orisinalitas
                            hanya dapat dilakukan
                            <strong
                                class="font-extrabold text-slate-800"
                                >1 kali</strong
                            >
                            dan
                            <strong
                                class="font-extrabold text-slate-800"
                                >tidak dapat diubah atau
                                dibatalkan</strong
                            >
                            setelah dikirim. Pastikan link Google Drive
                            Anda sudah benar dan dapat diakses secara
                            publik.
                        </AlertDialogDescription>
                    </div>
                </div>

                <AlertDialogFooter class="flex items-center justify-end space-x-3 mt-4">
                    <AlertDialogCancel
                        @click="closeConfirmSubmissionModal"
                        as-child
                    >
                        <Button variant="outline" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                            Periksa Kembali
                        </Button>
                    </AlertDialogCancel>
                    <AlertDialogAction
                        @click="submitProposal"
                        :disabled="submissionForm.processing"
                        as-child
                    >
                        <Button class="rounded-xl bg-[#1b2a4a] px-6 py-2.5 text-xs font-black text-white hover:bg-[#15233d]">
                            {{
                                submissionForm.processing
                                    ? 'Mengirim...'
                                    : 'Ya, Kumpulkan'
                            }}
                        </Button>
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
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
