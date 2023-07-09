<script>
export default {
    data: () => {
        return {
            googleRecaptchaPublicKey: import.meta.env.VITE_GOOGLE_RECAPTCHA_PUBLIC_KEY,
        };
    },

    methods: {
        recaptchaInvalid() { throw { response: { data: { message: 'Favor interagir com o reCAPTCHA!' } } }; }
    },

    mounted() {
        const recaptchaScript = document.createElement('script');
        recaptchaScript.setAttribute('src', 'https://www.google.com/recaptcha/api.js');
        document.head.appendChild(recaptchaScript);
    }
};
</script>
