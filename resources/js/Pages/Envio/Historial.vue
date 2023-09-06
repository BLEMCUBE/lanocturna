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
const tabla_ventas = ref()
const { permissions } = usePage().props.auth
const titulo = "Historial de Envios"
const ruta = 'envios'
const { tipo_cambio } = usePage().props

const formDelete = useForm({
    id: '',
});

const colorEstado = (estado) => {
    switch (estado) {
        case 'PENDIENTE DE FACTURACIÓN':
            return 'text-orange-600'
            break;
        case 'FACTURADO':
            return 'text-blue-600'
            break;
        case 'COMPLETADO':
            return 'text-green-600'
            break;
        case 'ANULADO':
            return 'text-red-600'
            break;
        default:
            return 'text-black'
            break;
    }
}

const btnVer = (id) => {
    router.get(route(ruta + '.detalle', id));

};
const btnImprimir = (id) => {
    router.get(route(ruta + '.generar_ticket', id));

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

const clickDetalle = (e) => {

    btnVer(e.data.id)
}
onMounted(() => {

    tabla_ventas.value = usePage().props.ventas.data;

});

const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};


const ok = (icono, mensaje) => {

    Swal.fire({
        width: 350,
        title: mensaje,
        icon: icono
    })
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


            </div>

            <div class="align-middle">

                <DataTable showGridlines :filters="filters" :value="tabla_ventas" :pt="{
                    bodyRow: { class: 'hover:cursor-pointer p-1' }
                }" scrollable scrollHeight="800px" :virtualScrollerOptions="{ class: 'min-h-screen', itemSize: 46 }"
                    @row-click="clickDetalle" size="small">
                    <template #header>
                        <div class="flex justify-content-end text-md">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                        </div>
                    </template>
                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>
                    <Column field="fecha" header="Facha y hora" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="codigo" header="No. Pedido" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>

                    <Column field="cliente" header="Cliente" sortable :pt="{
                        bodyCell: {
                            class: 'text-center border'
                        }
                    }"></Column>

                    <Column field="estado" header="Estado" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }">
                        <template #body="slotProps">
                            <span class="font-semibold text-md" :class="colorEstado(slotProps.data.estado)">
                                {{ slotProps.data.estado }}
                            </span>
                        </template>
                    </Column>

                    <Column field="total" sortable header="Total" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>

                    <Column field="observaciones" sortable header="Observaciones" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>

                    <Column header="Acciones" style="width:100px" :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }">
                        <template #body="slotProps">
                            <span
                                class="inline-block rounded bg-sky-300 px-2 py-1 text-base font-semibold text-white mr-1 mb-1 hover:bg-sky-400">
                                <a :href="route('envios.generar_ticket', slotProps.data.id)" target="_blank"><i
                                        class="fas fa-print"></i></a>
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
