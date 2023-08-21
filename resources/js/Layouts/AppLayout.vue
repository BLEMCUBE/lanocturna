<script setup>

import { ref, onMounted, computed, watch, } from "vue";
import Breadcrumb from 'primevue/breadcrumb';
import { Link } from '@inertiajs/vue3';
import AppTopBar from '@/Layouts/AppTopBar.vue';
import AppSideBar from '@/Layouts/AppSideBar.vue'
import AppFooter from '@/Layouts/AppFooter.vue'
import { useLayout } from '@/composables/layout';

const { layoutConfig, layoutState, isSidebarActive } = useLayout();

const outsideClickListener = ref(null);

const home = ref({
    icon: 'pi pi-home',
    label:' Incio',
    url: '/inicio',
    link:true
});
const items22 = ref([]);
const props = defineProps({
    pagina: {
        type: Object,
        default: [],
    },

});
onMounted(() => {
    items22.value = props.pagina;
}),




watch(isSidebarActive, (newVal) => {
    if (newVal) {
        bindOutsideClickListener();
    } else {
        unbindOutsideClickListener();
    }
});

const containerClass = computed(() => {
    return {
        'layout-theme-light': layoutConfig.darkTheme.value === 'light',
        'layout-theme-dark': layoutConfig.darkTheme.value === 'dark',
        'layout-overlay': layoutConfig.menuMode.value === 'overlay',
        'layout-static': layoutConfig.menuMode.value === 'static',
        'layout-static-inactive': layoutState.staticMenuDesktopInactive.value && layoutConfig.menuMode.value === 'static',
        'layout-overlay-active': layoutState.overlayMenuActive.value,
        'layout-mobile-active': layoutState.staticMenuMobileActive.value,
        'p-input-filled': layoutConfig.inputStyle.value === 'filled',
        'p-ripple-disabled': !layoutConfig.ripple.value
    };
});
const bindOutsideClickListener = () => {
    if (!outsideClickListener.value) {
        outsideClickListener.value = (event) => {
            if (isOutsideClicked(event)) {
                layoutState.overlayMenuActive.value = false;
                layoutState.staticMenuMobileActive.value = false;
                layoutState.menuHoverActive.value = false;
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
    const sidebarEl = document.querySelector('.layout-sidebar');
    const topbarEl = document.querySelector('.layout-menu-button');

    return !(sidebarEl.isSameNode(event.target) || sidebarEl.contains(event.target) || topbarEl.isSameNode(event.target) || topbarEl.contains(event.target));
};
</script>

<template>
    <div class="layout-wrapper layout-static" :class="containerClass">
        <!--Top-->
        <AppTopBar></AppTopBar>
        <div class="layout-sidebar bg-primary-900">
            <AppSideBar></AppSideBar>
        </div>

        <div class="layout-main-container">
            <div class="layout-main">
                <div class="col-span-12 px-2 flex justify-start text-base font-semibold">
                    <Breadcrumb :home="home" :model="items22"
                        :pt="{
                            breadcrumb:
                            {
                                root: { 'class': ['overflow-x-auto', 'bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 rounded-md p-2'] },
                                menu: { 'class': 'm-0 p-0 list-none flex items-center flex-nowrap' },
                                action: { 'class': ['text-decoration-none flex items-center', 'transition-shadow duration-200 rounded-md text-gray-600 dark:text-white/70', 'focus:outline-none focus:outline-offset-0 focus:shadow-[0_0_0_0.2rem_rgba(191,219,254,1)] dark:focus:shadow-[0_0_0_0.2rem_rgba(147,197,253,0.5)]'] }, 'icon': { 'class': 'text-gray-600 dark:text-white/70' }, 'separator': { 'class': ['mx-2 text-gray-600 dark:text-white/70', 'flex items-center'] }
                            }
                        }">
                        <template #item="{ item }">
                            <Link class="phover:fill-primary-100" :href="item.url"
                                as="button"
                                v-if="item.link==true"
                                @click="setMenu('inicio')">
                                <span :class="item.icon" class="hover:fill-primary-100"></span>
                                <span class="hover:text-primary-100">{{ item.label }}</span>
                        </Link>
                        <p v-if="item.link==false" class="m-0">
                                <span :class="item.icon" class="hover:fill-primary-100"></span>
                                <span class="hover:text-primary-100">{{ item.label }}</span>
                        </p>
                        </template>
                    </Breadcrumb>
                </div>
                <div class="grid grid-cols-12 gap-4 px-2 pt-4 overflow-x-auto">
                <slot></slot>
            </div>
            </div>

            <AppFooter></AppFooter>
        </div>
        <div class="layout-mask"></div>
    </div>
</template>

<style lang="scss" scoped></style>
