<template>
    <nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start">
                    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar"
                        type="button"
                        class="inline-flex items-center p-2 text-sm text-gray-500 rounded-md sm:hidden hover:bg-gray-100 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-700">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd" fill-rule="evenodd"
                                d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z">
                            </path>
                        </svg>
                    </button>
                    <Link :href="route('inicio')" class="flex ml-2 md:mr-24 dark:fill-white">
                    <ApplicationLogo class="h-8 mr-3"></ApplicationLogo>
                    <span
                        class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white">Inventario</span>
                    </Link>
                </div>


                <!--Botones-->
                <div class="flex items-center">


                    <div class="flex items-center ml-3">
                        <button @click="toggleDark()" id="theme-toggle" data-tooltip-target="tooltip-toggle" type="button"
                            class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none rounded text-sm p-2">
                            <svg id="theme-toggle-dark-icon" v-if="!isDark" class="w-5 h-5" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                            <svg id="theme-toggle-light-icon" v-if="isDark" class="w-5 h-5" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                    fill-rule="evenodd" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <div id="tooltip-toggle" role="tooltip"
                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-sm opacity-0 tooltip">
                            {{ !isDark ? 'Modo Oscuro' : 'Modo Claro' }}

                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                        <p class="text-sm font-medium text-gray-900 truncate dark:text-gray-300 ml-5" role="none">
                            {{ $page.props.auth.user.name }}
                        </p>

                        <!-- Profile -->
                        <div class="flex items-center ml-3">
                            <div>
                                <button type="button" class="flex text-sm bg-gray-800 rounded-full" aria-expanded="false"
                                    data-dropdown-toggle="dropdown-user">
                                    <span class="sr-only">Abrir Menu</span>
                                    <img class="w-8 h-8 rounded-full" :src="$page.props.auth.user.photo" alt="user photo">
                                </button>
                            </div>
                            <!-- Dropdown menu -->

                            <div class="z-50 hidden my-1 text-base list-none bg-white divide-y divide-gray-100 rounded shadow dark:bg-gray-700 dark:divide-gray-600"
                                id="dropdown-user">
                                <div class="px-4 py-3" role="none">
                                    <p class="text-sm text-gray-900 dark:text-white" role="none">
                                        {{ $page.props.auth.user.name }}
                                    </p>
                                </div>
                                <ul class="py-1" role="none">
                                    <li>
                                        <Link :href="route('logout')" method="post" as="button"
                                            class="block w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white"
                                            role="menuitem">
                                        Cerrar Sesión
                                        </Link>
                                    </li>

                                </ul>
                            </div>


                        </div>
                    </div>
                </div>
                <!--/Botones-->
            </div>
        </div>
    </nav>
</template>
<script setup>
import { computed } from 'vue'
import { useToggle, useDark } from '@vueuse/core'

import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from './ApplicationLogo.vue';
import { useConfigStore } from '@/store/config'

const configStore = useConfigStore();
const { notificaciones } = usePage().props.auth;
import moment from 'moment';
import 'moment/dist/locale/es.js';
moment.locale('es');
var now = moment();
const isDark = useDark();
const toggleDark = useToggle(isDark);
const expanded = computed(() => {
    return configStore.isOpen;
});


const toggleMobileMenu = () => {
    let sen = !expanded.value
    configStore.showSide(sen)
}


</script>
