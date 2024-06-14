<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted,watch } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { FilterMatchMode } from 'primevue/api';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";

import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
import Pagination from '@/Components/Pagination.vue';
const props = defineProps(['ventas'])
const toast = useToast();

const cargando = ref(false)
const { permissions } = usePage().props.auth
const titulo = "Historial de Ventas"
const ruta = 'ventas'
const { tipo_cambio } = usePage().props
const date = ref();
const formDelete = useForm({
    id: '',
});
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
const searchField = ref(''); //Should really load it from the query string

const url = route('ventas.index');

watch(searchField, 
() => {
	router.get(url, {buscar: searchField.value}, {preserveState: true, preserveScroll: true, only: ['ventas']}), 300}
    );

onMounted(() => {
    date.value = [subDays(new Date(), 2), new Date()];
    //filtrado(date.value);
});

//filtrado
const filtrado = (value) => {
    if (value[0] != null && value[1] != null) {
        cargando.value = true;
        router.get(route(ruta + '.index'),
            {
                inicio: moment(value[0]).format('YYYY-MM-DD'),
                fin: moment(value[1]).format('YYYY-MM-DD')
            },
            {
                preserveState: true,
                onSuccess: () => {
                    
                    cargando.value = false;
                }

            }
        );
        date.value = [moment(value[0]).format('YYYY-MM-DD'), moment(value[1]).format('YYYY-MM-DD')];
    } else {
        //router.get(route(ruta + '.index'))
    }

}

const colorEstado = (estado) => {
    switch (estado) {
        case 'PENDIENTE DE FACTURACIÓN':
            return 'text-orange-600'
            break;
        case 'FACTURADO':
            return 'text-blue-600'
            break;
        case 'COMPLETADO':
            return 'text-green-600'
            break;
        case 'ANULADO':
            return 'text-red-600'
            break;
        default:
            return 'text-black'
            break;
    }
}

const btnVer = (id) => {
    router.get(route(ruta + '.show', id));

};

const btnEliminar = (id) => {

    const alerta = Swal.mixin({ buttonsStyling: true });
    alerta.fire({
        width: 350,
        title: "Seguro de Anular ",
        text: 'Se anulará definitivamente',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Anular',
        cancelButtonText: 'Cancelar',
        cancelButtonColor: 'red',
        confirmButtonColor: '#2563EB',

    }).then((result) => {
        if (result.isConfirmed) {
            formDelete.delete(route(ruta + '.destroy', id),
                {
                    preserveScroll: true,
                    forceFormData: true,
                    onSuccess: () => {
                        show('success', 'Eliminado', 'Se ha anulado')
                        setTimeout(() => {
                            router.get(route(ruta + '.index'));
                        }, 1000);

                    }
                });
        }
    });
}

const clickDetalle = (id) => {
    btnVer(id)
}


const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const BtnCrear = () => {
    if (tipo_cambio == true) {

        router.get(route(ruta + '.create'));
    } else {
        ok('error', 'No se ha especificado el tipo de cambio para el día')
    }
}

const ok = (icono, mensaje) => {

    Swal.fire({
        width: 350,
        title: mensaje,
        icon: icono
    })
}

const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
</script>
<template>

    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div class="card p-4 mb-4 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12">
            <!--Contenido-->
            <Toast />
            <div class="px-3 pb-2 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
                <Button size="small" :label="'Crear Venta'" severity="success" @click="BtnCrear"></Button>

            </div>
            <!--tabla-->
            <div class="align-middle py-4">
                <div class="grid grid-cols-6 gap-4 m-1.5">
                    <InputText v-model="searchField" placeholder="Buscar N° Compra" :pt="{
                        root: { class: 'col-span-6 lg:col-span-2 m-1.5' }
                    }" />

                    <date-picker @change="filtrado" type="date" range value-type="YYYY-MM-DD" format="DD/MM/YYYY"
                        class="p-inputtext p-component col-span-6 lg:col-span-2 font-sans  font-normal text-gray-700  bg-white  transition-colors duration-200 border-0 text-sm"
                        v-model:value="date" :shortcuts="shortcuts" lang="es"
                        placeholder="Seleccione Fecha"></date-picker>

                </div>
                <div style="overflow:auto; max-height: 700px;">

                    <table class="w-full text-md bg-white shadow-md rounded mb-4">
                        <thead style="position: sticky;" class="top-0 z-[1]">
                            <tr class="bg-secondary-100">
                                <th class="p-1.5">Fecha y Hora</th>
                                <th>Nº Compra</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th>Observaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="post in props.ventas.data" 
                                class="border text-center hover:cursor-pointer hover:bg-gray-100">
                                <td @click="clickDetalle(post.id)">{{ post.fecha??"" }}</td>
                                <td @click="clickDetalle(post.id)">{{ post.nro_compra!="null"?post.nro_compra:"" }}</td>
                                <td @click="clickDetalle(post.id)">{{ post.cliente!="null"?post.cliente:""}}</td>
                                <td @click="clickDetalle(post.id)" class="p-1.5"> <span class="font-semibold text-md"
                                        :class="colorEstado(post.estado)">
                                        {{ post.estado??"" }}
                                    </span></td>
                                <td>{{ post.total }}</td>
                                <td @click="clickDetalle(post.id)">{{ post.observaciones }}</td>
                                <td>
                                    <Button v-if="permissions.includes('eliminar-ventas') && post.estado !== 'ANULADO'"
                                        @click="btnEliminar(post.id)"
                                        class="w-8 h-8 rounded border-red-700 bg-red-700 px-2 py-1 text-base font-normal text-white m-1 hover:bg-red-600 "
                                        v-tooltip.top="{ value: `Anular`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"><i
                                            class="fas fa-ban"></i></Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :elements="props.ventas"></Pagination>

            </div>
            <!--tabla-->

            <!--Contenido-->
        </div>
    </AppLayout>
</template>


<style type="text/css"></style>
