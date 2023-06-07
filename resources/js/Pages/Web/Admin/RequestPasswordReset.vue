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
                <form @submit.prevent="requestPasswordResetForm" autocomplete="off">
                    <div class="form-floating mb-3">
                        <input
                            id="requestPasswordReset-email"
                            class="form-control"
                            type="email"
                            placeholder="E-mail"
                            maxlength="100"
                            autofocus
                            required
                            v-model="requestPasswordReset.email"
                        />
                        <label for="requestPasswordReset-email">E-mail</label>
                    </div>
                    <div class="d-grid gap-2 d-flex justify-content-end align-items-center">
                        <div v-if="requestPasswordResetLoader" class="spinner-border" />
                        <button
                            class="btn btn-primary"
                            type="submit"
                            :disabled="requestPasswordResetLoader"
                            v-html="`Enviar`"
                        />
                    </div>
                </form>
                <hr />
                <div class="text-center small">
                    <Link class="link-secondary" :href="route('web.admin.signIn')">Voltar</Link>
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
            requestPasswordResetLoader: false,
            requestPasswordReset: { email: '' },
        };
    },

    methods: {
        async requestPasswordResetForm(el) {
            this.requestPasswordResetLoader = true;
            try {
                const response = await axios.post(route('web.action.admin.auth.password.email'), this.$prepareData(this.requestPasswordReset));
                this.$clearForm(el.target, this.requestPasswordReset);
                const { message, redirect } = response.data;
                this.$snotify.success(message).on('shown', () => this.$inertia.get(redirect));
            }
            catch (error) { this.$showErrors(error); }
            finally { this.requestPasswordResetLoader = false; }
        },
    },
}
</script>
