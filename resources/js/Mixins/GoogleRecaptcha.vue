<script>
import { loadScript } from "vue-plugin-load-script";
export default {
    data: () => {
        return {
            googleRecaptchaPublicKey: import.meta.env.VITE_GOOGLE_RECAPTCHA_PUBLIC_KEY,
        };
    },

    methods: {
        recaptchaInvalid() { throw { response: { data: { message: 'Favor interagir com o reCAPTCHA!' } } }; }
    },

    created() {
        loadScript('https://www.google.com/recaptcha/api.js')
            .then(() => {
                if (grecaptcha.hasOwnProperty('render'))
                    grecaptcha.render('g-recaptcha');
            });
    }
};
</script>
