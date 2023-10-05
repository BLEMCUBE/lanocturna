<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
import axios from 'axios';
import InputError from '@/Components/InputError.vue';

const { permissions } = usePage().props.auth
const previewImage = ref('/images/productos/sin_foto.png');
const { roles } = usePage().props.auth
const { costo_aprox } = usePage().props
const { ultimo_yang } = usePage().props
const { productoventa } = usePage().props
const titulo = "Detalle Producto"
const ruta = 'productos'
const cantidad = ref()
const cantidad_importacion = ref()
//const { cantidad_importacion } = usePage().props
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
const date = ref([new Date(), new Date()]);
const date2 = ref([new Date(), new Date()]);
//const fechaVentaExport = ref({inicio:'',fin:''});
const fechaVentaExport = ref([]);


//filtrado
const filtradoVenta = (value) => {
    if (value[0] != null && value[1] != null) {
        fechaVentaExport.value = [value[0], value[1]]
        axios.get(route(ruta + '.productoventa', [usePage().props.producto.id, moment(value[0]).format('YYYY-MM-DD'), moment(value[1]).format('YYYY-MM-DD')]))
            .then(res => {
                var datos = res.data
                cantidad.value = datos.cantidad
                tabla_vendidos.value = Array.from(datos.productoventa, (x) => x);
            })
    } else {

        location.reload()
    }
}

//descarga excel
const descargaExcelProductoVentas = (id) => {


    if (fechaVentaExport.value[0] != null && fechaVentaExport.value[1] != null) {
        window.open(route('productos.exportproductoventas', [form.id, { 'inicio': fechaVentaExport.value[0], 'fin': fechaVentaExport.value[1] }]), '_blank');
    } else {

        window.open(route('productos.exportproductoventas', [form.id]), '_blank');
    }
}

//filtrado importacion
const filtradoImportacion = (value) => {
    if (value[0] != null && value[1] != null) {
        axios.get(route(ruta + '.productoimportacion', [usePage().props.producto.id, moment(value[0]).format('YYYY-MM-DD'), moment(value[1]).format('YYYY-MM-DD')]))
            .then(res => {
                var datos = res.data
                cantidad_importacion.value = datos.cantidad_importacion
                tabla_importaciones.value = Array.from(datos.producto, (x) => x);
            })
    } else {

        location.reload()
    }
}
//*datepicker  */
const shortcuts = [
    {
        text: 'Hoy',
        onClick() {
            const date = [new Date(), new Date()];
            return date;
        },
    },
    {
        text: 'Ayer',
        onClick() {
            const date = [subDays(new Date(), 1), subDays(new Date(), 1)];
            //date.setTime(date.getTime() - 3600 * 1000 * 24);

            return date;
        },
    },
    {
        text: 'Este mes',
        onClick() {
            const date = [startOfMonth(new Date()), endOfMonth(new Date())];

            return date;
        },
    },
    {
        text: 'Este año',
        onClick() {
            const date = [startOfYear(new Date()), endOfYear(new Date())];

            return date;
        },
    },
]
const shortcuts2 = [
    {
        text: 'Hoy',
        onClick() {
            const date2 = [new Date(), new Date()];
            return date2;
        },
    },
    {
        text: 'Ayer',
        onClick() {
            const date2 = [subDays(new Date(), 1), subDays(new Date(), 1)];
            //date.setTime(date.getTime() - 3600 * 1000 * 24);

            return date2;
        },
    },
    {
        text: 'Este mes',
        onClick() {
            const date2 = [startOfMonth(new Date()), endOfMonth(new Date())];

            return date2;
        },
    },
    {
        text: 'Este año',
        onClick() {
            const date2 = [startOfYear(new Date()), endOfYear(new Date())];

            return date2;
        },
    },
]

