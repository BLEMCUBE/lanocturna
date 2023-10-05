<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const { user } = usePage().props.auth

</script>

<template>
    <Head title="Inicio" />
    <AppLayout>

        <div class="card px-4 mb-4 col-span-12 rounded-lg">
            <div class="col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">Hola, {{ user.name }}</h5>
            </div>

        </div>

    </AppLayout>
</template>
