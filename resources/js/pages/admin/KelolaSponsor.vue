<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    Plus,
    Pencil,
    Trash2,
    Globe,
    Upload,
    X,
    ExternalLink,
    AlertCircle,
} from '@lucide/vue';
import { ref } from 'vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Sponsor {
    id_sponsor: number;
    nama_sponsor: string;
    logo_sponsor: string | null;
    link_sponsor?: string | null;
    order: number;
    is_active: boolean;
}

const props = withDefaults(
    defineProps<{
        sponsors?: Sponsor[];
    }>(),
    {
        sponsors: () => [],
    }
);

// Modal State
const isOpenModal = ref(false);
const isEditMode = ref(false);
const editId = ref<number | null>(null);

// Form Setup
const form = useForm<{
    nama_sponsor: string;
    logo_sponsor: File | null;
    link_sponsor: string;
    order: number;
    is_active: boolean;
}>({
    nama_sponsor: '',
    logo_sponsor: null,
    link_sponsor: '',
    order: 0,
    is_active: true,
});

// Preview State
const logoPreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

// Drag & Drop State
const isDragging = ref(false);

const openAddModal = () => {
    isEditMode.value = false;
    editId.value = null;
    logoPreview.value = null;
    form.reset();
    form.clearErrors();
    isOpenModal.value = true;
};

const openEditModal = (sponsor: Sponsor) => {
    isEditMode.value = true;
    editId.value = sponsor.id_sponsor;
    logoPreview.value = sponsor.logo_sponsor
        ? `/storage/${sponsor.logo_sponsor}`
        : null;

    form.clearErrors();
    form.nama_sponsor = sponsor.nama_sponsor;
    form.logo_sponsor = null; // Keep null unless user uploads a new one
    form.link_sponsor = sponsor.link_sponsor || '';
    form.order = sponsor.order;
    form.is_active = sponsor.is_active;

    isOpenModal.value = true;
};

const closeModal = () => {
    isOpenModal.value = false;
    form.reset();
    form.clearErrors();
    logoPreview.value = null;
};

// File handling
const triggerFileSelect = () => {
    fileInput.value?.click();
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        processFile(file);
    }
};

const processFile = (file: File) => {
    if (!file.type.startsWith('image/')) {
        form.setError('logo_sponsor', 'File harus berupa gambar.');

        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        form.setError('logo_sponsor', 'Ukuran gambar maksimal adalah 2MB.');

        return;
    }

    form.logo_sponsor = file;
    form.clearErrors('logo_sponsor');

    const reader = new FileReader();
    reader.onload = (e) => {
        logoPreview.value = e.target?.result as string | null;
    };
    reader.readAsDataURL(file);
};

const onDragOver = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = true;
};

const onDragLeave = () => {
    isDragging.value = false;
};

const onDrop = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = false;
    const file = e.dataTransfer?.files[0];

    if (file) {
        processFile(file);
    }
};

