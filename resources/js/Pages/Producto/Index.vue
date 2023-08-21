<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, createApp, onMounted, nextTick } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import CrearModal from '@/Pages/Producto/Partials/CrearModal.vue';
import EditarModal from '@/Pages/Producto/Partials/EditarModal.vue';
import axios from 'axios'

import DataTable from 'primevue/datatable';

import { FilterMatchMode } from 'primevue/api';
import Column from 'primevue/column';

const data = ref([]);
const header = ref([]);
const datos_tabla = ref([]);
const table = ref();
const app = createApp({})
const tabla_clientes = ref([])
const webpaginate = ref(route('productos.paginate'))
const webpaginate2 = ref(route('productos.paginateweb'))
const { permissions } = usePage().props.auth
const { productos } = usePage().props
const titulo = "Productos"
const ruta = 'productos'

const formDelete = useForm({
    id: '',
});

const rowClass = (data) => {
    return [{ "bg-red-500/20": data.stock < 5 }];
};
const eliminar = (id, name) => {

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
                    onSuccess: () => {
                        ok('Eliminado')
                        router.get(route(ruta + '.index'));
                    }
                });
        }
    });
}
const customers = ref();
onMounted(() => {
    axios.get(route(ruta + '.paginateweb'))
        .then(res => {

            var datos = res.data
            header.value = datos.headers
            customers.value = datos.datos

        })

    axios.get(route(ruta + '.paginate'))
        .then(res => {
            var datos2 = res.data.data
            tabla_clientes.value = datos2
            console.log('tabla_clientes.value', tabla_clientes.value);
        })

});



nextTick(() => {

})


const ok = (mensaje) => {
    //form.reset();

    Swal.fire({
        width: 350,
        title: mensaje,
        icon: 'success'
    })
}


const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },

});
</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12 pb-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <div class=" px-3 col-span-full flex justify-between items-center">

                <h5 class="text-xl font-semibold">{{ titulo }}</h5>
                <CrearModal v-if="permissions.includes('crear-productos')"></CrearModal>
            </div>

            <div class="inline-block min-w-full  mt-4 align-middle">

                <DataTable :rowClass="rowClass" showGridlines size="small" v-model:filters="filters" :value="customers"
                    paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" tableStyle="min-width: 50rem">
                    <template #header size="small" class="bg-secondary-900">
                        <div class="flex justify-content-end text-md">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                        </div>
                    </template>
                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>
                    <Column field="imagen" header="Imagen"></Column>
                    <Column field="nombre" :pt="{ root: 'text-center' }" header="Nombre" sortable></Column>
                    <Column field="aduana" header="Aduana" sortable style="max-width: 25%"></Column>
                    <Column field="codigo_barra" header="Código barra" sortable></Column>
                    <Column field="stock" sortable header="Stock"></Column>
                    <Column field="stock_minimo" sortable header="Stock mínimo"></Column>
                    <Column field="stock_futuro" sortable header="Stock futuro"></Column>
                    <Column header="Acciones" style="width:100px">
                        <template #body="slotProps">
                            <span v-if="permissions.includes('editar-productos')"
                                class="inline-block rounded bg-primary-900  px-2 py-1 text-base font-normal text-white mr-1 mb-1 hover:bg-primary-100">
                                <EditarModal :cliente-id="slotProps.data.id"></EditarModal>
                            </span>
                            <span v-if="permissions.includes('eliminar-productos')"
                                class="inline-block rounded bg-red-700 px-2 py-1 text-base font-normal text-white mr-1 mb-1 hover:bg-red-600">
                                <button @click.prevent="eliminar(slotProps.data.id, slotProps.data.nombre)"><i
                                        class="fas fa-trash-alt"></i></button>
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
