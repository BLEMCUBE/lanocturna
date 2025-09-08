<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
import { FilterMatchMode } from 'primevue/api';

const { roles } = usePage().props.auth
const total_ventas = ref([]);
const date = ref();
const titulo = "Vendedores con más pedidos"
const ruta = 'reportes'
const rutaprod = 'productos'
//filtrado
const filtrado = (value) => {
	if (value[0] != null && value[1] != null) {
		router.get(
			route(ruta + '.vendedorespedidos'),
			{
				inicio: moment(value[0]).format('YYYY-MM-DD'),
				fin: moment(value[1]).format('YYYY-MM-DD')
			},
			{
				preserveState: true,
				onSuccess: () => {
					total_ventas.value = usePage().props.total_ventas;
				}
			}
		);
		date.value = [moment(value[0]).format('YYYY-MM-DD'), moment(value[1]).format('YYYY-MM-DD')];
	}
}

//descarga excel
const descargaExcelProductoVentas = () => {


	if (date.value[0] != null && date.value[1] != null) {
		window.open(route('reportes.exportvendedorespedidos', [{ 'inicio': date.value[0], 'fin': date.value[1] }]), '_blank');
	} else {

		return;
	}
}
onMounted(() => {
	date.value = [subDays(new Date(), 30), new Date()];
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
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
			</div>
			<div class="grid grid-cols-12 gap-4 mt-4 mb-2">
				<!--Contenido-->
				<!--total ventas-->
				<div class="grid grid-cols-1 col-span-full gap-4 lg:grid-cols-12  mt-2 2lg:grid-cols-12">
					<div class="col-span-1 md:col-span-3 lg:col-span-3">
						<date-picker @change="filtrado" type="date" range value-type="YYYY-MM-DD" format="DD/MM/YYYY"
							v-model:value="date" :shortcuts="shortcuts" lang="es" :clearable="false"
							placeholder="seleccione Fecha"></date-picker>
					</div>
				</div>


				<div
					class="card px-4 mb-4 bg-white col-span-12 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

					<DataTable size="small" v-model:filters="filters" :value="total_ventas" :paginator="true" :rows="20"
						:rowsPerPageOptions="[20, 40, 100, 200]" sortField="pedidos" :pt="{
							bodyRow: { class: 'text-end hover:bg-gray-100 hover:text-black' },
							root: { class: 'w-auto' }
						}" @row-click="clickDetalle"
						paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown">
						<template #header size="small" class="bg-secondary-900">
							<div class="flex justify-content-end text-md">
								<InputText v-model="filters['global'].value" placeholder="Buscar" />
								<div v-if="roles.includes('Super Administrador') || roles.includes('Administrador')"
									v-tooltip.top="{ value: 'Descargar Excel', pt: { text: 'bg-gray-500 text-xs text-white rounded' } }"
									class=" w-10 h-8  ml-5 rounded flex justify-center items-center text-base font-semibold text-white mr-1">
									<Button @click="descargaExcelProductoVentas()" :pt="{
										root: { class: 'py-auto px-3 py-2.5 text-xl bg-green-600 border-none hover:bg-green-500' }
									}"><i class="fas fa-file-excel text-white text-xl"></i>
									</Button>
								</div>
							</div>

						</template>
						<template #empty> No existe Resultado </template>
						<template #loading> Cargando... </template>
						<Column field="nombre" header="VENDEDOR" sortable></Column>
						<Column field="pedidos" :pt="{
							bodyCell: {
								class: 'text-end'
							}
						}" header="CANTIDAD PEDIDOS" sortable></Column>

						<Column field="total" :pt="{
							bodyCell: {
								class: 'text-end'
							}
						}" header="TOTAL" sortable>

						</Column>

					</DataTable>
				</div>

				<!--Contenido-->
			</div>
		</div>

	</AppLayout>
</template>
