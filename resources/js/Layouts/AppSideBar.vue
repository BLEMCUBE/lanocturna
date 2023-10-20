<script setup>
import { onMounted, computed, watch, ref } from "vue";
import NavLinkSideBar from '@/Components/NavLinkSideBar.vue';
import NavLinkSideBarNotIcon from '@/Components/NavLinkSideBarNotIcon.vue';
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

            <!--Reportes-->
            <Disclosure as="div" class="p-0" v-slot="{ open }" :default-open="configStore.getCurrentMenu == 'reportes'"
                v-show="permissions.includes('menu-reportes')">
                <h3 class="flow-root text-white hover:text-primary-900">
                    <DisclosureButton
                        class="flex w-full items-center py-2 justify-between bg-primary-900  hover:bg-secondary-100  text-sm text-white hover:text-primary-900">
                        <div
                            class="font-medium static  flex justify-start items-center w-full py-2 text-white hover:bg-secondary-100 hover:text-primary-900">
                            <div
                                class="font-medium  absolute right-0  uppercase tracking-wide flex hover:bg-secondary-100 justify-start items-center  text-base w-full px-2 py-2 text-white hover:text-primary-900">
                                <i class="fas fa-file-contract mr-3 ml-1"></i>
                                Reportes
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
                        <li @click="setMenu('reportes')" class="w-full"
                            v-show="permissions.includes('reporte-grafico-ventas')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('reportes.ventas')" :active="route().current('reportes.ventas')">
                                <span class="ml-2 uppercase">Gráfico Ventas</span>
                            </NavLinkSideBarNotIcon>
                        </li>

                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('reportes')" class="w-full"
                            v-show="permissions.includes('reporte-productos-vendidos')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('reportes.productosvendidos')"
                                :active="route().current('reportes.productosvendidos')">
                                <span class="ml-2 uppercase">PRODUCTOS VENDIDOS</span>
                            </NavLinkSideBarNotIcon>
                        </li>

                    </div>


                </DisclosurePanel>
            </Disclosure>
            <!--Reportes-->

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
                        <li @click="setMenu('configuraciones')" class="w-full"
                            v-show="permissions.includes('lista-usuarios')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('usuarios.index')" :active="route().current('usuarios.index')">
                                <span class="ml-2 uppercase">Usuarios</span>
                            </NavLinkSideBarNotIcon>
                        </li>

                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('configuraciones')" class="w-full" v-show="permissions.includes('ver-roles')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('roles.index')" :active="route().current('roles.index')">
                                <span class="ml-2 uppercase">Roles y Permisos</span>
                            </NavLinkSideBarNotIcon>
                        </li>

                    </div>

                    <div class="flex items-center">
                        <li @click="setMenu('configuraciones')" class="w-full"
                            v-show="permissions.includes('lista-tipocambio')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('tipo_cambio.index')" :active="route().current('tipo_cambio.index')">
                                <span class="ml-2 uppercase">Tipo de Cambio</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('configuraciones')" class="w-full"
                            v-show="permissions.includes('lista-tipocambio-yuanes')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('tipo_cambio_yuan.index')" :active="route().current('tipo_cambio_yuan.index')">
                                <span class="ml-2 uppercase">Tipo de Cambio Yuanes</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>
                    <div class="flex items-center">


                        <li @click="setMenu('configuraciones')" class="w-full"
                            v-show="permissions.includes('configuraciones')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('configuraciones.index')" :active="route().current('configuraciones.index')">
                                <span class="ml-2 uppercase">Código Maestro</span>
                            </NavLinkSideBarNotIcon>
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
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('importaciones.index')" :active="route().current('importaciones.index')">
                                <span class="ml-2 uppercase">importaciones</span>
                            </NavLinkSideBarNotIcon>
                        </li>

                    </div>

                    <div class="flex items-center">

                        <li @click="setMenu('compras')" class="w-full" v-show="permissions.includes('crear-ventas')">
                            <NavLinkSideBarNotIcon :href="route('compras.create')"
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :active="route().current('compras.create')">
                                <span class="ml-2 uppercase">Compra en plaza</span>
                            </NavLinkSideBarNotIcon>
                        </li>

                    </div>

                    <div class="flex items-center">
                        <li @click="setMenu('compras')" class="w-full" v-show="permissions.includes('lista-ventas')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('compras.index')" :active="route().current('compras.index')">
                                <span class="ml-2 uppercase">Historial de Compras</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('compras')" class="w-full" v-show="permissions.includes('rotacion-stock')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('rotacion-stock.index')" :active="route().current('rotacion-stock.index')">
                                <span class="ml-2 uppercase">Rotación de stock</span>
                            </NavLinkSideBarNotIcon>
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
                            <NavLinkSideBarNotIcon :href="linkCrear"
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :active="route().current('ventas.create')">
                                <span class="ml-2 uppercase">Crear Venta</span>
                            </NavLinkSideBarNotIcon>
                        </li>

                    </div>

                    <div class="flex items-center">
                        <li @click="setMenu('ventas')" class="w-full" v-show="permissions.includes('lista-cajas')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('cajas.index')" :active="route().current('cajas.index')">
                                <span class="ml-2 uppercase">Caja</span>
                            </NavLinkSideBarNotIcon>
                        </li>

                    </div>

                    <div class="flex items-center">
                        <li @click="setMenu('ventas')" class="w-full" v-show="permissions.includes('subir-envios')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('envios.create')" :active="route().current('envios.create')">
                                <span class="ml-2 uppercase">Mercado Libre</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('ventas')" class="w-full" v-show="permissions.includes('lista-expediciones')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('expediciones.index')" :active="route().current('expediciones.index')">
                                <span class="ml-2 uppercase">Expedición</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('ventas')" class="w-full" v-show="permissions.includes('lista-envios')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('envios.index')" :active="route().current('envios.index')">
                                <span class="ml-2 uppercase ">Envios</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('ventas')" class="w-full" v-show="permissions.includes('historial-envios')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('envios.historial')" :active="route().current('envios.historial')">
                                <span class="ml-2 uppercase">Historial de Envios</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('ventas')" class="w-full" v-show="permissions.includes('lista-ventas')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('ventas.index')" :active="route().current('ventas.index')">
                                <span class="ml-2 uppercase">Historial de Ventas</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>
                </DisclosurePanel>
            </Disclosure>
            <!--Ventas-->

            <!--Deposito-->
            <Disclosure as="div" class="py-1  hover:text-primary-900" v-slot="{ open }"
                :default-open="configStore.getCurrentMenu == 'depositos'" v-show="permissions.includes('menu-depositos')">
                <h3 class="text-white hover:text-primary-900">
                    <DisclosureButton
                        class="flex w-full items-center p-2 mx-1 justify-between bg-primary-900  hover:bg-secondary-100  text-sm text-white hover:text-primary-900">
                        <div
                            class="font-medium static  flex justify-start items-center w-full py-2 text-white hover:bg-secondary-100 hover:text-primary-900">
                            <div
                                class="font-medium  absolute right-0  uppercase tracking-wide flex hover:bg-secondary-100 justify-start items-center  text-base w-full px-2 py-2 text-white hover:text-primary-900">
                                <i class="fa-solid fa-warehouse mr-3 ml-1"></i>
                                Depósito
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
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('depositos.index')" :active="route().current('depositos.index')">
                                <span class="ml-2 uppercase">Depósitos</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>

                    <div class="flex items-center">
                        <li @click="setMenu('depositos')" class="w-full" v-show="permissions.includes('subir-depositos')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('depositos.create')" :active="route().current('depositos.create')">
                                <span class="ml-2 uppercase">SUBIR BULTOS DEPÓSITO</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('depositos')" class="w-full" v-show="permissions.includes('bultos-depositos')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('depositos.bultos')" :active="route().current('depositos.bultos')">
                                <span class="ml-2 uppercase">Bultos Importados</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('depositos')" class="w-full"
                            v-show="permissions.includes('historial-depositos')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('depositos.historial')" :active="route().current('depositos.historial')">
                                <span class="ml-2 uppercase">Historial De Depósito</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('depositos')" class="w-full" v-show="permissions.includes('nombre-depositos')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('depositoslista.index')" :active="route().current('depositoslista.index')">
                                <span class="ml-2 uppercase">Nombre Depósito</span>
                            </NavLinkSideBarNotIcon>
                        </li>
                    </div>

                </DisclosurePanel>
            </Disclosure>
            <!--Deposito-->

            <!--RMA-->
            <Disclosure as="div" class="py-1  hover:text-primary-900" v-slot="{ open }"
                :default-open="configStore.getCurrentMenu == 'rma'" v-show="permissions.includes('menu-rma')">
                <h3 class="text-white hover:text-primary-900">
                    <DisclosureButton
                        class="flex w-full items-center p-2 mx-1 justify-between bg-primary-900  hover:bg-secondary-100  text-sm text-white hover:text-primary-900">
                        <div
                            class="font-medium static  flex justify-start items-center w-full py-2 text-white hover:bg-secondary-100 hover:text-primary-900">
                            <div
                                class="font-medium  absolute right-0  uppercase tracking-wide flex hover:bg-secondary-100 justify-start items-center  text-base w-full px-2 py-2 text-white hover:text-primary-900">
                                <i class="fa-solid fa-warehouse mr-3 ml-1"></i>
                                RMA
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
                        <li @click="setMenu('rma')" class="w-full" v-show="permissions.includes('lista-rma')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('rmas.index')" :active="route().current('rmas.index')">
                                <span class="ml-2 uppercase">LISTADO RMA</span>
                            </NavLinkSideBarNotIcon>
                        </li>

                    </div>
                    <div class="flex items-center">
                        <li @click="setMenu('rma')" class="w-full" v-show="permissions.includes('historial-rma')">
                            <NavLinkSideBarNotIcon
                                class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                                :href="route('depositos.index')" :active="route().current('depositos.index')">
                            <span class="ml-2 uppercase">HISTORIAL RMA</span>
                        </NavLinkSideBarNotIcon>
                    </li>

                </div>
                <div class="flex items-center">
                    <li @click="setMenu('rma')" class="w-full" v-show="permissions.includes('subir-rma')">
                        <NavLinkSideBarNotIcon
                            class="flex items-center justify-start pl-6 pr-3 py-2 text-base font-medium"
                            :href="route('depositos.index')" :active="route().current('depositos.index')">
                            <span class="ml-2 uppercase">SUBIR ENVIO RMA</span>
                        </NavLinkSideBarNotIcon>
                    </li>

                </div>
            </DisclosurePanel>
        </Disclosure>
        <!--RMA-->

    </ul>

</div></template>
