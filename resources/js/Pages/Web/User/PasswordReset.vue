<template>
    <div class="container">
        <Head :title="title">
            <meta name="DC.title" :content="title" />
            <meta property="og:title" :content="title" />
            <meta name="abstract" :content="description" />
            <meta name="description" :content="description" />
            <meta property="og:description" :content="description" />
            <meta name="keywords" :content="keywords" />
            <meta property="og:url" :content="$page.url" />
            <meta property="og:image" content="/statics/images/cover.png" />
        </Head>
        <div class="card shadow mx-auto" style="max-width: 296px">
            <div class="card-header text-bg-primary fs-3" v-html="label" />
            <div class="card-body">
                <form @submit.prevent="passwordResetForm" autocomplete="off">
                    <div class="form-floating mb-3">
                        <input
                            id="passwordReset-email"
                            class="form-control"
                            type="email"
                            placeholder="E-mail"
                            maxlength="100"
                            required
                            v-model="passwordReset.email"
                        />
                        <label for="passwordReset-email">E-mail</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="passwordReset-password"
                            class="form-control"
                            type="password"
                            placeholder="Senha"
                            minlength="8"
                            required
                            v-model="passwordReset.password"
                        />
                        <label for="passwordReset-password">Senha</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="passwordReset-password_confirmation"
                            class="form-control"
                            type="password"
                            placeholder="Senha"
                            minlength="8"
                            required
                            v-model="passwordReset.password_confirmation"
                        />
                        <label for="passwordReset-password_confirmation">Repetir senha</label>
                    </div>
                    <div
                        id="g-recaptcha"
                        class="g-recaptcha"
                        :data-sitekey="googleRecaptchaPublicKey"
                    />
                    <div class="d-grid gap-2 d-flex justify-content-end align-items-center">
                        <div v-if="passwordResetLoader" class="spinner-border" />
                        <button
                            class="btn btn-primary"
                            type="submit"
                            :disabled="passwordResetLoader"
                            v-html="label"
                        />
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue2';
import GoogleRecaptcha from '../../../Mixins/GoogleRecaptcha.vue';
export default {
    components: { Head, Link },
    mixins: [GoogleRecaptcha],

    props: {
        title: String,
        description: String,
        keywords: String,
        label: String,
        email: String,
        token: { required: true, type: String },
    },

    data: (vm) => {
        return {
            passwordResetLoader: false,
            passwordReset: {
                token: vm.token,
                email: vm.email,
                password: '',
                password_confirmation: '',
                recaptcha: '',
            }
        };
    },

    methods: {
        async passwordResetForm(el) {
            this.passwordResetLoader = true;
            try {
                this.passwordReset.recaptcha = grecaptcha.getResponse();
                this.formValidation();
                const response = await axios.post(route('web.action.user.auth.password.update'), this.$prepareData(this.passwordReset));
                this.$clearForm(el.target, this.passwordReset);
                const { message, redirect } = response.data;
                this.$snotify.success(message).on('shown', () => this.$inertia.get(redirect));
            }
            catch (error) { this.$showErrors(error); }
            finally {
                this.passwordResetLoader = false;
                grecaptcha.reset();
            }
        },

        formValidation() {
            if (!this.passwordReset.recaptcha) this.recaptchaInvalid();

            if (this.passwordReset.password !== this.passwordReset.password_confirmation)
                throw { response: { data: { errors: { password: ['O campo senha de confirmação não confere.'] } } } };
        }
    },
}
</script>

<style scoped>
.g-recaptcha {
    transform: scale(0.87);
    transform-origin: 0 0;
}
</style>
