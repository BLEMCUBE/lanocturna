

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, reactive, computed, watch } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import CrearModal from '@/Pages/Cliente/Partials/CrearModal.vue';
import EditarModal from '@/Pages/Cliente/Partials/EditarModal.vue';
import { Breadcrumb, BreadcrumbItem } from 'flowbite-vue'
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import language from 'datatables.net-plugins/i18n/es-ES.mjs';
DataTable.use(DataTablesCore);

const tabla_clientes = ref({})
const searchQuery = ref('');
const { permissions } = usePage().props.auth
const titulo = "Clientes"
const ruta = 'clientes'
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
                        ok('Eliminado')
                        router.get(route(ruta + '.index'));
                    }
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

        <Head :title="titulo" />
        <AuthenticatedLayout>
            <div class="ml-4 col-span-full">
                <Breadcrumb>
                    <BreadcrumbItem href="/inicio" home>
                        Inicio
                    </BreadcrumbItem>
                    <BreadcrumbItem>
                        {{ titulo }}
                    </BreadcrumbItem>
                </Breadcrumb>
            </div>

            <div
                class="px-4 py-3 mb-4 bg-white col-span-12 pb-5 rounded-lg shadow-sm 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
                <!--Contenido-->
                <div class=" px-5 col-span-full flex justify-between items-center">

                    <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">{{ titulo }}</h1>
                    <CrearModal v-if="permissions.includes('crear-clientes')"></CrearModal>
                </div>
                <div class="overflow-x-auto">
                    <div class="inline-block min-w-full  mt-4 align-middle">
                        <div class="overflow-hidden">
                            <DataTable :options="{ language, order: [[1, 'asc']] }"
                                class="pt-3 w-full text-md text-center text-gray-600 dark:text-gray-400">
                                <thead
                                    class="text-md text-center text-primary-900 bg-secondary-900 dark:bg-gray-700 dark:text-gray-400">
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
                                                Telefono
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Localidad
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Dirección
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Empresa
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Rut
                                            </div>
                                        </th>
                                        <th scope="col" class="border border-gray-300 dark:border-gray-500">
                                            <div class="flex justify-center text-center">
                                                Email
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

                        </div>
                    </div>
                </div>
                <!--Contenido-->
            </div>

        </AuthenticatedLayout>

    </div>
</template>


<style type="text/css">
@import 'datatables.net-dt';
</style>
