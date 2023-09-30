<script setup>
import { onMounted, computed, watch, ref } from "vue";
import NavLinkSideBar from '@/Components/NavLinkSideBar.vue';
import { usePage, router } from '@inertiajs/vue3';
import { useConfigStore } from '@/store/config.js'
import Swal from 'sweetalert2';
import {
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
} from '@headlessui/vue'

import { ChevronDownIcon, ChevronUpIcon } from '@heroicons/vue/20/solid'
const props = defineProps({
    isOpen: {
        type: Boolean,
        default: true,

    },
});



const { permissions } = usePage().props.auth
const { hoy_tipo_cambio } = usePage().props.auth
const configStore = useConfigStore();
const classes = computed(() => props.isOpen ? 'sm:translate-x-0' : 'sm:hidden sm:translate-x-0');
const setMenu = (menu) => {
    configStore.showMenu(menu);
}
const linkCrear = ref('');

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

const showDropdown = ref(false)

</script>

<template>
    <div class="layout-menu">


        <!-- Productos Link -->

        <ul>
            <li @click="setMenu('inicio')">

                <NavLinkSideBar icon-class="fas fa-home"
                    class="flex items-center justify-start px-3 py-2 text-base font-medium" :href="route('inicio')"
                    :active="route().current('inicio')">
                    <span class="ml-2 uppercase">Inicio</span>

                </NavLinkSideBar>
            </li>

            <li @click="setMenu('productos')" v-show="permissions.includes('lista-productos')">
                <NavLinkSideBar icon-class="fas fa-boxes"
                    class="flex items-center justify-start px-3 py-2 text-base font-medium" :href="route('productos.index')"
                    :active="route().current('productos.index')">
                    <span class="ml-2 uppercase">Productos</span>
                </NavLinkSideBar>
            </li>
            <!--Configuraciones-->
            <Disclosure as="div" class="p-0" v-slot="{ open }"
                :default-open="configStore.getCurrentMenu == 'configuraciones'"
                v-show="permissions.includes('menu-configuraciones')">
                <h3 class="flow-root text-white hover:text-primary-900">
                    <DisclosureButton
                        class="flex w-full items-center py-2 justify-between bg-primary-900  hover:bg-secondary-100  text-sm text-white hover:text-primary-900">
                        <div
                            class="font-medium static  flex justify-start items-center w-full py-2 text-white hover:bg-secondary-100 hover:text-primary-900">
                            <div
                                class="font-medium  absolute right-0  uppercase tracking-wide flex hover:bg-secondary-100 justify-start items-center  text-base w-full px-2 py-2 text-white hover:text-primary-900">
                                <i class="fas fa-cog mr-3 ml-1"></i>
                                Configuraciones
                                <div class="pr-2 py-4 absolute right-0 z-50 ">
                                    <ChevronDownIcon v-if="!open" class="h-8 w-8" aria-hidden="true" />
                                    <ChevronUpIcon v-else class="h-8 w-8" aria-hidden="true" />

                                </div>
                            </div>

                        </div>

                    </DisclosureButton>
                </h3>
                <DisclosurePanel class="pt-1" as="div">

                    <div class="flex items-center">
                        <li @click="setMenu('configuraciones')" class="w-full" v-show="permissions.includes('lista-usuarios')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('usuarios.index')" :active="route().current('usuarios.index')">
                                <span class="ml-2 uppercase">Usuarios</span>
                            </NavLinkSideBar>
                        </li>

                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('configuraciones')" class="w-full" v-show="permissions.includes('ver-roles')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('roles.index')" :active="route().current('roles.index')">
                                <span class="ml-2 uppercase">Roles y Permisos</span>
                            </NavLinkSideBar>
                        </li>

                    </div>

                    <div class="flex items-center">
                        <li @click="setMenu('configuraciones')" class="w-full"
                            v-show="permissions.includes('lista-tipocambio')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('tipo_cambio.index')" :active="route().current('tipo_cambio.index')">
                                <span class="ml-2 uppercase">Tipo de Cambio</span>
                            </NavLinkSideBar>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('configuraciones')" class="w-full"
                            v-show="permissions.includes('lista-tipocambio')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('tipo_cambio_yuan.index')" :active="route().current('tipo_cambio_yuan.index')">
                                <span class="ml-2 uppercase">Tipo de Cambio Yuanes</span>
                            </NavLinkSideBar>
                        </li>
                    </div>
                    <div class="flex items-center">


                        <li @click="setMenu('configuraciones')" class="w-full"
                            v-show="permissions.includes('configuraciones')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('configuraciones.index')" :active="route().current('configuraciones.index')">
                                <span class="ml-2 uppercase">Código Maestro</span>
                            </NavLinkSideBar>
                        </li>
                    </div>
                </DisclosurePanel>
            </Disclosure>
            <!--Configuraciones-->

            <!--Compras-->
            <Disclosure as="div" class="py-1" v-slot="{ open }" :default-open="configStore.getCurrentMenu == 'compras'"
                v-show="permissions.includes('menu-compras')">
                <h3 class="flow-root text-white hover:text-primary-900">
                    <DisclosureButton
                        class="flex w-full items-center p-2 justify-between bg-primary-900  hover:bg-secondary-100  text-sm text-white hover:text-primary-900">
                        <div
                            class="font-medium static  flex justify-start items-center w-full py-2 text-white hover:bg-secondary-100 hover:text-primary-900">
                            <div
                                class="font-medium  absolute right-0  uppercase tracking-wide flex hover:bg-secondary-100 justify-start items-center  text-base w-full px-2 py-2 text-white hover:text-primary-900">
                                <i class="fa fa-shopping-cart mr-3 ml-1"></i>
                                Compras
                                <div class="pr-2 py-4 absolute right-0 z-50 ">
                                    <ChevronDownIcon v-if="!open" class="h-8 w-8" aria-hidden="true" />
                                    <ChevronUpIcon v-else class="h-8 w-8" aria-hidden="true" />

                                </div>
                            </div>

                        </div>

                    </DisclosureButton>
                </h3>

                <DisclosurePanel class="pt-1" as="div">

                    <div class="flex items-center">
                        <li @click="setMenu('compras')" class="w-full" v-show="permissions.includes('lista-importaciones')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('importaciones.index')" :active="route().current('importaciones.index')">
                                <span class="ml-2 uppercase">importaciones</span>
                            </NavLinkSideBar>
                        </li>

                    </div>

                    <div class="flex items-center">

                        <li @click="setMenu('compras')" class="w-full" v-show="permissions.includes('crear-ventas')">
                            <NavLinkSideBar :href="route('compras.create')"
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :active="route().current('compras.create')">
                                <span class="ml-2 uppercase">Compra en plaza</span>
                            </NavLinkSideBar>
                        </li>

                    </div>

                    <div class="flex items-center">
                        <li @click="setMenu('compras')" class="w-full" v-show="permissions.includes('lista-ventas')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('compras.index')" :active="route().current('compras.index')">
                                <span class="ml-2 uppercase">Historial de Compras</span>
                            </NavLinkSideBar>
                        </li>
                    </div>
                </DisclosurePanel>
            </Disclosure>
            <!--Compras-->

            <!--Ventas-->
            <Disclosure as="div" class="py-1" v-slot="{ open }" :default-open="configStore.getCurrentMenu == 'ventas'"
                v-show="permissions.includes('menu-ventas')">
                <h3 class="flow-root text-white hover:text-primary-900">

                    <DisclosureButton
                        class="flex w-full items-center p-2 justify-between bg-primary-900  hover:bg-secondary-100  text-sm text-white hover:text-primary-900">
                        <div
                            class="font-medium static  flex justify-start items-center w-full py-2 text-white hover:bg-secondary-100 hover:text-primary-900">
                            <div
                                class="font-medium  absolute right-0  uppercase tracking-wide flex hover:bg-secondary-100 justify-start items-center  text-base w-full px-2 py-2 text-white hover:text-primary-900">
                                <i class="fas fa-cash-register mr-3 ml-1"></i>
                                Ventas
                                <div class="pr-2 my-4 absolute right-0 ">
                                    <ChevronDownIcon v-if="!open" class="h-8 w-8" aria-hidden="true" />
                                    <ChevronUpIcon v-else class="h-8 w-8" aria-hidden="true" />

                                </div>
                            </div>

                        </div>

                    </DisclosureButton>
                </h3>
                <DisclosurePanel class="pt-1" as="div">

                    <div class="flex items-center">
                        <li @click="setMenu('ventas'); btnCrear()" class="w-full"
                            v-show="permissions.includes('crear-ventas')">
                            <NavLinkSideBar :href="linkCrear"
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :active="route().current('ventas.create')">
                                <span class="ml-2 uppercase">Crear Venta</span>
                            </NavLinkSideBar>
                        </li>

                    </div>

                    <div class="flex items-center">
                        <li @click="setMenu('ventas')" class="w-full" v-show="permissions.includes('lista-cajas')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('cajas.index')" :active="route().current('cajas.index')">
                                <span class="ml-2 uppercase">Caja</span>
                            </NavLinkSideBar>
                        </li>

                    </div>

                    <div class="flex items-center">
                        <li @click="setMenu('ventas')" class="w-full" v-show="permissions.includes('subir-envios')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('envios.create')" :active="route().current('envios.create')">
                                <span class="ml-2 uppercase">Mercado Libre</span>
                            </NavLinkSideBar>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('ventas')" class="w-full" v-show="permissions.includes('lista-expediciones')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('expediciones.index')" :active="route().current('expediciones.index')">
                                <span class="ml-2 uppercase">Expedición</span>
                            </NavLinkSideBar>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('ventas')" class="w-full" v-show="permissions.includes('lista-envios')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('envios.index')" :active="route().current('envios.index')">
                                <span class="ml-2 uppercase ">Envios</span>
                            </NavLinkSideBar>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('ventas')" class="w-full" v-show="permissions.includes('historial-envios')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('envios.historial')" :active="route().current('envios.historial')">
                                <span class="ml-2 uppercase">Historial de Envios</span>
                            </NavLinkSideBar>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('ventas')" class="w-full" v-show="permissions.includes('lista-ventas')">
                            <NavLinkSideBar class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('ventas.index')" :active="route().current('ventas.index')">
                                <span class="ml-2 uppercase">Historial de Ventas</span>
                            </NavLinkSideBar>
                        </li>
                    </div>
                </DisclosurePanel>
            </Disclosure>
            <!--Ventas-->

            <!--Deposito-->
            <Disclosure as="div" class="py-1  hover:text-primary-900" v-slot="{ open }"
                :default-open="configStore.getCurrentMenu == 'servicios'" v-show="permissions.includes('menu-servicios')">
                <h3 class="text-white hover:text-primary-900">
                    <DisclosureButton
                        class="flex w-full items-center p-2 mx-1 justify-between bg-primary-900  hover:bg-secondary-100  text-sm text-white hover:text-primary-900">
                        <div
                            class="font-medium static  flex justify-start items-center w-full py-2 text-white hover:bg-secondary-100 hover:text-primary-900">
                            <div
                                class="font-medium  absolute right-0  uppercase tracking-wide flex hover:bg-secondary-100 justify-start items-center  text-base w-full px-2 py-2 text-white hover:text-primary-900">
                                <i class="fa-solid fa-warehouse mr-3 ml-1"></i>
                                Servicio Ténico
                                <div class="pr-2 py-4 absolute right-0 z-50 ">
                                    <ChevronDownIcon v-if="!open" class="h-8 w-8" aria-hidden="true" />
                                    <ChevronUpIcon v-else class="h-8 w-8" aria-hidden="true" />

                                </div>
                            </div>

                        </div>

                    </DisclosureButton>
                </h3>
                <DisclosurePanel class="pt-1" as="div">

                    <div class="flex items-center">
                        <li @click="setMenu('depositos')" class="w-full" v-show="permissions.includes('lista-depositos')">
                            <NavLinkSideBar
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('depositos.index')" :active="route().current('depositos.index')">
                                <span class="ml-2 uppercase">Servicios</span>
                            </NavLinkSideBar>
                        </li>

                    </div>

                </DisclosurePanel>
            </Disclosure>
            <!--Servicios-->

        </ul>

    </div>
</template>
