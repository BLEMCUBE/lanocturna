<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage,router } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
import moment from 'moment';
import EditarModal from '@/Pages/Deposito/Partials/EditarModal.vue';
import EditarProductoModal from '@/Pages/Deposito/Partials/EditarProductoModal.vue';
const { deposito } = usePage().props
const { deposito_detalle } = usePage().props
const titulo = "Detalle Subir bultos a Déposito"
const ruta = 'depositos'
const isShowModalProducto = ref(false);
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const closeModalProducto = () => {
    isShowModalProducto.value = false;
};
const clickDetalle=(e)=>{
    console.log('e');
    if(deposito.estado=='Arribado'){
        isShowModalProducto.value=true;
    }else{

        document.getElementById("show-"+e.data.id).click();
    }
}
</script>
<template>
    <Head :title="titulo" />
    <AppLayout
        :pagina="[{ 'label': 'Bultos importados', link: true, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">

         <!--Modal productos-->
         <Dialog v-model:visible="isShowModalProducto" modal  :style="{ width: '30vw' }" :pt="{
            header: {
                class: 'mt-5 pb-2 px-5'
            },
            content: {
                class: 'p-4'
            },
        }">
            <p class="mb-2 font-bold">
               NO SE PUEDE EDITAR MIENTRAS EL ESTADO SEA "Arribado", DEBE DE CAMBIAR EL ESTADO A "En camino" PARA PODER EDITAR.
            </p>


            <template #header>
                <div class="flex flex-column align-items-center" style="flex: 1">
                    <div class="text-center">
                        <i class="pi pi-exclamation-triangle text-yellow-500" style="font-size: 3rem"></i>
                    </div>
                    <div class="font-bold text-2xl m-3"></div>
                </div>
            </template>


            <div class="flex justify-end py-3">
                <Button label="Aceptar" size="small" type="button" @click="closeModalProducto()" />

            </div>
        </Dialog>
        <!--Modal productos-->
        <div
            class="card px-4 py-3 mb-0 bg-white col-span-12 justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <div class="mb-5 px-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>
            <div class="px-0 py-1 m-2 mt-0 text-white  col-span-full  flex justify-end items-center">
                <span class="inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-primary-100">
                    <EditarModal :clienteId="deposito.id">

                    </EditarModal>
                </span>


            </div>

            <div class="grid grid-cols-6 my-2 col-span-12 ">
                <div class="mx-5 col-span-6 gap-4 m-2 lg:col-span-3 flex">
                    <b> No. de Carpeta:</b>
                    <p>{{ deposito.nro_carpeta }}</p>
                </div>
                <div class="mx-5 col-span-6 gap-4 m-2 lg:col-span-3 flex">
                    <b> BL o No. de Contenedor:</b>
                    <p>{{ deposito.nro_contenedor }}</p>
                </div>
                <div class="mx-5 col-span-6 gap-4 m-2 lg:col-span-3 flex">
                    <b> Estado:</b>
                    <p>{{ deposito.estado }}</p>
                </div>
                <div class="mx-5 col-span-6 gap-4 m-2 lg:col-span-3 flex">
                    <b>Fecha En Camino :</b>
                    <p>{{ moment(deposito.fecha_camino).format('DD/MM/YYYY') }}</p>
                </div>
                <div class="mx-5 col-span-6 gap-4 m-2 lg:col-span-3 flex">
                    <b>Fecha Arribado :</b>
                    <p>{{ moment(deposito.fecha_arribado).format('DD/MM/YYYY') }}</p>
                </div>
                <div class="mx-5 col-span-6 gap-4 m-2 lg:col-span-3 flex">
                    <b>Cantidad productos :</b>
                    <p>{{ deposito_detalle.length }}</p>
                </div>

            </div>

            <div class="bg-white mr-0 ml-0 mt-6  shadow rounded-lg border border-gray-300">
                <div class="bg-gray-100 rounded-t-lg py-2 px-3">
                    <h2 class="text-2xl font-medium pb-0 pl-2">Productos ({{ deposito_detalle.length }}) </h2>
                </div>
                <div class="bg-gradient-to-r from-primary-900 to-primary-100  h-1 mb-0"></div>
                <!-- Línea con gradiente -->
                <div class="align-middle">


                    <DataTable sortField="id" :sortOrder="1" :filters="filters"
                       :value="deposito_detalle" scrollable scrollHeight="800px"
                       @row-click="clickDetalle"
                       :pt="{
                    bodyRow:{class:'hover:cursor-pointer hover:bg-gray-100 hover:text-black' },
                    root:{class:'w-auto'}
                }"
                        :virtualScrollerOptions="{ itemSize: 46 }" tableStyle="min-width: 50rem" size="small">
                        <template #header>
                            <div class="flex justify-content-end text-md">
                                <InputText v-model="filters['global'].value" placeholder="Buscar" />
                            </div>
                        </template>
                        <template #empty> No existe Resultado </template>
                        <template #loading> Cargando... </template>
                        <Column field="sku" header="Origen" sortable :pt="{
                            bodyCell: {
                                class: 'text-center'
                            },
                            headerContent: { class: 'break-words ' }

                        }"></Column>
                        <Column field="imagen" header="Referencia"  :pt="{
                            bodyCell: {
                                class: 'flex items-center'
                            },
                            bodyCellContent:{
                                class:'flex items-center'
                            }

                        }">
                            <template #body="slotProps">

                                <div class="flex  items-center">
                                    <img class="rounded  bg-white shadow-2xl text-center w-10 h-10 object-contain"
                                        :src="slotProps.data.imagen" alt="image">
                                    <p class="text-xs text-center flex-wrap">{{ slotProps.data.nombre }}</p>
                                </div>
                            </template>
                        </Column>
                        <Column field="unidad" header="Unidad" sortable :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="pcs_bulto" header="PCS Bulto" sortable :pt="{
                            bodyCell: {
                                class: 'text-center'
                            },
                            headerContent: {
                                class: 'text-center break-all w-36'
                            }

                        }"></Column>
                        <Column field="bultos" sortable header="Bultos" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            },
                            headerCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="cantidad_total" sortable header="Cantidad Total" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>

                        <Column field="codigo_barra" sortable header="Código de Barra" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                          <Column :pt="{
                            bodyCell: {
                                class: 'hidden'
                            },
                            headerCell: {
                                class: 'hidden'
                            }
                        }">

                        <template #body="slotProps">
                           <EditarProductoModal :clienteId="slotProps.data.id" ></EditarProductoModal>
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
