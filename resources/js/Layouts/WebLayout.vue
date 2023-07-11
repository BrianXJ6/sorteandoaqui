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
                                        <Link class="nav-link" :href="route('web.home')">Início</Link>
                                    </li>
                                    <li class="nav-item" data-bs-dismiss="offcanvas">
                                        <Link class="nav-link" :href="route('web.contact')">Contato</Link>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a
                                            class="nav-link dropdown-toggle"
                                            href="#"
                                            role="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            v-html="`Cliente`"
                                        />
                                        <ul v-if="!user" class="dropdown-menu dropdown-menu-end">
                                            <li data-bs-dismiss="offcanvas">
                                                <Link
                                                    class="dropdown-item"
                                                    :href="route('web.user.signIn')"
                                                >Login</Link>
                                            </li>
                                            <li data-bs-dismiss="offcanvas">
                                                <Link
                                                    class="dropdown-item"
                                                    :href="route('web.user.signUp')"
                                                >Seja cliente</Link>
                                            </li>
                                        </ul>
                                        <ul v-else class="dropdown-menu dropdown-menu-end">
                                            <li data-bs-dismiss="offcanvas">
                                                <Link
                                                    class="dropdown-item"
                                                    :href="route('web.user.dashboard.home')"
                                                >Painel de acesso</Link>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li data-bs-dismiss="offcanvas">
                                                <UserLogout class="dropdown-item" :label="`Sair`" />
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a
                                            class="nav-link dropdown-toggle m-pointer"
                                            role="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            v-html="`Admin`"
                                        />
                                        <ul v-if="!admin" class="dropdown-menu dropdown-menu-end">
                                            <li data-bs-dismiss="offcanvas">
                                                <Link
                                                    class="dropdown-item"
                                                    :href="route('web.admin.signIn')"
                                                >Login</Link>
                                            </li>
                                            <li data-bs-dismiss="offcanvas">
                                                <Link
                                                    class="dropdown-item"
                                                    :href="route('web.admin.signUp')"
                                                >Novo admin</Link>
                                            </li>
                                        </ul>
                                        <ul v-else class="dropdown-menu dropdown-menu-end">
                                            <li data-bs-dismiss="offcanvas">
                                                <Link
                                                    class="dropdown-item"
                                                    :href="route('web.admin.dashboard.home')"
                                                >Painel administrativo</Link>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li data-bs-dismiss="offcanvas">
                                                <AdminLogout class="dropdown-item" :label="`Sair`" />
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
import UserLogout from './../Components/User/Logout.vue';
import AdminLogout from './../Components/Admin/Logout.vue';
export default {
    components: {
        Link,
        UserLogout,
        AdminLogout
    },

    data: () => {
        return {
            appName: import.meta.env.VITE_APP_NAME,
        };
    },

    computed: {
        user: (vm) => vm.$page.props.user,
        admin: (vm) => vm.$page.props.admin,
    },
}
</script>
