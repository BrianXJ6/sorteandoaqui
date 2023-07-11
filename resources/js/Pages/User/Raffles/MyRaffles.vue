<template>
    <div class="container">
        <Head :title="title" />
        <h1 v-html="label" />
        <hr class="mt-0" />
        <div class="card shadow">
            <div class="card-header text-bg-primary fw-bold" v-html="`Minhas rifas`" />
            <div class="card-body">
                <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Recusandae, consectetur harum.</p>
            </div>
        </div>
    </div>
</template>

<script>
import { Head } from '@inertiajs/vue2';
export default {
    components: { Head },
    props: { title: String, label: String },

    computed: {
        user: (vm) => vm.$page.props.user,
    },
}
</script>
