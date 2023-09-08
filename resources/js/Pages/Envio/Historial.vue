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
const { permissions } = usePage().props.auth
const titulo = "Historial de Envios"
const ruta = 'envios'
const { tipo_cambio } = usePage().props

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

const date = ref([new Date(), new Date()]);
//const date = ref([]);
//filtrado
const filtrado = (value) => {
console.log('d ',value)
if(value[0]!=null && value[1]!=null){

    router.get('/envios/historial/',
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
    }else{
        router.get(route(ruta+'.historial'))
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
    router.get(route(ruta + '.detalle', id));

};
const btnImprimir = (id) => {
    router.get(route(ruta + '.generar_ticket', id));

};
const btnEliminar = (id, name) => {

    const alerta = Swal.mixin({ buttonsStyling: true });
    alerta.fire({
        width: 350,
        title: "Seguro de eliminar " + name,
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

    tabla_ventas.value = usePage().props.ventas.data;

});

const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};


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
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <Toast />
            <div class="px-3 pb-2 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>


            </div>

            <div class="align-middle">

                <DataTable showGridlines :filters="filters" :value="tabla_ventas" :pt="{
                    bodyRow: { class: 'hover:cursor-pointer p-1' }
                }" scrollable scrollHeight="800px" :virtualScrollerOptions="{ class: 'min-h-screen', itemSize: 46 }"
                    @row-click="clickDetalle" size="small">
                    <template #header>
                        <div class="grid grid-cols-6 gap-4">
                            <InputText v-model="filters['global'].value" placeholder="Buscar"
                            :pt="{
                                root:{class:'col-span-6 lg:col-span-2'}
                            }"/>

                                <date-picker @change="filtrado" type="date" range value-type="YYYY-MM-DD"
                                    format="DD/MM/YYYY"
                                    class="p-inputtext p-component col-span-6 lg:col-span-2 font-sans  font-normal text-gray-700  bg-white  transition-colors duration-200 border-0 text-sm px-0 py-0"
                                     v-model:value="date" :shortcuts="shortcuts" lang="es"
                                    placeholder="Seleccione Fecha"></date-picker>

                        </div>
                    </template>
                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>
                    <Column field="fecha" header="Facha y hora" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="destino" header="Destino" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>

                    <Column field="nro_compra" header="N° Compra" sortable :pt="{
                        bodyCell: {
                            class: 'text-center border'
                        }
                    }"></Column>
                    <Column field="cliente" header="Cliente" sortable :pt="{
                        bodyCell: {
                            class: 'text-center border'
                        }
                    }"></Column>
                    <!--

    <Column field="estado" header="Estado" sortable :pt="{
        bodyCell: {
            class: 'text-center'
                        }
                    }">
                        <template #body="slotProps">
                            <span class="font-semibold text-md" :class="colorEstado(slotProps.data.estado)">
                                {{ slotProps.data.estado }}
                            </span>
                        </template>
                    </Column>
                -->

                    <Column field="observaciones" sortable header="Observaciones" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>

                    <Column header="Acciones" style="width:100px" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }">
                        <template #body="slotProps">
                            <span
                                class="inline-block rounded bg-sky-300 px-2 py-1 text-base font-semibold text-white mr-1 mb-1 hover:bg-sky-400">
                                <a :href="route('envios.generar_ticket', slotProps.data.id)" target="_blank"><i
                                        class="fas fa-print"></i></a>
                            </span>
                        </template>
                    </Column>
                </DataTable>

            </div>
            <!--Contenido-->
        </div>
    </AppLayout>
</template>


<style type="text/css"></style>
