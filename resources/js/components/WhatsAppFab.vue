<script setup>
import { computed } from 'vue';

const props = defineProps({
    phoneNumber: {
        type: String,
        default: '6282257157550',
    },
    message: {
        type: String,
        default: 'Halo Humas CITECH, saya ingin bertanya...',
    },
});

const waLink = computed(() => {
    const base = `https://wa.me/${props.phoneNumber}`;

    return props.message ? `${base}?text=${encodeURIComponent(props.message)}` : base;
});
</script>

<template>
    <a
        :href="waLink"
        target="_blank"
        rel="noopener noreferrer"
        class="fixed bottom-6 right-6 z-[9999] flex items-center group transition-all duration-300 md:bottom-8 md:right-8"
        aria-label="Hubungi Humas CITECH via WhatsApp"
    >
        <!-- Label/Tooltip that slides out on hover -->
        <span
            class="mr-3 scale-0 origin-right transition-all duration-300 opacity-0 group-hover:scale-100 group-hover:opacity-100 bg-white text-slate-800 text-xs font-black px-3.5 py-2.5 rounded-xl shadow-[0_10px_30px_rgba(0,0,0,0.08)] border border-slate-100 whitespace-nowrap select-none pointer-events-none"
        >
            Hubungi Humas CITECH
        </span>

        <!-- The circular button with ring animation -->
        <div
            class="relative flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-[0_8px_24px_rgba(37,211,102,0.3)] transition-all duration-300 hover:scale-110 active:scale-95 animate-pulse-ring"
        >
            <!-- WhatsApp SVG Icon -->
            <svg
                viewBox="0 0 24 24"
                class="h-7 w-7 fill-current"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.513 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.625 1.45 5.436-.003 9.803-4.375 9.806-9.814.001-2.63-1.019-5.101-2.873-6.958C16.3 2.016 13.808.997 11.995.997 6.553.997 2.185 5.367 2.182 10.8c-.002 1.554.409 3.076 1.192 4.417l-.953 3.477 3.562-.934zm11.367-7.404c-.31-.155-1.838-.907-2.122-1.01-.284-.104-.49-.156-.697.155-.207.311-.801 1.01-.981 1.217-.18.208-.362.233-.672.078-1.55-.777-2.586-1.378-3.626-3.178-.273-.473.272-.44.78-1.45.084-.17.042-.317-.02-.472-.063-.156-.49-1.191-.672-1.631-.177-.428-.358-.37-.49-.376l-.417-.008c-.145 0-.38.054-.578.271-.199.217-.758.74-0.758 1.803s.774 2.088.882 2.234c.109.145 1.522 2.324 3.687 3.257.515.221.917.354 1.23.454.517.164.988.141 1.361.085.417-.063 1.838-.752 2.096-1.48.259-.729.259-1.353.182-1.48-.077-.127-.284-.207-.594-.362z"
                />
            </svg>
        </div>
    </a>
</template>

<style scoped>
@keyframes pulse-ring {
    0% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.6);
    }
    70% {
        box-shadow: 0 0 0 14px rgba(37, 211, 102, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
    }
}
.animate-pulse-ring {
    animation: pulse-ring 2.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}
</style>
