

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import CambiarDepositoModal from '@/Pages/Deposito/Partials/CambiarDepositoModal.vue';

import { FilterMatchMode } from 'primevue/api';
const tabla_clientes = ref()
//const { permissions } = usePage().props.auth
const expandedRows = ref([]);
import { useToast } from "primevue/usetoast";
const toast = useToast();
const titulo = "Depósitos"
const ruta = 'depositos'
const lista_depositos = ref([]);


const formDelete = useForm({
    id: '',
});

onMounted(() => {
    lista_depositos.value = usePage().props.depositos;
    expandedRows.value = lista_depositos.value.filter((p) => p.id);

});


const collapseAll = () => {
    expandedRows.value = null;
};

const expandAll = () => {
    expandedRows.value = lista_depositos.value.filter((p) => p.id);
};
const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

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
                <DataTable showGridlines v-model:expandedRows="expandedRows" size="small" v-model:filters="filters"
                :globalFilterFields="['origen','nombre']"  :value="lista_depositos" resizableColumns sortField="created_at" :sortOrder="-1" :filters="filters"
                    scrollable scrollHeight="400px" :virtualScrollerOptions="{ itemSize: 46 }"
                    tableStyle="min-width: 50rem">

                    <template #header size="small" class="bg-secondary-900">
                        <div class="flex justify-content-end text-md text-primary-900">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                            <Button text icon="pi pi-plus" label="Expandir todo" @click="expandAll" />
                            <Button text icon="pi pi-minus" label="Contraer todo" @click="collapseAll" />
                        </div>
                    </template>

                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>

                    <Column expander style="width: 3rem" :pt="{
                        bodyCell: { class: 'bg-secondary-100/40 font-bold' }
                    }" />
                    <Column header="Imagen" :pt="{
                        bodyCell: { class: 'w-12 bg-secondary-100/40 font-bold border-2 border-primary-100' },

                    }">
                        <template #body="slotProps">
                            <img class="rounded mx-auto bg-white shadow-2xl border-2 text-center w-10 h-10 object-contain"
                                :src="slotProps.data.imagen" alt="image">
                        </template>
                    </Column>
                    <Column sortable field="origen" header="Origen" :pt="{
                        bodyCell: { class: 'bg-secondary-100/40 font-bold' }
                    }"></Column>
                    <Column sortable field="nombre" header="Producto" :pt="{
                        bodyCell: { class: 'bg-secondary-100/40 font-bold' }
                    }"></Column>

                    <Column header="Bultos" :pt="{
                        bodyCell: { class: 'bg-secondary-100/40 font-bold w-12 text-center' }
                    }">
                        <template #body="slotProps">
                            {{  (slotProps.data.deposito_detalles.reduce((acc, cur) => acc + parseFloat(cur['bultos']), 0)) }}
                        </template>
                    </Column>

                    <template #expansion="slotProps">
                        <div class="px-1">
                            <DataTable showGridlines :value="slotProps.data.deposito_detalles">
                                <Column field="importacion.nro_carpeta" header="Nro Carpeta" sortable :pt="{
                                    bodyCell: { class: 'text-center' },
                                    headerCell: { class: 'text-white bg-primary-100/50 p-0 m-0 w-24' }

                                }">
                                    {{ slotProps.data }}</Column>
                                <Column field="importacion.nro_contenedor" header="Nro Contenedor" sortable :pt="{
                                    bodyCell: { class: 'text-center' },
                                    headerCell: { class: 'text-white bg-primary-100/50 p-0 m-0 w-24' }

                                }">
                                    {{ slotProps.data }}</Column>
                                <Column field="bultos" header="Bultos" sortable :pt="{
                                    bodyCell: { class: 'text-center p-0 m-0' },
                                    headerCell: { class: 'text-white bg-primary-100/50 p-0 m-0 w-24' }

                                }">
                                    {{ slotProps.data }}</Column>

                                <Column field="deposito.nombre" header="Depósito" sortable :pt="{
                                    bodyCell: { class: 'text-center' },
                                    headerCell: { class: 'text-white bg-primary-100/50 p-0 m-0 w-24' }

                                }"></Column>
                                <Column header="Acciones" :pt="{
                                    bodyCell: { class: 'text-center' },
                                    headerCell: { class: 'text-white bg-primary-100/50 p-0 m-0 w-10' }

                                }">
                                    <template #body="slotProps">
                                        <div v-if="slotProps.data.importacion.estado == 'Arribado'">
                                            <span
                                                class="inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-primary-100">
                                                <CambiarDepositoModal :detalle-id="slotProps.data.id">
                                                </CambiarDepositoModal>
                                            </span>
                                        </div>

                                    </template>
                                </Column>

                            </DataTable>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!--Contenido-->
        </div>

    </AppLAyout>
</template>


<style type="text/css"></style>
