<script>
import { onMounted, computed, watch,ref } from "vue";
import NavLinkSideBar from '@/Components/NavLinkSideBar.vue';
import { usePage,router } from '@inertiajs/vue3';
import { useConfigStore } from '@/store/config.js'
import Swal from 'sweetalert2';
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
        const { hoy_tipo_cambio } = usePage().props.auth
        const configStore = useConfigStore();
        const classes = computed(() => props.isOpen ? 'sm:translate-x-0' : 'sm:hidden sm:translate-x-0');
        const setMenu = (menu) => {
            configStore.showMenu(menu);
        }
        const linkCrear=ref('');

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
        const btnCrear = () => {
            if (hoy_tipo_cambio == true) {

                router.get(route('ventas.create'));
            } else {
                ok('error', 'No se ha especificado el tipo de cambio para el día')
            }
        }
        const ok = (icono, mensaje) => {

            Swal.fire({
                width: 350,
                title: mensaje,
                icon: icono
            })
        }

        return {
            classes,
            configStore,
            setMenu,
            permissions,
            btnCrear,
            ok,
            linkCrear
        }
    }
}

</script>

<template>
    <div class="layout-menu">


        <!-- Productos Link -->
        <!--


        <div>


            <NavLinkSideBar icon-class="fas fa-user-friends"
                class="flex items-center justify-start px-4 py-1.5 text-base font-medium"
                :active="route().current('usuarios.index')" @click="setMenu('usuario')" href="#" preserve-state>
                <span class="ml-3">Usuarios</span>
                <i class="fas fa-chevron-down absolute right-5"></i>
                <i class="fas fa-chevron-up absolute right-5"></i>
            </NavLinkSideBar>
            <div role="menu" class="mt-2 space-y-0.5 px-5" aria-label="Main Link"
                :class="configStore.getCurrentMenu == 'usuario' ? '' : 'hidden'">

                <li @click="setMenu('usuario')" v-show="permissions.includes('lista-usuarios')">
                    <NavLinkSideBar
                        class="mt-0 px-3 py-1.5 text-base font-medium block text-sm transition-colors duration-200 rounded-0"
                        :href="route('usuarios.index')" :active="route().current('usuarios.index')">
                        <span class="ml-5">Usuarios</span>
                    </NavLinkSideBar>
                </li>
                <li class="pt-0" @click="setMenu('clientes')" v-show="permissions.includes('lista-clientes')">
                    <NavLinkSideBar
                        class="mt-0 px-5 py-1.5 text-base font-medium block text-sm transition-colors duration-200 rounded-0"
                        :href="route('clientes.index')" :active="route().current('clientes.index')">
                        <span class="ml-4">Clientes</span>
                    </NavLinkSideBar>
                </li>

            </div>
        </div>
-->
        <ul class="font-normal">
            <li @click="setMenu('inicio')">

                <NavLinkSideBar icon-class="fas fa-home"
                    class="flex items-center justify-start px-4 py-1.5 text-base font-medium" :href="route('inicio')"
                    :active="route().current('inicio')">
                    <span class="ml-3">Inicio</span>

                </NavLinkSideBar>
            </li>
            <li @click="setMenu('usuario')" v-show="permissions.includes('lista-usuarios')">
                <NavLinkSideBar icon-class="fas fa-user-friends"
                    class="flex items-center justify-start px-4 py-1.5 text-base font-medium"
                    :href="route('usuarios.index')" :active="route().current('usuarios.index')">
                    <span class="ml-3">Usuarios</span>
                </NavLinkSideBar>
            </li>
            <!--
            <li @click="setMenu('clientes')" v-show="permissions.includes('lista-clientes')">
                <NavLinkSideBar icon-class="fas fa-users"
                    class="flex items-center justify-start px-4 py-1.5 text-base font-medium"
                    :href="route('clientes.index')" :active="route().current('clientes.index')">
                    <span class="ml-3">Clientes</span>
                </NavLinkSideBar>
            </li>

            -->
            <li @click="setMenu('productos')" v-show="permissions.includes('lista-productos')">
                <NavLinkSideBar icon-class="fas fa-boxes"
                    class="flex items-center justify-start px-4 py-1.5 text-base font-medium"
                    :href="route('productos.index')" :active="route().current('productos.index')">
                    <span class="ml-3">Productos</span>
                </NavLinkSideBar>
            </li>
            <li @click="setMenu('ventas')" v-show="permissions.includes('lista-ventas')">
                <NavLinkSideBar icon-class="fa fa-cash-register"
                    class="flex items-center justify-start px-4 py-1.5 text-base font-medium" :href="route('ventas.index')"
                    :active="route().current('ventas.index')">
                    <span class="ml-3">Ventas</span>
                </NavLinkSideBar>
            </li>
            <li @click="setMenu('crear-ventas'); btnCrear()" v-show="permissions.includes('crear-ventas')">
                <NavLinkSideBar icon-class="fa fa-cash-register" :href="linkCrear"
                    class="flex items-center justify-start px-4 py-1.5 text-base font-medium"
                    :active="route().current('ventas.create')">
                    <span class="ml-3">Crear Venta</span>
                </NavLinkSideBar>
            </li>
            <li @click="setMenu('imoprtaciones')" v-show="permissions.includes('lista-productos')">
                <NavLinkSideBar icon-class="fa-solid fa-cart-plus"
                    class="flex items-center justify-start px-4 py-1.5 text-base font-medium"
                    :href="route('importaciones.index')" :active="route().current('importaciones.index')">
                    <span class="ml-3">importaciones</span>
                </NavLinkSideBar>
            </li>
            <li @click="setMenu('tipo_cambio')" v-show="permissions.includes('lista-productos')">
                <NavLinkSideBar icon-class="far fa-money-bill-alt"
                    class="flex items-center justify-start px-4 py-1.5 text-base font-medium"
                    :href="route('tipo_cambio.index')" :active="route().current('tipo_cambio.index')">
                    <span class="ml-3">Tipo de Cambio</span>
                </NavLinkSideBar>
            </li>

        </ul>

        <ul class="font-normal">
            <li @click="setMenu('roles')" v-show="permissions.includes('ver-roles')">
                <NavLinkSideBar icon-class="fas fa-user-tag"
                    class="flex items-center justify-start px-4 py-1.5 text-base font-medium" :href="route('roles.index')"
                    :active="route().current('roles.index')">
                    <span class="ml-3">Roles y Permisos</span>
                </NavLinkSideBar>
            </li>

        </ul>

    </div>
</template>
