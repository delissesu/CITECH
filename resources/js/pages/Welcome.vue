<script setup>
import { Head, Link } from '@inertiajs/vue3';
import {
    Code,
    Users,
    Trophy,
    Sparkles,
    ChevronDown,
    ArrowRight,
    FileText,
    Globe,
} from '@lucide/vue';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    activeTimeline: Object,
    allTimelines: Array,
    sponsors: {
        type: Array,
        default: () => [],
    },
});

// Navbar Scroll Effect
const isScrolled = ref(false);
const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

// Countdown logic
const days = ref(0);
const hours = ref(0);
const minutes = ref(0);
const seconds = ref(0);
let timer = null;

const targetDate = computed(() => {
    if (props.activeTimeline && props.activeTimeline.tanggal_selesai) {
        return new Date(props.activeTimeline.tanggal_selesai);
    }

    // Fallback date
    return new Date('2026-07-18 23:59:59');
});

const activeStageName = computed(() => {
    if (props.activeTimeline) {
        switch (props.activeTimeline.tahap) {
            case 'pendaftaran_b1':
                return 'Batch 1 Pendaftaran';
            case 'pendaftaran_b2':
                return 'Batch 2 Pendaftaran';
            case 'penyisihan':
                return 'Pengumpulan Berkas';
            case 'final':
                return 'Babak Final CITECH';
            case 'awarding':
                return 'Acara Awarding';
            default:
                return 'CITECH 2026';
        }
    }

    return 'CITECH 2026';
});

const calculateTimeLeft = () => {
    const difference = targetDate.value - new Date();

    if (difference > 0) {
        days.value = Math.floor(difference / (1000 * 60 * 60 * 24));
        hours.value = Math.floor((difference / (1000 * 60 * 60)) % 24);
        minutes.value = Math.floor((difference / 1000 / 60) % 60);
        seconds.value = Math.floor((difference / 1000) % 60);
    } else {
        days.value = 0;
        hours.value = 0;
        minutes.value = 0;
        seconds.value = 0;
        clearInterval(timer);
    }
};

// Section Reveal Observer
const revealedSections = ref({
    tentang: false,
    countdown: false,
    timeline: false,
    sponsor: false,
    faq: false,
});

onMounted(() => {
    calculateTimeLeft();
    timer = setInterval(calculateTimeLeft, 1000);
    window.addEventListener('scroll', handleScroll);
    handleScroll(); // initial check

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const id = entry.target.id;

                    if (id && id in revealedSections.value) {
                        revealedSections.value[id] = true;
                    }
                }
            });
        },
        { threshold: 0.1 },
    );

    document.querySelectorAll('section[id]').forEach((sec) => {
        observer.observe(sec);
    });
});

onUnmounted(() => {
    if (timer) {
clearInterval(timer);
}

    window.removeEventListener('scroll', handleScroll);
});

// Auto-duplicate sponsors if the active count is small to ensure smooth marquee scrolling
const duplicatedSponsors = computed(() => {
    if (!props.sponsors || props.sponsors.length === 0) {
return [];
}

    let list = [...props.sponsors];

    while (list.length < 12) {
        list = [...list, ...props.sponsors];
    }

    return list;
});

// FAQ Accordion

const faqs = [
    {
        q: 'Apakah kompetisi ini berbayar?',
        a: 'Ya, pendaftaran dikenakan biaya pendaftaran sesuai dengan batch pendaftaran kategori lomba Web Design. Informasi lengkap nominal biaya dapat dilihat pada panduan pendaftaran.',
    },
    {
        q: 'Berapa orang dalam satu tim?',
        a: 'Kategori perlombaan Web Design ini dapat diikuti secara individu maupun tim (maksimal 3 orang per tim). Anggota tim dapat dikonfigurasi langsung di dashboard setelah membuat tim.',
    },
    {
        q: 'Apakah perlombaan dilaksanakan online atau offline?',
        a: 'Babak penyisihan dan pengumpulan berkas dilakukan secara online melalui website ini. Sedangkan babak final dan awarding akan diselenggarakan secara offline di Universitas Jember.',
    },
    {
        q: 'Dimana saya dapat melihat informasi terbaru?',
        a: 'Informasi terbaru akan selalu diperbarui di dashboard peserta website ini, serta dipublikasikan melalui akun Instagram resmi kami @citech_unej.',
    },
];

// Timeline static fallback
const timelineItemsFallback = [
    {
        tahap: 'Batch 1 Pendaftaran',
        desc: 'Pendaftaran Seluruh Peserta Batch 1',
        date: '27 Juni - 18 Juli 2026',
        alignLeft: false,
    },
    {
        tahap: 'Batch 2 Pendaftaran',
        desc: 'Pendaftaran Seluruh Peserta Batch 2',
        date: '19 Juli - 1 Agustus 2026',
        alignLeft: true,
    },
    {
        tahap: 'Pengumpulan Berkas',
        desc: 'Pengumpulan Berkas Babak Penyisihan',
        date: '27 Juni - 1 Agustus 2026',
        alignLeft: false,
    },
    {
        tahap: 'Pengumuman Lolos',
        desc: 'Pengumuman Lolos Babak Penyisihan',
        date: '10 Agustus 2026',
        alignLeft: true,
    },
    {
        tahap: 'Technical Meeting Finalis',
        desc: 'Technical Meeting Finalis',
        date: '11 Agustus 2026',
        alignLeft: false,
    },
    {
        tahap: 'Pengumpulan Berkas Final',
        desc: 'Pengumpulan Berkas Babak Final',
        date: '19 Agustus - 21 Agustus 2026',
        alignLeft: true,
    },
    {
        tahap: 'Final & Awarding',
        desc: 'Final & Awarding',
        date: '23 Agustus 2026',
        alignLeft: false,
    },
];

