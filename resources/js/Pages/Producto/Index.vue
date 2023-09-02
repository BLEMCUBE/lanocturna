<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
//import DataTable from 'primevue/datatable';
import ColumnGroup from 'primevue/columngroup';   // optional
import Row from 'primevue/row';                   // optional

import { FilterMatchMode } from 'primevue/api';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";
const toast = useToast();
const tabla_productos = ref()
const { permissions } = usePage().props.auth
const titulo = "Productos"
const ruta = 'productos'

const formDelete = useForm({
    id: '',
});

const rowClass = (data) => {
    //Si stock = < stock minimo Y stock_futuro = stock
    if (parseFloat(data.stock) <= parseFloat(data.stock_minimo) && parseFloat(data.stock) == parseFloat(data.stock_futuro)) {
        //return "text-red-700 text-xs"
        return ["bg-red-700 text-xs text-white"]
    }
    //Si stock = < stock mínimo y stock_futuro > stock
    if (parseFloat(data.stock) <= parseFloat(data.stock_minimo) && parseFloat(data.stock_futuro) > parseFloat(data.stock)) {
        //return ["text-orange-500 text-xs"]
        return "bg-orange-500 text-xs text-white"
    }
};


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

onMounted(() => {

    tabla_productos.value = usePage().props.productos.data;

});

const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const BtnCrear = () => {
    router.get(route(ruta + '.create'));
}
const clickDetalle=(e)=>{
btnVer(e.data.id)
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

                <Button size="small" :label="'Agregar Producto'" severity="success" @click="BtnCrear"></Button>

            </div>

            <div class="align-middle">

                <DataTable :rowClass="rowClass" showGridlines :filters="filters" :value="tabla_productos"
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

                    <Column field="stock" sortable header="Stock" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="stock_futuro" sortable header="Stock futuro" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="imagen" header="Imagen" style="width:60px" :pt="{
                        bodyCell: {
                            class: 'flex justify-center text-center'
                        }
                    }">
                        <template #body="slotProps">
                            <img class="rounded  bg-white shadow-2xl border-2 text-center w-12 h-12 object-contain"
                                :src="slotProps.data.imagen" alt="image description">
                        </template>
                    </Column>
                    <Column field="origen" header="Origen" sortable></Column>
                    <Column field="nombre" header="Nombre" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>



                    <Column header="Acciones" style="width:130px">
                        <template #body="slotProps">
                            <!--

                                <button v-if="permissions.includes('editar-productos')"
                                class="w-8 h-8 rounded bg-yellow-500  px-2 py-1 text-base font-normal text-black m-1 hover:bg-yellow-400"
                                v-tooltip.top="{ value: `Ver`, pt: { text: 'bg-gray-500 p-1 m-0 text-xs text-white rounded' } }"
                                @click.prevent="btnVer(slotProps.data.id)"><i class="fas fa-eye"></i></button>
                            -->

                            <button v-if="permissions.includes('editar-productos')"
                                class="w-8 h-8 rounded bg-primary-900   px-2 py-1 text-base font-normal text-white m-1 hover:bg-primary-100"
                                v-tooltip.top="{ value: `Editar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
                                @click.prevent="btnEditar(slotProps.data.id)"><i class="fas fa-edit"></i></button>
                            <button v-if="permissions.includes('eliminar-productos')"
                                class="w-8 h-8 rounded bg-red-700   px-2 py-1 text-base font-normal text-white m-1 hover:bg-red-600"
                                v-tooltip.top="{ value: `Eliminar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
                                @click.prevent="btnEliminar(slotProps.data.id, slotProps.data.nombre)"><i
                                    class="fas fa-trash-alt"></i></button>

                        </template>
                    </Column>
                </DataTable>

            </div>
            <!--Contenido-->

        </div>

    </AppLayout>
</template>


<style type="text/css"></style>
