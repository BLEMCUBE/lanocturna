

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import CrearModal from '@/Pages/Cliente/Partials/CrearModal.vue';
import EditarModal from '@/Pages/Cliente/Partials/EditarModal.vue';

import { FilterMatchMode } from 'primevue/api';
const tabla_clientes = ref()
const { permissions } = usePage().props.auth
import { useToast } from "primevue/usetoast";
const toast = useToast();
const titulo = "Clientes"
const ruta = 'clientes'


const formDelete = useForm({
    id: '',
});

onMounted(() => {
    tabla_clientes.value = usePage().props.clientes.data;
});



//modal eliminar
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
                        show('success', 'Eliminado', 'Se ha eliminado')
                        router.get(route(ruta + '.index'));
                    }
                });
        }
    });
}


const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },

});
</script>
<template>
    <div>

        <Head :title="titulo" />
        <AuthenticatedLayout :pagina="[{ 'label': titulo, link: false }]">
            <Toast />
            <div
                class="px-4 py-3 mb-4 bg-white col-span-12 pb-5 rounded-lg shadow-sm 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
                <!--Contenido-->
                <div class=" px-5 col-span-full flex justify-between items-center">
                    <h5 class="text-xl font-semibold">{{ titulo }}</h5>
                    <CrearModal v-if="permissions.includes('crear-clientes')"></CrearModal>
                </div>
                <div class="overflow-x-auto">
                    <div class="inline-block min-w-full  mt-4 align-middle">
                        <div class="overflow-hidden">
                            <div class="card">
                                <DataTable :rowClass="rowClass" showGridlines size="small" v-model:filters="filters"
                                    :value="tabla_clientes" :paginator="true" :rows="10"
                                    :rowsPerPageOptions="[5, 10, 20, 50]" :pt="{ bodycell: { class: 'bg-red-500' } }"
                                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                                    tableStyle="width: 100%">
                                    <template #header size="small" class="bg-secondary-900">
                                        <div class="flex justify-content-end text-md">
                                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                                        </div>

                                    </template>
                                    <template #empty> No existe Resultado </template>
                                    <template #loading> Cargando... </template>
                                    <Column field="id" header="ID"></Column>
                                    <Column field="nombre" header="Nombre" sortable></Column>
                                    <Column field="telefono" header="Telefono" sortable></Column>
                                    <Column field="localidad" header="Localidad" sortable></Column>
                                    <Column field="direccion" header="Dirección" sortable></Column>
                                    <Column field="empresa" header="Empresa" sortable></Column>
                                    <Column field="rut" header="Rut" sortable></Column>
                                    <Column field="email" header="Email" sortable></Column>
                                    <Column header="Acciones" style="width:100px">
                                        <template #body="slotProps">

                                            <span v-if="permissions.includes('editar-clientes')"
                                                class="inline-block rounded bg-primary-900 px-2 py-1 text-base font-semibold text-white mr-1 mb-1 hover:bg-primary-100">
                                                <EditarModal :cliente-id="slotProps.data.id"></EditarModal>
                                            </span>
                                            <span v-if="permissions.includes('eliminar-clientes')"
                                                class="inline-block rounded bg-red-700 px-2 py-1 text-base font-semibold text-white mr-1 mb-1 hover:bg-red-600">
                                                <button @click.prevent="eliminar(slotProps.data.id, slotProps.data.name)"><i
                                                        class="fas fa-trash-alt"></i></button>
                                            </span>
                                        </template>
                                    </Column>
                                </DataTable>
                            </div>
                            <!--


                            <DataTable :options="{ language, order: [[1, 'asc']] }"
                                class="pt-3 w-full text-md text-center text-gray-600 dark:text-gray-400">


                                <tbody>

                                    <tr :key="id" v-for="{
                                        id, nombre, localidad, direccion, telefono, empresa, rut, email
                                    }, index in datosTabla"
                                        class="bg-white border  text-center dark:bg-gray-800 dark:border-gray-700">
                                        <th scope="row"
                                            class="p-1 border dark:bg-gray-800 dark:border-gray-700 font-medium text-gray-900 dark:text-white">
                                            {{ id }}
                                        </th>
                                        <th scope="row"
                                            class="p-1 border dark:bg-gray-800 dark:border-gray-700 font-medium text-gray-900 dark:text-white">
                                            {{ nombre }}
                                        </th>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ telefono }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ localidad }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ direccion }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ empresa }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ rut }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ email }}
                                        </td>

                                        <td scope="row" class="p-1 border dark:border-gray-700 w-24">
                                            <span v-if="permissions.includes('editar-clientes')"
                                                class="inline-block rounded bg-primary-900 px-2 py-1 text-base font-normal text-white mr-1 mb-1 hover:bg-primary-100">
                                                <EditarModal :cliente-id="id"></EditarModal>
                                            </span>
                                            <span v-if="permissions.includes('eliminar-clientes')"
                                                class="inline-block rounded bg-red-700 px-2 py-1 text-base font-normal text-white mr-1 mb-1 hover:bg-red-600">
                                                <button @click.prevent="eliminar(id, nombre)"><i
                                                        class="fas fa-trash-alt"></i></button>
                                            </span>
                                        </td>
                                    </tr>

                                </tbody>
                            </DataTable>
   -->
                        </div>
                    </div>
                </div>
                <!--Contenido-->
            </div>

        </AuthenticatedLayout>

    </div>
</template>


<style type="text/css"></style>
