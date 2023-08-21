<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, Link } from '@inertiajs/vue3';
import { onMounted, reactive, ref, watch } from 'vue';
import { useLayout } from '@/composables/layout';
import { Head } from '@inertiajs/vue3';

const { permissions } = usePage().props.auth
</script>

<template>
    <Head title="Inicio" />
    <AppLayout>

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

    </AppLayout>
</template>
