<template>
    <div
        class="container h-100 d-flex flex-column justify-content-center align-items-center text-center"
    >
        <Head :title="title" />
        <div class="card shadow-lg">
            <div class="card-body">
                <h1 v-html="title" />
                <p v-html="description" class="small text-secondary" />
                <Link class="btn btn-dark px-5 shadow-sm" href="/">Sair</Link>
            </div>
        </div>
    </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue2';
export default {
    components: { Head, Link },
    props: { status: Number },

    computed: {
        title() {
            return {
                503: '503: Serviço indisponível',
                500: '500: Ops! Problemas interno',
                404: '404: Página não encontrada',
                403: '403: Ação não autorizada!',
            }[this.status]
        },

        description() {
            return {
                503: 'Desculpe, estamos em manutenção. Por favor, volte em breve.',
                500: 'Whoops, Estamos trabalhando para reverter isso, Por favor, volte em breve.',
                404: 'Desculpe, a página que você está procurando não foi encontrada.',
                403: 'Desculpe, sem permissão para acessar esta página.',
            }[this.status]
        },
    },
}
</script>
