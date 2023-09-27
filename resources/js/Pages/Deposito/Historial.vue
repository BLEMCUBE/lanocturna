

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';

import { FilterMatchMode } from 'primevue/api';
//const { permissions } = usePage().props.auth


import { useToast } from "primevue/usetoast";
const toast = useToast();
const titulo = "Depósitos Historial"
const ruta = 'depositos'
const lista_depositos = ref();

onMounted(() => {
    lista_depositos.value = usePage().props.historial.data;
});


const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },


});
</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <Toast />
            <div class=" px-5 pb-2 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>
            <div class="align-middle">
                <DataTable size="small" :filters="filters" :value="lista_depositos" scrollable scrollHeight="500px"
                    :virtualScrollerOptions="{ itemSize: 46 }" tableStyle="min-width: 50rem">

                    <template #header size="small" class="bg-secondary-900">
                        <div class="flex justify-content-end text-base text-primary-900">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                        </div>
                    </template>

                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>

                    <Column sortable field="sku" header="SKU" :pt="{
                        bodyCell: { class: 'text-center w-36' },
                        headerTitle: { class: 'text-center bg-secondary-100  w-36' },
                        headerContent: {
                            class: 'text-center uppercase w-36'
                        },
                        bodyCellContent: {
                            class: 'text-center w-36'
                        },
                    }">
                    </Column>
                    <Column sortable field="producto" header="producto" :pt="{
                        bodyCell: { class: 'text-center w-auto' },
                        headerTitle: {
                            class: 'uppercase text-center w-auto'
                        },
                        bodyCellContent: {
                            class: ' text-center w-auto'
                        },
                    }">
                    </Column>
                    <Column sortable field="bultos" header="bultos" :pt="{
                        bodyCell: { class: 'text-center  w-32' },
                        headerCell: { class: 'bg-secondary-100 w-32' },
                        headerTitle: {
                            class: 'text-center uppercase w-32'
                        },
                        bodyCellContent: {
                            class: ' text-center w-32'
                        },
                    }">
                    </Column>
                    <Column sortable field="origen" header="Déposito Origen" :pt="{
                        bodyCell: { class: 'text-center w-48' },

                        bodyCellContent: {
                            class: 'text-center w-48'
                        },
                        headerTitle: {
                            class: 'uppercase text-center w-48'
                        },
                    }"></Column>
                    <Column sortable field="destino" header="Déposito Destino" :pt="{
                        bodyCell: { class: 'text-center w-48' },
                        headerCell: { class: 'uppercase bg-secondary-100  w-48' },


                        headerTitle: {

                            class: 'text-center w-48'
                        },
                    }"></Column>

                    <Column sortable field="usuario" header="Usuario" :pt="{
                        bodyCell: { class: 'text-center w-40' },
                        headerCell: { class: 'uppercase bg-secondary-100  w-40' },
                        headerTitle: {

                            class: 'uppercase text-center w-40'
                        },
                        bodyCellContent: {

                            class: 'text-center w-40'
                        },
                    }"></Column>

                    <Column sortable field="fecha" header="Fecha" :pt="{
                        bodyCell: { class: 'text-center w-40' },
                        headerCell: { class: 'uppercase bg-secondary-100  w-40' },
                        headerTitle: {
                            class: 'uppercase text-center w-40'
                        },
                        bodyCellContent: {

                            class: 'text-center w-40'
                        },
                    }"></Column>




                </DataTable>

            </div>

            <!--Contenido-->
        </div>

    </AppLAyout>
</template>


<style type="text/css"></style>
