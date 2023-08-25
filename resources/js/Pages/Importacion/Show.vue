<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
const { importacion } = usePage().props
const { importacion_detalle } = usePage().props


const titulo = "Detalle Importación"
const ruta = 'importaciones'


onMounted(() => {

    //importacion.value = usePage().props.importacion;


});

const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
});
</script>
<template>
    <Head :title="titulo" />
    <AppLayout
        :pagina="[{ 'label': 'Importaciones', link: true, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-0 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <div class="mb-5 px-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div class="grid grid-cols-6 my-2 col-span-12 ">
                <div class="mx-5 col-span-6 gap-4 m-2 lg:col-span-3 flex">
                    <b> No. de Carpeta:</b>
                    <p>{{ importacion.nro_carpeta }}</p>
                </div>
                <div class="mx-5 col-span-6 gap-4 m-2 lg:col-span-3 flex">
                    <b> BL o No. de Contenedor:</b>
                    <p>{{ importacion.nro_contenedor }}</p>
                </div>
                <div class="mx-5 col-span-6 gap-4 m-2 lg:col-span-3 flex">
                    <b>Total:</b>
                    <p>{{ importacion.total }}</p>
                </div>
                <div class="mx-5 col-span-6 gap-4 m-2 lg:col-span-3 flex">
                    <b>Fecha :</b>
                    <p>{{ importacion.fecha }}</p>
                </div>
                <div class="mx-5 col-span-6 gap-4 m-2 lg:col-span-3 flex">
                    <b>Cantidad productos :</b>
                    <p>{{ importacion_detalle.length }}</p>
                </div>

            </div>

            <div class="bg-white mr-0 ml-0 mt-6  shadow rounded-lg border border-gray-300">
                <div class="bg-gray-100 rounded-t-lg py-2 px-3">
                    <h2 class="text-2xl font-medium pb-0 pl-2">Productos ({{ importacion_detalle.length }}) </h2>
                </div>
                <div class="bg-gradient-to-r from-primary-900 to-primary-100  h-1 mb-0"></div>
                <!-- Línea con gradiente -->
                <div class="align-middle">

                    <DataTable showGridlines resizableColumns columnResizeMode="fit" :filters="filters"
                        :value="importacion_detalle" paginator :rows="20" :rowsPerPageOptions="[5, 10, 20, 50]"
                        tableStyle="min-width: 50rem" size="small">
                        <template #header>
                            <div class="flex justify-content-end text-md">
                                <InputText v-model="filters['global'].value" placeholder="Buscar" />
                            </div>
                        </template>
                        <template #empty> No existe Resultado </template>
                        <template #loading> Cargando... </template>
                        <Column field="id" header="ID" sortable :pt="{
                            bodyCell: {
                                class: 'text-center'
                            },
                            headerContent: { class: 'break-words ' }

                        }"></Column>
                        <Column field="imagen" header="Referencia" style="width:300px" :pt="{
                            bodyCell: {
                                class: 'flex items-center break-words  '
                            }
                            ,
                            headerContent: { class: 'break-words ' }


                        }">
                            <template #body="slotProps">

                                <div class="flex  items-center gap-2">
                                    <img class="rounded  bg-white shadow-2xl border-2 text-center w-10 h-10 object-contain"
                                        :src="slotProps.data.imagen" alt="image description">
                                    <p class="text-xs">{{ slotProps.data.nombre }}</p>
                                </div>
                            </template>
                        </Column>

                        <Column field="precio" header="Precio" sortable :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="unidad" header="Unidad" sortable :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="pcs_bulto" header="PCS Bulto" sortable :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }

                        }"></Column>
                        <Column field="bultos" sortable header="Bultos" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="cantidad_total" sortable header="Cantidad" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="valor_total" sortable header="T. Valor" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="cbm_bulto" sortable header="CBM/Bulto" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="cbm_total" sortable header="Total CBM" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="codigo_barra" sortable header="Código de Barra" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="aduana" sortable header="Nombre Aduana" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="created_at" sortable header="Fecha" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column header="Acciones" style="width:130px">
                            <template #body="slotProps">


                            </template>
                        </Column>
                    </DataTable>

                </div>
            </div>


            <!--Contenido-->
        </div>
    </AppLayout>
</template>

<style type="text/css" scoped></style>
