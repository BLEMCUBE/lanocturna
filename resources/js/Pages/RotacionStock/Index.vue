<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, watch } from 'vue'
import { Head, usePage, router } from '@inertiajs/vue3';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
import Pagination from '@/Components/Pagination.vue';
import Multiselect from '@vueform/multiselect';

const { roles } = usePage().props.auth
const titulo = "Rotación de Stock"
const ruta = 'rotacion-stock'
const mes = ref()
const props = defineProps({
	productos: {
		type: Object,
		default: () => ({}),
	},
	filtro: {
		type: Object,
		default: () => ({}),
	},
});
//let categorias = ref([props.filtro.categoria])
let categorias = ref([])
//let buscar = ref(props.filtro.buscar);
let buscar = ref();
//let date = ref([props.filtro.inicio, props.filtro.fin]);
let date = ref([]);
//let inicio = ref(props.filtro.inicio);
let inicio = ref();
//let fin = ref(props.filtro.fin);
let fin = ref();

//const date = ref();
date.value = [moment(subDays(new Date(), 30)).format('YYYY-MM-DD'), moment(new Date()).format('YYYY-MM-DD')];

const fechaVentaExport = ref([]);



watch(buscar, (value) => {
	router.get(
		route(ruta + '.index'),
		{
			buscar: buscar.value,
			categoria: categorias.value,
			inicio: date.value[0],
			fin: date.value[1],
		},
		{
			preserveState: true,
			replace: true,
			onSuccess: () => {
				mes.value = usePage().props.meses;
				categorias.value = usePage().props.filtro.categoria

			}
		}
	);
});


watch(categorias, (value) => {
	router.get(
		route(ruta + '.index'),
		{
			buscar: buscar.value,
			categoria: categorias.value,
			inicio: date.value[0],
			fin: date.value[1],
		},
		{
			preserveState: true,
			replace: true,
			onSuccess: () => {
				mes.value = usePage().props.meses;


			}
		}
	);
});



//descarga excel
const descargaExcelProductoVentas = () => {


	if (date.value[0] != null && date.value[1] != null) {
		window.open(route('rotacion-stock.exportproductoventas', [
			{
			'categoria': categorias.value,
			'inicio': date.value[0],
			'fin': date.value[1] }]), '_blank');
	} else {

		return;
	}
}


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
	router.get(
		route(ruta + '.index'),
		{
			buscar: buscar.value,
			categoria: categorias.value,
			inicio: inicio.value,
			fin: fin.value
		},
		{
			preserveState: true,
			replace: true,
			onSuccess: () => {
				mes.value = usePage().props.meses;
				categorias.value = usePage().props.filtro.categoria

			}
		}
	);

}
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
const lista_categorias = ref({
	value: '',
	closeOnSelect: true,
	placeholder: "Categorías",
	mode: 'tags',
	searchable: true,
	options: [],
});

onMounted(() => {
	lista_categorias.value.options = usePage().props.lista_categorias
	date.value = [moment(subDays(new Date(), 30)).format('YYYY-MM-DD'), moment(new Date()).format('YYYY-MM-DD')];
	mes.value = usePage().props.meses;
});

</script>
<template>

	<Head :title="titulo" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">
		<div
			class="card px-4 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
			<!--Contenido-->
			<div class=" px-3 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
			</div>

			<div class="bg-white mr-0 ml-0 shadow rounded-lg border border-gray-300">

				<div class="align-middle px-3 pt-3">
					<div class="w-full flex items-center">
						<div class="font-bold">
							MESES: {{ mes }}
						</div>

						<div v-if="roles.includes('Super Administrador') || roles.includes('Administrador')"
							v-tooltip.top="{ value: 'Descargar Excel', pt: { text: 'bg-gray-500 text-xs text-white rounded' } }"
							class=" w-10 h-10 ml-20 rounded flex justify-center items-center text-base font-semibold text-white mr-1">

							<Button @click="descargaExcelProductoVentas()" :pt="{
								root: { class: 'py-auto px-2.5 py-2 text-lg bg-green-600 border-none hover:bg-green-500' }
							}"><i class="fas fa-file-excel text-white text-lg"></i>
							</Button>
						</div>
					</div>
					<!--tabla-->
					<div class="align-middle py-2">

						<div class="grid grid-cols-12 gap-4 m-3">

							<div class="flex justify-content-end text-md col-span-12 lg:col-span-3 2xl:col-span-3">
								<InputText class="h-9 w-full" v-model="buscar" placeholder="Buscar" />
							</div>
							<div class="flex justify-content-end text-md col-span-12 lg:col-span-5 2xl:col-span-5">
								<Multiselect id="categorias" v-model="categorias" class="w-full"
									v-bind="lista_categorias">
								</Multiselect>
							</div>
							<div class="flex justify-content-end text-md col-span-12 lg:col-span-4 2xl:col-span-4">
								<date-picker @change="filtrado" type="date" range value-type="YYYY-MM-DD"
									format="DD/MM/YYYY"
									class="col-span-6 lg:col-span-2 font-sans  font-normal text-gray-700  bg-white  transition-colors duration-200 border-0 text-sm"
									v-model:value="date" :shortcuts="shortcuts" lang="es" editable="false"
									placeholder="Seleccione Fecha"></date-picker>
							</div>

						</div>
						<div style="overflow:auto; max-height: 600px;">

							<table class="w-full text-md bg-white shadow-md rounded mb-4">
								<thead style="position: sticky;" class="top-0 z-[1]">
									<tr class="bg-secondary-100">
										<th class="p-1.5">Origen</th>
										<th>Nombre</th>
										<th>Categoria</th>
										<th>Fecha Última Compra</th>
										<th>Fecha Última Venta</th>
										<th>Ventas Totales</th>
										<th>Stock</th>
										<th>Stock Futuro</th>
										<th>Rotacion Del Stock</th>
									</tr>
								</thead>

								<tbody>
									<tr v-for="post in productos.data" class="border text-center">

										<td>{{ post.origen }} </td>
										<td>{{ post.nombre }}</td>
										<!--<td> {{ post.categorias.map(entry => entry.name).join(', ') }}</td>-->
										<td> {{ post.categorias }}</td>
										<td>{{ post.ultima_compra }}</td>
										<td>{{ post.ultima_venta }}</td>
										<td>{{ post.ventas_totales }}</td>
										<td>{{ post.stock }}</td>
										<td>{{ post.stock_futuro }}</td>
										<td>{{ post.rotacion_stock }}</td>

									</tr>
								</tbody>
							</table>
						</div>

						<Pagination :elements="productos"></Pagination>

					</div>
					<!--tabla-->
				</div>
			</div>

			<!--Contenido-->
		</div>
	</AppLayout>
</template>

<style type="text/css" scoped></style>
