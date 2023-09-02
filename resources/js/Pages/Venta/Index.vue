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
const titulo = "Ventas"
const ruta = 'ventas'
const { tipo_cambio } = usePage().props

const formDelete = useForm({
    id: '',
});


const btnVer = (id) => {
    router.get(route(ruta + '.show', id));

};
const btnEditar = (id) => {
    router.get(route(ruta + '.edit', id));

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

const clickDetalle=(e)=>{

btnVer(e.data.id)
}
onMounted(() => {

    tabla_ventas.value = usePage().props.ventas.data;

});

const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const BtnCrear = () => {
    if(tipo_cambio==true){

        router.get(route(ruta + '.create'));
    }else{
        ok('error','No se ha especificado el tipo de cambio para el día')
    }
}

const ok = (icono,mensaje) => {

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
                <Button size="small" :label="'Crear Venta'" severity="success" @click="BtnCrear"></Button>

            </div>

            <div class="align-middle">

                <DataTable  showGridlines :filters="filters" :value="tabla_ventas"
                :pt="{
                    bodyRow:{class:'hover:cursor-pointer'}
                }"
                scrollable scrollHeight="400px" :virtualScrollerOptions="{ itemSize: 46 }" tableStyle="min-width: 50rem"
                @row-click="clickDetalle"
                    size="small">
                    <template #header>
                        <div class="flex justify-content-end text-md">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                        </div>
                    </template>
                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>
                    <Column field="fecha" header="Facha y hora" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="codigo" header="No. Pedido" sortable
                    :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }}"></Column>
                    <Column field="vendedor" header="Vendedor" sortable
                    :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }}"></Column>
                    <Column field="cliente" header="Cliente" sortable
                    :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }}"></Column>

                    <Column field="destino" header="Destino" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>

                    <Column field="estado" header="Estado" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="moneda" header="Moneda" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="total_sin_iva" sortable header="Total sin IVA" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }

                    }"></Column>

                    <Column field="total" sortable header="Total" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="observaciones" sortable header="Observaciones" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>

                    <Column header="Acciones" style="width:100px">
                        <template #body="slotProps">
                            <Button v-if="permissions.includes('editar-ventas')"  @click="btnEditar(slotProps.data.id)"
                                class="w-8 h-8 rounded bg-primary-900 px-2 py-1 text-base font-normal text-white m-2 hover:bg-primary-100"
                                v-tooltip.top="{ value: `Editar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
                               ><i class="fas fa-edit"></i></Button>

                        </template>
                    </Column>
                </DataTable>

            </div>
            <!--Contenido-->

        </div>

    </AppLayout>
</template>


<style type="text/css"></style>
