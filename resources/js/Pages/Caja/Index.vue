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
const titulo = "Caja"
const ruta = 'cajas'

setTimeout(()=>{
    if(route().current('cajas.index')){
        window.open(self.location, '_self');
    }
}, 60000);

const btnVer = (id) => {
    router.get(route(ruta + '.show', id));

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
            class="card px-4 py-3 mb-4 bg-white col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <Toast />
            <div class="px-3 pb-2 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div class="align-middle">

                <DataTable showGridlines :filters="filters" :value="tabla_ventas" :pt="{
                    bodyRow: { class: 'hover:cursor-pointer' }
                }" scrollable scrollHeight="800px" :virtualScrollerOptions="{ itemSize: 46 }"
                    tableStyle="min-width: 50rem" @row-click="clickDetalle" size="small">
                    <template #header>
                        <div class="flex justify-content-end text-md">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                        </div>
                    </template>
                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>
                    <Column field="codigo" header="N° de Venta" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
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
                    <Column field="cliente" header="Cliente" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>

                    <Column field="observaciones" sortable header="Observaciones" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                </DataTable>

            </div>
            <!--Contenido-->

        </div>

    </AppLayout>
</template>


<style type="text/css"></style>
