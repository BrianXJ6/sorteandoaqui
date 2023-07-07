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
                <form @submit.prevent="signInForm" autocomplete="off">
                    <div class="d-flex justify-content-around small">
                        <div class="form-check form-check-inline">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="op-signIn"
                                id="op-email"
                                value="email"
                                checked
                                @change="signIn.field = ''"
                                v-model="signIn.option"
                            />
                            <label class="form-check-label" for="op-email">E-mail</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="op-signIn"
                                id="op-phone"
                                value="phone"
                                @change="signIn.field = ''"
                                v-model="signIn.option"
                            />
                            <label class="form-check-label" for="op-phone">Telefone</label>
                        </div>
                    </div>
                    <div v-if="signIn.option === 'email'" class="form-floating mb-3">
                        <input
                            id="signIn-email"
                            class="form-control"
                            type="email"
                            placeholder="E-mail"
                            maxlength="100"
                            v-maska="null"
                            autofocus
                            required
                            v-model="signIn.field"
                        />
                        <label for="signIn-email">E-mail</label>
                    </div>
                    <div v-if="signIn.option === 'phone'" class="form-floating mb-3">
                        <input
                            id="signIn-phone"
                            class="form-control"
                            type="tel"
                            placeholder="Telefone"
                            minlength="11"
                            maxlength="15"
                            pattern="\(?\d{2}\)?\s?\d{5}-?\d{4}"
                            v-maska
                            data-maska="(##) #####-####"
                            required
                            v-model="signIn.field"
                        />
                        <label for="signIn-phone">Telefone</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="signIn-password"
                            class="form-control"
                            type="password"
                            placeholder="Senha"
                            minlength="8"
                            required
                            v-model="signIn.password"
                        />
                        <label for="signIn-password">Senha</label>
                    </div>
                    <div
                        id="g-recaptcha"
                        class="g-recaptcha"
                        :data-sitekey="googleRecaptchaPublicKey"
                    />
                    <div class="d-grid gap-2 d-flex justify-content-end align-items-center">
                        <div v-if="signInLoader" class="spinner-border" />
                        <button
                            class="btn btn-primary"
                            type="submit"
                            :disabled="signInLoader"
                            v-html="label"
                        />
                    </div>
                </form>
                <hr />
                <div class="text-center small">
                    <Link class="link-secondary" :href="route('web.user.signUp')">Cadastre-se</Link>
                    <br />
                    <Link
                        class="link-secondary"
                        :href="route('web.user.password.request')"
                    >Esqueci a senha</Link>
                </div>
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
    },

    data: () => {
        return {
            signInLoader: false,
            signIn: {
                option: 'email',
                field: '',
                password: '',
                recaptcha: '',
            },
        };
    },

    methods: {
        async signInForm(el) {
            this.signInLoader = true;
            try {
                // Check recaptcha
                this.signIn.recaptcha = grecaptcha.getResponse();
                if (!this.signIn.recaptcha) this.recaptchaInvalid();

                // Request
                const response = await axios.post(route('web.action.user.auth.signIn'), this.$prepareData(this.signIn));
                this.$clearForm(el.target, this.signIn, ['option']);
                const { message, redirect } = response.data;
                this.$snotify.success(message).on('shown', () => this.$inertia.get(redirect));
            }
            catch (error) { this.$showErrors(error) }
            finally {
                this.signInLoader = false;
                grecaptcha.reset();
            }
        },
    },
}
</script>

<style scoped>
.g-recaptcha {
    transform: scale(0.87);
    transform-origin: 0 0;
}
</style>