const removeSelectedLogo = () => {
    form.logo_sponsor = null;
    logoPreview.value =
        isEditMode.value &&
        props.sponsors.find((s) => s.id_sponsor === editId.value)?.logo_sponsor
            ? `/storage/${props.sponsors.find((s) => s.id_sponsor === editId.value)?.logo_sponsor}`
            : null;

    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Submit Logic
const handleSubmit = () => {
    if (isEditMode.value) {
        // Laravel multipart bug workaround: must use POST but can spoof update or call post endpoint
        form.post(route('admin.kelola-sponsor.update', editId.value!), {
            onSuccess: () => closeModal(),
            forceFormData: true,
        });
    } else {
        form.post(route('admin.kelola-sponsor.store'), {
            onSuccess: () => closeModal(),
            forceFormData: true,
        });
    }
};

// Delete Logic
const showConfirmDelete = ref(false);
const deleteId = ref<number | null>(null);
const deleteSponsorName = ref('');

const confirmDelete = (sponsor: Sponsor) => {
    deleteId.value = sponsor.id_sponsor;
    deleteSponsorName.value = sponsor.nama_sponsor;
    showConfirmDelete.value = true;
};

const executeDelete = () => {
    if (deleteId.value !== null) {
        router.delete(route('admin.kelola-sponsor.destroy', deleteId.value), {
            onSuccess: () => {
                showConfirmDelete.value = false;
                deleteId.value = null;
                deleteSponsorName.value = '';
            },
        });
    }
};

const cancelDelete = () => {
    showConfirmDelete.value = false;
    deleteId.value = null;
    deleteSponsorName.value = '';
};

// Toggle Visiblity directly
const toggleStatus = (sponsor: Sponsor) => {
    router.post(
        route('admin.kelola-sponsor.update', sponsor.id_sponsor),
        {
            nama_sponsor: sponsor.nama_sponsor,
            link_sponsor: sponsor.link_sponsor || '',
            order: sponsor.order,
            is_active: !sponsor.is_active,
        },
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <Head title="Kelola Sponsor" />

    <CitechDashboardLayout activeMenu="admin.kelola-sponsor" role="admin">
        <template #header-title>
            <h2 class="text-lg font-black tracking-wide text-slate-800">
                Kelola Sponsor
            </h2>
        </template>

        <div class="animate-fade-in-up space-y-6">
            <!-- Intro Header Card -->
            <div
                class="flex flex-col justify-between gap-6 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:flex-row md:items-center md:p-8"
            >
                <div class="space-y-1">
                    <h3 class="text-xl font-extrabold text-slate-800">
                        Manajemen Sponsor & Partnership
                    </h3>
                    <p class="text-sm leading-relaxed text-slate-500">
                        Kelola mitra sponsor CITECH. Logo sponsor yang aktif
                        akan otomatis ditampilkan pada scrolling marquee di
                        landing page.
                    </p>
                </div>
                <div>
                    <button
                        @click="openAddModal"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#1e4d8c] px-5 py-3 text-xs font-extrabold tracking-wider text-white uppercase shadow-md transition hover:bg-[#153a6b] hover:shadow-lg active:scale-95 md:w-auto"
                    >
                        <Plus class="h-4 w-4" />
                        Tambah Sponsor
                    </button>
                </div>
            </div>

            <!-- Sponsor Grid -->
            <div
                v-if="sponsors.length > 0"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="sponsor in sponsors"
                    :key="sponsor.id_sponsor"
                    class="group flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-[0_4px_20px_rgba(0,0,0,0.02)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_10px_30px_rgba(0,0,0,0.05)]"
                >
                    <!-- Logo container -->
                    <div
                        class="relative flex h-44 items-center justify-center border-b border-slate-50 bg-slate-50 p-6"
                    >
                        <img
                            :src="`/storage/${sponsor.logo_sponsor}`"
                            :alt="sponsor.nama_sponsor"
                            class="max-h-24 max-w-full object-contain drop-shadow-sm filter transition-transform duration-300 group-hover:scale-105"
                        />

                        <!-- Order Badge -->
                        <span
                            class="absolute top-3 left-3 rounded-md bg-slate-800 px-2 py-0.5 font-mono text-[10px] font-bold text-white shadow-sm"
                        >
                            Urutan: {{ sponsor.order }}
                        </span>

                        <!-- Status Badge -->
                        <button
                            @click="toggleStatus(sponsor)"
                            class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-extrabold tracking-wider uppercase transition-colors"
                            :class="[
                                sponsor.is_active
                                    ? 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                    : 'border border-slate-200 bg-slate-100 text-slate-500 hover:bg-slate-200',
                            ]"
                            title="Klik untuk mengubah status aktif"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="[
                                    sponsor.is_active
                                        ? 'bg-emerald-500'
                                        : 'bg-slate-400',
                                ]"
                            ></span>
                            {{ sponsor.is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </div>

                    <!-- Card Body -->
                    <div class="flex flex-1 flex-col justify-between p-5">
                        <div class="space-y-1.5">
                            <h4
                                class="text-base leading-tight font-extrabold text-slate-800"
                            >
                                {{ sponsor.nama_sponsor }}
                            </h4>
                            <div
                                class="flex items-center gap-1.5 text-xs text-slate-400"
                            >
                                <Globe class="h-3.5 w-3.5 flex-shrink-0" />
                                <a
                                    v-if="sponsor.link_sponsor"
                                    :href="sponsor.link_sponsor"
                                    target="_blank"
                                    class="inline-flex items-center gap-0.5 truncate font-semibold text-blue-600 hover:underline"
                                >
                                    {{ sponsor.link_sponsor }}
                                    <ExternalLink class="h-3 w-3" />
                                </a>
                                <span v-else class="font-medium italic"
                                    >Tidak ada tautan</span
                                >
                            </div>
                        </div>

                        <!-- Card Action buttons -->
                        <div
                            class="mt-5 flex items-center gap-2 border-t border-slate-50 pt-4"
                        >
                            <button
                                @click="openEditModal(sponsor)"
                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700"
                            >
                                <Pencil class="h-3.5 w-3.5" />
                                Edit
                            </button>
                            <button
                                @click="confirmDelete(sponsor)"
                                class="inline-flex items-center justify-center rounded-xl border border-red-100/50 bg-red-50 p-2.5 text-red-600 transition hover:bg-red-100"
                                title="Hapus Sponsor"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Placeholder -->
            <div
                v-else
                class="space-y-4 rounded-3xl border border-slate-100 bg-white p-12 text-center shadow-[0_10px_35px_rgba(0,0,0,0.02)]"
            >
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-slate-100 bg-slate-50 text-slate-400"
                >
                    <Globe class="h-8 w-8" />
                </div>
                <div class="mx-auto max-w-md space-y-1">
                    <h4 class="text-lg font-extrabold text-slate-800">
                        Belum Ada Sponsor
                    </h4>
                    <p class="text-sm leading-relaxed text-slate-500">
                        Daftar sponsor masih kosong. Klik tombol "Tambah
                        Sponsor" untuk mendaftarkan mitra sponsor CITECH
                        pertama.
                    </p>
                </div>
                <div class="pt-2">
                    <button
                        @click="openAddModal"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-100 px-5 py-2.5 text-xs font-extrabold text-slate-700 transition hover:bg-slate-200 active:scale-95"
                    >
                        <Plus class="h-4 w-4" />
                        Tambah Sponsor Baru
                    </button>
                </div>
            </div>
        </div>

        <!-- Add / Edit Modal -->
        <Dialog
            v-model:open="isOpenModal"
            @update:open="(val) => !val && closeModal()"
        >
            <DialogContent
                class="overflow-hidden rounded-3xl border-none p-0 shadow-2xl sm:max-w-lg"
            >
                <!-- Modal Header -->
                <DialogHeader
                    class="flex flex-row items-center justify-between space-y-0 border-b border-slate-100 px-6 py-5"
                >
                    <DialogTitle
                        class="text-base font-extrabold text-slate-800"
                    >
                        {{
                            isEditMode
                                ? 'Edit Mitra Sponsor'
                                : 'Tambah Mitra Sponsor Baru'
                        }}
                    </DialogTitle>
                </DialogHeader>

                <!-- Modal Body / Form -->
                <form @submit.prevent="handleSubmit" class="space-y-4 p-6">
                    <!-- Nama Sponsor -->
                    <div class="space-y-1.5">
                        <Label
                            class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                            >Nama Sponsor</Label
                        >
                        <Input
                            type="text"
                            v-model="form.nama_sponsor"
                            placeholder="Masukkan nama brand atau perusahaan"
                            class="h-11 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:border-transparent focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                            required
                        />
                        <div
                            v-if="form.errors.nama_sponsor"
                            class="mt-1 text-xs font-bold text-red-500"
                        >
                            {{ form.errors.nama_sponsor }}
                        </div>
                    </div>

                    <!-- Link Sponsor -->
                    <div class="space-y-1.5">
                        <Label
                            class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                            >Tautan Website (Opsional)</Label
                        >
                        <Input
                            type="url"
                            v-model="form.link_sponsor"
                            placeholder="https://example.com"
                            class="h-11 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:border-transparent focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                        />
                        <div
                            v-if="form.errors.link_sponsor"
                            class="mt-1 text-xs font-bold text-red-500"
                        >
                            {{ form.errors.link_sponsor }}
                        </div>
                    </div>

                    <!-- Grid Order & Status -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Urutan Tampilan -->
                        <div class="space-y-1.5">
                            <Label
                                class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                >Urutan Tampilan</Label
                            >
                            <Input
                                type="number"
                                v-model="form.order"
                                min="0"
                                class="h-11 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-800 focus:border-transparent focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                                required
                            />
                            <div
                                v-if="form.errors.order"
                                class="mt-1 text-xs font-bold text-red-500"
                            >
                                {{ form.errors.order }}
                            </div>
                        </div>

                        <!-- Toggle Status Aktif -->
                        <div class="flex flex-col justify-end space-y-1.5">
                            <Label
                                class="mb-3 block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                >Status Aktif</Label
                            >
                            <label
                                class="inline-flex cursor-pointer items-center select-none"
                            >
                                <input
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="peer sr-only"
                                />
                                <div
                                    class="peer relative h-6 w-11 rounded-full bg-slate-200 peer-checked:bg-[#1e4d8c] peer-focus:outline-none after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white rtl:peer-checked:after:-translate-x-full"
                                ></div>
                                <span
                                    class="ms-3 text-xs font-bold"
                                    :class="[
                                        form.is_active
                                            ? 'text-slate-800'
                                            : 'text-slate-400',
                                    ]"
                                >
                                    {{
                                        form.is_active ? 'Aktif' : 'Sembunyikan'
                                    }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Upload Logo Sponsor -->
                    <div class="space-y-1.5">
                        <Label
                            class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                            >Logo Sponsor</Label
                        >

                        <!-- File Input Hidden -->
                        <input
                            type="file"
                            ref="fileInput"
                            @change="handleFileChange"
                            accept="image/*"
                            class="hidden"
                        />

                        <!-- Drag & Drop Zone -->
                        <div
                            @dragover="onDragOver"
                            @dragleave="onDragLeave"
                            @drop="onDrop"
                            @click="triggerFileSelect"
                            class="flex min-h-[140px] cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed p-6 text-center transition-all duration-300"
                            :class="[
                                isDragging
                                    ? 'border-blue-500 bg-blue-50/20'
                                    : logoPreview
                                      ? 'border-slate-200 bg-slate-50/30'
                                      : 'border-slate-200 hover:border-blue-400 hover:bg-slate-50/30',
                            ]"
                        >
                            <!-- Preview Image -->
                            <div
                                v-if="logoPreview"
                                class="group/preview relative max-w-full"
                            >
                                <img
                                    :src="logoPreview"
                                    alt="Pratinjau Logo"
                                    class="max-h-24 max-w-full rounded-md object-contain"
                                />
                                <button
                                    type="button"
                                    @click.stop="removeSelectedLogo"
                                    class="absolute -top-2 -right-2 rounded-full bg-red-500 p-1 text-white shadow-md transition hover:scale-105 hover:bg-red-600"
                                    title="Hapus Logo"
                                >
                                    <X class="h-3.5 w-3.5" />
                                </button>
                            </div>

                            <!-- Upload Placeholder -->
                            <div v-else class="space-y-2 text-slate-400">
                                <Upload
                                    class="mx-auto h-8 w-8 stroke-[1.5] text-slate-400"
                                />
                                <div class="text-xs">
                                    <span class="font-extrabold text-blue-600"
                                        >Klik untuk upload</span
                                    >
                                    atau seret file gambar
                                </div>
                                <p class="text-[10px] font-medium">
                                    JPEG, PNG, JPG, SVG, WEBP (Max 2MB)
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="form.errors.logo_sponsor"
                            class="mt-1 text-xs font-bold text-red-500"
                        >
                            {{ form.errors.logo_sponsor }}
                        </div>
                    </div>

                    <!-- Submit Footer -->
                    <div
                        class="mt-6 flex justify-end gap-3 border-t border-slate-50 pt-4"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            @click="closeModal"
                            class="h-11 rounded-xl border border-none border-slate-200 bg-slate-100 px-5 py-3 text-xs font-extrabold tracking-wider text-slate-600 uppercase transition hover:bg-slate-200"
                            :disabled="form.processing"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            class="h-11 rounded-xl bg-[#1e4d8c] px-5 py-3 text-xs font-extrabold tracking-wider text-white uppercase shadow-md transition hover:scale-[1.01] hover:bg-[#153a6b] hover:shadow-blue-500/10 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : isEditMode
                                      ? 'Simpan Perubahan'
                                      : 'Tambah Sponsor'
                            }}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showConfirmDelete"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div
                class="flex min-h-full items-center justify-center p-4 text-center"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                    @click="cancelDelete"
                ></div>

                <!-- Modal Content Wrapper -->
                <div
                    class="animate-fade-in-up relative z-10 my-8 inline-block w-full max-w-md transform overflow-hidden rounded-3xl border border-slate-100 bg-white text-left align-middle shadow-[0_25px_50px_-12px_rgba(0,0,0,0.15)] transition-all"
                >
                    <div class="space-y-6 p-6">
                        <div class="flex items-start gap-4">
                            <div
                                class="rounded-2xl border border-red-100 bg-red-50 p-3 text-red-600"
                            >
                                <AlertCircle class="h-6 w-6" />
                            </div>
                            <div class="space-y-1">
                                <h3
                                    class="text-base font-extrabold text-slate-800"
                                >
                                    Hapus Mitra Sponsor
                                </h3>
                                <p
                                    class="text-xs leading-relaxed text-slate-500"
                                >
                                    Apakah kamu yakin ingin menghapus sponsor
                                    <strong class="text-slate-800">{{
                                        deleteSponsorName
                                    }}</strong
                                    >? Tindakan ini akan menghapus data beserta
                                    berkas logo secara permanen.
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                @click="cancelDelete"
                                class="rounded-xl border-none bg-slate-100 px-4 py-2.5 text-xs font-extrabold tracking-wider text-slate-600 uppercase transition hover:bg-slate-200"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                @click="executeDelete"
                                class="rounded-xl border-none bg-red-600 px-4 py-2.5 text-xs font-extrabold tracking-wider text-white uppercase shadow-md transition hover:scale-[1.01] hover:bg-red-700 hover:shadow-red-500/10"
                            >
                                Hapus Permanen
                            </button>
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
@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.animate-scale-in {
    animation: scaleIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
