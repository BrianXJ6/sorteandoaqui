<template>
    <div class="container">
        <Head :title="title" />
        <h1 v-html="label" />
        <hr class="mt-0" />
        <p>Olá, {{ user.nick }}.</p>
        <div class="card shadow">
            <div class="card-header text-bg-primary fw-bold" v-html="`Minhas Rifas`" />
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-raffle shadow-sm">
                            <Link
                                :href="user.email_verified_at? route('web.user.dashboard.raffles.new'): '#'"
                                class="link-secondary card-body text-center"
                                title="Nova Rifa"
                                @click="!user.email_verified_at? $snotify.info('Para essa opção é necessário validar o email.'): ''"
                            >
                                <p class="m-0">Nova Rifa</p>
                                <i class="bi bi-plus-circle-dotted fs-1"></i>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue2';
export default {
    components: { Head, Link },
    props: { title: String, label: String },

    computed: {
        user: (vm) => vm.$page.props.user,
    },
}
</script>
