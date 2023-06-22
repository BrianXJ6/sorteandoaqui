<template>
    <div class="container">
        <Head :title="title" />
        <h1 v-html="label" />
        <hr />
        <p>Olá, {{ user.nick }}.</p>
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
