import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { toast } from 'vue-sonner';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const showFlashToast = (page) => {
    const flash = page?.props?.flash;

    if (flash) {
        if (flash.success) {
            toast.success(flash.success);
            page.props.flash.success = null;
        }

        if (flash.error) {
            toast.error(flash.error);
            page.props.flash.error = null;
        }

        if (flash.toast) {
            const t = flash.toast;

            if (t?.type && t?.message) {
                toast[t.type](t.message);
            } else if (typeof t === 'string') {
                toast.success(t);
            }

            page.props.flash.toast = null;
        }
    }
};

router.on('finish', () => {
    showFlashToast(router.page);
});

router.on('error', (event) => {
    const errors = event.detail.errors;

    if (errors && Object.keys(errors).length > 0) {
        const firstError = Object.values(errors)[0];
        toast.error(typeof firstError === 'string' ? firstError : 'Gagal memproses data. Silakan periksa kembali input Anda.');
    } else {
        toast.error('Gagal melakukan tindakan. Silakan periksa kembali data Anda.');
    }
});

router.on('invalid', () => {
    toast.error('Terjadi kesalahan pada server. Silakan coba lagi.');
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.vue');
        const key = Object.keys(pages).find(
            (k) => k.toLowerCase() === `./pages/${name.toLowerCase()}.vue`,
        );

        if (!key) {
            throw new Error(`Page not found: ${name}`);
        }

        return typeof pages[key] === 'function' ? pages[key]() : pages[key];
    },
    setup({ el, App, props, plugin }) {
        showFlashToast(props.initialPage);

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
