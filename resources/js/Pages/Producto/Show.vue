<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
const previewImage = ref('/images/productos/sin_foto.png');

const titulo = "Detalle Producto"
const ruta = 'productos'
const { cantidad } = usePage().props
const { cantidad_importacion } = usePage().props
const tabla_vendidos = ref()
const tabla_importaciones = ref()
const form = useForm({
    id: '',
    origen: '',
    nombre: '',
    aduana: '',
    codigo_barra: '',
    stock: 0,
    stock_minimo: 0,
    imagen: '',
    photo: ''
})

onMounted(() => {
    tabla_vendidos.value = usePage().props.producto.detalles_ventas;
    tabla_importaciones.value = usePage().props.producto.importacion_detalles;
    var datos = usePage().props.producto;
    form.id = datos.id
    form.nombre = datos.nombre
    form.origen = datos.origen
    form.aduana = datos.aduana
    form.codigo_barra = datos.codigo_barra
    form.stock = datos.stock
    form.stock_minimo = datos.stock_minimo
    form.stock_futuro = datos.stock_futuro
    previewImage.value = datos.imagen
    form.imagen = datos.imagen

});
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const filters_importacion = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});

</script>
<template>
    <Head :title="titulo" />
    <AppLayout
        :pagina="[{ 'label': 'Productos', link: true, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <div class=" px-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div
                class="mx-auto grid max-w-2xl grid-cols-1  gap-x-10 gap-y-16 px-4 py-5 sm:px-6 sm:py-5 lg:max-w-7xl lg:grid-cols-3 lg:px-8">

                <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:gap-8">
                    <div class="col-span-2">
                        <img class="w-full text-center rounded-xl" :alt="form.nombre" :src="form.imagen" />
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="border-b border-gray-200 pb-4">
                        <h1
                            class="lg:text-2xl text-lg font-semibold lg:leading-6 leading-7 text-gray-800 dark:text-white mt-2">
                            {{ form.nombre }}</h1>
                    </div>

                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Nombre Aduana:
                        </b>
                        {{ form.aduana }}
                    </p>
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Origen:
                        </b>
                        {{ form.origen }}
                    </p>
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Código barra:
                        </b>
                        {{ form.codigo_barra }}
                    </p>
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Stock:
                        </b>
                        {{ form.stock }}
                    </p>
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Stock Mínimo:
                        </b>
                        {{ form.stock_minimo }}
                    </p>
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Stock Futuro:
                        </b>
                        {{ form.stock_futuro }}
                    </p>
                </div>

            </div>

            <div class="bg-white mr-0 ml-0 mt-6  shadow rounded-lg border border-gray-300">
                <div class="bg-gray-100 rounded-t-lg py-2 px-3">
                    <h2 class="text-2xl font-medium pb-0 pl-2">Pedidos: {{ cantidad }}</h2>
                </div>
                <div class="bg-gradient-to-r from-primary-900 to-primary-100  h-1 mb-4"></div>
                <!-- Línea con gradiente -->
                <div class="align-middle p-2">

                    <DataTable showGridlines sortField="venta.fecha" :sortOrder="-1" :filters="filters" :value="tabla_vendidos" :pt="{
                        bodyRow: { class: '' }
                    }" scrollable scrollHeight="350px" :virtualScrollerOptions="{ itemSize: 46 }"
                        tableStyle="min-width: 50rem" size="small">
                        <template #header>
                            <div class="flex justify-content-end text-md">
                                <InputText v-model="filters['global'].value" placeholder="Buscar" />
                            </div>
                        </template>
                        <template #empty> No existe Resultado </template>
                        <template #loading> Cargando... </template>

                        <Column field="venta.fecha" sortable header="Fecha" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="cantidad" sortable header="Cantidad" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="venta.destino" sortable header="Destino" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>

                    </DataTable>

                </div>
            </div>

            <div class="bg-white mr-0 ml-0 mt-6  shadow rounded-lg border border-gray-300">
                <div class="bg-gray-100 rounded-t-lg py-2 px-3">
                    <h2 class="text-2xl font-medium pb-0 pl-2">Importaciones: {{ cantidad_importacion }}</h2>
                </div>
                <div class="bg-gradient-to-r from-secondary-900 to-secondary-100 h-1 mb-4"></div>
                <!-- Línea con gradiente -->
                <div class="align-middle p-2">

                    <DataTable showGridlines sortField="importacion.fecha" :sortOrder="-1" :filters="filters_importacion" :value="tabla_importaciones" :pt="{
                        bodyRow: { class: '' }
                    }" scrollable scrollHeight="350px" :virtualScrollerOptions="{ itemSize: 46 }"
                        tableStyle="min-width: 50rem" size="small">
                        <template #header>
                            <div class="flex justify-content-end text-md">
                                <InputText v-model="filters_importacion['global'].value" placeholder="Buscar" />
                            </div>
                        </template>
                        <template #empty> No existe Resultado </template>
                        <template #loading> Cargando... </template>

                        <Column field="importacion.fecha" sortable header="Fecha" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="importacion.nro_carpeta" header="No. de Carpeta" sortable :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="importacion.nro_contenedor" header="BL o No. de Contenedor" sortable :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="precio" sortable header="EXW Precio" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="unidad" sortable header="Unidad" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="pcs_bulto" sortable header="PCS/Bulto" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="bultos" sortable header="Bulto" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="cantidad_total" sortable header="Cantidad Total" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="valor_total" sortable header="Valor Total" :pt="{
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

                        <Column sortable header="Nombre Aduana" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }">
                            <template #body="slotProps">
                                {{ form.aduana }}
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
