<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    Home,
    Users,
    User,
    LogOut,
    Menu,
    FileCheck,
    CreditCard,
    Users2,
    UploadCloud,
    CalendarDays,
    Handshake,
    X,
} from '@lucide/vue';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    activeMenu: {
        type: String,
        required: true,
    },
    role: {
        type: String,
        default: 'peserta', // 'peserta' | 'admin'
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
// Get team name if it exists (e.g. from userTeam prop or user relationship)
const teamName = computed(() => {
    return (
        page.props.userTeam?.nama_tim ||
        page.props.auth?.user?.tim?.nama_tim ||
        ''
    );
});

const isSidebarExpanded = ref(false);

onMounted(() => {
    if (window.innerWidth >= 1024) {
        isSidebarExpanded.value = true;
    }
});

const toggleSidebar = () => {
    isSidebarExpanded.value = !isSidebarExpanded.value;
};

const handleMenuClick = () => {
    if (window.innerWidth < 1024) {
        isSidebarExpanded.value = false;
    }
};

// Menus based on role
const menus = computed(() => {
    if (props.role === 'admin') {
        return [
            {
                name: 'Dashboard',
                route: 'admin.dashboard',
                icon: Home,
            },
            {
                name: 'Konfirmasi Persyaratan',
                route: 'admin.persyaratan',
                icon: FileCheck,
            },
            {
                name: 'Konfirmasi Pembayaran',
                route: 'admin.pembayaran',
                icon: CreditCard,
            },
            {
                name: 'Tim Terdaftar',
                route: 'admin.tim-terdaftar',
                icon: Users2,
            },
            {
                name: 'Submission',
                route: 'admin.submission',
                icon: UploadCloud,
            },
            {
                name: 'Atur Tanggal',
                route: 'admin.atur-tanggal',
                icon: CalendarDays,
            },
            {
                name: 'Kelola Sponsor',
                route: 'admin.kelola-sponsor',
                icon: Handshake,
            },
            {
                name: 'Profil',
                route: 'admin.profil',
                icon: User,
            },
        ];
    } else {
        return [
            { name: 'Beranda', route: 'dashboard', icon: Home },
            { name: 'Tim', route: 'peserta.tim', icon: Users },
            { name: 'Profil', route: 'peserta.profil', icon: User },
        ];
    }
});

const logout = () => {
    handleMenuClick();
    router.post(route('logout'));
};
</script>

<template>
    <div class="flex min-h-screen bg-[#f0f4f9] font-sans">
        <!-- Sidebar -->
        <aside
            class="fixed z-30 flex h-full flex-col bg-[#1b2a4a] text-white transition-all duration-300 lg:sticky lg:top-0 lg:h-screen"
            :class="[
                isSidebarExpanded
                    ? 'w-64 translate-x-0'
                    : '-translate-x-full lg:w-20 lg:translate-x-0',
            ]"
        >
            <!-- Sidebar Header -->
            <div
                class="flex min-h-[80px] items-center justify-between border-b border-slate-700/50 p-6"
            >
                <div
                    v-if="isSidebarExpanded"
                    class="flex items-center space-x-3"
                >
                    <img
                        src="/assets/logo-citech.png"
                        alt="Logo CITECH"
                        class="h-8 w-auto flex-shrink-0 object-contain"
                    />
                    <div>
                        <h1
                            class="text-xl leading-none font-black tracking-wider text-white"
                        >
                            CITECH
                        </h1>
                        <p
                            class="mt-1 text-[9px] leading-none font-bold tracking-wider text-slate-400 uppercase"
                        >
                            {{
                                role === 'admin'
                                    ? 'Dashboard Admin'
                                    : 'Dashboard Peserta'
                            }}
                        </p>
                    </div>
                </div>
                <div
                    v-else
                    class=" mx-auto flex items-center justify-center"
                >
                    <img
                        src="/assets/logo-citech.png"
                        alt="Logo CITECH"
                        class="h-8 w-auto object-contain"
                    />
                </div>

                <!-- Mobile close button -->
                <button
                    @click="toggleSidebar"
                    class="text-slate-400 hover:text-white lg:hidden"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-grow space-y-1 pt-6">
                <div v-for="menu in menus" :key="menu.route">
                    <Link
                        :href="route(menu.route)"
                        @click="handleMenuClick"
                        class="group relative flex items-center transition duration-200"
                        :class="[
                            activeMenu === menu.route
                                ? 'border-l-4 border-l-[#eab308] bg-[#203156] text-white'
                                : 'text-slate-300 hover:bg-[#203156] hover:text-white',
                        ]"
                        :title="!isSidebarExpanded ? menu.name : ''"
                    >
                        <!-- Highlight bar -->
                        <div
                            class="flex w-full items-center px-6 py-4"
                            :class="[
                                !isSidebarExpanded ? 'justify-center' : '',
                            ]"
                        >
                            <component
                                :is="menu.icon"
                                class="h-5 w-5 flex-shrink-0"
                                :class="[
                                    activeMenu === menu.route
                                        ? 'text-[#eab308]'
                                        : 'text-slate-400 group-hover:text-slate-200',
                                ]"
                            />
                            <span
                                v-if="isSidebarExpanded"
                                class="ml-4 text-sm font-bold tracking-wide"
                                >{{ menu.name }}</span
                            >
                        </div>
                    </Link>
                </div>
            </nav>

            <!-- Sidebar Footer (Logout) -->
            <div class="border-t border-slate-700/50 p-4">
                <button
                    @click="logout"
                    class="group flex w-full items-center rounded-xl px-4 py-3 text-slate-300 transition duration-200 hover:bg-[#203156] hover:text-white"
                    :class="[!isSidebarExpanded ? 'justify-center' : '']"
                    :title="!isSidebarExpanded ? 'Keluar' : ''"
                >
                    <LogOut
                        class="h-5 w-5 flex-shrink-0 text-slate-400 group-hover:text-slate-200"
                    />
                    <span
                        v-if="isSidebarExpanded"
                        class="ml-4 text-sm font-bold tracking-wide"
                        >Logout</span
                    >
                </button>
            </div>
        </aside>

        <!-- Overlay for mobile sidebar -->
        <div
            v-if="isSidebarExpanded"
            @click="toggleSidebar"
            class="fixed inset-0 z-20 bg-black/40 transition-opacity lg:hidden"
        ></div>

        <!-- Main Content Wrapper -->
        <div class="flex min-w-0 flex-grow flex-col">
            <!-- Topbar Header -->
            <header
                class="flex h-20 flex-shrink-0 items-center justify-between border-b border-slate-200/80 bg-white px-6 md:px-8"
            >
                <div class="flex items-center space-x-4">
                    <!-- Toggle Sidebar Button -->
                    <button
                        @click="toggleSidebar"
                        class="rounded-lg p-2 text-slate-600 transition hover:bg-slate-100"
                    >
                        <Menu class="h-6 w-6" />
                    </button>

                    <slot name="header-title"></slot>
                </div>

                <!-- Right Side Brand/Team Name -->
                <div class="flex items-center space-x-4">
                    <span
                        class="max-w-[140px] truncate rounded-xl border border-slate-200/50 bg-slate-100 px-3 py-1.5 text-xs font-black tracking-wider text-slate-800 uppercase transition hover:bg-slate-200/80 sm:max-w-[200px] sm:px-4 sm:py-2 sm:text-sm md:max-w-[280px] lg:max-w-[360px]"
                    >
                        {{
                            role === 'admin'
                                ? 'ADMINISTRATOR'
                                : teamName || user.name
                        }}
                    </span>
                </div>
            </header>

            <!-- Page Content -->
            <main
                class="mx-auto w-full max-w-7xl flex-grow overflow-y-auto p-6 md:p-8"
            >
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.2s ease-out forwards;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateX(-5px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