// Dynamic timeline items mapped from Database
const formattedTimelineItems = computed(() => {
    if (!props.allTimelines || props.allTimelines.length === 0) {
        return timelineItemsFallback.map((item) => ({
            ...item,
            isActive: props.activeTimeline?.tahap === item.tahap,
        }));
    }

    const formatRange = (startStr, endStr) => {
        if (!startStr || !endStr) {
return '';
}

        const start = new Date(startStr);
        const end = new Date(endStr);
        const months = [
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];

        // Since we know the database dates are in June-August:
        const getMonthName = (date) => {
            const m = date.getMonth();
            const monthsFull = [
                'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember',
            ];

            return monthsFull[m];
        };

        if (start.getFullYear() === end.getFullYear()) {
            if (start.getMonth() === end.getMonth()) {
                return `${start.getDate()} - ${end.getDate()} ${getMonthName(start)} ${start.getFullYear()}`;
            } else {
                return `${start.getDate()} ${getMonthName(start)} - ${end.getDate()} ${getMonthName(end)} ${start.getFullYear()}`;
            }
        }

        return `${start.getDate()} ${getMonthName(start)} ${start.getFullYear()} - ${end.getDate()} ${getMonthName(end)} ${end.getFullYear()}`;
    };

    const b1 = props.allTimelines.find((t) => t.tahap === 'pendaftaran_b1');
    const b2 = props.allTimelines.find((t) => t.tahap === 'pendaftaran_b2');
    const penyisihan = props.allTimelines.find((t) => t.tahap === 'penyisihan');
    const final = props.allTimelines.find((t) => t.tahap === 'final');
    const awarding = props.allTimelines.find((t) => t.tahap === 'awarding');

    const items = [];

    if (b1) {
        items.push({
            tahap: 'Batch 1 Pendaftaran',
            desc: 'Pendaftaran Seluruh Peserta Batch 1',
            date: formatRange(b1.tanggal_mulai, b1.tanggal_selesai),
            alignLeft: false,
            isActive: props.activeTimeline?.tahap === 'pendaftaran_b1',
        });
    }

    if (b2) {
        items.push({
            tahap: 'Batch 2 Pendaftaran',
            desc: 'Pendaftaran Seluruh Peserta Batch 2',
            date: formatRange(b2.tanggal_mulai, b2.tanggal_selesai),
            alignLeft: true,
            isActive: props.activeTimeline?.tahap === 'pendaftaran_b2',
        });
    }

    if (penyisihan) {
        items.push({
            tahap: 'Pengumpulan Berkas (Penyisihan)',
            desc: 'Pengumpulan Berkas Babak Penyisihan',
            date: formatRange(
                penyisihan.tanggal_mulai,
                penyisihan.tanggal_selesai,
            ),
            alignLeft: false,
            isActive: props.activeTimeline?.tahap === 'penyisihan',
        });
    }

    // Intermediate chronological dates relative to Final
    let pengumumanDate = '10 Agustus 2026';
    let tmDate = '11 Agustus 2026';

    if (final && final.tanggal_mulai) {
        const finalStart = new Date(final.tanggal_mulai);
        const dMinus1 = new Date(finalStart);
        dMinus1.setDate(dMinus1.getDate() - 1);
        const monthsFull = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];
        pengumumanDate = `${dMinus1.getDate()} ${monthsFull[dMinus1.getMonth()]} ${dMinus1.getFullYear()}`;
        tmDate = `${finalStart.getDate()} ${monthsFull[finalStart.getMonth()]} ${finalStart.getFullYear()}`;
    }

    items.push({
        tahap: 'Pengumuman Lolos Penyisihan',
        desc: 'Pengumuman Peserta Lolos ke Babak Final',
        date: pengumumanDate,
        alignLeft: true,
        isActive: false,
    });

    items.push({
        tahap: 'Technical Meeting Finalis',
        desc: 'Pemberian arahan dan panduan untuk Babak Final',
        date: tmDate,
        alignLeft: false,
        isActive: false,
    });

    if (final) {
        items.push({
            tahap: 'Pengumpulan Berkas Final',
            desc: 'Pengumpulan Berkas untuk Penjurian Final',
            date: formatRange(final.tanggal_mulai, final.tanggal_selesai),
            alignLeft: true,
            isActive: props.activeTimeline?.tahap === 'final',
        });
    }

    if (awarding) {
        items.push({
            tahap: 'Final & Awarding CITECH',
            desc: 'Presentasi Finalis dan Acara Penganugerahan Pemenang',
            date: formatRange(awarding.tanggal_mulai, awarding.tanggal_selesai),
            alignLeft: false,
            isActive: props.activeTimeline?.tahap === 'awarding',
        });
    }

    return items;
});
</script>

