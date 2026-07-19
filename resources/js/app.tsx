import '../css/app.css';
import { createInertiaApp, ResolvedComponent } from '@inertiajs/react';
import { createRoot, hydrateRoot } from 'react-dom/client';
// import './echo';

const appName = import.meta.env.VITE_APP_NAME || 'Sorteando Aqui';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const pages = import.meta.glob<ResolvedComponent>("./Pages/**/*.tsx");
        return pages[`./Pages/${name}.tsx`]();
    },
    setup({ el, App, props }) {
        if (import.meta.env.SSR) {
            hydrateRoot(el, <App {...props} />);
            return;
        }

        createRoot(el).render(<App {...props} />);
    }
});
