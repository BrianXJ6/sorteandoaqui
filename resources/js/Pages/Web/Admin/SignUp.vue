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
                <form @submit.prevent="signUpForm" autocomplete="off">
                    <div class="form-floating mb-3">
                        <input
                            id="signUp-name"
                            class="form-control"
                            type="text"
                            placeholder="Nome"
                            maxlength="100"
                            pattern="^[a-zA-Z]{1,}[a-zA-Zà-úÀ-Ú]{1,}\.?\s[a-zA-Zà-úÀ-Ú\.\s]{1,}"
                            autofocus
                            required
                            v-model="signUp.name"
                        />
                        <label for="signUp-name">Nome</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="signUp-email"
                            class="form-control"
                            type="email"
                            placeholder="E-mail"
                            maxlength="100"
                            required
                            v-model="signUp.email"
                        />
                        <label for="signUp-email">E-mail</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="signUp-phone"
                            class="form-control"
                            type="tel"
                            placeholder="Telefone"
                            minlength="11"
                            maxlength="15"
                            pattern="\(?\d{2}\)?\s?\d{5}-?\d{4}"
                            required
                            v-maska
                            data-maska="(##) #####-####"
                            v-model="signUp.phone"
                        />
                        <label for="signUp-phone">Telefone</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="signUp-password"
                            class="form-control"
                            type="password"
                            placeholder="Senha"
                            minlength="8"
                            required
                            v-model="signUp.password"
                        />
                        <label for="signUp-password">Senha</label>
                    </div>
                    <div class="d-grid gap-2 d-flex justify-content-end align-items-center">
                        <div v-if="signUpLoader" class="spinner-border" />
                        <button
                            class="btn btn-primary"
                            type="submit"
                            :disabled="signUpLoader"
                            v-html="label"
                        />
                    </div>
                </form>
                <hr />
                <div class="text-center small">
                    <Link class="link-secondary" :href="route('web.admin.signIn')">Já possuo conta</Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue2';
export default {
    components: { Head, Link },

    props: {
        title: String,
        description: String,
        keywords: String,
        label: String,
    },

    data: () => {
        return {
            signUpLoader: false,
            signUp: {
                name: '',
                email: '',
                phone: '',
                password: '',
            }
        };
    },

    methods: {
        async signUpForm(el) {
            this.signUpLoader = true;
            try {
                const response = await axios.post(route('web.action.admin.auth.signUp'), this.$prepareData(this.signUp));
                this.$clearForm(el.target, this.signUp);
                const { message, redirect } = response.data;
                this.$snotify.success(message).on('shown', () => this.$inertia.get(redirect));
            }
            catch (error) { this.$showErrors(error); }
            finally { this.signUpLoader = false; }
        },
    },
}
</script>
