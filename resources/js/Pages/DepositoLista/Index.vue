

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage } from '@inertiajs/vue3';
import CrearModal from '@/Pages/DepositoLista/Partials/CrearModal.vue';
import EditarModal from '@/Pages/DepositoLista/Partials/EditarModal.vue';

import { FilterMatchMode } from 'primevue/api';
const tabla_clientes = ref()
const titulo = "Nombre Depósito"
onMounted(() => {
    tabla_clientes.value = usePage().props.tipo_cambio.data;
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },

});
</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div
            class="px-4 py-3 mb-4 bg-white col-span-12  rounded-lg shadow-lg lg:col-span-6">
            <!--Contenido-->
            <div class=" px-5 pb-2 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
                <CrearModal></CrearModal>
            </div>
            <div class="align-middle">
                <DataTable  size="small" v-model:filters="filters" :value="tabla_clientes" :paginator="true"
                    :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    tableStyle="width: 100%">
                    <template #header size="small" class="bg-secondary-900">
                        <div class="flex justify-content-end text-md">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                        </div>
                    </template>
                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>
                    <Column field="id" header="ID" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="nombre" header="Depósito" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="descripcion" header="Descripción" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>

                    <Column header="Acciones" style="width:80px"
                    :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }">
                        <template #body="slotProps">
                            <div>
                                <span
                                class="inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-primary-100">
                                <EditarModal :cliente-id="slotProps.data.id"></EditarModal>
                            </span>
                        </div>
                            <!--
                                <span

                                class="inline-block rounded bg-red-700 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-red-600">
                                <button @click.prevent="eliminar(slotProps.data.id, slotProps.data.name)"><i
                                    class="fas fa-trash-alt"></i></button>
                                </span>
                            -->
                        </template>
                    </Column>
                </DataTable>
            </div>

            <!--Contenido-->
        </div>

    </AppLAyout>
</template>


<style type="text/css"></style>
