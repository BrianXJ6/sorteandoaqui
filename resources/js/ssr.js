import Vue from 'vue';
import { createInertiaApp } from '@inertiajs/vue2';
import createServer from '@inertiajs/vue2/server';
import { createRenderer } from 'vue-server-renderer';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { vMaska } from "maska";

// Plugins
import './Plugins/Snotify';
import './Plugins/ShowErrors';
import './Plugins/Helpers/PrepareFormData';
import './Plugins/Helpers/ClearFormData';

// Layouts
import AppLayout from './Layouts/AppLayout.vue';
import WebLayout from './Layouts/WebLayout.vue';
import UserLayout from './Layouts/UserLayout.vue';
import AdminLayout from './Layouts/AdminLayout.vue';

createServer(page =>
    createInertiaApp({
        page,
        title: title => title || import.meta.env.VITE_APP_NAME,
        render: createRenderer().renderToString,
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
            Vue.use(plugin).use(ZiggyVue).directive("maska", vMaska);
            new Vue({ render: h => h(App, props) }).$mount(el);
        },
    })
);
