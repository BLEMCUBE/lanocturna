<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, Link } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
const tabla_categorias = ref()
const titulo = "Roles"

onMounted(() => {
    tabla_categorias.value = usePage().props.roles;
});


const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    'country.name': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    representative: { value: null, matchMode: FilterMatchMode.IN },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
    verified: { value: null, matchMode: FilterMatchMode.EQUALS }
});

</script>
<template>
    <div>

        <Head title="Roles" />
        <AuthenticatedLayout :pagina="[{ 'label': titulo, link: false }]">

            <div
                class="px-4 py-0 mb-4 bg-white col-span-6 pb-5 rounded-lg shadow-sm 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
                <div class=" px-5 py-2 col-span-full flex justify-between items-center">
                    <h5 class="text-xl font-semibold">{{ titulo }}</h5>
                </div>
                <div class="overflow-x-auto">
                    <div class="inline-block min-w-full mt-1 align-middle">
                        <div class="overflow-hidden">

                            <div class="card">
                                <DataTable :rowClass="rowClass" showGridlines size="small" v-model:filters="filters"
                                    :value="tabla_categorias" :paginator="true" :rows="10"
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
                                    <Column header="Acciones" style="width:100px">
                                        <template #body="slotProps">

                                            <Link :href="route('roles.edit', slotProps.data.id)"
                                                class="inline-block rounded bg-primary-900 px-2 py-1 mx-auto text-sm font-semibold text-white hover:bg-primary-100">
                                            Permisos
                                            </Link>
                                        </template>
                                    </Column>
                                </DataTable>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>

    </div>
</template>


<style type="text/css" scoped></style>
