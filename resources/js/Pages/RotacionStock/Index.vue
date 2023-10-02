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
const titulo = "Rotación de Stock"
const ruta = 'rotacion-stock'
const cantidad = ref()
const cantidad_importacion = ref()
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
                tabla_vendidos.value = Array.from(datos.producto.detalles_ventas, (x) => x);
            })
    } else {

        location.reload()
    }
}

//descarga excel
const descargaExcelProductoVentas = (id) => {

    console.log(fechaVentaExport);
    if (fechaVentaExport.value[0] != null && fechaVentaExport.value[1] != null) {
        window.open(route('productos.exportproductoventas', [form.id, { 'inicio': fechaVentaExport.value[0], 'fin': fechaVentaExport.value[1] }]), '_blank');
    } else {

        window.open(route('productos.exportproductoventas', [form.id]), '_blank');
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


onMounted(() => {
    //tabla_vendidos.value = usePage().props.producto.detalles_ventas;
    tabla_vendidos.value = Array.from(usePage().props.producto.detalles_ventas, (x) => x);
    //tabla_importaciones.value = usePage().props.producto.importacion_detalles;
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

const btnEditar = (id) => {
    router.get(route(ruta + '.edit', id));

};
const clickDetalle = (e) => {

    router.get(route('ventas.show', e.data.venta_id));
}


</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <div class=" px-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div class="bg-white mr-0 ml-0 mt-6  shadow rounded-lg border border-gray-300">

                <div class="align-middle p-2">

                    <DataTable sortField="venta.fecha" :sortOrder="-1" :filters="filters" :value="tabla_vendidos" :pt="{
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

                        <Column field="venta.fecha" sortable header="ORIGEN" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="venta.fecha" sortable header="NOMBRE" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="venta.fecha" sortable header="FECHA ULTIMA COMPRA" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="venta.fecha" sortable header="FECHA ULTIMA VENTA" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="venta.fecha" sortable header="VENTAS TOTALES" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="venta.fecha" sortable header="STOCK" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="venta.fecha" sortable header="STOCK FUTURO" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="venta.fecha" sortable header="ROTACION DEL STOCK " :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>


                    </DataTable>

                </div>
            </div>

            <!--Contenido-->
        </div>
    </AppLayout>
</template>

<style type="text/css" scoped></style>
