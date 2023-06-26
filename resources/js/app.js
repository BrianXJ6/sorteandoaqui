import Vue from 'vue';
import { createInertiaApp } from '@inertiajs/vue2';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import PortalVue from 'portal-vue';
import { vMaska } from "maska";

// Plugins
import 'bootstrap';
import './Plugins/Axios';
import './Plugins/Snotify';
import './Plugins/ShowErrors';
import './Plugins/Helpers/PrepareFormData';
import './Plugins/Helpers/ClearFormData';

// Layouts
import AppLayout from './Layouts/AppLayout.vue';
import WebLayout from './Layouts/WebLayout.vue';
import UserLayout from './Layouts/UserLayout.vue';
import AdminLayout from './Layouts/AdminLayout.vue';

createInertiaApp({
    title: title => title || import.meta.env.VITE_APP_NAME,
    resolve: async name => {
        const pages = await import.meta.glob('./Pages/**/*.vue', { eager: true });
        const app = await pages[`./Pages/${name}.vue`];
        const layout = [AppLayout];

        // Preparing sub layout
        switch (true) {
            case name.startsWith('Web/'): layout.push(WebLayout); break;
            case name.startsWith('User/'): layout.push(UserLayout); break;
            case name.startsWith('Admin/'): layout.push(AdminLayout); break;
        }

        app.default.layout = app.default.layout || layout;

        return app;
    },
    setup({ el, App, props, plugin }) {
        Vue.use(plugin).use(ZiggyVue).use(PortalVue).directive("maska", vMaska);
        new Vue({ render: h => h(App, props) }).$mount(el);
    },
});