<template>
    <Head title="Carnival Technology UNEJ" />

    <div
        class="animate-page-fade relative min-h-screen overflow-x-hidden bg-slate-50/20 font-sans text-slate-700"
    >
        <!-- Floating background blobs -->
        <div
            class="pointer-events-none absolute top-[10%] left-[5%] z-0 h-[400px] w-[400px] animate-float-slow rounded-full bg-blue-300/15 blur-[100px]"
        ></div>
        <div
            class="pointer-events-none absolute top-[40%] right-[5%] z-0 h-[450px] w-[450px] animate-float-delayed rounded-full bg-purple-300/15 blur-[120px]"
        ></div>
        <div
            class="pointer-events-none absolute bottom-[20%] left-[10%] z-0 h-[350px] w-[350px] rounded-full bg-orange-200/10 blur-[90px]"
        ></div>

        <!-- Sticky Header/Navbar (Transitions dynamically based on scroll) -->
        <header
            class="animate-navbar fixed top-0 right-0 left-0 z-50 w-full transition-all duration-[1000ms] ease-[cubic-bezier(0.16,1,0.3,1)]"
            :class="[
                isScrolled
                    ? 'border-b border-slate-100 bg-white/90 py-4 shadow-md backdrop-blur-md'
                    : 'border-b border-transparent bg-transparent py-6',
            ]"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-6"
            >
                <!-- Logo -->
                <div
                    class="animate-nav-item flex cursor-pointer items-center space-x-3 transition-all duration-300 hover:scale-[1.02]"
                >
                    <img
                        src="/assets/logo-citech.png"
                        alt="Logo CITECH"
                        class="h-10 w-auto object-contain"
                    />
                    <span
                        class="text-2xl font-extrabold tracking-wider text-blue-900"
                    >
                        CITECH
                    </span>
                </div>

                <!-- Navigation Links with smooth transition & hover underline animation -->
                <nav
                    class="hidden items-center space-x-10 text-sm font-semibold md:flex"
                >
                    <a
                        href="#tentang"
                        class="group animate-nav-item relative py-2 text-slate-700 transition-all delay-100 duration-300 hover:text-blue-900"
                    >
                        Tentang
                        <span
                            class="absolute bottom-0 left-0 h-0.5 w-0 bg-[#1e4d8c] transition-all duration-300 group-hover:w-full"
                        ></span>
                    </a>
                    <a
                        href="#timeline"
                        class="group animate-nav-item relative py-2 text-slate-700 transition-all delay-200 duration-300 hover:text-blue-900"
                    >
                        Timeline
                        <span
                            class="absolute bottom-0 left-0 h-0.5 w-0 bg-[#1e4d8c] transition-all duration-300 group-hover:w-full"
                        ></span>
                    </a>
                    <a
                        href="#sponsor"
                        class="group animate-nav-item relative py-2 text-slate-700 transition-all delay-300 duration-300 hover:text-blue-900"
                    >
                        Event
                        <span
                            class="absolute bottom-0 left-0 h-0.5 w-0 bg-[#1e4d8c] transition-all duration-300 group-hover:w-full"
                        ></span>
                    </a>
                    <a
                        href="#faq"
                        class="group animate-nav-item relative py-2 text-slate-700 transition-all delay-400 duration-300 hover:text-blue-900"
                    >
                        FAQ
                        <span
                            class="absolute bottom-0 left-0 h-0.5 w-0 bg-[#1e4d8c] transition-all duration-300 group-hover:w-full"
                        ></span>
                    </a>
                </nav>

                <!-- Auth / Call To Action Button -->
                <div class="animate-nav-item delay-500">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="route('dashboard')"
                            class="inline-block rounded-lg bg-[#3769a6] px-6 py-2.5 text-xs font-bold text-white transition-all duration-300 hover:scale-[1.05] hover:bg-[#2b5487] hover:shadow-lg hover:shadow-blue-500/25 active:scale-95"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="inline-block rounded-lg bg-[#3769a6] px-6 py-2.5 text-xs font-bold text-white transition-all duration-300 hover:scale-[1.05] hover:bg-[#2b5487] hover:shadow-lg hover:shadow-blue-500/25 active:scale-95"
                        >
                            Daftar
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Hero Section (Full viewport height, centered content, premium layout) -->
        <section
            class="relative z-10 flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-b from-[#f4f7fe]/70 via-white to-white px-6 pt-32 pb-24 text-center"
        >
            <!-- Tech Dotted Grid Overlay -->
            <div
                class="pointer-events-none absolute inset-0 z-0 bg-[radial-gradient(#cbd5e1_1.2px,transparent_1.2px)] [mask-image:radial-gradient(ellipse_60%_60%_at_50%_40%,#000_60%,transparent_100%)] [background-size:24px_24px] opacity-40"
            ></div>

            <!-- Floating Code Syntax Tags (Styled as glassmorphic editor stickers) -->
            <div
                class="pointer-events-none absolute inset-0 z-0 overflow-hidden select-none"
            >
                <div
                    class="absolute top-[20%] left-[10%] hidden animate-float-slow rounded-2xl border border-blue-100/80 bg-blue-50/60 px-3.5 py-2 font-mono text-sm text-blue-900/70 backdrop-blur-[2px] lg:block"
                >
                    &lt;div class="citech"&gt;
                </div>
                <div
                    class="absolute top-[64%] left-[16%] hidden animate-float-delayed rounded-xl border border-slate-200/80 bg-slate-50/60 px-3.5 py-1.5 font-mono text-xs text-slate-700/80 backdrop-blur-[2px] lg:block"
                >
                    const year = 2026;
                </div>
                <div
                    class="absolute top-[28%] right-[12%] hidden animate-float-slow rounded-2xl border border-amber-100/80 bg-amber-50/60 px-3.5 py-2 font-mono text-xs text-amber-800/80 backdrop-blur-[2px] lg:block"
                >
                    color: "#eab308"
                </div>
                <div
                    class="absolute top-[58%] right-[10%] hidden animate-float-delayed rounded-2xl border border-blue-100/80 bg-blue-50/60 px-3.5 py-2 font-mono text-sm text-blue-900/70 backdrop-blur-[2px] lg:block"
                >
                    design: "premium"
                </div>
                <div
                    class="absolute top-[42%] left-[6%] hidden animate-float-delayed rounded-2xl border border-purple-100/80 bg-purple-50/60 px-3.5 py-2 font-mono text-xs text-purple-900/70 backdrop-blur-[2px] lg:block"
                >
                    &lt;template&gt;
                </div>
                <div
                    class="absolute top-[47%] right-[6%] hidden animate-float-slow rounded-2xl border border-purple-100/80 bg-purple-50/60 px-3.5 py-2 font-mono text-xs text-purple-900/70 backdrop-blur-[2px] lg:block"
                >
                    &lt;/template&gt;
                </div>
            </div>

            <!-- Two Pills in Header -->
            <div
                class="animate-hero-pills mb-8 flex flex-wrap items-center justify-center gap-3"
            >
                <!-- Pill 1 -->
                <div
                    class="inline-flex items-center space-x-2 rounded-full border border-purple-100 bg-purple-50 px-4 py-1.5 text-xs font-semibold text-purple-700 shadow-sm transition duration-300 hover:scale-105"
                >
                    <Globe class="animate-spin-slow h-3.5 w-3.5" />
                    <span>National Competition</span>
                </div>
                <!-- Pill 2 -->
                <div
                    class="inline-flex items-center rounded-full border border-slate-200/80 bg-slate-50 px-4 py-1.5 text-xs font-semibold text-slate-600 shadow-sm transition duration-300 hover:scale-105"
                >
                    <span>UKM LAOS UNEJ</span>
                </div>
            </div>

            <!-- Main Title with premium scaling/weight and gradient backdrop aura -->
            <div
                class="animate-hero-title relative z-10 mx-auto mb-6 max-w-5xl"
            >
                <h1
                    class="text-shadow-blue cursor-default text-5xl leading-[1.08] font-extrabold tracking-tight text-[#0c2448] sm:text-8xl"
                >
                    Carnival Technology <br />
                    <span
                        class="text-shadow-gold relative inline-block text-[#eab308] transition-transform duration-300 hover:scale-105"
                        >UNEJ 2026</span
                    >
                </h1>
            </div>

            <!-- Description -->
            <p
                class="animate-hero-desc z-10 mx-auto mb-12 max-w-2xl text-sm leading-relaxed text-slate-500 sm:text-base"
            >
                Kompetisi Web Design tingkat nasional yang mempertemukan talenta
                digital muda untuk berkreasi, berinovasi, dan berkontribusi
                dalam perkembangan teknologi Indonesia.
            </p>

            <!-- Interactive Buttons with hover translate & scale -->
            <div
                class="animate-hero-buttons z-10 mb-20 flex w-full max-w-md flex-col items-center justify-center gap-4 sm:flex-row"
            >
                <Link
                    :href="route('register')"
                    class="flex w-full items-center justify-center rounded-xl bg-[#1e4d8c] px-10 py-4 text-sm font-bold text-white transition-all duration-300 hover:scale-[1.03] hover:bg-[#153a6b] hover:shadow-lg hover:shadow-blue-500/20 active:scale-95 sm:w-auto"
                >
                    <span>Daftar Sekarang</span>
                </Link>
                <a
                    href="#"
                    class="flex w-full items-center justify-center space-x-2 rounded-xl border border-slate-200 bg-white px-10 py-4 text-sm font-bold text-slate-700 shadow-sm transition-all duration-300 hover:scale-[1.03] hover:bg-slate-50 active:scale-95 sm:w-auto"
                >
                    <FileText class="h-4 w-4 text-slate-400" />
                    <span>Guidebook</span>
                </a>
            </div>

            <!-- Double down arrow anchor pointing to about section -->
            <a
                href="#tentang"
                class="group animate-hero-indicator absolute bottom-10 left-1/2 z-10 flex -translate-x-1/2 flex-col items-center space-y-1 text-slate-400 transition-colors duration-300 hover:text-blue-900"
            >
                <span
                    class="text-xs font-bold tracking-widest uppercase opacity-70 transition-opacity duration-300 group-hover:opacity-100"
                    >Selengkapnya</span
                >
                <ChevronDown class="h-5 w-5 animate-bounce" />
            </a>
        </section>

        <!-- Tentang Citech Section (Seamless gradient from bottom of hero) -->
        <section
            id="tentang"
            class="relative overflow-hidden bg-gradient-to-b from-white via-[#f4f7fe] to-white py-32"
        >
            <!-- Background Glow Blobs -->
            <div
                class="pointer-events-none absolute top-[10%] right-[-5%] z-0 h-[350px] w-[350px] rounded-full bg-blue-300/15 blur-[100px]"
            ></div>
            <div
                class="pointer-events-none absolute bottom-[5%] left-[-5%] z-0 h-[350px] w-[350px] rounded-full bg-purple-300/10 blur-[100px]"
            ></div>

            <div class="relative z-10 mx-auto max-w-7xl px-6">
                <div
                    class="mb-20 transform text-center transition-all duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)]"
                    :class="[
                        revealedSections.tentang
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-16 opacity-0',
                    ]"
                >
                    <span
                        class="text-xs font-extrabold tracking-widest text-blue-600 uppercase"
                        >Tentang Event</span
                    >
                    <h2
                        class="mt-2 text-3xl font-extrabold text-blue-900 sm:text-4xl"
                    >
                        Tentang Citech
                    </h2>
                </div>

                <!-- Main Glass Card with hover lift and glow -->
                <div
                    class="relative mx-auto mb-20 max-w-4xl transform transition-all delay-[200ms] duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)]"
                    :class="[
                        revealedSections.tentang
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-16 opacity-0',
                    ]"
                >
                    <!-- Soft gradient shadow behind the card -->
                    <div
                        class="pointer-events-none absolute -inset-4 -z-10 rounded-[40px] bg-gradient-to-r from-blue-300/20 via-purple-300/15 to-blue-200/20 opacity-60 blur-[50px]"
                    ></div>
                    <div
                        class="rounded-[32px] border border-slate-200/50 bg-white/90 p-8 shadow-[0_15px_40px_-20px_rgba(0,0,0,0.06)] backdrop-blur-sm transition-all duration-500 hover:scale-[1.01] hover:shadow-[0_30px_70px_rgba(30,77,140,0.08)] md:p-14"
                    >
                        <h3
                            class="mb-6 text-center text-xl leading-snug font-bold text-slate-900 md:text-2xl"
                        >
                            Wadah talenta digital muda di Indonesia.
                        </h3>
                        <div
                            class="space-y-6 text-sm leading-relaxed text-slate-500 md:text-base"
                        >
                            <p>
                                CITECH (Carnival Technology) UNEJ merupakan
                                kompetisi Web Design tingkat nasional yang
                                diselenggarakan oleh UKM LAOS Universitas Jember
                                sebagai wadah pengembangan kreativitas dan
                                kemampuan mahasiswa jenjang D3, D4, dan S1 di
                                bidang teknologi digital.
                            </p>
                            <p>
                                Dengan target peserta dari kalangan mahasiswa
                                dan komunitas teknologi, kegiatan ini juga
                                menjadi peluang strategis bagi sponsor untuk
                                meningkatkan brand awareness serta menunjukkan
                                dukungan terhadap pengembangan talenta digital
                                muda.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 4 Feature Cards (Interactive lift and glow) -->
                <div
                    class="mx-auto grid max-w-6xl grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-4"
                >
                    <!-- Card 1 -->
                    <div
                        class="group flex transform flex-col items-start rounded-2xl border border-slate-100 bg-white p-7 shadow-sm transition-all delay-[300ms] duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-2 hover:border-blue-400/40 hover:shadow-xl"
                        :class="[
                            revealedSections.tentang
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-16 opacity-0',
                        ]"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-orange-600 transition-all duration-300 group-hover:scale-110 group-hover:bg-orange-100"
                        >
                            <Code class="h-5 w-5" />
                        </div>
                        <h4
                            class="mb-2 text-sm font-extrabold text-slate-900 transition-colors group-hover:text-blue-900"
                        >
                            Web Design
                        </h4>
                        <p class="text-xs leading-relaxed text-slate-400">
                            Kompetisi desain web tingkat nasional.
                        </p>
                    </div>

                    <!-- Card 2 -->
                    <div
                        class="group flex transform flex-col items-start rounded-2xl border border-slate-100 bg-white p-7 shadow-sm transition-all delay-[400ms] duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-2 hover:border-blue-400/40 hover:shadow-xl"
                        :class="[
                            revealedSections.tentang
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-16 opacity-0',
                        ]"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-orange-600 transition-all duration-300 group-hover:scale-110 group-hover:bg-orange-100"
                        >
                            <Users class="h-5 w-5" />
                        </div>
                        <h4
                            class="mb-2 text-sm font-extrabold text-slate-900 transition-colors group-hover:text-blue-900"
                        >
                            Mahasiswa D3/D4/S1
                        </h4>
                        <p class="text-xs leading-relaxed text-slate-400">
                            Terbuka untuk seluruh mahasiswa Indonesia.
                        </p>
                    </div>

                    <!-- Card 3 -->
                    <div
                        class="group flex transform flex-col items-start rounded-2xl border border-slate-100 bg-white p-7 shadow-sm transition-all delay-[500ms] duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-2 hover:border-blue-400/40 hover:shadow-xl"
                        :class="[
                            revealedSections.tentang
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-16 opacity-0',
                        ]"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-orange-600 transition-all duration-300 group-hover:scale-110 group-hover:bg-orange-100"
                        >
                            <Trophy class="h-5 w-5" />
                        </div>
                        <h4
                            class="mb-2 text-sm font-extrabold text-slate-900 transition-colors group-hover:text-blue-900"
                        >
                            Hadiah Menarik
                        </h4>
                        <p class="text-xs leading-relaxed text-slate-400">
                            Total hadiah jutaan rupiah & sertifikat.
                        </p>
                    </div>

                    <!-- Card 4 -->
                    <div
                        class="group flex transform flex-col items-start rounded-2xl border border-slate-100 bg-white p-7 shadow-sm transition-all delay-[600ms] duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-2 hover:border-blue-400/40 hover:shadow-xl"
                        :class="[
                            revealedSections.tentang
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-16 opacity-0',
                        ]"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-orange-600 transition-all duration-300 group-hover:scale-110 group-hover:bg-orange-100"
                        >
                            <Sparkles class="h-5 w-5" />
                        </div>
                        <h4
                            class="mb-2 text-sm font-extrabold text-slate-900 transition-colors group-hover:text-blue-900"
                        >
                            Pengembangan Talenta
                        </h4>
                        <p class="text-xs leading-relaxed text-slate-400">
                            Wadah pengembangan kreativitas digital.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Countdown Section (Dynamic stage heading) -->
        <section
            id="countdown"
            class="bg-gradient-to-b from-white via-slate-50/50 to-white py-24 text-center"
        >
            <div
                class="transform transition-all duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)]"
                :class="[
                    revealedSections.countdown
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-16 opacity-0',
                ]"
            >
                <span
                    class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                    >Hitung Mundur</span
                >
                <h2 class="mt-2 mb-12 text-3xl font-extrabold text-[#0c2448]">
                    Menuju {{ activeStageName }}
                </h2>
            </div>

            <!-- Countdown Cards -->
            <div
                class="mx-auto flex max-w-4xl items-center justify-center space-x-3 px-6 sm:space-x-6"
            >
                <!-- Days -->
                <div
                    class="group flex w-24 transform flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all delay-[200ms] duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)] hover:border-amber-400/50 hover:shadow-md sm:w-36 sm:p-9"
                    :class="[
                        revealedSections.countdown
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-16 opacity-0',
                    ]"
                >
                    <span
                        class="mb-2 text-4xl font-extrabold tracking-tight text-amber-500 transition-transform group-hover:scale-105 sm:text-6xl"
                        >{{ String(days).padStart(2, '0') }}</span
                    >
                    <span class="text-xs font-bold text-slate-400 uppercase"
                        >Hari</span
                    >
                </div>
                <!-- Hours -->
                <div
                    class="group flex w-24 transform flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all delay-[300ms] duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)] hover:border-amber-400/50 hover:shadow-md sm:w-36 sm:p-9"
                    :class="[
                        revealedSections.countdown
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-16 opacity-0',
                    ]"
                >
                    <span
                        class="mb-2 text-4xl font-extrabold tracking-tight text-amber-500 transition-transform group-hover:scale-105 sm:text-6xl"
                        >{{ String(hours).padStart(2, '0') }}</span
                    >
                    <span class="text-xs font-bold text-slate-400 uppercase"
                        >Jam</span
                    >
                </div>
                <!-- Minutes -->
                <div
                    class="group flex w-24 transform flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all delay-[400ms] duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)] hover:border-amber-400/50 hover:shadow-md sm:w-36 sm:p-9"
                    :class="[
                        revealedSections.countdown
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-16 opacity-0',
                    ]"
                >
                    <span
                        class="mb-2 text-4xl font-extrabold tracking-tight text-amber-500 transition-transform group-hover:scale-105 sm:text-6xl"
                        >{{ String(minutes).padStart(2, '0') }}</span
                    >
                    <span class="text-xs font-bold text-slate-400 uppercase"
                        >Menit</span
                    >
                </div>
                <!-- Seconds -->
                <div
                    class="group flex w-24 transform flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all delay-[500ms] duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)] hover:border-amber-400/50 hover:shadow-md sm:w-36 sm:p-9"
                    :class="[
                        revealedSections.countdown
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-16 opacity-0',
                    ]"
                >
                    <span
                        class="mb-2 text-4xl font-extrabold tracking-tight text-amber-500 transition-transform group-hover:scale-105 sm:text-6xl"
                        >{{ String(seconds).padStart(2, '0') }}</span
                    >
                    <span class="text-xs font-bold text-slate-400 uppercase"
                        >Detik</span
                    >
                </div>
            </div>
        </section>

        <!-- Timeline Section (Redesigned: Larger, interactive, and synced with DB) -->
        <section
            id="timeline"
            class="border-t border-slate-100 bg-gradient-to-b from-white via-[#f8faff] to-white py-32"
        >
            <div class="mx-auto max-w-5xl px-6">
                <div
                    class="mb-24 transform text-center transition-all duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)]"
                    :class="[
                        revealedSections.timeline
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-16 opacity-0',
                    ]"
                >
                    <span
                        class="text-xs font-extrabold tracking-widest text-blue-600 uppercase"
                        >Rangkaian Acara</span
                    >
                    <h2 class="mt-2 text-4xl font-extrabold text-[#0c2448]">
                        Timeline CITECH
                    </h2>
                    <p class="mt-3 text-sm text-slate-400">
                        Jadwal kegiatan perlombaan resmi disinkronkan dari
                        database
                    </p>
                </div>

                <!-- Vertical Alternating Timeline -->
                <div class="relative">
                    <!-- Central line -->
                    <div
                        class="absolute left-6 h-full w-[4px] -translate-x-1/2 transform bg-[#1e4d8c]/15 md:left-1/2"
                    ></div>

                    <!-- Timeline Loop -->
                    <div class="relative z-10 space-y-16">
                        <div
                            v-for="(item, index) in formattedTimelineItems"
                            :key="index"
                            class="relative flex transform flex-col items-start transition-all duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)] md:flex-row md:items-center md:justify-between"
                            :class="[
                                revealedSections.timeline
                                    ? 'translate-x-0 opacity-100'
                                    : item.alignLeft
                                      ? '-translate-x-16 opacity-0'
                                      : 'translate-x-16 opacity-0',
                                { 'md:flex-row-reverse': item.alignLeft },
                            ]"
                            :style="{
                                transitionDelay: revealedSections.timeline
                                    ? `${200 + index * 150}ms`
                                    : '0ms',
                            }"
                        >
                            <!-- Line dot connector with pulse ring on active -->
                            <div
                                class="absolute top-6 left-6 z-20 flex h-6 w-6 -translate-x-1/2 transform items-center justify-center rounded-full border-4 bg-white transition-all duration-500 md:left-1/2"
                                :class="[
                                    item.isActive
                                        ? 'scale-125 border-amber-500 shadow-md ring-4 shadow-amber-500/35 ring-amber-500/25'
                                        : 'border-[#1e4d8c]',
                                ]"
                            >
                                <span
                                    v-if="item.isActive"
                                    class="h-2.5 w-2.5 animate-pulse rounded-full bg-amber-500"
                                ></span>
                                <span
                                    v-else
                                    class="h-2 w-2 rounded-full bg-[#1e4d8c]/80"
                                ></span>
                            </div>

                            <!-- Date Badge Column (Pill with solid highlight) -->
                            <div
                                class="flex w-full justify-start pr-4 pl-16 md:w-1/2 md:px-12"
                                :class="[
                                    item.alignLeft
                                        ? 'md:justify-start'
                                        : 'md:justify-end',
                                ]"
                            >
                                <div
                                    class="rounded-xl px-5 py-2.5 text-center text-xs font-bold tracking-wide shadow-md transition duration-300 sm:rounded-2xl sm:px-8 sm:py-4 sm:text-sm"
                                    :class="[
                                        item.isActive
                                            ? 'bg-amber-500 text-white shadow-amber-500/20 hover:scale-105 hover:bg-amber-600'
                                            : 'bg-[#1e4d8c] text-white shadow-blue-900/10 hover:scale-105 hover:bg-[#153a6b]',
                                    ]"
                                >
                                    {{ item.date }}
                                </div>
                            </div>

                            <!-- Content details panel -->
                            <div
                                class="mt-3 w-full pr-4 pl-16 text-left md:mt-0 md:w-1/2 md:px-12"
                                :class="[
                                    item.alignLeft
                                        ? 'md:text-right'
                                        : 'md:text-left',
                                ]"
                            >
                                <div
                                    class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-blue-300/60 hover:shadow-lg"
                                    :class="[
                                        item.isActive
                                            ? 'border-amber-300 bg-amber-500/[0.01] shadow-md ring-2 ring-amber-400/50'
                                            : '',
                                    ]"
                                >
                                    <!-- Badge status -->
                                    <div
                                        v-if="item.isActive"
                                        class="mb-2.5 inline-flex items-center space-x-1.5 rounded-full bg-amber-100 px-3 py-0.5 text-[10px] font-bold tracking-wider text-amber-800 uppercase"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 animate-ping rounded-full bg-amber-600"
                                        ></span>
                                        <span>Berlangsung</span>
                                    </div>
                                    <h4
                                        class="mb-2 text-xl font-extrabold text-slate-900"
                                    >
                                        {{ item.tahap }}
                                    </h4>
                                    <p
                                        class="text-sm leading-relaxed text-slate-500"
                                    >
                                        {{ item.desc }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sponsor Section (Seamless Infinite Horizontal Marquee moving Right-to-Left) -->
        <section
            id="sponsor"
            class="overflow-hidden border-t border-slate-100 bg-[#fdfdfd] py-28"
        >
            <div
                class="mx-auto max-w-7xl transform px-6 text-center transition-all duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)]"
                :class="[
                    revealedSections.sponsor
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-16 opacity-0',
                ]"
            >
                <span
                    class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                    >Sponsored By</span
                >
                <h2
                    class="mt-2 mb-16 text-2xl font-extrabold text-[#0c2448] md:text-3xl"
                >
                    Didukung oleh brand & mitra teknologi terbaik
                </h2>
            </div>

            <!-- Infinite Horizontal Scrolling Wrapper -->
            <div
                class="mask-gradient relative w-full transform overflow-hidden py-6 transition-all delay-[300ms] duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)]"
                :class="[
                    revealedSections.sponsor
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-16 opacity-0',
                ]"
            >
                <div class="flex animate-marquee whitespace-nowrap">
                    <!-- If we have dynamic sponsors from the database, render them -->
                    <template v-if="sponsors && sponsors.length > 0">
                        <!-- Group of logos (1st half) -->
                        <div
                            class="flex shrink-0 items-center space-x-20 pr-20"
                        >
                            <template
                                v-for="(sponsor, index) in duplicatedSponsors"
                                :key="'sp-1-' + index"
                            >
                                <a
                                    v-if="sponsor.link_sponsor"
                                    :aria-label="sponsor.nama_sponsor"
                                    :href="sponsor.link_sponsor"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-block transition-all duration-300 hover:scale-110"
                                    :title="sponsor.nama_sponsor"
                                >
                                    <img
                                        :src="`/storage/${sponsor.logo_sponsor}`"
                                        :alt="sponsor.nama_sponsor"
                                        class="h-10 w-auto object-contain opacity-60 grayscale filter transition-all duration-300 hover:opacity-100 hover:grayscale-0 sm:h-12"
                                    />
                                </a>
                                <div
                                    v-else
                                    class="inline-block cursor-default transition-all duration-300 hover:scale-110"
                                    :title="sponsor.nama_sponsor"
                                >
                                    <img
                                        :src="`/storage/${sponsor.logo_sponsor}`"
                                        :alt="sponsor.nama_sponsor"
                                        class="h-10 w-auto object-contain opacity-60 grayscale filter transition-all duration-300 hover:opacity-100 hover:grayscale-0 sm:h-12"
                                    />
                                </div>
                            </template>
                        </div>
                        <!-- Group of logos (2nd half for seamless transition) -->
                        <div
                            class="flex shrink-0 items-center space-x-20 pr-20"
                            aria-hidden="true"
                        >
                            <template
                                v-for="(sponsor, index) in duplicatedSponsors"
                                :key="'sp-2-' + index"
                            >
                                <a
                                    v-if="sponsor.link_sponsor"
                                    :aria-label="sponsor.nama_sponsor"
                                    :href="sponsor.link_sponsor"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-block transition-all duration-300 hover:scale-110"
                                    :title="sponsor.nama_sponsor"
                                >
                                    <img
                                        :src="`/storage/${sponsor.logo_sponsor}`"
                                        :alt="sponsor.nama_sponsor"
                                        class="h-10 w-auto object-contain opacity-60 grayscale filter transition-all duration-300 hover:opacity-100 hover:grayscale-0 sm:h-12"
                                    />
                                </a>
                                <div
                                    v-else
                                    class="inline-block cursor-default transition-all duration-300 hover:scale-110"
                                    :title="sponsor.nama_sponsor"
                                >
                                    <img
                                        :src="`/storage/${sponsor.logo_sponsor}`"
                                        :alt="sponsor.nama_sponsor"
                                        class="h-10 w-auto object-contain opacity-60 grayscale filter transition-all duration-300 hover:opacity-100 hover:grayscale-0 sm:h-12"
                                    />
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Fallback to static text brands if no sponsors exist in database -->
                    <template v-else>
                        <!-- Group of logos (1st half) -->
                        <div
                            class="flex shrink-0 items-center space-x-24 pr-24"
                        >
                            <span
                                class="cursor-default text-2xl font-black tracking-widest text-slate-300 transition duration-300 select-none hover:text-[#1e4d8c]"
                                >TECHCORP</span
                            >
                            <span
                                class="cursor-default text-2xl font-black tracking-widest text-slate-300 transition duration-300 select-none hover:text-[#1e4d8c]"
                                >NEXALABS</span
                            >
                            <span
                                class="cursor-default text-2xl font-black tracking-widest text-slate-300 transition duration-300 select-none hover:text-[#1e4d8c]"
                                >PIXELHUB</span
                            >
                            <span
                                class="cursor-default text-2xl font-black tracking-widest text-slate-300 transition duration-300 select-none hover:text-[#1e4d8c]"
                                >CLOUDIFY</span
                            >
                            <span
                                class="cursor-default text-2xl font-black tracking-widest text-slate-300 transition duration-300 select-none hover:text-[#1e4d8c]"
                                >DEVFORGE</span
                            >
                            <span
                                class="cursor-default text-2xl font-black tracking-widest text-slate-300 transition duration-300 select-none hover:text-[#1e4d8c]"
                                >BRANDIO</span
                            >
                        </div>
                        <!-- Group of logos (2nd half for seamless transition) -->
                        <div
                            class="flex shrink-0 items-center space-x-24 pr-24"
                            aria-hidden="true"
                        >
                            <span
                                class="cursor-default text-2xl font-black tracking-widest text-slate-300 transition duration-300 select-none hover:text-[#1e4d8c]"
                                >TECHCORP</span
                            >
                            <span
                                class="cursor-default text-2xl font-black tracking-widest text-slate-300 transition duration-300 select-none hover:text-[#1e4d8c]"
                                >NEXALABS</span
                            >
                            <span
                                class="cursor-default text-2xl font-black tracking-widest text-slate-300 transition duration-300 select-none hover:text-[#1e4d8c]"
                                >PIXELHUB</span
                            >
                            <span
                                class="cursor-default text-2xl font-black tracking-widest text-slate-300 transition duration-300 select-none hover:text-[#1e4d8c]"
                                >CLOUDIFY</span
                            >
                            <span
                                class="cursor-default text-2xl font-black tracking-widest text-slate-300 transition duration-300 select-none hover:text-[#1e4d8c]"
                                >DEVFORGE</span
                            >
                            <span
                                class="cursor-default text-2xl font-black tracking-widest text-slate-300 transition duration-300 select-none hover:text-[#1e4d8c]"
                                >BRANDIO</span
                            >
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section
            id="faq"
            class="border-t border-slate-100 bg-[#f9fbff]/40 py-28"
        >
            <div class="mx-auto max-w-3xl px-6">
                <div
                    class="mb-16 transform text-center transition-all duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)]"
                    :class="[
                        revealedSections.faq
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-16 opacity-0',
                    ]"
                >
                    <span
                        class="text-xs font-extrabold tracking-widest text-blue-600 uppercase"
                        >FAQ</span
                    >
                    <h2 class="mt-2 text-3xl font-extrabold text-[#0c2448]">
                        Pertanyaan yang sering diajukan
                    </h2>
                </div>

                <!-- FAQ Accordion List with hover and smooth layout changes -->
                <Accordion type="single" class="space-y-4 w-full" collapsible>
                    <AccordionItem
                        v-for="(faq, index) in faqs"
                        :key="index"
                        :value="`faq-${index}`"
                        class="transform overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all duration-[1500ms] ease-[cubic-bezier(0.16,1,0.3,1)]"
                        :class="[
                            revealedSections.faq
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-16 opacity-0',
                        ]"
                        :style="{
                            transitionDelay: revealedSections.faq
                                ? `${200 + index * 120}ms`
                                : '0ms',
                        }"
                    >
                        <AccordionTrigger
                            class="flex w-full items-center justify-between px-6 py-5 text-left transition duration-300 hover:bg-slate-50/50 hover:no-underline [&[data-state=open]>svg]:text-blue-500 focus:outline-none"
                        >
                            <span
                                class="text-sm font-extrabold text-slate-800 md:text-base"
                                >{{ faq.q }}</span
                            >
                            <template #icon>
                                <ChevronDown
                                    class="h-5 w-5 text-slate-400 transition-transform duration-300"
                                />
                            </template>
                        </AccordionTrigger>
                        <AccordionContent
                            class="border-t border-slate-100/80 px-6 pt-3 pb-5 text-xs leading-relaxed text-slate-500 md:text-sm"
                        >
                            {{ faq.a }}
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>
        </section>

        <!-- Footer Section -->
        <footer
            class="relative overflow-hidden bg-[#0b0f19] py-20 text-slate-400"
        >
            <!-- Subtle glow in footer -->
            <div
                class="pointer-events-none absolute -right-32 -bottom-32 h-80 w-80 rounded-full bg-blue-500/5 blur-3xl"
            ></div>

            <div class="relative z-10 mx-auto max-w-7xl px-6">
                <div
                    class="mb-8 grid grid-cols-1 gap-12 border-b border-slate-800/80 pb-16 md:grid-cols-4"
                >
                    <!-- Info Column -->
                    <div class="space-y-6 md:col-span-2">
                        <div
                            class="group flex cursor-pointer items-center space-x-3 transition-all duration-300"
                        >
                            <img
                                src="/assets/logo-citech.png"
                                alt="Logo CITECH"
                                class="h-10 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
                            />
                            <span
                                class="text-lg font-extrabold tracking-tight text-white"
                                >CITECH UNEJ</span
                            >
                        </div>
                        <p
                            class="max-w-sm text-xs leading-relaxed text-slate-400"
                        >
                            Carnival Technology — kompetisi Web Design nasional
                            yang diselenggarakan UKM LAOS Universitas Jember.
                        </p>
                        <div class="flex space-x-3.5 pt-2">
                            <!-- Social Media Links (Simulated inline SVGs for stability) -->
                            <a
                                href="#"
                                class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-800 text-slate-400 transition-all duration-300 hover:scale-110 hover:border-slate-600 hover:bg-slate-800 hover:text-white"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <rect
                                        x="2"
                                        y="2"
                                        width="20"
                                        height="20"
                                        rx="5"
                                        ry="5"
                                    ></rect>
                                    <path
                                        d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"
                                    ></path>
                                    <line
                                        x1="17.5"
                                        y1="6.5"
                                        x2="17.51"
                                        y2="6.5"
                                    ></line>
                                </svg>
                            </a>
                            <a
                                href="#"
                                class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-800 text-slate-400 transition-all duration-300 hover:scale-110 hover:border-slate-600 hover:bg-slate-800 hover:text-white"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"
                                    ></path>
                                    <rect
                                        x="2"
                                        y="9"
                                        width="4"
                                        height="12"
                                    ></rect>
                                    <circle cx="4" cy="4" r="2"></circle>
                                </svg>
                            </a>
                            <a
                                href="#"
                                class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-800 text-slate-400 transition-all duration-300 hover:scale-110 hover:border-slate-600 hover:bg-slate-800 hover:text-white"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"
                                    ></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Nav Column -->
                    <div>
                        <h5
                            class="mb-5 text-xs font-bold tracking-wider text-white uppercase"
                        >
                            Navigasi
                        </h5>
                        <ul class="space-y-3.5 text-xs">
                            <li>
                                <a
                                    href="#tentang"
                                    class="transition hover:text-white hover:underline"
                                    >Tentang</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#timeline"
                                    class="transition hover:text-white hover:underline"
                                    >Timeline</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#sponsor"
                                    class="transition hover:text-white hover:underline"
                                    >Event</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#faq"
                                    class="transition hover:text-white hover:underline"
                                    >FAQ</a
                                >
                            </li>
                        </ul>
                    </div>

                    <!-- Contact Column -->
                    <div>
                        <h5
                            class="mb-5 text-xs font-bold tracking-wider text-white uppercase"
                        >
                            Kontak
                        </h5>
                        <ul class="space-y-3.5 text-xs">
                            <li>UKM LAOS UNEJ</li>
                            <li>Universitas Jember</li>
                            <li class="text-blue-400 hover:underline">
                                <a href="mailto:citech@unej.ac.id"
                                    >citech@unej.ac.id</a
                                >
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom Footer -->
                <div
                    class="flex flex-col items-center justify-between text-[11px] text-slate-600 md:flex-row"
                >
                    <p>&copy; 2026 CITECH UNEJ. All rights reserved.</p>
                    <p class="mt-2 md:mt-0">
                        Made with care by UKM LAOS Universitas Jember.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes pageFade {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
.animate-page-fade {
    animation: pageFade 1.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(24px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
.animate-navbar {
    animation: slideDown 1.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.animate-nav-item {
    animation: fadeInUp 1.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}
.animate-hero-pills {
    animation: fadeInUp 1.5s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards;
    opacity: 0;
}
.animate-hero-title {
    animation: fadeInUp 1.5s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards;
    opacity: 0;
}
.animate-hero-desc {
    animation: fadeInUp 1.5s cubic-bezier(0.16, 1, 0.3, 1) 0.45s forwards;
    opacity: 0;
}
.animate-hero-buttons {
    animation: fadeInUp 1.5s cubic-bezier(0.16, 1, 0.3, 1) 0.6s forwards;
    opacity: 0;
}
.animate-hero-indicator {
    animation: fadeIn 2s ease-out 1.4s forwards;
    opacity: 0;
}
.animate-spin-slow {
    animation: spin 8s linear infinite;
}
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
.delay-100 {
    animation-delay: 0.1s;
}
.delay-200 {
    animation-delay: 0.2s;
}
.delay-300 {
    animation-delay: 0.3s;
}
.delay-400 {
    animation-delay: 0.4s;
}
.delay-500 {
    animation-delay: 0.5s;
}
.text-shadow-blue {
    text-shadow:
        0 4px 20px rgba(12, 36, 72, 0.2),
        0 8px 32px rgba(12, 36, 72, 0.1),
        0 1px 2px rgba(12, 36, 72, 0.06);
}
.text-shadow-gold {
    text-shadow:
        0 4px 20px rgba(234, 179, 8, 0.35),
        0 8px 32px rgba(234, 179, 8, 0.18),
        0 1px 2px rgba(234, 179, 8, 0.12);
}
</style>
