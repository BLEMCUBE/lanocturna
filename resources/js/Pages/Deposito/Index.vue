

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, watch, computed } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import CambiarDepositoModal from '@/Pages/Deposito/Partials/CambiarDepositoModal.vue';
import CambiarProductosDepositoModal from '@/Pages/Deposito/Partials/CambiarProductosDepositoModal.vue';

import { FilterMatchMode } from 'primevue/api';
const { roles } = usePage().props.auth
const expandedRows = ref([]);
import { useToast } from "primevue/usetoast";
const toast = useToast();
const titulo = "Depósitos"
const ruta = 'depositos'
const lista_depositos = ref();
const isSend = ref(false);

const formDelete = useForm({
    id: '',
    productos: []
});

onMounted(() => {
    lista_depositos.value = usePage().props.depositos;
    //expandedRows.value = lista_depositos.value.filter((p) => p.id);
    expandedRows.value = null;

});
const btnEliminar = () => {

    const alerta = Swal.mixin({ buttonsStyling: true });
    alerta.fire({
        width: 350,
        title: "Seguro de eliminar ",
        text: 'Se eliminará definitivamente, tenga en cuenta que no podrá editar la importacion de bultos donde el producto este incluido.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        cancelButtonColor: 'red',
        confirmButtonColor: '#2563EB',

    }).then((result) => {
        if (result.isConfirmed) {
            isSend.value=true;
            formDelete.post(route(ruta + '.destroyproductos'),
                {
                    preserveScroll: true,
                    forceFormData: true,
                    onSuccess: () => {
                        isSend.value=false;
                        formDelete.reset();
                        show('success', 'Eliminado', 'Se ha eliminado')
                        setTimeout(() => {
                            router.get(route(ruta + '.index'));
                        }, 700);

                    }
                });
        }
    });
}


const rowClass = (data) => {

    var valores = data.productos.map((el) => {
        return el.sku.toLowerCase();
    })
    var producto = data.productos.map((el) => {
        return el.nombre.toLowerCase();
    })

    if (filters.value.global.value != undefined && filters.value.global.value != '') {
        const result = valores.filter((word) => word.includes(filters.value.global.value.toLowerCase()));
        const prod = producto.filter((word) => word.includes(filters.value.global.value.toLowerCase()));

        return [
            {

                'bg-orange-400': result.length > 0 || prod.length > 0
            },

        ];

    }
    else {
        return [
            'bg-secondary-900/70'
        ];
    }


};
const eventoEnvio = (checked) => {

    if (checked) {
        formDelete.reset();
    } else {

    }
};
const collapseAll = () => {
    expandedRows.value = null;
};

const expandEvent = (event) => {
    expandedRows.value = lista_depositos.value.filter((p) => p.id==event.data.id);
    formDelete.reset();

};
const collapseEvent = (event) => {
    expandedRows.value = null;
    formDelete.reset();

};

const expandAll = () => {
    expandedRows.value = lista_depositos.value.filter((p) => p.id);

};
const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};



