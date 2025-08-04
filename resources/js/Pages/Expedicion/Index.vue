<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
import Column from 'primevue/column';
import { useToast } from "primevue/usetoast";
const toast = useToast();
const tabla_ventas = ref()
const titulo = "Expedición"
const ruta = 'expediciones'

//actualizar pagina
setTimeout(() => {
    if (route().current('expediciones.index')) {
        window.open(self.location, '_self');
    }
}, 60000);

const btnVer = (id) => {
    router.get(route(ruta + '.show', id));

};



const clickDetalle = (e) => {

    btnVer(e.data.id)
}
onMounted(() => {

    tabla_ventas.value = usePage().props.ventas.data;

});

const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

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
        case 'VALIDADO':
            return 'text-green-600'
            break;
        default:
            return 'text-black'
            break;
    }
}
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div
            class="card p-3 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <Toast />
            <div class="p-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div class="align-middle">

                <DataTable :filters="filters" :value="tabla_ventas" :pt="{
                    bodyRow: { class: 'hover:cursor-pointer' }
                }" scrollable scrollHeight="700px" paginator :rows="50"
                    tableStyle="min-width: 50rem" @row-click="clickDetalle" size="small">
                    <template #header>
                        <div class="flex justify-start  text-md">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                            <h5 class="text-2xl font-medium text-black ml-8">
                                Productos listos para entrega
                            </h5>
                        </div>
                    </template>
                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>
                    <Column field="fecha" header="Fecha y Hora" sortable :pt="{
                        bodyCell: {
                            class: 'text-center p-2'
                        }
                    }"></Column>
                    <Column field="destino" header="Destino" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="nro_compra" header="N° Compra" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>
                    <Column field="cliente" header="Cliente" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>

                    <Column field="estado" header="Estado del pedido" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }">
                        <template #body="slotProps">
                            <span class="font-semibold text-md" :class="colorEstado(slotProps.data.estado)">
                                {{ slotProps.data.estado }}  {{ (slotProps.data.tipo=='RMA'?'- '+slotProps.data.tipo:'') }}
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
