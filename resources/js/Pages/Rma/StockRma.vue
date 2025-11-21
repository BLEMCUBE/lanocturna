<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

import { FilterMatchMode } from 'primevue/api';
const expandedRows = ref([]);
import { useToast } from "primevue/usetoast";
const toast = useToast();
const titulo = "Stock Rma"
const ruta = 'rmas'
const lista_depositos = ref();
const formDelete = useForm({
    id: '',
});

onMounted(() => {
    lista_depositos.value = usePage().props.depositos;
    expandedRows.value = null;

});

//descarga excel
const descargaExcel = (tipo) => {

    if (tipo != null) {
        window.open(route('reportes.exportstockrma', { 'completo':tipo  }), '_blank');
    } else {

        return;
    }
}

const btnEliminar = (id) => {

    const alerta = Swal.mixin({ buttonsStyling: true });
    alerta.fire({
        width: 350,
        title: "Seguro de eliminar ",
        text: 'Se eliminará definitivamente?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        cancelButtonColor: 'red',
        confirmButtonColor: '#2563EB',

    }).then((result) => {
        if (result.isConfirmed) {
            formDelete.delete(route(ruta + '.destroy-stock', id),
                {
                    preserveScroll: true,
                    forceFormData: true,
                    onSuccess: () => {
                        show('success', 'Eliminado', 'Se ha eliminado')
                        setTimeout(() => {
                            router.get(route(ruta + '.rma-stock'));
                        }, 1000);

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

const collapseAll = () => {
    expandedRows.value = null;
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
            class="card px-4 mb-4 bg-white col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <div class=" px-5 pb-2 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>
            <div class="align-middle">

                <DataTable v-model:expandedRows="expandedRows" size="small" v-bind:rowClass="rowClass"
                    :value="lista_depositos" scrollable scrollHeight="800px" tableStyle="min-width: 50rem" :pt="{

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

                    <Column sortable field="nombre" header="Items" :pt="{
                        bodyCell: { class: 'bg-secondary-900/30 font-bold text-center' },
                        headerCell: { class: 'uppercase bg-secondary-100 text-md' },



                    }"></Column>

                    <Column header="Cantidad Productos" :pt="{
                        bodyCell: { class: 'bg-secondary-900/30 text-sm font-bold w-56  text-center' },
                        headerContent: { class: ' mx-2 ' },
                        headerCell: { class: 'uppercase bg-secondary-900 text-md' },

                    }">

                        <template #body="slotProps">
                            <div class="flex items-center justify-center">
                                <div class="mx-2">
                                    {{ (slotProps.data.productos.length) }}
                            </div>

                                <Button
                                :pt="{
                                    root: { class: 'px-2 py-2 text-xl bg-green-600 border-none hover:bg-green-500' }
                                }"
                                    @click.prevent="descargaExcel(slotProps.data.producto_completo)"><i class="fas fa-file-excel text-white text-xl"></i></Button>

                            </div>
                        </template>
                    </Column>

                    <template #expansion="slotProps">
                        <div class="px-1">
                            <DataTable :value="slotProps.data.productos" v-model:filters="filters" :paginator="true"
                                :rows="20" :rowsPerPageOptions="[20, 40, 100, 200]">

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

                                <Column header="Imagen" :pt="{
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

                                        class: 'text-center  w-96 font-sm'
                                    },

                                }">
                                </Column>

                                <Column field="defecto" header="Defecto" sortable :pt="{
                                    bodyCell: { class: 'text-center p-0 m-0 w-96' },
                                    headerCell: { class: 'bg-sky-300 p-0 m-0 w-96' },
                                    headerContent: {

                                        class: 'text-center w-96 font-sm'
                                    },
                                    bodyCellContent: {

                                        class: 'text-center w-36'
                                    },

                                }">
                                </Column>
                                <Column field="observaciones" header="Observaciones" sortable :pt="{
                                    bodyCell: { class: 'text-center p-0 m-0 w-96' },
                                    headerCell: { class: 'bg-sky-300 p-0 m-0 w-96' },
                                    headerContent: {

                                        class: 'text-center w-96'
                                    },
                                    bodyCellContent: {

                                        class: 'text-center w-96 font-sm'
                                    },

                                }">
                                </Column>
                                <Column field="cantidad_total" header="Cantidad Total" sortable :pt="{
                                    bodyCell: { class: 'text-center p-0 m-0 w-36 font-sm' },
                                    headerCell: { class: 'bg-sky-300 p-0 m-0 w-36 font-sm' },
                                    headerContent: {

                                        class: 'text-center w-36 font-sm'
                                    },
                                    bodyCellContent: {

                                        class: 'text-center w-36'
                                    },

                                }">
                                </Column>


                                <Column header="Acciones" :pt="{
                                    bodyCell: { class: 'text-center' },
                                    headerCell: { class: 'bg-sky-300 p-0 m-0 w-10' }

                                }">
                                    <template #body="slotProps">
                                        <div>
                                            <!--

                                                <span
                                                class="inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-primary-100">
                                                <CambiarDepositoModal :detalle-id="slotProps.data.id">
                                                </CambiarDepositoModal>
                                            </span>
                                        -->
                                            <Button
                                                class="w-8 h-8 rounded bg-red-700 border-0 px-2  text-base font-normal text-white m-1 hover:bg-red-600"
                                                v-tooltip.top="{ value: `Eliminar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
                                                @click.prevent="btnEliminar(slotProps.data.stock_id)"><i
                                                    class="fas fa-trash-alt"></i></Button>

                                        </div>

                                    </template>
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
