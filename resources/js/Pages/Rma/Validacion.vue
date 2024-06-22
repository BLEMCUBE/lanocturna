<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { FilterMatchMode } from 'primevue/api';
import Column from 'primevue/column';

import { useToast } from "primevue/usetoast";
const toast = useToast();
const tabla_ventas = ref()
const { permissions } = usePage().props.auth

const titulo = "Validación Rma"
const ruta = 'rmas'

setTimeout(() => {
    if (route().current('rmas.validacion')) {
        //window.open(self.location, '_self');
    }
}, 60000);

const btnVer = (id) => {
    router.get(route(ruta + '.show-validacion', id));

};

const formDelete = useForm({
    id: '',
});


const clickDetalle = (e) => {

    btnVer(e.data.id)
}
onMounted(() => {

    tabla_ventas.value = usePage().props.ventas.data;

});


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
            formDelete.delete(route(ruta + '.destroy-subido', id),
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
            class="card p-3 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <Toast />
            <div class="p-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div class="align-middle">

                <DataTable :filters="filters" :value="tabla_ventas" :pt="{
                    bodyRow: { class: 'hover:cursor-pointer p-1 hover:bg-gray-100 hover:text-black' }
                }" scrollable scrollHeight="700px" paginator :rows="50" @row-click="clickDetalle" size="small">
                    <template #header>
                        <div class="grid grid-cols-6 gap-4 m-1.5">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" :pt="{
                                root: { class: 'col-span-6 lg:col-span-2 m-1.5' }
                            }" />

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

                    <Column header="Acciones" style="width:100px" :pt="{
                        bodyCell: {
                            class: 'text-center p-0'
                        }
                    }">
                        <template #loading>
                        </template>
                        <template #body="slotProps">
                            <div class="flex items-center justify-center py-1">
                                <Button v-if="slotProps.data.modo !== 'ENTREGADO'"
                                    @click="btnEliminar(slotProps.data.id)"
                                    class="w-8 h-8 rounded py-0.5 border-red-700 bg-red-700 hover:bg-red-600 hover:border-red-600 text-base font-normal text-white"
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
