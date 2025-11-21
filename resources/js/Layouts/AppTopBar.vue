<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useLayout } from '@/composables/layout';
import Logo from '/public/images/logo.png';
const { onMenuToggle } = useLayout();
import { Link, router} from '@inertiajs/vue3';
const outsideClickListener = ref(null);
const topbarMenuActive = ref(false);


onMounted(() => {
    bindOutsideClickListener();
});

onBeforeUnmount(() => {
    unbindOutsideClickListener();
});

const onTopBarMenuButton = () => {
    topbarMenuActive.value = !topbarMenuActive.value;
};

const topbarMenuClasses = computed(() => {
    return {
        'layout-topbar-menu-mobile-active': topbarMenuActive.value
    };
});

const bindOutsideClickListener = () => {
    if (!outsideClickListener.value) {
        outsideClickListener.value = (event) => {
            if (isOutsideClicked(event)) {
                topbarMenuActive.value = false;
            }
        };
        document.addEventListener('click', outsideClickListener.value);
    }
};
const unbindOutsideClickListener = () => {
    if (outsideClickListener.value) {
        document.removeEventListener('click', outsideClickListener);
        outsideClickListener.value = null;
    }
};
const isOutsideClicked = (event) => {
    if (!topbarMenuActive.value) return;

    const sidebarEl = document.querySelector('.layout-topbar-menu');
    const topbarEl = document.querySelector('.layout-topbar-menu-button');

    return !(sidebarEl.isSameNode(event.target) || sidebarEl.contains(event.target) || topbarEl.isSameNode(event.target) || topbarEl.contains(event.target));
};
</script>

<template>
    <div class="layout-topbar bg-primary-900">

        <a href="/inicio" class="layout-topbar-logo text-white">
            <img :src="Logo" alt="logo" />
            <span class="text-xl text-white">LA NOCTURNA</span>
        </a>

        <button class="p-link layout-menu-button layout-topbar-button text-white hover:bg-primary-100" @click="onMenuToggle()">
            <i class="pi pi-bars"></i>
        </button>

        <button class="p-link layout-topbar-menu-button layout-topbar-button text-white hover:bg-primary-100"
            @click="onTopBarMenuButton()">
            <i class="pi pi-ellipsis-v"></i>
        </button>

        <div class="layout-topbar-menu-button layout-topbar-menu bg-primary-900" :class="topbarMenuClasses">
            <div class="my-auto text-center">
                <p class="text-xs font-normal text-white my-2 dark:text-gray-300">
                    {{ $page.props.auth.user.name }}
                </p>
            </div>

<div class="text-end">

    <Link :href="route('logout')" method="post" as="button"
    class="layout-topbar-button text-white  hover:bg-primary-100 w-36 h-10 p-2 text-xs dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white">
    <i class="pi pi-sign-out"></i>
    <span class="ml-2 inline text-xs">
        Cerrar Sesión
    </span>
</Link>
</div>
        </div>


    </div>
</template>

<style  scoped></style>
