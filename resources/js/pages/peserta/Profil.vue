<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { User, Settings, Lock } from '@lucide/vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => !!user.value?.is_admin);
const role = computed(() => isAdmin.value ? 'admin' : 'peserta');
const activeMenu = computed(() => isAdmin.value ? 'admin.profil' : 'peserta.profil');
const subtitle = computed(() => isAdmin.value ? 'Administrator' : 'Peserta Citech 2026');
</script>

<template>
    <Head title="Profil Pengguna" />

    <CitechDashboardLayout :activeMenu="activeMenu" :role="role">
        <template #header-title>
            <h2 class="text-lg font-black tracking-wide text-slate-800">
                Profil Saya
            </h2>
        </template>

        <div class="animate-fade-in-up space-y-8">
            <div
                class="space-y-6 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8"
            >
                <div class="flex items-center space-x-3">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600"
                    >
                        <User class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-800">
                            {{ $page.props.auth.user.name }}
                        </h3>
                        <p class="text-xs font-bold text-slate-400">
                            {{ subtitle }}
                        </p>
                    </div>
                </div>

                <div class="space-y-4 border-t border-slate-100 pt-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-1">
                            <span
                                class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                >Alamat Email</span
                            >
                            <span
                                class="text-sm font-extrabold text-slate-800"
                                >{{ $page.props.auth.user.email }}</span
                            >
                        </div>

                        <div class="space-y-1">
                            <span
                                class="block text-[10px] font-black tracking-wider text-slate-400 uppercase"
                                >Tanggal Pendaftaran Akun</span
                            >
                            <span class="text-sm font-extrabold text-slate-800">
                                {{
                                    new Date(
                                        $page.props.auth.user.created_at,
                                    ).toLocaleDateString('id-ID', {
                                        day: 'numeric',
                                        month: 'long',
                                        year: 'numeric',
                                    })
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Settings Redirection Section -->
                <div
                    class="flex flex-wrap gap-4 border-t border-slate-100 pt-6"
                >
                    <Link
                        :href="route('profile.edit')"
                        class="inline-flex items-center space-x-2 rounded-xl bg-[#1e4d8c] px-5 py-3 text-xs font-black text-white shadow-sm transition hover:bg-[#153a6b]"
                    >
                        <Settings class="h-4 w-4" />
                        <span>Edit Profil</span>
                    </Link>
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
