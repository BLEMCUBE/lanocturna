<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
const { permissions } = usePage().props.auth

</script>

<template>
    <Head title="Inicio" />
    <AppLayout>

        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <div class="px-5 pb-2 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">Panel</h5>

            </div>
            <div class="px-2 grid grid-cols-12 gap-4 mb-2">
                <!--Contenido-->


                <a v-if="permissions.includes('historial-pedidos')"
                    class="p-4 col-span-12 lg:col-span-3  rounded-lg bg-primary-900 shadow-lg cursor-pointer hover:bg-primary-100 dark:hover:bg-gray-600 dark:bg-gray-700">
                    <div class="flex justify-center text-white  items-center p-2 mx-auto mb-2">
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                    <div class="font-medium text-center text-white dark:text-gray-400">Historial Pedidos</div>
                </a>

                <a v-if="permissions.includes('importaciones')"
                    class="p-4 col-span-12 lg:col-span-3  rounded-lg bg-primary-900 shadow-lg cursor-pointer  hover:bg-primary-100 dark:hover:bg-gray-600 dark:bg-gray-700">
                    <div class="flex justify-center text-white items-center p-2 mx-auto mb-2">
                        <i class="fa fa-ship icon fa-2x"></i>
                    </div>
                    <div class="font-medium text-center text-white dark:text-gray-400">Importaciones</div>
                </a>
                <Link :href="route('usuarios.index')" method="get" as="button"
                    class="p-4 col-span-12 lg:col-span-3  rounded-lg bg-primary-900 shadow-lg cursor-pointer  hover:bg-primary-100 dark:hover:bg-gray-600 dark:bg-gray-700">
                <div class="flex justify-center text-white items-center p-2 mx-auto mb-2">
                    <i class="fa fa-ship icon fa-2x"></i>
                </div>
                <div class="font-medium text-center text-white dark:text-gray-400">Importaciones</div>
                </Link>

                <!--Contenido-->
            </div>
        </div>

    </AppLayout>
</template>
