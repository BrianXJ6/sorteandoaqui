<template>
    <div>
        <a
            class="nav-link dropdown-toggle m-houver"
            role="button"
            data-bs-auto-close="outside"
            data-bs-toggle="dropdown"
            title="Notificações"
        >
            <i v-if="!unRead" class="bi bi-bell"></i>
            <div v-else>
                <i class="bi bi-bell-fill"></i>
                <span
                    class="bg-danger border border-danger-subtle border-light p-1 position-absolute rounded-circle shadow-sm translate-middle"
                />
            </div>
        </a>
        <div class="dropdown-menu dropdown-menu-end pb-0">
            <h6 class="dropdown-header text-center fw-bold fs-5">
                Notificações
                <span
                    v-if="unRead"
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    v-html="unRead"
                />
            </h6>
            <hr class="mt-1 mb-0" />
            <div class="list-group">
                <Link
                    v-for="(notification, i) in notifications"
                    :key="i"
                    href="#"
                    :class="['list-group-item rounded-top-0 list-group-item-action small', {'bg-primary-subtle': !notification.read_at}]"
                    @click="executeNotificaiton(notification)"
                >
                    <div class="d-flex w-100 justify-content-between gap-2">
                        <div class="d-inline-flex align-items-baseline gap-2">
                            <div v-html="notification.data.icon" />
                            <h5
                                class="fw-bold text-decoration-underline"
                                v-html="notification.data.title"
                            />
                        </div>
                        <small class="small text-end">{{ notification.created_at | moment('from') }}</small>
                    </div>
                    <p class="m-0" v-html="notification.data.message" />
                </Link>
            </div>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue2';
export default {
    components: { Link },

    data: () => {
        return {};
    },

    methods: {
        executeNotificaiton(notification) {
            if (!notification.read_at) this.markReadNotification(notification);
        },

        async markReadNotification(notification) {
            try {
                await axios.post(route('api.user.notifications.mark-read', notification.id));
                notification.read_at = new Date();
                this.$store.state.user.notifications.unRead = this.notifications.filter(($item) => !$item.read_at).length;
            } catch (error) { this.$showErrors(error) }
        },
    },

    computed: {
        notifications: (vm) => vm.$store.state.user.notifications.data,
        unRead: (vm) => vm.$store.state.user.notifications.unRead,
    },

    created() {
        this.$store.dispatch('user/notificationsList');
    },
}
</script>

<style scoped>
.dropdown-menu {
    width: 297px;
}
</style>
