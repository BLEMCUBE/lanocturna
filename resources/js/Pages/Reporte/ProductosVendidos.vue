<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
import { FilterMatchMode } from 'primevue/api';
import Multiselect from '@vueform/multiselect';

const { roles } = usePage().props.auth
const titulo = "Productos Vendidos"
const ruta = 'reportes'
const rutaprod = 'productos'
const props = defineProps({
	filtro: {
		type: Object,
		default: () => ({}),
	},

});
const total_productos = ref([]);
const total_cantidad = ref();

let categorias = ref([])
let date = ref();
let inicio = ref();
let fin = ref();

date.value = [moment(subDays(new Date(), 30)).format('YYYY-MM-DD'), moment(new Date()).format('YYYY-MM-DD')];

watch(categorias, (value) => {
	router.get(
		route(ruta + '.productosvendidos'),
		{
			categoria: categorias.value,
			inicio: date.value[0],
			fin: date.value[1],
		},
		{
			preserveState: true,
			//replace: true,
			onSuccess: () => {
				total_cantidad.value = usePage().props.total_cantidad;
				total_productos.value = usePage().props.total_productos;

			}
		}
	);
});


//filtrado
const filtrado = (value) => {
	if (value[0] != null && value[1] != null) {
		//date.value = [moment(value[0]).format('YYYY-MM-DD'), moment(value[1]).format('YYYY-MM-DD')];
		inicio.value = date.value[0];
		fin.value = date.value[1];
	}
	router.get(
		route(ruta + '.productosvendidos'),
		{

			categoria: categorias.value,
			inicio: date.value[0],
			fin: date.value[1],
		},
		{
			preserveState: true,
			//replace: true,
			onSuccess: () => {
				total_cantidad.value = usePage().props.total_cantidad;
				categorias.value = usePage().props.filtro.categoria
				total_productos.value = usePage().props.total_productos;
			}
		}
	);

}

const lista_categorias = ref({
	value: '',
	closeOnSelect: true,
	placeholder: "Categorías",
	mode: 'tags',
	searchable: true,
	options: [],
});

//descarga excel
const descargaExcelProductoVentas = () => {


	if (date.value[0] != null && date.value[1] != null) {
		window.open(route('reportes.exportproductoventas', [{
			'categoria': categorias.value,
			'inicio': date.value[0],
			'fin': date.value[1]
		}]), '_blank');
	} else {

		return;
	}
}

onMounted(() => {
	lista_categorias.value.options = usePage().props.lista_categorias
    filtrado(date.value);
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

const filters = ref({
	global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});


</script>

<template>

	<Head title="Reporte Ventas" />
	<AppLayout :pagina="[{ 'label': 'Reportes', link: false }, { 'label': titulo, link: false }]">

		<div
			class="card px-4 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

			<!--Contenido-->
			<div class=" px-3 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">Listado Productos más vendidos</h5>
			</div>
			<div class="bg-white mr-0 ml-0 shadow rounded-l">
				<div class="align-middle px-3 pt-3">
					<div class="w-full flex items-center">
						<div class="font-bold">
							TOTAL CANTIDADES: {{ total_cantidad }}
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

					<div class="align-middle p-2">

						<DataTable size="small" v-model:filters="filters" :value="total_productos" :paginator="true"
							:rows="100" :pt="{
								bodyRow: { class: 'hover:cursor-pointer hover:bg-gray-100 hover:text-black' },
								root: { class: 'w-auto text-base' }
							}" @row-click="clickDetalle"
							paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown">
							<template #header size="small" class="bg-secondary-900">

								<div class="grid grid-cols-12 gap-2 m-1">

									<div
										class="flex justify-content-end text-md col-span-12 lg:col-span-3 2xl:col-span-3">
										<InputText class="h-9 w-full" v-model="filters['global'].value"
											placeholder="Buscar" />
									</div>
									<div
										class="flex justify-content-end text-md col-span-12 lg:col-span-5 2xl:col-span-7">
										<Multiselect id="categorias" v-model="categorias" class="w-full"
											v-bind="lista_categorias">
										</Multiselect>
									</div>
									<div
										class="flex justify-content-end text-md col-span-12 lg:col-span-4 2xl:col-span-2">
										<date-picker @change="filtrado" type="date" range value-type="YYYY-MM-DD"
											format="DD/MM/YYYY"
											class="col-span-6 lg:col-span-2 font-sans  font-normal text-gray-700  bg-white  transition-colors duration-200 border-0 text-sm"
											v-model:value="date" :shortcuts="shortcuts" :clearable = "false" lang="es" :editable="false"
											placeholder="Seleccione Fecha"></date-picker>
									</div>
								</div>
							</template>
							<template #empty> No existe Resultado </template>
							<template #loading> Cargando... </template>
							<Column field="sku" header="SKU"></Column>
							<Column field="imagen" header="Imagen" :pt="{
								bodyCell: { class: 'text-center' },
								headerTitle: { class: 'text-center w-12' },
							}">
								<template #loading>
								</template>
								<template #body="slotProps">
									<img class="rounded  bg-white shadow-2xl border-2 text-center w-10 h-10 object-contain"
										:src="slotProps.data.imagen" alt="image">
								</template>
							</Column>
							<Column field="nombre" header="Nombre" ></Column>
							<Column field="categorias" header="Categoria" ></Column>
							<Column field="stock" header="Stock" sortable></Column>
							<Column field="costo_aprox" header="Costo aprox" sortable></Column>
							<Column field="ventas_totales" header="Ventas Totales" sortable :pt="{
								bodyCell: { class: 'text-center' },
								headerTitle: { class: 'text-center w-14' },
							}"></Column>
							<Column field="porcentaje" header="Porcentaje" sortable :pt="{
								bodyCell: { class: 'text-center' },
								headerTitle: { class: 'text-center w-20' },
							}">
								<template #body="slotProps">
									{{ (slotProps.data.porcentaje) }} %
								</template>
							</Column>
						</DataTable>
					</div>
				</div>
				<!--Contenido-->
			</div>
		</div>

	</AppLayout>
</template>
