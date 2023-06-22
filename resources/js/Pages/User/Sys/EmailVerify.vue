<template>
    <div class="container h-100 d-flex flex-fill justify-content-center align-items-center">
        <h1 class="text-center fs-3">
            <p>Validando e-mail, aguarde...</p>
            <div class="spinner-border" />
        </h1>
    </div>
</template>

<script>
import AppLayout from './../../../Layouts/AppLayout.vue';
export default {
    layout: AppLayout,

    props: {
        id: { required: true, type: String },
        hash: { required: true, type: String },
    },

    computed: {
        user: (vm) => vm.$page.props.user,
    },

    methods: {
        redirectToDashboard() { this.$inertia.get(route('web.user.dashboard.home')) },
        async emailVerify() {
            try {
                const response = await axios.post(route('web.action.user.auth.email.verify'), {
                    id: this.id,
                    hash: this.hash,
                });

                this.$snotify.success(response.data.message).on('shown', () => this.redirectToDashboard());
            }
            catch (error) {
                this.$showErrors(error);
                this.redirectToDashboard();
            }
        },

    },

    created() {
        if (!this.user.email_verified_at) this.emailVerify();
        else this.redirectToDashboard();
    }
}
</script>
