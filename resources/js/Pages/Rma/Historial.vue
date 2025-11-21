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
const titulo = "Historial de RMA"
const ruta = 'rmas'

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
const rowClass = (data) => {
    if (data.dias >= 5 && data.dias <= 9) {
        return "bg-orange-500 text-white"
    }

    if (data.dias >= 10) {
        return ["bg-red-700 text-white"]
    }
};

const date = ref([new Date(), new Date()]);
//const date = ref([]);
//filtrado
const filtrado = (value) => {
    if (value[0] != null && value[1] != null) {

        router.get(route(ruta + '.historial'),
            {
                inicio: moment(value[0]).format('YYYY-MM-DD'),
                fin: moment(value[1]).format('YYYY-MM-DD')
            },
            {
                preserveState: true,
                onSuccess: () => {
                    tabla_ventas.value = usePage().props.ventas.data;
                }

            }
        );
    } else {
        router.get(route(ruta + '.index'))
    }

}

const btnVer = (id) => {
    router.get(route(ruta + '.show', id));

};

const btnEliminar = (id) => {

    const alerta = Swal.mixin({ buttonsStyling: true });
    alerta.fire({
        width: 350,
        title: "Seguro de Eliminar ",
        text: 'Se eliminará definitivamente',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
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
                        show('success', 'Eliminado', 'Se ha eliminado')
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
onMounted(() => {

    //tabla_ventas.value = usePage().props.ventas.data;
    tabla_ventas.value = Array.from(usePage().props.ventas.data, (x) => x);


});

const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const BtnCrearRma = () => {
    router.get(route(ruta + '.rma-create'));
}

const ok = (icono, mensaje) => {

    Swal.fire({
        width: 350,
        title: mensaje,
        icon: icono
    })
}
const formatDate = (dat) => moment(dat).format("DD/MM/YYYY");
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div class="card px-4 mb-4 bg-white col-span-12 rounded-lg shadow-lg 2xl:col-span-12">

            <!--Contenido-->
            <div class="p-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
                <Button size="small" :label="'Ingresar RMA'" severity="success" @click="BtnCrearRma"></Button>

            </div>

            <div class="align-middle">

                <DataTable :filters="filters" :rowClass="rowClass" :value="tabla_ventas" :pt="{
                    bodyRow: { class: 'hover:cursor-pointer hover:bg-gray-100 hover:text-black' },
                    root: { class: 'w-auto' }
                }" scrollable scrollHeight="700px" paginator :rows="50"
                    @row-click="clickDetalle" size="small">

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

                    <Column field="fecha_ingreso" header="Fecha Ingreso" sortable :pt="{
                        bodyCellContent: {
                            class: 'text-center w-40'
                        },
                        headerContent: {

                            class: 'text-center w-40'
                        },
                        headerCell: {

                            class: 'text-center w-40'
                        },
                        bodyCell: {

                            class: 'text-center'
                        }
                    }"> <template #loading>
                        </template>

                        <template #body="{ data }" #sortable>
                            {{ formatDate(data.fecha_ingreso) }}
                        </template>
                    </Column>

                    <Column field="fecha_limite" header="Fecha Límite" sortable :pt="{
                        bodyCellContent: {
                            class: 'text-center w-40'
                        },
                        headerContent: {

                            class: 'text-center w-40'
                        },
                        headerCell: {

                            class: 'text-center w-40'
                        },
                        bodyCell: {

                            class: 'text-center'
                        }
                    }"> <template #loading>
                        </template>

                        <template #body="{ data }" #sortable>
                            {{ formatDate(data.fecha_limite) }}
                        </template>
                    </Column>

                    <Column field="nro_servicio" header="Nº de Servicio" context="small" sortable :pt="{

                        bodyCellContent: {
                            class: ' w-36'
                        },
                        headerCell: {
                            class: 'w-36'
                        },
                        bodyCell: {

                            class: 'text-center'
                        },
                        headerContent: {

                            class: 'text-center w-36'
                        },

                    }">
                        <template #loading>
                        </template>
                    </Column>

                    <Column field="cliente" header="Cliente" sortable :pt="{
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

                            class: 'text-center'
                        }
                    }">
                        <template #loading>
                            <div class="flex align-items-center"
                                :style="{ height: '17px', 'flex-grow': '1', overflow: 'hidden' }">

                            </div>
                        </template>
                    </Column>
                    <Column field="telefono" header="Teléfono" sortable :pt="{
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

                            class: 'text-center'
                        }
                    }">
                        <template #loading>
                            <div class="flex align-items-center"
                                :style="{ height: '17px', 'flex-grow': '1', overflow: 'hidden' }">

                            </div>
                        </template>
                    </Column>
                    <Column field="tipo" header="Tipo" sortable :pt="{
                        bodyCellContent: {
                            class: 'text-center w-32'
                        },
                        headerContent: {

                            class: 'text-center w-32'
                        },
                        headerCell: {

                            class: 'text-center w-32'
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
                            class: 'text-center w-36'
                        },
                        headerContent: {

                            class: 'text-center w-36'
                        },
                        headerCell: {

                            class: 'text-center w-36'
                        },
                        bodyCell: {

                            class: 'text-center'
                        },
                    }">
                        <template #loading>
                        </template>
                        <template #body="slotProps">
                            <span class="text-md">
                                {{ slotProps.data.estado }}
                            </span>
                        </template>
                    </Column>

                    <Column field="modo" sortable header="Ingresado/Entregado" :pt="{
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
                        }
                    }"></Column>



                    <Column header="Acciones" style="width:100px" :pt="{
                        bodyCell: {
                            class: 'text-center p-0'
                        }
                    }">
                        <template #loading>
                        </template>
                        <template #body="slotProps">
                            <div class="flex items-center py-1">
                                <a class="w-8 h-8 rounded bg-sky-300 px-2 py-1 text-base font-normal text-white mr-2 hover:bg-sky-400" :href="route('rmas.generar_ticket', slotProps.data.id)" target="_blank"><i
                                        class="fas fa-print"></i></a>
                            <Button v-if="slotProps.data.modo !== 'ENTREGADO'"
                                @click="btnEliminar(slotProps.data.id)"
                                class="w-8 h-8 rounded border-red-700 bg-red-700 px-2 py-1 text-base font-normal text-white m-0 hover:bg-red-600"
                                v-tooltip.top="{ value: `Eliminar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"><i
                                    class="fas fa-trash"></i></Button>
                            </div>

                        </template>
                    </Column>
                </DataTable>

            </div>
            <!--Contenido-->
        </div>
    </AppLayout>
</template>


<style type="text/css"></style>
