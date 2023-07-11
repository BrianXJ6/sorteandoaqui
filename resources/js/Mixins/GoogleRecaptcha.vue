<script>
export default {
    data: () => {
        return {
            recaptchaEnabled: (import.meta.env.VITE_GOOGLE_RECAPTCHA_ENABLED === 'true'),
            googleRecaptchaPublicKey: import.meta.env.VITE_GOOGLE_RECAPTCHA_PUBLIC_KEY,
        };
    },

    methods: {
        validateRecaptcha() {
            if (!this.recaptchaEnabled) return;

            const recaptcha = grecaptcha.getResponse();
            if (!recaptcha) throw { response: { data: { message: 'Favor interagir com o reCAPTCHA!' } } };

            return recaptcha;
        },

        recaptchaReset() { if (this.recaptchaEnabled) grecaptcha.reset() },
    },

    mounted() {
        if (!this.recaptchaEnabled) return;

        const recaptchaScript = document.createElement('script');
        recaptchaScript.setAttribute('src', 'https://www.google.com/recaptcha/api.js');
        document.head.appendChild(recaptchaScript);
    }
};
</script>
