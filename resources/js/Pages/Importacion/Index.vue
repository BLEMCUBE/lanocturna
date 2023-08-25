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
const tabla_productos = ref()
const { permissions } = usePage().props.auth
const titulo = "Importaciones"
const ruta = 'importaciones'

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

onMounted(() => {

    tabla_productos.value = usePage().props.productos.data;

});

const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const BtnCrear = () => {
    router.get(route(ruta + '.create'));
}


const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
});
</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <Toast />
            <div class="px-3 pb-4 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>

                <Button size="small" :label="'Importar Excel'" severity="success" @click="BtnCrear"></Button>

            </div>

            <div class="align-middle">

                <DataTable  showGridlines :filters="filters" :value="tabla_productos" paginator
                    :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" tableStyle="min-width: 50rem" size="small">
                    <template #header>
                        <div class="flex justify-content-end text-md">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                        </div>
                    </template>
                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>
                    <Column field="id" header="ID" sortable
                    :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }}"></Column>

                    <Column field="nro_carpeta" header="No. de Carpeta" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="nro_contenedor" header="BL o No. de Contenedor" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="estado" header="Estado de pedido" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="total" sortable header="Total" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="cbm_total" sortable header="Total CBM" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="cantidad_productos" sortable header="Cantidad productos" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="created_at" sortable header="Fecha" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column header="Acciones" style="width:130px">
                        <template #body="slotProps">
                            <button v-if="permissions.includes('editar-productos')"
                                class="w-8 h-8 rounded bg-yellow-500  px-2 py-1 text-base font-normal text-black m-1 hover:bg-yellow-400"
                                v-tooltip.top="{ value: `Ver`, pt: { text: 'bg-gray-500 p-1 m-0 text-xs text-white rounded' } }"
                                @click.prevent="btnVer(slotProps.data.id)"><i class="fas fa-eye"></i></button>

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
