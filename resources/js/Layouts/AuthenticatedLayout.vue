<template>
    <a-layout class="bg-primary-900 ">
        <!--Sidebar-->
        <a-layout-sider class="bg-primary-900" breakpoint="md" v-model:collapsed="collapsed" :trigger="null"
            collapsed-width="0" collapsible>

            <a-menu v-model:selectedKeys="selectedKeys" theme="dark" mode="inline" class="bg-primary-900">

                <Link :href="route('inicio')" class="flex dark:fill-white"
                @click="setMenu('inicio')">
                <img class="mx-auto h-16  w-auto" :src="Logo" alt="Imagen" />
                </Link>

                <a-menu-item key="inicio" class="p-0 m-0 w-full rounded" @click="setMenu('inicio')">
                    <NavLinkSideBar icon-class="fas fa-home" class="flex items-center px-4 py-1.5 text-base font-normal"
                        :href="route('inicio')" :active="route().current('inicio')">
                        <span class="ml-2">Inicio</span>
                    </NavLinkSideBar>
                </a-menu-item>

                <a-menu-item key="usuarios" class="p-0 m-0 w-full rounded" @click="setMenu('usuarios')"
                    v-show="permissions.includes('lista-usuarios')">
                    <NavLinkSideBar icon-class="fas fa-user-friends"
                        class="flex items-center justify-start px-4 py-1.5 text-base font-normal" :href="route('usuarios.index')"
                        :active="route().current('usuarios.index')">
                        <span class="ml-2">Usuarios</span>
                    </NavLinkSideBar>
                </a-menu-item>

                <a-menu-item key="clientes" class="p-0 m-0 w-full rounded" @click="setMenu('clientes')"
                    v-show="permissions.includes('lista-clientes')">
                    <NavLinkSideBar icon-class="fas fa-users" class="flex items-center justify-start px-4 py-1.5 text-base font-normal"
                        :href="route('clientes.index')" :active="route().current('clientes.index')">
                        <span class="ml-2">Clientes</span>
                    </NavLinkSideBar>
                </a-menu-item>

                <a-menu-item key="productos" class="p-0 m-0 w-full rounded" @click="setMenu('productos')"
                    v-show="permissions.includes('lista-productos')">
                    <NavLinkSideBar icon-class="fas fa-boxes" class="flex items-center justify-start px-4 py-1.5 text-base font-normal"
                        :href="route('productos.index')" :active="route().current('productos.index')">
                        <span class="ml-2">Productos</span>
                    </NavLinkSideBar>
                </a-menu-item>
                <Divider />
                <a-menu-item key="roles" class="p-0 m-0 w-full rounded" @click="setMenu('roles')"
                    v-show="permissions.includes('ver-roles')">
                    <NavLinkSideBar icon-class="fas fa-user-tag" class="flex items-center justify-start px-4 py-1.5 text-base font-normal"
                        :href="route('roles.index')" :active="route().current('roles.index')">
                        <span class="ml-3" sidebar-toggle-item>Roles y Permisos</span>
                    </NavLinkSideBar>
                </a-menu-item>

            </a-menu>

        </a-layout-sider>
        <!--Sidebar-->
        <!--header-->
        <a-layout>
            <a-layout-header class="bg-primary-900 leading-none w-full flex justify-between items-center px-2 h-16">
                <div class="trigger cursor-pointer my-auto" @click="() => (collapsed = !collapsed)">
                    <i class="fas fa-bars  text-white fa-2x"></i>
                    <span class="pl-5 text-left text-xl font-semibold text-white sm:text-2xl  dark:text-white">LA
                        NOCTURNA</span>
                </div>
                <div class="flex items-center justify-between py-2 h-14">
                    <div>

                        <p class="text-sm font-semibold text-end text-white dark:text-gray-300 my-auto mx-4">
                            {{ usuario }}
                        </p>
                    </div>
                    <div>

                        <Button type="button" size="sm" class="bg-primary-900 p-0 m-0 border-0" label="Toggle"
                            @click="toggle" aria-haspopup="true" aria-controls="overlay_tmenuref">
                            <img class="w-8 h-8 rounded-full border-1 border-white" :src="$page.props.auth.user.photo"
                                alt="user photo">
                        </Button>
                    </div>
                    <TieredMenu aria-setsize="sm" ref="menuref" class="border-0" id="overlay_tmenuref" :model="items" popup>
                        <template #item="{ item }">
                            <p :class="item.class" class="px-2 text-sm m-1 text-primary-900 dark:text-white bg-white"
                                v-if="item.link == false">
                                <span :class="item.icon"></span>
                                <span>{{ item.label }}</span>
                            </p>

                            <Link class="px-2 text-sm text-primary-900 dark:text-white" :href="item.url" method="post"
                                as="button" v-if="item.link == true" role="menuitem">
                            <span :class="item.icon"></span>
                            <span>{{ item.label }}</span>
                            </Link>
                        </template>
                    </TieredMenu>
                </div>

            </a-layout-header>

            <!--Contenido---->
            <a-layout-content
                class="bg-gray-100 dark:bg-gray-800 grid grid-cols-1 p-4 xl:grid-cols-12 md:grid-span-12 xl:gap-4 dark:bg-gray-900">
                <div class="col-span-12 flex justify-start text-base font-semibold">
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
                <slot></slot>
            </a-layout-content>
            <!--Contenido---->
            <a-layout-footer
                class="fixed ml-0  text-center bottom-0 z-20 w-full p-2 bg-white border-t border-gray-200 shadow md:flex md:items-center md:justify-between md:p-2 dark:bg-gray-800 dark:border-gray-600">
                <span class="text-xs text-gray-500 text-center sm:text-center dark:text-gray-400"><a
                        href="https://walink.co/38ecb3" class="hover:underline">Desarrollado por: Ing. Oscar Jimmy
                        Marquez </a> | +591 69081668
                </span>
            </a-layout-footer>
        </a-layout>
        <!--header-->
    </a-layout>
</template>
<script setup>
import { ref, onMounted } from "vue";
import Logo from '/public/images/logo.png';
const { permissions } = usePage().props.auth
import NavLinkSideBar from '@/Components/NavLinkSideBar.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useConfigStore } from '@/store/config.js'
import Breadcrumb from 'primevue/breadcrumb';
const collapsed = ref(false);
const menuref = ref();
const configStore = useConfigStore();
const selectedKeys = ref([configStore.getCurrentMenu]);
const setMenu = (menu) => {
    configStore.showMenu(menu);
}
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
    console.log('r ', props.pagina)
const usuario = usePage().props.auth.user.name;
const items = ref([
    {
        label: usuario,
        icon: '',
        link: false,
        url: ''

    },

    {
        separator: true
    },
    {
        label: 'Cerrar Sesión',
        icon: 'fa-solid fa-right-from-bracket',
        link: true,
        url: route('logout')

    },
]);

const toggle = (event) => {
    menuref.value.toggle(event);
};
</script>
