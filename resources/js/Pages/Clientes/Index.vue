

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, reactive, computed, watch } from 'vue'
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { Breadcrumb, BreadcrumbItem } from 'flowbite-vue'
import Swal from 'sweetalert2';
import CrearModal from '@/Pages/Clientes/Partials/CrearModal.vue';
import EditarModal from '@/Pages/Clientes/Partials/EditarModal.vue';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import language from 'datatables.net-plugins/i18n/es-ES.mjs';
DataTable.use(DataTablesCore);

const tabla_clientes = ref({})
const searchQuery = ref('');
const {permissions} =usePage().props.auth
const datosTabla = computed(() => {
    return usePage().props.clientes.data;
})
//buscador
const filteredItems = computed(() => {
    let filteredItems1 = usePage().props.clientes.data;
    if (searchQuery.value !== "") {
        pagination.currentPage = 1;
        filteredItems1 = tabla_clientes.value.filter(bet => {
            return bet.nombre.toLowerCase().includes(searchQuery.value.toLowerCase())
                || bet.rfc.toLowerCase().includes(searchQuery.value.toLowerCase())
                || bet.email.toLowerCase().includes(searchQuery.value.toLowerCase())
                || bet.tipo_cliente.toLowerCase().includes(searchQuery.value.toLowerCase())


        })
    }
    return filteredItems1;

})

const pagination = reactive({
    currentPage: 1,
    perPage: 10,
    totalPages: computed(() =>
        Math.ceil(filteredItems.value.length / pagination.perPage)
    ),
});

watch(
    () => pagination.totalPages,
    () => (pagination.currentPage = 1)
);

const paginatedItems = computed(() => {
    const { currentPage, perPage } = pagination;
    const start = (currentPage - 1) * perPage;
    const stop = start + perPage;

    return filteredItems.value.slice(start, stop);
});


const clickCallback = (page) => {
    pagination.currentPage = page;
}

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
            formDelete.delete(route('clientes.destroy', id),
                {
                    preserveScroll: true,
                    onSuccess: () => { ok('Eliminado') }
                });
        }
    });
}

const ok = (mensaje) => {
    //form.reset();

    Swal.fire({
        width: 350,
        title: mensaje,
        icon: 'success'
    })
}


</script>
<template>
    <div>

        <Head title="Clientes" />
        <AuthenticatedLayout>

            <div class="ml-4 col-span-full">

                <Breadcrumb>
                    <BreadcrumbItem href="/" home>
                        Inicio
                    </BreadcrumbItem>
                    <BreadcrumbItem>
                        Clientes
                    </BreadcrumbItem>
                </Breadcrumb>
            </div>
            <div class=" px-5 col-span-full flex justify-between items-center">

                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Clientes</h1>
                <CrearModal v-if="permissions.includes('crear-clientes')"></CrearModal>
            </div>
            <div
                class="p-4 mb-4 bg-white col-span-12 pb-5 rounded-lg shadow-sm 2xl:col-span-12 dark:border-gray-700 sm:p-4 dark:bg-gray-800">

                <div class="overflow-x-auto">
                    <div class="inline-block min-w-full  mt-4 align-middle">
                        <div class="overflow-hidden">
                            <DataTable :options="{ language,  order: [[1, 'asc']] }"
                                class="pt-3 w-full text-xs text-center text-gray-600 dark:text-gray-400">
                                <thead class="text-md text-center text-gray-700 bg-gray-200/80 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center">
                                                ID
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center  text-center">
                                                Nombre
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                RFC
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Email
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Télefono
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Casa dirección
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Casa colonia
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Oficina dirección
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Oficina colonia
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Fecha nacimiento
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center">
                                                Total compras
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center">
                                                Última compra
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Tipo Cliente
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center">
                                                Ingreso sistema
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center">
                                                Acciones
                                            </div>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <tr :key="id" v-for="{
                                        id, nombre, rfc, email, telefono, casa_direccion, casa_colonia, oficina_direccion, oficina_colonia,
                                        fecha_nacimiento, total_compras, ultima_compra, tipo_cliente, created_at
                                    }, index in datosTabla"
                                        class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-700">
                                        <th scope="row"
                                            class="p-1 border-2 dark:bg-gray-800 dark:border-gray-700 font-medium text-gray-900 dark:text-white">
                                            {{ id }}
                                        </th>
                                        <th scope="row"
                                            class="p-1 border-2 dark:bg-gray-800 dark:border-gray-700 font-medium text-gray-900 dark:text-white">
                                            {{ nombre }}
                                        </th>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ rfc }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ email }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ telefono }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ casa_direccion }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ casa_colonia }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ oficina_direccion }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ oficina_colonia }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ fecha_nacimiento }}
                                        </td>
                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ total_compras }}
                                        </td>
                                        <td scope="row" class="p-1 border-2 dark:border-gray-700 text-center">
                                            {{ ultima_compra }}
                                        </td>
                                        <td scope="row" class="p-1 border-2 dark:border-gray-700 text-center">
                                            {{ tipo_cliente }}
                                        </td>

                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            {{ created_at }}
                                        </td>

                                        <td scope="row" class="p-1 border dark:border-gray-700">
                                            <span
                                            v-if="permissions.includes('editar-clientes')"
                                                class="inline-block rounded bg-blue-600 px-2 py-1 text-base font-semibold text-white mr-1 mb-1 hover:bg-blue-700">
                                                <EditarModal :cliente-id="id" ></EditarModal>
                                            </span>
                                            <span
                                            v-if="permissions.includes('eliminar-clientes')"
                                                class="inline-block rounded bg-red-600 px-2 py-1 text-base font-semibold text-white mr-1 mb-1 hover:bg-red-700">
                                                <button @click.prevent="eliminar(id, nombre)" ><i
                                                        class="fas fa-trash-alt"></i></button>
                                            </span>
                                        </td>
                                    </tr>

                                </tbody>
                            </DataTable>



                        </div>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>

    </div>
</template>


<style type="text/css">
@import 'datatables.net-dt';

.imagePreviewWrapper {
    background-repeat: no-repeat;
    width: 100px;
    height: 100px;
    display: block;
    margin: 0 auto;
    background-size: contain;
    background-position: center center;
}
</style>