onMounted(() => {
    tabla_vendidos.value = Array.from(usePage().props.productoventa, (x) => x);
    cantidad_importacion.value = usePage().props.cantidad_importacion;
    cantidad.value = usePage().props.cantidad;
    tabla_importaciones.value = Array.from(usePage().props.productoImportacion, (x) => x);
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
const btnEditar = (id) => {
    router.get(route(ruta + '.edit', id));

};
const clickDetalle = (e) => {

    router.get(route('ventas.show', e.data.venta_id));
}
const clickDetImportacion = (e) => {
    router.get(route('importaciones.show', e.data.importacion_id));
}

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
            <div class="px-0 py-1 m-2 mt-0 text-white  col-span-full  flex justify-end items-center">
                <Button label="Editar" v-if="permissions.includes('editar-productos')" @click="btnEditar(form.id)" :pt="{
                    root: {
                        class: 'flex items-center  bg-primary-900 justify-center font-medium w-10'
                    },
                    label: {
                        class: 'hidden'
                    }
                }" v-tooltip.top="{ value: `Editar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"><i
                        class="fas fa-edit"></i></Button>

            </div>


            <div class="mx-auto grid max-w-2xl grid-cols-1  gap-2 sm:px-6 sm:py-5 lg:max-w-7xl lg:grid-cols-3 lg:px-8">

                <div class="grid grid-cols-2 gap-2">
                    <div class="col-span-2">
                        <img class="w-48 text-center rounded-xl" :alt="form.nombre" :src="form.imagen" />
                    </div>
                </div>
                <div class="col-span-2 grid grid-cols-4 gap-2">

                    <div class="col-span-4 border-b border-gray-200">
                        <h1 class="lg:text-2xl text-lg font-semibold leading-0 text-gray-800 mt-2">
                            {{ form.nombre }}</h1>
                    </div>

                    <div class="col-span-2">
                        <p class="text-lg leading-2 mt-0 text-gray-700 dark:text-gray-300"><b>
                                Origen:
                            </b>
                            {{ form.origen }}
                        </p>
                    </div>

                    <div class="col-span-2">
                        <p class="text-lg leading-2 mt-0 text-gray-700 dark:text-gray-300"><b>
                                Código barra:
                            </b>
                            {{ form.codigo_barra }}
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-lg leading-2 mt-0 text-gray-700 dark:text-gray-300"><b>
                                Stock:
                            </b>
                            {{ form.stock }}
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-lg leading-2 mt-0 text-gray-700 dark:text-gray-300"><b>
                                Stock Futuro:
                            </b>
                            {{ form.stock_futuro }}
                        </p>
                    </div>
                    <div class="col-span-4">
                        <p class="text-lg leading-2 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Costo aprox. USD (iva inc):
                            </b>
                             {{ costo_aprox }}
                            </p>
                            <InputError v-if="ultimo_yang ==0" class="mt-1 text-xs" message="Debe de registrar tipo de cambio yuanes" />
                    </div>
                    <div class="col-span-4" v-if="ultimo_yang >0">
                        <p class="text-lg leading-2 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Cotización Yuanes:
                            </b>
                             {{ ultimo_yang }}
                        </p>
                    </div>
                </div>

            </div>

            <div class="bg-white mr-0 ml-0 mt-6  shadow rounded-lg border border-gray-300">
                <div class="bg-gray-100 rounded-t-lg py-2 px-3">
                    <h2 class="text-2xl font-medium pb-0 pl-2">Pedidos: {{ cantidad }}</h2>
                </div>
                <div class="bg-gradient-to-r from-primary-900 to-primary-100  h-1 mb-4"></div>
                <!-- Línea con gradiente -->
                <div class="align-middle p-2">

                    <DataTable sortField="fecha" :sortOrder="-1" :filters="filters" @row-click="clickDetalle"
                        :value="tabla_vendidos" :pt="{
                            bodyRow: { class: 'hover:cursor-pointer hover:bg-gray-100 hover:text-black' },
                            root: { class: 'w-auto' }
                        }" scrollable scrollHeight="350px" :virtualScrollerOptions="{
    numToleratedItems: 30, itemSize: 46
}" tableStyle="min-width: 50rem" size="small">
                        <template #header>
                            <div class="flex justify-content-end text-md">
                                <InputText v-model="filters['global'].value" placeholder="Buscar" />
                                <div class="ml-5 col-span-1 md:col-span-3 lg:col-span-3">
                                    <date-picker @change="filtradoVenta" type="date" range value-type="YYYY-MM-DD"
                                        format="DD/MM/YYYY" v-model:value="date" :shortcuts="shortcuts" lang="es"
                                        :clearable="false" placeholder="seleccione Fecha"></date-picker>
                                </div>
                                <div v-if="roles.includes('Super Administrador') || roles.includes('Administrador')"
                                    v-tooltip.top="{ value: 'Descargar Excel', pt: { text: 'bg-gray-500 text-xs text-white rounded' } }"
                                    class=" w-10 h-8  ml-5 rounded flex justify-center items-center text-base font-semibold text-white mr-1">
                                    <Button @click="descargaExcelProductoVentas(form.id)" :pt="{
                                        root: { class: 'py-auto px-3 py-2.5 text-xl bg-green-600 border-none hover:bg-green-500' }
                                    }"><i class="fas fa-file-excel text-white text-xl"></i>
                                    </Button>
                                </div>
                            </div>
                        </template>
                        <template #empty> No existe Resultado </template>
                        <template #loading> Cargando... </template>

                        <Column field="fecha" sortable header="Fecha" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="cantidad" sortable header="Cantidad" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="precio" sortable header="Precio" :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="destino" sortable header="Destino" :pt="{
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

                    <DataTable sortField="fecha_arribado" :sortOrder="-1" :filters="filters_importacion"
                        @row-click="clickDetImportacion" :value="tabla_importaciones" :pt="{
                            bodyRow: { class: 'hover:cursor-pointer hover:bg-gray-100 hover:text-black' },
                            root: { class: 'w-auto' }
                        }" scrollable scrollHeight="350px" :virtualScrollerOptions="{ itemSize: 46 }"
                        tableStyle="min-width: 50rem" size="small">
                        <template #header>
                            <div class="flex justify-content-end text-md">
                                <InputText v-model="filters_importacion['global'].value" placeholder="Buscar" />
                                <div class="ml-5 col-span-1 md:col-span-3 lg:col-span-3">
                                    <date-picker @change="filtradoImportacion" type="date" range value-type="YYYY-MM-DD"
                                        format="DD/MM/YYYY" v-model:value="date2" :shortcuts="shortcuts2" lang="es"
                                        placeholder="seleccione Fecha" :clearable="false"></date-picker>
                                </div>
                            </div>
                        </template>
                        <template #empty> No existe Resultado </template>
                        <template #loading> Cargando... </template>

                        <Column field="nro_carpeta" header="No. de Carpeta" sortable :pt="{
                            bodyCell: {
                                class: 'text-center'
                            }
                        }"></Column>
                        <Column field="fecha_arribado" header="Fecha Arribo" sortable :pt="{
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



                    </DataTable>

                </div>
            </div>

            <!--Contenido-->
        </div>
    </AppLayout>
</template>

<style type="text/css" scoped></style>
