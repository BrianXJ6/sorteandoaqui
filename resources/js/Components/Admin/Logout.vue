<template>
    <a class="m-pointer" @click.prevent="processSignOut">
        <span v-if="!label">
            <slot />
        </span>
        <span v-else v-html="label" />
    </a>
</template>

<script>
export default {
    props: {
        label: { required: false, type: String, default: null }
    },

    methods: {
        processSignOut() {
            this.$snotify.clear();
            this.$snotify.confirm('Deseja finalizar a sessão?', {
                backdrop: .5,
                buttons: [
                    { text: 'Sim', action: (toast) => this.$snotify.async('Finalizando...', () => this.signOut(toast)) },
                    { text: 'Não', action: (toast) => this.$snotify.remove(toast.id) },
                ]
            });
        },

        async signOut(toast) {
            try {
                await axios.post(route('web.action.admin.auth.signOut'));
                this.$inertia.get('/');

                return {
                    body: 'Sessão finalizada',
                    config: {
                        showProgressBar: true,
                        closeOnClick: true,
                        timeout: 3000,
                    },
                };
            } catch (error) {
                return {
                    body: 'Ops! tente novamente.',
                    config: {
                        type: 'error',
                        closeOnClick: true,
                    },
                };
            }
            finally { this.$snotify.remove(toast.id) }
        },
    },
}
</script>
