/**
 * |--------------------------------------------------------------------------
 * | Clear form data plugin
 * |--------------------------------------------------------------------------
 */
import Vue from 'vue';

Vue.use({
    install: (Vue) => {
        Vue.prototype.$clearForm = function (form, obj, except = []) {
            // Clearing all notifications from the screen
            this.$snotify.clear();

            // Reset Data Object Fields
            Object.entries(obj).forEach(([field]) => {
                if (!except.includes(field)) obj[field] = '';
            });

            // Reset form fields
            // Clearing all fields containing the "is-invalid" class
            form.reset();
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        };
    }
});
