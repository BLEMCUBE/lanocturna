<script>
import { onMounted, computed, watch } from "vue";
import NavLinkSideBar from '@/Components/NavLinkSideBar.vue';
import { usePage } from '@inertiajs/vue3';
import { useConfigStore } from '@/store/config.js'

export default {
    components: {
        NavLinkSideBar,
    },
    props: {
        isOpen: {
            type: Boolean,
            default: true,

        },

    },
    setup(props) {

        const { permissions } = usePage().props.auth
        const configStore = useConfigStore();
        const classes = computed(() => props.isOpen ? 'sm:translate-x-0' : 'sm:hidden sm:translate-x-0');
        const setMenu = (menu) => {
            configStore.showMenu(menu);
        }

        onMounted(() => {

        });

        watch(
            () => props.isOpen,
            () => {

                if (props.isOpen) {
                } else {
                }
            }
        );

        return {
            classes,
            configStore,
            setMenu,
            permissions
        }
    }
}


</script>

<template>
    <aside id="logo-sidebar"
        class="fixed top-0 left-0 z-40 w-56 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
        aria-label="Sidebar">
        <div class="h-full px-1 pb-4 overflow-y-auto bg-white dark:bg-gray-800">
            <ul class="space-y-1 font-normal">
                <li @click="setMenu('inicio')">

                    <NavLinkSideBar icon-class="fas fa-home"
                        class="flex items-center px-2 py-1.5 text-md text-gray-900 rounded hover:bg-gray-200 group dark:text-gray-200 dark:hover:bg-gray-700"
                        :href="route('inicio')" :active="route().current('inicio')">
                        <span class="ml-3" sidebar-toggle-item>Inicio</span>

                    </NavLinkSideBar>
                </li>
                <li @click="setMenu('usuario')" v-show="permissions.includes('lista-usuarios')">
                    <NavLinkSideBar icon-class="fas fa-user-friends"
                        class="flex items-center px-2 py-1.5 text-md text-gray-900 rounded hover:bg-gray-200 group dark:text-gray-200 dark:hover:bg-gray-700"
                        :href="route('usuarios.index')" :active="route().current('usuarios.index')">
                        <span class="ml-3" sidebar-toggle-item>Usuarios</span>
                    </NavLinkSideBar>
                </li>
                <li @click="setMenu('clientes')" v-show="permissions.includes('lista-clientes')">
                    <NavLinkSideBar icon-class="fas fa-users"
                        class="flex items-center px-2 py-1.5 text-md text-gray-900 rounded hover:bg-gray-200 group dark:text-gray-200 dark:hover:bg-gray-700"
                        :href="route('clientes.index')" :active="route().current('clientes.index')">
                        <span class="ml-3" sidebar-toggle-item>Clientes</span>
                    </NavLinkSideBar>
                </li>

            </ul>

            <ul class="space-y-1 font-normal">

                <li @click="setMenu('roles')" v-show="permissions.includes('ver-roles')">
                    <NavLinkSideBar icon-class="fas fa-user-tag"
                    class="flex items-center px-2 py-1.5 text-md text-gray-900 rounded hover:bg-gray-200 group dark:text-gray-200 dark:hover:bg-gray-700"
                        :href="route('roles.index')" :active="route().current('roles.index')">
                        <span class="ml-3" sidebar-toggle-item>Roles y Permisos</span>
                    </NavLinkSideBar>
                </li>

            </ul>

        </div>
    </aside>
</template>
