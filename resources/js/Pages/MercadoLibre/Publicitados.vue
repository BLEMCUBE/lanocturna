<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, computed } from 'vue'
import InputLabel from '@/Components/InputLabel.vue';
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear, subMonths } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
import Pagination from '@/Components/Pagination.vue';
const titulo = `Productos publicados activos`
import { FilterMatchMode } from 'primevue/api';
const ruta = 'reportes'

const { tiendas } = usePage().props
const inicio = ref(props.filtro.inicio ?? null);
const fin = ref(props.filtro.fin ?? null);
let date = ref([props.filtro.inicio, props.filtro.fin]);
const buscar = ref(props.filtro.buscar ?? "");
const datosFinal = computed(() => usePage().props.datos)
const filters = ref({
	'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
//*datepicker  */
const props = defineProps({
	/*datos: {
		type: Object,
		default: () => ({}),
	},*/
	filtro: {
		type: Object,
		default: () => ({}),
	},
});

const funcBuscar = () => {
	router.get(
		route(ruta + '.mlpublicidad'),
		{
			//buscar: buscar.value,
			inicio: inicio.value,
			fin: fin.value,
		},
		{
			preserveState: true,
			replace: true,
		}
	);
}

const shortcuts = [

	{
		text: 'Últimos 7 días',
		onClick() {
			const date = [subDays(new Date(), 7), new Date()];

			return date;
		},
	},
	{
		text: 'Últimos 15 días',
		onClick() {
			const date = [subDays(new Date(), 15), new Date()];

			return date;
		},
	},
	{
		text: 'Últimos 30 días',
		onClick() {
			const date = [subDays(new Date(), 30), new Date()];
			return date;
		},
	},
	{
		text: 'Últimos 60 días',
		onClick() {
			const date = [subDays(new Date(), 60), new Date()];
			return date;
		},
	},
	{
		text: 'Últimos 90 días',
		onClick() {
			const date = [subDays(new Date(), 90), new Date()];
			return date;
		},
	},

]

//Fechas desde hace 90 días hasta hoy
const disabledBefore90DaysAndAfterToday = (date) => {
	const today = new Date();
	today.setHours(23, 59, 59, 999); // Fin del día hoy

	const ninetyDaysAgo = new Date();
	ninetyDaysAgo.setDate(today.getDate() - 90);
	ninetyDaysAgo.setHours(0, 0, 0, 0); // Inicio del día hace 90 días

	// Deshabilitar si es antes de 90 días atrás O después de hoy
	return date < ninetyDaysAgo || date > today;
};

//Si quieres incluir hoy en las fechas disponibles
const disabledOutsideLast90Days = (date) => {
	const today = new Date();
	today.setHours(0, 0, 0, 0); // Inicio del día hoy

	const ninetyDaysAgo = new Date(today);
	ninetyDaysAgo.setDate(today.getDate() - 90);

	// Deshabilitar si es antes de 90 días atrás O después de ayer
	return date < ninetyDaysAgo || date >= today;
};

const rowClass = (data) => {

	if (data.ambas_tiendas>=1) {

		return "bg-green-300"
	}else{

		return " "
	}
};

//filtrado
const filtrado = (value) => {
	if (value[0] != null && value[1] != null) {
		date.value = [moment(value[0]).format('YYYY-MM-DD'), moment(value[1]).format('YYYY-MM-DD')];
		inicio.value = date.value[0];
		fin.value = date.value[1];
	} else {
		date.value = [];
		inicio.value = null;
		fin.value = null;
	}
	setTimeout(function () {
		router.get(
			route(ruta + '.mlpublicidad'),
			{
				//buscar: buscar.value,
				inicio: inicio.value,
				fin: fin.value,
			},
			{
				preserveState: true,
				replace: true,
			}

		);
	}, 100);

}
onMounted(() => {

});


</script>

<template>

	<Head :title="titulo" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">
		<div class="px-4 py-3 mb-4 bg-white col-span-12 2xl:col-span-9">
			<div class="p-3 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
			</div>
			<div class="align-middle">
				<div class="grid grid-cols-6 gap-4 mx-1.5 mt-3 mb-6">
					<div class="col-span-2">
						<InputLabel for="buscar" value="Buscar" class="text-base font-medium leading-1 text-gray-900" />
						<InputText id="buscar" v-model="filters['global'].value" placeholder="sku" :pt="{
							root: { class: 'col-span-6 lg:col-span-2 w-full' }
						}" />
					</div>
					<div class="col-span-2">
						<InputLabel for="date1" value="Rango de Fecha"
							class="text-base font-medium leading-1 text-gray-900" />
						<DatePicker id="date1" @change="filtrado" type="date" range value-type="YYYY-MM-DD"
							format="DD/MM/YYYY"
							class="col-span-6 lg:col-span-2 font-sans  font-normal text-gray-700  bg-white  transition-colors duration-200 border-0 text-sm"
							v-model:value="date" :clearable="false" :disabled-date="disabledBefore90DaysAndAfterToday"
							:shortcuts="shortcuts" lang="es" editable="false" placeholder="Seleccione Fecha">
						</DatePicker>
					</div>

				</div>

				<div class="w-full  flex items-center min-h-full p-2">
					<div class="container max-w-4xl">
						<div class="bg-white rounded shadow-md overflow-hidden">

							<DataTable :filters="filters"
							sortField="sku" :sortOrder="-1" v-bind:rowClass="rowClass" :value="datosFinal.data" scrollable paginator :rows="50" :pt="{

								root: { class: 'text-base' }
							}" size="small">

								<template #empty> No existe Resultado </template>
								<template #loading> Cargando... </template>

								<Column field="sku" header="SKU" sortable :pt="{
									bodyCell: {
										class: 'py-2 text-center border-y-[1px] border-gray-300'
									}
								}">
								</Column>
								<Column field="tienda_1" :header="tiendas[0].nombre" sortable :pt="{
									bodyCell: {
										class: 'py-2 text-center  border-y-[1px] border-gray-300'
									}
								}">
								</Column>
								<Column field="tienda_2" :header="tiendas[1].nombre" sortable :pt="{
									bodyCell: {
										class: 'py-2 text-center  border-y-[1px]  border-gray-300'
									}
								}">
								</Column>
							</DataTable>
						</div>
					</div>
				</div>
			</div>

		</div>
	</AppLayout>
</template>


<style type="text/css" scoped></style>
