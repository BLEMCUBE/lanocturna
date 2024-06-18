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
const { meses } = usePage().props
const titulo = "Rotación de Stock"
const ruta = 'rotacion-stock'
const mes = ref()

const tabla_vendidos = ref()
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
const date = ref();
date.value = [moment(subDays(new Date(), 30)).format('YYYY-MM-DD'), moment(new Date()).format('YYYY-MM-DD')];
//const date = ref([new Date(), new Date()]);
//const fechaVentaExport = ref({inicio:'',fin:''});
const fechaVentaExport = ref([]);

//filtrado
/*const filtradoVenta = (value) => {
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
}*/

//filtrado
const filtradoVenta = (value) => {
    if (value[0] != null && value[1] != null) {
        router.get(
            route(ruta+'.index'),
            {
                inicio: moment(value[0]).format('YYYY-MM-DD'),
                fin: moment(value[1]).format('YYYY-MM-DD')
            },
            {
                preserveState: true,
                onSuccess: () => {
                    tabla_vendidos.value = Array.from(usePage().props.productos, (x) => x);
                    mes.value = usePage().props.meses;

                }
            }
        );
        date.value = [moment(value[0]).format('YYYY-MM-DD'), moment(value[1]).format('YYYY-MM-DD')];
    }
}


//descarga excel
const descargaExcelProductoVentas = () => {


    if (date.value[0] != null && date.value[1] != null) {
        window.open(route('rotacion-stock.exportproductoventas', [{ 'inicio': date.value[0], 'fin': date.value[1] }]), '_blank');
    } else {

       return;
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
    filtradoVenta(date.value)


});
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});


</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div
            class="card px-4 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <div class=" px-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div class="bg-white mr-0 ml-0 mt-6  shadow rounded-lg border border-gray-300">

                <div class="align-middle p-2">
                  <b>
                      MESES: {{ mes }}

                  </b>
                    <DataTable sortField="rotacion_stock" :sortOrder="1" :filters="filters" :value="tabla_vendidos" :pt="{
                        root: { class: 'w-auto' }
                    }" scrollable scrollHeight="350px"
                        paginator :rows="50" tableStyle="min-width: 50rem"
                        size="small">
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
                                    <Button @click="descargaExcelProductoVentas()" :pt="{
                                        root: { class: 'py-auto px-3 py-2.5 text-xl bg-green-600 border-none hover:bg-green-500' }
                                    }"><i class="fas fa-file-excel text-white text-xl"></i>
                                    </Button>
                                </div>
                            </div>
                        </template>
                        <template #empty> No existe Resultado </template>
                        <template #loading> Cargando... </template>

                        <Column field="origen" sortable header="ORIGEN" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="nombre" sortable header="NOMBRE" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="ultima_compra" sortable header="FECHA ÚLTIMA COMPRA" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="ultima_venta" sortable header="FECHA ÚLTIMA VENTA" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="ventas_totales" sortable header="VENTAS TOTALES" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="stock" sortable header="STOCK" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="stock_futuro" sortable header="STOCK FUTURO" :pt="{
                            bodyCell: { class: 'text-center p-0 m-0 w-56' },
                            headerTitle: { class: 'text-center  w-56' },
                            headerContent: {
                                class: 'text-center w-56'
                            },
                            bodyCellContent: {

                                class: 'flex align-items-center text-center w-56'
                            },

                        }"></Column>

                        <Column field="rotacion_stock" sortable header="ROTACION DEL STOCK " :pt="{
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
