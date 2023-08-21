

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted} from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import CrearModal from '@/Pages/Usuario/Partials/CrearModal.vue';
import EditarModal from '@/Pages/Usuario/Partials/EditarModal.vue';
import { FilterMatchMode } from 'primevue/api';
import { useToast } from "primevue/usetoast";

const tabla_clientes = ref()
const { permissions } = usePage().props.auth
const titulo = "Usuarios"
const ruta = 'usuarios'
const toast = useToast();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});


const formDelete = useForm({
    id: '',
});


onMounted(() => {
    tabla_clientes.value = usePage().props.usuarios.data;
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
                        show('success','Eliminado','Se ha eliminado')
                        router.get(route(ruta + '.index'));
                    }
                });
        }
    });
}

const show = (tipo,titulo,mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

</script>
<template>

        <Head :title="titulo" />
        <AppLayout :pagina="[{ 'label': titulo, link: false }]">
            <div
            class="px-4 py-3 mb-4 bg-white col-span-7 pb-2 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <Toast />
                <div class="px-5 col-span-full flex justify-between items-center">

                    <h5 class="text-xl font-semibold">{{ titulo }}</h5>
                    <CrearModal v-if="permissions.includes('crear-usuarios')"></CrearModal>
                </div>

                    <div class="inline-block min-w-full mt-4 align-middle">
                                <DataTable  showGridlines size="small" v-model:filters="filters"
                                    :value="tabla_clientes" :paginator="true" :rows="10"
                                    :rowsPerPageOptions="[5, 10, 20, 50]"
                                    :pt="{bodycell:{class:'bg-red-500'}}"
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
                                    <Column field="name" header="Nombre" sortable></Column>
                                    <Column field="username" header="Usuario" sortable></Column>
                                    <Column field="roles" header="Rol" sortable></Column>
                                    <Column header="Acciones" style="width:100px">
                                        <template #body="slotProps">

                                            <span v-if="permissions.includes('editar-usuarios')"
                                                class="inline-block rounded bg-primary-900 px-2 py-1 text-base font-semibold text-white mr-1 mb-1 hover:bg-primary-100">
                                                <EditarModal :cliente-id="slotProps.data.id"></EditarModal>
                                            </span>
                                            <span v-if="permissions.includes('eliminar-usuarios')"
                                                class="inline-block rounded bg-red-700 px-2 py-1 text-base font-semibold text-white mr-1 mb-1 hover:bg-red-600">
                                                <button @click.prevent="eliminar(slotProps.data.id, slotProps.data.name)"><i
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


<style type="text/css">

</style>
