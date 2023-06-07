/**
 * |--------------------------------------------------------------------------
 * | ShowErrors config
 * |--------------------------------------------------------------------------
 */
import Vue from 'vue';

Vue.use({
    install: (Vue) => {
        // Show error notifications together with the vue-snotify plugin
        Vue.prototype.$showErrors = function (error) {
            // Clearing all notifications from the screen
            this.$snotify.clear();

            const { errors, message } = error.response.data;

            if (typeof errors === 'undefined' && typeof message === 'undefined') return error;

            if (typeof errors === 'undefined' && typeof message !== 'undefined') {
                this.$snotify.error(message);
                return error;
            }

            // Retrieving the form from which the request started
            const form = this.$el;

            // Clearing all fields containing the "is-invalid" class
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            Object.entries(errors).reverse().forEach(([key, messages]) => {
                // Recovering the failed field selector.
                // Adding "is-invalid" class to the failed field
                const selector = (key.indexOf('password') !== -1) ? form.querySelectorAll(`*[id*='-${key}']`) : form.querySelectorAll(`*[id$='-${key}']`);
                selector?.forEach(el => el.classList.add('is-invalid'));

                // Creating the content to be displayed in the notification
                let content = '<ul class="list-group list-group-flush">';
                messages.forEach(msg => content += `<li class="list-group-item bg-transparent text-light">${msg}</li>`);
                content += `</ul>`;

                // Triggering failure notification with callback to clear field containing "is-invalid" class
                this.$snotify
                    .html(content, { type: 'error', icon: false, timeout: 0 })
                    .on('hidden', () => selector?.forEach(el => el.classList.remove('is-invalid')));
            });

            return error;
        };
    }
});
