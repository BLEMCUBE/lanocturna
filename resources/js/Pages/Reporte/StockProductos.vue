<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, reactive } from 'vue';
const { permissions } = usePage().props.auth
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
import { FilterMatchMode } from 'primevue/api';

const { roles } = usePage().props.auth
const total_productos = ref([]);
const total_cantidad = ref();
const cargando = ref(false)
const date = ref();
const titulo = "Stock por Fecha"
const ruta = 'reportes'
const now = moment();
const rutaprod = 'productos'
//filtrado
const filtrado = (value) => {
    total_productos.value = [];
    cargando.value=true;

    if (value != null) {
        router.get(
            "/reportes-productos-stock/",
            {
                inicio: moment(value).format('YYYY-MM-DD'),
                fin: now.format('YYYY-MM-DD')
            },
            {
                preserveState: true,
                onSuccess: () => {
                    total_productos.value = usePage().props.total_productos;
                    cargando.value=false;
                }
            }
        );
        date.value = moment(value).format('YYYY-MM-DD');
    }
}

//descarga excel
const descargaExcel = () => {


if (date.value != null) {
    window.open(route('reportes.exportxls', [{ 'inicio': moment(date.value).format('YYYY-MM-DD'), 'fin': now.format('YYYY-MM-DD') }]), '_blank');
} else {

   return;
}
}
onMounted(() => {
    date.value = subDays(new Date(), 30);
    filtrado(date.value);

});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

//*datepicker  */
const shortcuts = [
    {
        text: 'Hoy',
        onClick() {
            const date = [new Date(), new Date()];
            return date;
        },
    },
    {
        text: 'Ayer',
        onClick() {
            const date = [subDays(new Date(), 1), subDays(new Date(), 1)];
            //date.setTime(date.getTime() - 3600 * 1000 * 24);

            return date;
        },
    },
    {
        text: 'Este mes',
        onClick() {
            const date = [startOfMonth(new Date()), endOfMonth(new Date())];

            return date;
        },
    },
    {
        text: 'Este año',
        onClick() {
            const date = [startOfYear(new Date()), endOfYear(new Date())];

            return date;
        },
    },
]

const clickDetalle = (e) => {
    btnVer(e.data.id)
}
const btnVer = (id) => {
    router.get(route(rutaprod + '.show', id));

};

</script>

<template>
    <Head title="Reporte Ventas" />
    <AppLayout :pagina="[{ 'label': 'Reportes', link: false }, { 'label': titulo, link: false }]">

        <div class="card px-4 mb-4 col-span-12 rounded-lg">
            <div class="col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">Stock por Fecha</h5>
            </div>
            <div class="grid grid-cols-12 gap-4 mt-4 mb-2">
                <!--Contenido-->
                <!--total ventas-->
                <div class="grid grid-cols-1 col-span-full gap-4 lg:grid-cols-12  mt-2 2xl:grid-cols-12">
                    <div class="col-span-1 md:col-span-3 lg:col-span-3">
                        <date-picker @change="filtrado" type="date" value-type="YYYY-MM-DD" format="DD/MM/YYYY"
                            v-model:value="date"  lang="es" :clearable="false"
                            placeholder="seleccione Fecha"></date-picker>
                    </div>
                </div>


                <div
                    class="card px-4 mb-4 bg-white col-span-10 rounded-lg shadow-lg 2xl:col-span-6 dark:border-gray-700  dark:bg-gray-800">
                <!--

                    <b>TOTAL CANTIDADES : {{ total_cantidad }}
                    </b>
                    -->
                        
                    <DataTable size="small" 
                
                    sortField="resultado_final" :sortOrder="1" :loading="cargando"
                    v-model:filters="filters" :value="total_productos" :paginator="true" :rows="20"
                        :rowsPerPageOptions="[20, 40, 100, 200]"
                        :pt="{
                    bodyRow: { class: 'hover:cursor-pointer hover:bg-gray-100 hover:text-black' },
                    root: { class: 'w-auto' }
                }"
                 @row-click="clickDetalle"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                        >
                        <template #header size="small" class="bg-secondary-900">
                            <div class="flex justify-content-end text-md">
                            
                                <InputText v-model="filters['global'].value" placeholder="Buscar" />
                            
                                <div v-if="roles.includes('Super Administrador') || roles.includes('Administrador')"
                                v-tooltip.top="{ value: 'Descargar Excel', pt: { text: 'bg-gray-500 text-xs text-white rounded' } }"
                                class=" w-10 h-8  ml-5 rounded flex justify-center items-center text-base font-semibold text-white mr-1">

                                <Button @click="descargaExcel" :pt="{
                                    root: { class: 'py-auto px-3 py-2.5 text-xl bg-green-600 border-none hover:bg-green-500' }
                                }"><i class="fas fa-file-excel text-white text-xl"></i>
                                    </Button>
                                </div>
                            </div>

                        </template>
                        <template #empty> No existe Resultado </template>
                        <template #loading> Cargando... </template>
                        <Column field="sku" header="SKU"></Column>
                        <Column field="nombre" header="Nombre" sortable></Column>
                        <Column field="resultado_final" class="text-center" header="Stock" sortable>
                            <template #loading>
                            </template>
                            <template #body="slotProps">
                                <div class="text-center">
                                    {{ slotProps.data.resultado_final }}
                                </div>
                            </template></Column>
                      
                    </DataTable>
                </div>

                <!--Contenido-->
            </div>
        </div>

    </AppLayout>
</template>
