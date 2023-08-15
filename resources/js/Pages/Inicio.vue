<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head,usePage,Link } from '@inertiajs/vue3';
const { permissions } = usePage().props.auth
</script>

<template>
    <Head title="Inicio" />

    <AuthenticatedLayout>

        <div class=" px-5 col-span-full flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Inicio</h1>
        </div>

        <div
            class="px-4 py-0 mb-4 col-span-12 pb-5 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <div class="grid grid-cols-12 gap-4 p-4 lg:grid-cols-12">
                <a v-if="permissions.includes('historial-pedidos')"
                    class="p-4 col-span-12 lg:col-span-3  rounded-lg bg-primary-900 shadow-lg cursor-pointer hover:bg-primary-100 dark:hover:bg-gray-600 dark:bg-gray-700">
                    <div
                        class="flex justify-center text-white  items-center p-2 mx-auto mb-2">
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                    <div class="font-medium text-center text-white dark:text-gray-400">Historial Pedidos</div>
                </a>

                <a v-if="permissions.includes('importaciones')"
                class="p-4 col-span-12 lg:col-span-3  rounded-lg bg-primary-900 shadow-lg cursor-pointer  hover:bg-primary-100 dark:hover:bg-gray-600 dark:bg-gray-700">
                    <div
                    class="flex justify-center text-white items-center p-2 mx-auto mb-2">
                        <i class="fa fa-ship icon fa-2x"></i>
                    </div>
                    <div class="font-medium text-center text-white dark:text-gray-400">Importaciones</div>
                </a>
                <Link :href="route('usuarios.index')" method="get" as="button"
                class="p-4 col-span-12 lg:col-span-3  rounded-lg bg-primary-900 shadow-lg cursor-pointer  hover:bg-primary-100 dark:hover:bg-gray-600 dark:bg-gray-700"
                                           >
                                           <div
                    class="flex justify-center text-white items-center p-2 mx-auto mb-2">
                        <i class="fa fa-ship icon fa-2x"></i>
                    </div>
                    <div class="font-medium text-center text-white dark:text-gray-400">Importaciones</div>
                                        </Link>

            </div>


            <!--Contenido-->
        </div>


    </AuthenticatedLayout>
</template>
