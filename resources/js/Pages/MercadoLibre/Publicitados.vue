<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, computed } from 'vue'
import InputLabel from '@/Components/InputLabel.vue';
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
import Pagination from '@/Components/Pagination.vue';
const titulo = `Productos publicados activos`
const ruta = 'reportes'

const { tiendas } = usePage().props
const inicio = ref(props.filtro.inicio ?? null);
const fin = ref(props.filtro.fin ?? null);
let date = ref([props.filtro.inicio, props.filtro.fin]);
const buscar = ref(props.filtro.buscar ?? "");
const datosFinal = computed(() => usePage().props.datos)

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
			buscar: buscar.value,
			inicio: inicio.value,
			fin: fin.value,
		},
		{
			preserveState: true,
			preserveScroll: true,
			replace: true,
		}
	);
}

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
				buscar: buscar.value,
				inicio: inicio.value,
				fin: fin.value,
			},
			{
				preserveState: true,
				preserveScroll: true,
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
						<InputText id="buscar" v-model="buscar" v-debounce:500ms="funcBuscar"
							:debounce-events="['keyup']" placeholder="sku" :pt="{
								root: { class: 'col-span-6 lg:col-span-2 w-full' }
							}" />
					</div>
					<div class="col-span-2">
						<InputLabel for="date1" value="Rango de Fecha"
							class="text-base font-medium leading-1 text-gray-900" />
						<DatePicker id="date1" @change="filtrado" type="date" range value-type="YYYY-MM-DD"
							format="DD/MM/YYYY"
							class="col-span-6 lg:col-span-2 font-sans  font-normal text-gray-700  bg-white  transition-colors duration-200 border-0 text-sm"
							v-model:value="date" :shortcuts="shortcuts" lang="es" editable="false"
							placeholder="Seleccione Fecha"></DatePicker>
					</div>

				</div>
				<div class="w-full  flex items-center justify-center min-h-full p-2">
					<div class="container max-w-4xl">
						<div class="bg-white rounded-xl shadow-md overflow-hidden">
							<!-- Table -->
							<div class="overflow-x-auto">
								<table class="min-w-full divide-y divide-gray-200">
									<thead class="bg-gray-50 uppercase">
										<tr>

											<th scope="col"
												class="px-6 py-3 text-center text-xs font-medium text-gray-800  tracking-wider">
												Sku
											</th>
											<th
												class="px-6 py-3 text-center text-xs font-medium text-gray-800  tracking-wider">
												{{ tiendas[0].nombre }}
											</th>
											<th
												class="px-6 py-3 text-center text-xs font-medium text-gray-800  tracking-wider">
												{{ tiendas[1].nombre }}
											</th>

										</tr>
									</thead>
									<tbody class="bg-white divide-y divide-gray-200">
										<!-- Row 1 -->
										<tr v-for="value in datosFinal.data"
											:class="value.ambas_tiendas > 0 ? 'bg-green-300 ' : ' '">

											<td class="p-3 whitespace-nowrap text-center">
												<div class="text-sm text-gray-900">{{ value.item_sku }}</div>
											</td>
											<td class="p-3 whitespace-nowrap text-center">
												<div class="text-sm text-gray-900">{{
													value['tienda_1'] }}</div>
											</td>
											<td class="p-3 whitespace-nowrap text-center">
												<div class="text-sm text-gray-900">{{
													value['tienda_2'] }}</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>

							<!-- Pagination -->
							<div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
								<div class="flex items-center justify-between flex-col sm:flex-row">
									<div class="mb-4 sm:mb-0">
										<p class="text-xs text-gray-700">
											total <span class="font-medium">{{ datosFinal.total }}</span>
										</p>
									</div>
									<div>
										<Pagination :elements="datosFinal"></Pagination>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</AppLayout>
</template>


<style type="text/css" scoped></style>
