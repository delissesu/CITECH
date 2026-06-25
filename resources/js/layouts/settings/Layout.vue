<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft } from '@lucide/vue';
import CitechDashboardLayout from '@/components/CitechDashboardLayout.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profil',
        href: editProfile(),
    },
    {
        title: 'Password & Keamanan',
        href: editSecurity(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();

const page = usePage<any>();
const user = computed(() => page.props.auth.user);
const role = computed(() => user.value?.is_admin ? 'admin' : 'peserta');
const activeMenu = computed(() => role.value === 'admin' ? 'admin.profil' : 'peserta.profil');

const backRoute = computed(() => role.value === 'admin' ? route('admin.profil') : route('peserta.profil'));
const backText = computed(() => 'Profil');
</script>

<template>
    <CitechDashboardLayout :activeMenu="activeMenu" :role="role">
        <template #header-title>
            <div class="flex items-center space-x-3">
                <Link
                    :href="backRoute"
                    class="inline-flex items-center space-x-1.5 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-black tracking-wide text-slate-600 transition hover:bg-slate-200"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    <span>Kembali ke {{ backText }}</span>
                </Link>
                <span class="text-slate-300">|</span>
                <h2 class="text-lg font-black tracking-wide text-slate-800">
                    Pengaturan
                </h2>
            </div>
        </template>

        <div class="animate-fade-in-up space-y-6">
            <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                <!-- Left Side Nav: Settings Tabs -->
                <div class="lg:col-span-3">
                    <div class="space-y-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)]">
                        <h3 class="text-[10px] font-black tracking-wider text-slate-400 uppercase">
                            Navigasi Pengaturan
                        </h3>
                        <nav class="flex flex-col space-y-1">
                            <Link
                                v-for="item in sidebarNavItems"
                                :key="toUrl(item.href)"
                                :href="item.href"
                                class="flex items-center space-x-3 rounded-xl px-4 py-3 text-xs font-black tracking-wide transition-all duration-200"
                                :class="[
                                    isCurrentOrParentUrl(item.href) || (item.title === 'Password & Keamanan' && page.url.startsWith('/user/confirm-password'))
                                        ? 'bg-blue-50 text-blue-900 shadow-sm'
                                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'
                                ]"
                            >
                                <span>{{ item.title }}</span>
                            </Link>
                        </nav>
                    </div>
                </div>

                <!-- Right Side: Settings Content -->
                <div class="lg:col-span-9">
                    <div class="space-y-8 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_10px_35px_rgba(0,0,0,0.03)] md:p-8">
                        <slot />
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

