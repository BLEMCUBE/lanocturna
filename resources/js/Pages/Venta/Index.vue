<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { FilterMatchMode } from 'primevue/api';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";

import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';

const toast = useToast();
const tabla_ventas = ref()
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

onMounted(() => {
    date.value = [subDays(new Date(), 2), new Date()];
    filtrado(date.value);
});

//filtrado
const filtrado = (value) => {
    if (value[0] != null && value[1] != null) {
        tabla_ventas.value = [];
        cargando.value = true;
        router.get('/ventas/',
            {
                inicio: moment(value[0]).format('YYYY-MM-DD'),
                fin: moment(value[1]).format('YYYY-MM-DD')
            },
            {
                preserveState: true,
                onSuccess: () => {
                    //tabla_ventas.value = Array.from(usePage().props.ventas.data, (x) => x);
                    tabla_ventas.value = usePage().props.ventas.data;
                    cargando.value = false;
                }

            }
        );
        date.value = [moment(value[0]).format('YYYY-MM-DD'), moment(value[1]).format('YYYY-MM-DD')];
    } else {
        router.get(route(ruta + '.index'))
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

const clickDetalle = (e) => {

    btnVer(e.data.id)
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
        <div class="card px-4 mb-4 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12">

            <!--Contenido-->
            <Toast />
            <div class="px-3 pb-2 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
                <Button size="small" :label="'Crear Venta'" severity="success" @click="BtnCrear"></Button>

            </div>

            <div class="align-middle">

                <DataTable :filters="filters" :value="tabla_ventas" :loading="cargando" :pt="{
                    bodyRow: { class: 'hover:cursor-pointer hover:bg-gray-100' }
                }" scrollable scrollHeight="700px" paginator :rows="50" @row-click="clickDetalle"
                    size="small">

                    <template #header>
                        <div class="grid grid-cols-6 gap-4 m-1.5">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" :pt="{
                                root: { class: 'col-span-6 lg:col-span-2 m-1.5' }
                            }" />

                            <date-picker @change="filtrado" type="date" range value-type="YYYY-MM-DD" format="DD/MM/YYYY"
                                class="p-inputtext p-component col-span-6 lg:col-span-2 font-sans  font-normal text-gray-700  bg-white  transition-colors duration-200 border-0 text-sm"
                                v-model:value="date" :shortcuts="shortcuts" lang="es"
                                placeholder="Seleccione Fecha"></date-picker>

                        </div>
                    </template>

                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>
                    <Column field="fecha" header="Fecha y Hora" sortable :pt="{
                        bodyCellContent: {
                            class: 'text-center w-44'
                        },
                        headerContent: {

                            class: 'text-center w-44'
                        },
                        headerCell: {

                            class: 'text-center w-44'
                        },
                        bodyCell: {

                            class: 'text-center'
                        }
                    }"> <template #loading>
                        </template></Column>
                    <Column field="nro_compra" header="Nº Compra" context="small" sortable :pt="{

                        bodyCellContent: {
                            class: ' w-44'
                        },
                        headerCell: {
                            class: 'w-44'
                        },
                        bodyCell: {

                            class: 'text-center'
                        },
                        headerContent: {

                            class: 'text-center w-44'
                        },

                    }">
                        <template #loading>
                        </template>
                    </Column>

                    <Column field="cliente" header="Cliente" sortable :pt="{
                        bodyCellContent: {
                            class: 'text-center w-56'
                        },
                        headerContent: {

                            class: 'text-center w-56'
                        },
                        headerCell: {

                            class: 'text-center w-56'
                        },
                        bodyCell: {

                            class: 'text-center'
                        }
                    }">
                        <template #loading>
                            <div class="flex align-items-center"
                                :style="{ height: '17px', 'flex-grow': '1', overflow: 'hidden' }">

                            </div>
                        </template>
                    </Column>

                    <Column field="estado" header="Estado" sortable :pt="{
                        bodyCellContent: {
                            class: 'text-center w-52'
                        },
                        headerContent: {

                            class: 'text-center w-52'
                        },
                        headerCell: {

                            class: 'text-center w-52'
                        },
                        bodyCell: {

                            class: 'text-center'
                        },
                    }">
                        <template #loading>
                        </template>
                        <template #body="slotProps">
                            <span class="font-semibold text-md" :class="colorEstado(slotProps.data.estado)">
                                {{ slotProps.data.estado }}
                            </span>
                        </template>
                    </Column>

                    <Column field="total" sortable header="Total" :pt="{
                        bodyCellContent: {
                            class: 'text-center w-36'
                        },
                        headerContent: {

                            class: 'text-center w-36'
                        },
                        headerCell: {

                            class: 'text-center w-36'
                        },
                        bodyCell: {

                            class: 'text-end'
                        }
                    }"></Column>

                    <Column field="observaciones" sortable header="Observaciones" :pt="{
                        bodyCellContent: {
                            class: 'text-center w-52'
                        },
                        bodyCell: {

                            class: 'text-center'
                        },
                    }"></Column>

                    <Column header="Acciones" style="width:100px" :pt="{
                        bodyCell: {
                            class: 'text-center p-0'
                        }
                    }">
                        <template #loading>
                        </template>
                        <template #body="slotProps">

                            <Button v-if="permissions.includes('eliminar-ventas') && slotProps.data.estado !== 'ANULADO'"
                                @click="btnEliminar(slotProps.data.id)"
                                class="w-8 h-8 rounded border-red-700 bg-red-700 px-2 py-1 text-base font-normal text-white m-1 hover:bg-red-600 "
                                v-tooltip.top="{ value: `Anular`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"><i
                                    class="fas fa-ban"></i></Button>


                        </template>
                    </Column>
                </DataTable>

            </div>
            <!--Contenido-->
        </div>
    </AppLayout>
</template>


<style type="text/css"></style>
