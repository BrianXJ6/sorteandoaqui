<template>
    <div class="container">
        <Head :title="title" />
        <h1 v-html="label" />
        <hr />
        <p>Olá, {{ admin.name }}.</p>
    </div>
</template>

<script>
import { Head } from '@inertiajs/vue2';
export default {
    components: { Head },
    props: { title: String, label: String },

    computed: {
        admin: (vm) => vm.$page.props.admin,
    },

}
</script>
