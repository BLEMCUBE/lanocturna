<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

import { FilterMatchMode } from 'primevue/api';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";

const toast = useToast();
const tabla_ventas = ref()
const { permissions } = usePage().props.auth
const titulo = "Envios"
const ruta = 'envios'

//actualizar pagina
setTimeout(() => {
    if (route().current('envios.index')) {
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

                <DataTable :filters="filters" :value="tabla_ventas" :pt="{
                    bodyRow: { class: 'hover:cursor-pointer hover:bg-gray-100'  }
                }" scrollable scrollHeight="800px" :virtualScrollerOptions="{ itemSize: 46 }"
                    tableStyle="min-width: 50rem" @row-click="clickDetalle" size="small">
                    <template #header>
                        <div class="flex justify-start  text-md">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                            <h5 class="text-2xl font-medium text-black ml-8">
                                Productos listos para entrega
                            </h5>
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
                        }
                    }"></Column>
                    <Column field="destino" header="Destino" sortable :pt="{
                        bodyCellContent: {
                            class: 'text-center w-52'
                        },
                        headerContent: {

                            class: 'text-center w-52'
                        }
                    }"></Column>
                    <Column field="nro_compra" header="N° Compra" sortable :pt="{
                        bodyCellContent: {
                            class: 'text-center w-52'
                        },
                        headerContent: {

                            class: 'text-center w-52'
                        }
                    }"></Column>
                    <Column field="cliente" header="Cliente" sortable :pt="{
                         bodyCellContent: {
                            class: 'text-center w-52'
                        },
                        headerContent: {

                            class: 'text-center w-52'
                        }
                    }"></Column>
                    <Column field="observaciones" header="Observaciones" sortable :pt="{
                        bodyCellContent: {
                            class: 'text-center w-52'
                        },
                        headerContent: {

                            class: 'text-center w-52'
                        }
                    }"></Column>

                    <Column field="estado" header="Estado del pedido" sortable :pt="{
                       bodyCellContent: {
                            class: 'text-center w-52'
                        },
                        headerContent: {

                            class: 'text-center w-52'
                        }
                    }">
                        <template #body="slotProps">
                            <span class="font-semibold text-md" :class="colorEstado(slotProps.data.estado)">
                                {{ slotProps.data.estado }}
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
