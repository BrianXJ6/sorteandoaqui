<template>
    <div>
        <a
            class="nav-link dropdown-toggle m-houver"
            role="button"
            data-bs-auto-close="outside"
            data-bs-toggle="dropdown"
            title="Notificações"
        >
            <!-- <i class="bi bi-bell"></i> -->
            <div>
                <i class="bi bi-bell-fill"></i>
                <span
                    class="bg-danger border border-danger-subtle border-light p-1 position-absolute rounded-circle shadow-sm translate-middle"
                >
                    <span class="visually-hidden">New alerts</span>
                </span>
            </div>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <h6 class="dropdown-header text-center fw-bold" v-html="`Notificações`" />
            <hr class="mt-1" />
            <div class="list-group">
                <a
                    href="#"
                    class="list-group-item list-group-item-action active"
                    aria-current="true"
                >
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">List group item heading</h5>
                        <small>3 days ago</small>
                    </div>
                    <p class="mb-1">Some placeholder content in a paragraph.</p>
                    <small>And some small print.</small>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">List group item heading</h5>
                        <small class="text-body-secondary">3 days ago</small>
                    </div>
                    <p class="mb-1">Some placeholder content in a paragraph.</p>
                    <small class="text-body-secondary">And some muted small print.</small>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">List group item heading</h5>
                        <small class="text-body-secondary">3 days ago</small>
                    </div>
                    <p class="mb-1">Some placeholder content in a paragraph.</p>
                    <small class="text-body-secondary">And some muted small print.</small>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
export default {

}
</script>

<style scoped>
.dropdown-menu {
    width: 297px;
}
</style>
