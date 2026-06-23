<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Calendar, Save } from '@lucide/vue';
import { ref } from 'vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

const props = defineProps({
    timelines: Array,
});

const tahapLabels = {
    pendaftaran_b1: 'Pendaftaran Batch 1',
    pendaftaran_b2: 'Pendaftaran Batch 2',
    penyisihan: 'Babak Penyisihan',
    final: 'Babak Final',
    awarding: 'Awarding',
};

const tahapOrder = [
    'pendaftaran_b1',
    'pendaftaran_b2',
    'penyisihan',
    'final',
    'awarding',
];

const getTimelineData = (tahap) => {
    const tl = props.timelines?.find((t) => t.tahap === tahap);

    return {
        tanggal_mulai: tl?.tanggal_mulai
            ? tl.tanggal_mulai.substring(0, 16)
            : '',
        tanggal_selesai: tl?.tanggal_selesai
            ? tl.tanggal_selesai.substring(0, 16)
            : '',
    };
};

const formData = {};
tahapOrder.forEach((tahap) => {
    const data = getTimelineData(tahap);
    formData[`tanggal_mulai_${tahap}`] = data.tanggal_mulai;
    formData[`tanggal_selesai_${tahap}`] = data.tanggal_selesai;
});

const form = useForm(formData);
const showConfirm = ref(false);

const handleSubmit = () => {
    showConfirm.value = true;
};

const confirmSave = () => {
    showConfirm.value = false;
    form.post(route('admin.atur-tanggal.update'));
};
const cancelSave = () => {
    showConfirm.value = false;
};
</script>

<template>
    <Head title="Atur Tanggal Timeline - CITECH 2026" />

    <CitechDashboardLayout activeMenu="admin.atur-tanggal" role="admin">
        <template #header-title>
            <h2 class="text-lg font-black tracking-wide text-slate-800">
                Atur Tanggal
            </h2>
        </template>

        <div class="animate-fade-in-up space-y-6">
            <div
                class="rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8"
            >
                <h3 class="text-xl font-extrabold text-slate-800">
                    Manajemen Timeline Lomba
                </h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Atur tanggal mulai dan tanggal selesai untuk setiap tahapan
                    lomba CITECH. Perubahan akan berlaku langsung setelah
                    disimpan.
                </p>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div
                    v-for="tahap in tahapOrder"
                    :key="tahap"
                    class="rounded-2xl border border-slate-100 bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] md:p-6"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-blue-600" />
                        <h4
                            class="text-sm font-black tracking-wider text-slate-700 uppercase"
                        >
                            {{ tahapLabels[tahap] }}
                        </h4>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label
                                class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                >Tanggal Mulai</Label
                            >
                            <Input
                                type="datetime-local"
                                :value="form[`tanggal_mulai_${tahap}`]"
                                @input="
                                    form[`tanggal_mulai_${tahap}`] =
                                        $event.target.value
                                "
                                class="w-full h-11 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                            />
                            <div
                                v-if="form.errors[`tanggal_mulai_${tahap}`]"
                                class="mt-1 text-xs font-bold text-red-500"
                            >
                                {{ form.errors[`tanggal_mulai_${tahap}`] }}
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <Label
                                class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                >Tanggal Selesai</Label
                            >
                            <Input
                                type="datetime-local"
                                :value="form[`tanggal_selesai_${tahap}`]"
                                @input="
                                    form[`tanggal_selesai_${tahap}`] =
                                        $event.target.value
                                "
                                class="w-full h-11 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-[#1e4d8c] focus:outline-none"
                            />
                            <div
                                v-if="form.errors[`tanggal_selesai_${tahap}`]"
                                class="mt-1 text-xs font-bold text-red-500"
                            >
                                {{ form.errors[`tanggal_selesai_${tahap}`] }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#1e4d8c] px-6 py-3 text-xs font-extrabold tracking-wider text-white uppercase shadow-md transition hover:bg-[#153a6b] hover:shadow-lg disabled:opacity-50"
                    >
                        <Save class="h-4 w-4" />
                        Simpan Timeline
                    </button>
                </div>
            </form>
        </div>

        <!-- Confirmation Dialog -->
        <AlertDialog v-model:open="showConfirm">
            <AlertDialogContent class="rounded-2xl max-w-sm">
                <AlertDialogHeader>
                    <AlertDialogTitle class="text-lg font-black text-slate-800">
                        Konfirmasi Perubahan
                    </AlertDialogTitle>
                    <AlertDialogDescription class="text-sm leading-relaxed text-slate-500">
                        Apakah kamu yakin untuk mengubah tanggal submission?
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter class="flex justify-end gap-3 pt-2">
                    <AlertDialogCancel
                        @click="cancelSave"
                        class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600 border-none transition hover:bg-slate-200"
                    >
                        Batal
                    </AlertDialogCancel>
                    <AlertDialogAction
                        @click="confirmSave"
                        class="rounded-xl bg-[#1e4d8c] px-4 py-2 text-sm font-bold text-white border-none transition hover:bg-[#153a6b]"
                    >
                        Ya, Simpan
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
