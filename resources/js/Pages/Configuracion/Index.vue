

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, reactive, computed, watch } from 'vue'
import { Head, usePage,useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const tabla_categorias = ref({})
const titulo = "Configuración"
//buscador


const form=useForm({
    id:'',
    slug: '',
    key: '',
    value: '',
});



onMounted(() => {
    tabla_categorias.value = usePage().props.configuraciones;
});



</script>
<template>
    <div>

        <Head title="Configuraciones" />
        <AppLayout :pagina="[{ 'label': titulo, link: false }]">

            <div
            class="card px-4 py-3 mb-4 bg-white col-span-12 py-5 rounded-lg shadow-lg lg:col-span-4 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <div class="px-5 col-span-full flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Configuraciones</h1>
            </div>
            <div
                class="p-2 mb-4 bg-white col-span-4 lg:col-span-12 rounded-lg shadow-sm sm:p-4">

                <div :key="config.id" class="col-span-6 mt-3 shadow-default xl:col-span-6" v-for="config in tabla_categorias">
                    <InputLabel :for="config.slug" :value="config.key" class="text-base font-bold leading-6 text-gray-900" />
                    <TextInput :id="config.key" type="text" :value="config.value" />
                </div>
            </div>
            </div>

        </AppLayout>

    </div>
</template>


<style type="text/css" scoped></style>
