<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
import Pagination from '@/Components/Pagination.vue';
const props = defineProps({
    ventas: {
        type: Object,
        default: () => ({}),
    },
    filtro: {
        type: Object,
        default: () => ({}),
    },
});

const titulo = "Historial de Envios"
const ruta = 'envios'


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


let buscar = ref(props.filtro.buscar);
let date = ref([props.filtro.inicio, props.filtro.fin]);
let inicio = ref(props.filtro.inicio);
let fin = ref(props.filtro.fin);


const funcBuscar = () => {
	   router.get(
        route(ruta + '.historial'),
        {
            buscar: buscar.value,
            inicio: inicio.value,
            fin: fin.value
        },
        {
            preserveState: true,
            replace: true,
        }
    );
}

//filtrado
const filtrado = (value) => {
    if (value[0] != null && value[1] != null) {
        date.value = [moment(value[0]).format('YYYY-MM-DD'), moment(value[1]).format('YYYY-MM-DD')];
        inicio.value = date.value[0];
        fin.value = date.value[1];
    } else {
        date.value = [];
        inicio.value = null;
        fin.value = null;
    }
    router.get(
        route(ruta + '.historial'),
        {
            buscar: buscar.value,
            inicio: inicio.value,
            fin: fin.value
        },
        {
            preserveState: true,
            replace: true,
        }
    );

}


const btnVer = (id) => {
    router.get(route(ruta + '.detalle', id));

};


const clickDetalle = (e) => {

    btnVer(e.data.id)
}



</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div
            class="card p-3 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <div class="p-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>


            </div>

             <!--tabla-->
             <div class="align-middle py-4">
                <div class="grid grid-cols-6 gap-4 m-1.5">
                    <InputText v-model="buscar"  v-debounce:500ms="funcBuscar" :debounce-events="['keyup']" placeholder="N° Compra, Cliente, Destino" :pt="{
                        root: { class: 'col-span-6 lg:col-span-2 m-1.5' }
                    }" />

                    <date-picker @change="filtrado" type="date" range value-type="YYYY-MM-DD" format="DD/MM/YYYY"
                        class="p-inputtext p-component col-span-6 lg:col-span-2 font-sans  font-normal text-gray-700  bg-white  transition-colors duration-200 border-0 text-sm"
                        v-model:value="date" :shortcuts="shortcuts" lang="es" editable="false"
                        placeholder="Seleccione Fecha"></date-picker>

                </div>
                <div style="overflow:auto; max-height: 700px;">

                    <table class="w-full text-md bg-white shadow-md rounded mb-4">
                        <thead style="position: sticky;" class="top-0 z-[1]">
                            <tr class="bg-secondary-100">
                                <th class="p-1.5">Fecha y Hora</th>
                                <th>Destino</th>
                                <th>Nº Compra</th>
                                <th>Cliente</th>
                                <th>Observaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="post in ventas.data"
                                class="border text-center hover:cursor-pointer hover:bg-gray-100">
                                <td @click="btnVer(post.id)">{{ post.fecha ?? "" }}</td>
                                <td @click="btnVer(post.id)">{{ post.destino ?? "" }}</td>
                                <td @click="btnVer(post.id)">{{ post.nro_compra != "null" ? post.nro_compra : "" }}</td>
                                <td @click="btnVer(post.id)">{{ post.cliente != "null" ? post.cliente : "" }}</td>
                                <td @click="clickDetalle(post.id)">{{ post.observaciones }}</td>
                                <td>
                                    <span
                                class="inline-block rounded bg-sky-300 px-2 py-1 text-base font-semibold text-white mr-1 mb-1 hover:bg-sky-400">
                                <a :href="route('envios.generar_ticket', post.id)" target="_blank"><i
                                        class="fas fa-print"></i></a>
                            </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :elements="ventas"></Pagination>

            </div>
            <!--tabla-->
            <!--Contenido-->
        </div>
    </AppLayout>
</template>


<style type="text/css"></style>