const filters = ref({

    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    'productos': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <Toast />
            <div class=" px-5 pb-2 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>
            <div class="align-middle">

                <DataTable v-model:expandedRows="expandedRows" size="small" v-bind:rowClass="rowClass"
                v-on:row-collapse="collapseEvent" v-on:row-expand="expandEvent"
                                    :value="lista_depositos" scrollable scrollHeight="800px" :virtualScrollerOptions="{ itemSize: 46 }"
                    tableStyle="min-width: 50rem" :pt="{

                    }">

                    <template #header size="small" class="bg-secondary-900">
                        <div class="flex justify-content-end text-base text-primary-900">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                            <Button class="ml-5 rounded-sm" text icon="pi pi-plus" label="Expandir todo"
                                @click="expandAll" />
                            <Button class="ml-5 rounded-sm" text icon="pi pi-minus" label="Contraer todo"
                                @click="collapseAll" />
                        </div>
                    </template>

                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>

                    <Column expander style="width: 4rem" :pt="{
                        bodyCell: { class: 'bg-secondary-900/30 font-bold m-2 text-2xl text-center' },
                        headerCell: { class: 'uppercase bg-secondary-900 text-md ' },
                    }" />

                    <Column sortable field="nombre" header="Depósito" :pt="{
                        bodyCell: { class: 'bg-secondary-900/30 font-bold text-center' },
                        headerCell: { class: 'uppercase bg-secondary-100 text-md' },



                    }"></Column>

                    <Column header="Cantidad Productos" :pt="{
                        bodyCell: { class: 'bg-secondary-900/30 font-bold w-24  text-center' },
                        headerContent: { class: ' mx-2' },
                        headerCell: { class: 'uppercase bg-secondary-900 text-md' },

                    }">
                        <template #body="slotProps">
                            {{ (slotProps.data.productos.length)
                            }}
                        </template>
                    </Column>
                    <Column header="" :pt="{
                            bodyCell: { class: 'bg-secondary-900/30 font-bold w-14  text-center' },
                            headerContent: { class: 'mx-2 text-center' },
                            headerCell: { class: 'uppercase bg-secondary-900 text-md' },

                        }">
                        <template #body="slotProps">
                            <div v-if="roles.includes('Super Administrador') || roles.includes('Administrador')"
                                v-tooltip.top="{ value: 'Descargar Excel', pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
                                class=" w-8 h-8 rounded bg-green-600 flex justify-center items-center text-base font-semibold text-white mr-1 hover:bg-green-600">
                                <a :href="route('depositos.exportar', slotProps.data.id)" target="_blank"
                                    class="py-auto p-2 text-xl"><i class="fas fa-file-excel text-white"></i>
                                </a>
                            </div>

                        </template>
                    </Column>
                    <template #expansion="slotProps">
                        <div class="px-1">
                            <DataTable :value="slotProps.data.productos" scrollable scrollHeight="300px"
                            paginator :rows="100" v-model:filters="filters"
                                v-model:selection="formDelete.productos"
                                :pt="{

                                    header: { class: 'text-center pb-0 mb-0' },
                                    headerContent: { class: 'bg-sky-300 pb-0 mb-0' },

                                }"
                                >
                                <template #header size="small">
                                    <div class="flex justify-content-end text-base bg-sky-300 px-5 py-2">

                                        <div>

                                                <CambiarProductosDepositoModal :origen-id="slotProps.data.id"
                                                :origen-nombre="slotProps.data.nombre"
                                                :productos="formDelete.productos"
                                                @update:valor=eventoEnvio($event)
                                                :disabled-status="!formDelete.productos || !formDelete.productos.length">
                                                </CambiarProductosDepositoModal>

                                        </div>

                                                <div v-if=" isSend==false">
                                                    <Button :disabled="!formDelete.productos || !formDelete.productos.length"
                                                    v-if="slotProps.data.id == 2"
                                                    class="w-auto rounded bg-red-700 px-2  text-base font-normal text-white m-1 hover:bg-red-600"
                                                    v-tooltip.top="{ value: `Eliminar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
                                                    @click.prevent="btnEliminar()"><i class="fas fa-trash-alt w-6 h-4"></i></Button>
                                                </div>
                                    </div>
                                </template>
                                <Column selectionMode="multiple" headerStyle="width: 3rem"
                                :pt="{
                                          headerCell: { class: 'text-center bg-sky-300' },
                                          bodyCell: { class: 'text-center' },
                                    }"></Column
                                    >
                                <Column field="sku" filterField="sku" header="Sku" sortable :pt="{
                                    bodyCell: { class: 'text-center p-0 m-0 w-36' },
                                    headerCell: { class: 'bg-sky-300 p-0 m-0 w-36 ' },
                                    headerContent: {

                                        class: 'text-center w-36 stickyToTopTableHeaders'
                                    },
                                    bodyCellContent: {

                                        class: 'text-center w-36'
                                    },
                                }">
                                </Column>

                                <Column header="Imagen" select :pt="{
                                    bodyCell: {
                                        class: 'flex justify-center text-center w-12'
                                    },
                                    headerCell: {
                                        class: 'bg-sky-300 w-12'
                                    },
                                    headerContent: {

                                        class: 'text-center w-12'
                                    },
                                    bodyCellContent: {

                                        class: 'text-center w-12'
                                    },
                                }">
                                    <template #body="slotProps">
                                        <img class="rounded  bg-white shadow-2xl border-2 text-center w-10 h-10 object-contain"
                                            :src="slotProps.data.imagen" alt="image">
                                    </template>
                                </Column>
                                <Column field="nombre" filterField="nombre" header="Producto" sortable :pt="{
                                    bodyCell: { class: 'text-center p-0 m-0  w-96' },
                                    headerCell: { class: 'text-center bg-sky-300 p-0 m-0  w-96' },
                                    headerContent: {

                                        class: 'text-center  w-96'
                                    },
                                    bodyCellContent: {

                                        class: 'text-center  w-96'
                                    },

                                }">
                                </Column>

                                <Column field="bultos" header="Bultos" sortable :pt="{
                                    bodyCell: { class: 'text-center p-0 m-0 w-36' },
                                    headerCell: { class: 'bg-sky-300 p-0 m-0 w-36' },
                                    headerContent: {

                                        class: 'text-center w-36'
                                    },
                                    bodyCellContent: {

                                        class: 'text-center w-36'
                                    },

                                }">
                                </Column>
                                <Column field="pcs_bulto" header="Pcs por Bulto" sortable :pt="{
                                    bodyCell: { class: 'text-center p-0 m-0 w-36' },
                                    headerCell: { class: 'bg-sky-300 p-0 m-0 w-36' },
                                    headerContent: {

                                        class: 'text-center w-36'
                                    },
                                    bodyCellContent: {

                                        class: 'text-center w-36'
                                    },

                                }">
                                </Column>
                                <Column field="cantidad_total" header="Cantidad Total" sortable :pt="{
                                    bodyCell: { class: 'text-center p-0 m-0 w-36' },
                                    headerCell: { class: 'bg-sky-300 p-0 m-0 w-36' },
                                    headerContent: {

                                        class: 'text-center w-36'
                                    },
                                    bodyCellContent: {

                                        class: 'text-center w-36'
                                    },

                                }">
                                </Column>
                            </DataTable>
                        </div>
                    </template>


                </DataTable>

            </div>
            <!--Contenido-->
        </div>

    </AppLAyout>
</template>


<style type="text/css" scoped>
.stickyToTopTableHeaders {
    position: sticky;
    background-color: #fff;
    z-index: 1100;
}
</style>
