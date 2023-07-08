<template>
    <div class="h-100 d-flex flex-column">
        <main class="flex-shrink-0">
            <header class="fixed-top shadow-lg">
                <nav class="navbar navbar-expand-md navbar-dark bg-primary shadow-sm">
                    <div class="container">
                        <Link class="navbar-brand" :href="route('web.home')" :title="appName">
                            <img
                                src="/statics/images/logo-navbar-brand.png"
                                alt="logo-navbar-brand.png"
                            />
                        </Link>
                        <button
                            class="navbar-toggler"
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasNavbar"
                            aria-controls="offcanvasNavbar"
                        >
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div
                            class="offcanvas bg-primary offcanvas-end"
                            tabindex="-1"
                            id="offcanvasNavbar"
                        >
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title fw-bold text-light">Menu</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                            </div>
                            <div class="offcanvas-body text-center">
                                <ul class="navbar-nav justify-content-end flex-grow-1">
                                    <li class="nav-item" data-bs-dismiss="offcanvas">
                                        <Link
                                            class="nav-link"
                                            :href="route('web.user.dashboard.home')"
                                        >Início</Link>
                                    </li>
                                    <li v-if="user.email_verified_at" class="nav-item dropdown">
                                        <a
                                            class="nav-link dropdown-toggle m-houver"
                                            role="button"
                                            data-bs-toggle="dropdown"
                                            v-html="`Rifas`"
                                        />
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li data-bs-dismiss="offcanvas">
                                                <Link
                                                    class="dropdown-item"
                                                    :href="route('web.user.dashboard.raffles.my-raffles')"
                                                >Minhas rifas</Link>
                                            </li>
                                            <li data-bs-dismiss="offcanvas">
                                                <Link
                                                    class="dropdown-item"
                                                    :href="route('web.user.dashboard.raffles.new')"
                                                >Nova rifa</Link>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a
                                            class="nav-link dropdown-toggle"
                                            href="#"
                                            role="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            v-html="`Olá, ${user.nick}`"
                                        />
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li data-bs-dismiss="offcanvas">
                                                <Link
                                                    class="dropdown-item"
                                                    :href="route('web.user.dashboard.my-account.personal-data')"
                                                >Meus dados</Link>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li data-bs-dismiss="offcanvas">
                                                <Logout class="dropdown-item" :label="`Sair`" />
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </header>
            <section class="py-5" style="margin-top: 3.8rem">
                <slot />
            </section>
        </main>
        <footer class="mt-auto bg-gradient text-bg-primary">
            <div class="container text-center py-1 small">
                <span class="small">{{ appName }}&reg; - Todos os direitos reservados.</span>
            </div>
        </footer>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue2';
import Logout from './../Components/User/Logout.vue';
export default {
    components: { Link, Logout },

    data: () => {
        return {
            appName: import.meta.env.VITE_APP_NAME,
        };
    },

    computed: {
        user: (vm) => vm.$page.props.user,
    },
}
</script>
