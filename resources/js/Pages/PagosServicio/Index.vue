<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, watch } from 'vue'
import { Head, usePage, router, useForm } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
import Column from 'primevue/column';
import Button from 'primevue/button';
import CrearModal from '@/Pages/PagosServicio/Partials/CrearModal.vue';
import EditarModal from '@/Pages/PagosServicio/Partials/EditarModal.vue';
import Multiselect from '@vueform/multiselect';
import Swal from 'sweetalert2';
import { useToast } from "primevue/usetoast";
const props = defineProps({
	filtro: {
		type: Object,
		default: () => ({}),
	},
});
const { permissions } = usePage().props.auth
const tabla_items = ref([])
const toast = useToast();
const titulo = "Pagos"
const ruta = 'pago-servicio'
const formDelete = useForm({
	id: '',
});
let date = ref();
let inicio = ref();
let fin = ref();

let conceptos = ref([])
date.value = [moment(subDays(new Date(), 30)).format('YYYY-MM-DD'), moment(new Date()).format('YYYY-MM-DD')];



watch(conceptos, (value) => {
	router.get(
		route(ruta + '.index'),
		{
			concepto: conceptos.value,
			inicio: date.value[0],
			fin: date.value[1],
		},
		{
			preserveState: true,
			onSuccess: () => {
				tabla_items.value = usePage().props.items.data;

			}
		}
	);
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


const filters = ref({
	'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};
//modal eliminar
const eliminar = (id, name) => {

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

const lista_conceptos = ref({
	value: '',
	closeOnSelect: true,
	placeholder: "Conceptos",
	mode: 'tags',
	searchable: true,
	options: [],
});

//descarga excel
const btnDescargar = () => {

	if (date.value[0] != null && date.value[1] != null) {
		window.open(route(ruta + '.exportar', [{
			'concepto': conceptos.value,
			'inicio': date.value[0],
			'fin': date.value[1]
		}]), '_blank');
	} else {

		return;
	}
}

//filtrado
const filtrado = (value) => {
	if (value[0] != null && value[1] != null) {
		//date.value = [moment(value[0]).format('YYYY-MM-DD'), moment(value[1]).format('YYYY-MM-DD')];
		inicio.value = date.value[0];
		fin.value = date.value[1];
	}
	router.get(
		route(ruta + '.index'),
		{

			concepto: conceptos.value,
			inicio: date.value[0],
			fin: date.value[1],
		},
		{
			preserveState: true,
			onSuccess: () => {
				conceptos.value = usePage().props.filtro.concepto
				tabla_items.value = usePage().props.items.data;
			}
		}
	);

}

onMounted(() => {
	lista_conceptos.value.options = usePage().props.conceptos
	filtrado(date.value);
});

</script>
<template>

	<Head :title="titulo" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">
		<div
			class="card p-3 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
			<!--Contenido-->

			<div class=" px-2.5 pb-2 grid grid-cols-12 gap-4">
				<div class="py-1 px-3 col-span-12 sm:col-span-8 flex justify-between items-center">
					<h5 class="text-2xl font-medium">{{ titulo }}</h5>
				</div>

				<div class="ml-5 flex justify-end col-span-12 sm:col-span-4 ">
					<CrearModal></CrearModal>
					<Button @click.prevent="btnDescargar"
						v-tooltip.top="{ value: `Exportar Excel`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
						:pt="{
							root: { class: 'py-auto px-2.5 ml-5 py-2 text-lg bg-green-600 border-none hover:bg-green-500' }
						}"><i class="fas fa-file-excel text-white text-lg"></i>
					</Button>
				</div>
			</div>
			<div class="align-middle  py-1 px-3 ">
				<DataTable :filters="filters" :value="tabla_items" :globalFilterFields="['tconcepto', 'observacion']"
					scrollable scrollHeight="700px" paginator :rows="50" columnResizeMode="expand" :pt="{
						bodyRow: { class: 'hover:bg-gray-100 hover:text-black' },
						root: { class: 'text-base' }
					}" size="small">
					<template #header>
						<div class="grid grid-cols-12 gap-2 m-1">

							<div class="flex justify-content-end text-md col-span-12 lg:col-span-3 2xl:col-span-3">
								<InputText class="h-9 w-full" v-model="filters['global'].value" placeholder="Buscar" />
							</div>
							<div class="flex justify-content-end text-md col-span-12 lg:col-span-5 2xl:col-span-7">
								<Multiselect id="conceptos" v-model="conceptos" class="w-full" v-bind="lista_conceptos">
								</Multiselect>
							</div>
							<div class="flex justify-content-end text-md col-span-12 lg:col-span-4 2xl:col-span-2">
								<date-picker @change="filtrado" type="date" range value-type="YYYY-MM-DD"
									format="DD/MM/YYYY"
									class="col-span-6 lg:col-span-2 font-sans  font-normal text-gray-700  bg-white  transition-colors duration-200 border-0 text-sm"
									v-model:value="date" :shortcuts="shortcuts" :clearable="false" lang="es"
									:editable="false" placeholder="Seleccione Fecha"></date-picker>
							</div>
						</div>

					</template>
					<template #empty> No existe Resultado </template>
					<template #loading> Cargando... </template>
					<Column field="fecha" sortable header="Fecha" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
					</Column>
					<Column field="nro_factura" header="No. de Factura" sortable :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
					</Column>
					<Column field="moneda" header="Moneda" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
					</Column>

					<Column sortable header="Monto" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
						<template #body="slotProps">
							<span>
								{{ $numberFormat(slotProps.data.monto) }}
							</span>
						</template>
					</Column>

					<Column field="tconcepto" sortable header="Concepto" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
					</Column>

						<Column sortable header="Método Pago" :pt="{
							bodyCell: { class: 'text-center' },
							headerTitle: { class: 'text-center' },
						}">
						<template #body="slotProps">
							<span>
								{{ slotProps.data.tmetodo }}
							</span>
						</template>
					</Column>

					<Column field="observacion" header="Observación" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
					</Column>
					<Column header="Acciones" style="width:100px" :pt="{
						bodyCell: {
							class: 'text-center'
						}
					}">
						<template #body="slotProps">
							<div class="flex justify-end justify-items-center">
								<div v-if="permissions.includes('pagoservicio-editar')"
									class="h-8 inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-primary-100">
									<EditarModal :item-id="slotProps.data.id"></EditarModal>
								</div>
								<div v-if="permissions.includes('pagoservicio-eliminar')"
									class="h-8 inline-block rounded bg-red-700 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-red-600">
									<button @click.prevent="eliminar(slotProps.data.id, slotProps.data.nro_factura)"><i
											class="fas fa-trash-alt"></i></button>
								</div>
							</div>

						</template>
					</Column>

				</DataTable>
				<!--Contenido-->
			</div>
		</div>

	</AppLayout>
</template>


<style type="text/css"></style>
