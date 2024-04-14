<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';

import { FilterMatchMode } from 'primevue/api';
import Column from 'primevue/column';
import { useToast } from "primevue/usetoast";
const toast = useToast();
const tabla_ventas = ref()
const { permissions } = usePage().props.auth
const titulo = "Validación Rma"
const ruta = 'rmas'

setTimeout(()=>{
    if(route().current('rmas.validacion')){
        window.open(self.location, '_self');
    }
}, 60000);

const btnVer = (id) => {
    router.get(route(ruta + '.show-validacion', id));

};




const clickDetalle = (e) => {

    btnVer(e.data.id)
}
onMounted(() => {

    tabla_ventas.value = usePage().props.ventas.data;

});

const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};



const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div
            class="card px-4 mb-4 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <Toast />
            <div class="px-3 pb-2 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div class="align-middle">

<DataTable :filters="filters" :value="tabla_ventas" :pt="{
    bodyRow: { class: 'hover:cursor-pointer p-1 hover:bg-gray-100 hover:text-black' }
}" scrollable scrollHeight="700px" paginator :rows="50"
    @row-click="clickDetalle" size="small">
    <template #header>
        <div class="grid grid-cols-6 gap-4 m-1.5">
            <InputText v-model="filters['global'].value" placeholder="Buscar" :pt="{
                root: { class: 'col-span-6 lg:col-span-2 m-1.5' }
            }" />

            <date-picker @change="filtrado" type="date" range value-type="YYYY-MM-DD" format="DD/MM/YYYY"
                class="p-inputtext p-component col-span-6 lg:col-span-2 px-2 font-sans  font-normal text-gray-700  bg-white  transition-colors duration-200 border-0 text-sm"
                v-model:value="date" :shortcuts="shortcuts" lang="es"
                placeholder="Seleccione Fecha"></date-picker>

        </div>
    </template>
    <template #empty> No existe Resultado </template>
    <template #loading> Cargando... </template>
    <Column field="fecha" header="Fecha y Hora" sortable :pt="{
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
    }"></Column>
    <Column field="destino" header="Destino" sortable :pt="{
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
    }"></Column>
    <Column field="parametro.rma.nro_servicio" header="Nº de Servicio" context="small" sortable :pt="{

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
    <Column field="parametro.rma.nro_compra" header="N° Compra" sortable :pt="{
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
    <Column field="cliente" header="Cliente" sortable :pt="{
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


    <Column field="observaciones" sortable header="Observaciones" :pt="{
        bodyCell: {
            class: 'text-center'
        }
    }"></Column>

    <!--
    <Column header="Acciones" style="width:100px" :pt="{
        bodyCell: {
            class: 'text-center'
        }
    }">
        <template #body="slotProps">
            <span
                class="inline-block rounded bg-sky-300 px-2 py-1 text-base font-semibold text-white mr-1 mb-1 hover:bg-sky-400">
                <a :href="route('rmas.generar_ticket', slotProps.data.id)" target="_blank"><i
                        class="fas fa-print"></i></a>
            </span>
        </template>
    </Column>

    -->
</DataTable>

</div>
            <!--Contenido-->

        </div>

    </AppLayout>
</template>


<style type="text/css"></style>
