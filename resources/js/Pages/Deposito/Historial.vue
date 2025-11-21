

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';

const titulo = "Depósitos Historial"
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
            class="card p-3 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <div class=" px-5 pb-2 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>
            <div class="align-middle">
                <DataTable size="small"  :filters="filters" :value="lista_depositos"
                scrollable scrollHeight="800px"
                paginator :rows="50"
                :pt="{
                    root:{class:'text-xs'}
                }"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    tableStyle="width: 100%">

                    <template #header size="small" class="bg-secondary-900">
                        <div class="flex justify-content-end text-sm text-primary-900">
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
                        bodyCell: { class: 'text-center w-72' },
                        headerTitle: {
                            class: 'uppercase text-center w-72'
                        },
                        bodyCellContent: {
                            class: ' text-center w-72'
                        },
                    }">
                    </Column>
                    <Column sortable field="bultos" header="bultos" :pt="{
                        bodyCell: { class: 'text-center  w-24' },
                        headerCell: { class: 'bg-secondary-100 w-24' },
                        headerTitle: {
                            class: 'text-center uppercase w-24'
                        },
                        bodyCellContent: {
                            class: ' text-center w-24'
                        },
                    }">
                    </Column>
                    <Column sortable field="pcs_bulto" header="pcs bulto" :pt="{
                        bodyCell: { class: 'text-center  w-24' },
                        headerCell: { class: 'bg-secondary-100 w-24' },
                        headerTitle: {
                            class: 'text-center uppercase w-24'
                        },
                        bodyCellContent: {
                            class: ' text-center w-24'
                        },
                    }">
                    </Column>
                    <Column sortable field="origen" header="Depósito Origen" :pt="{
                        bodyCell: { class: 'text-center w-48' },

                        bodyCellContent: {
                            class: 'text-center w-48'
                        },
                        headerTitle: {
                            class: 'uppercase text-center w-48'
                        },
                    }"></Column>
                    <Column sortable field="destino" header="Depósito Destino" :pt="{
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
