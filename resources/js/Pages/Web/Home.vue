<template>
    <div>
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
        <div class="container">
            <h1 v-html="label" />
            <hr class="mt-0" />
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Illo culpa, sapiente ullam enim soluta odio. Placeat, eligendi iste? Ut, odit accusamus! Aut inventore repellat cupiditate sapiente ipsam quam aperiam deleniti.</p>
        </div>
    </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue2';
export default {
    components: { Head, Link },
    props: {
        title: String,
        label: String,
        description: String,
        keywords: String,
    },

    computed: {
        user: (vm) => vm.$page.props.user,
    },
}
</script>
