<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import InputLabel from '@/Components/InputLabel.vue';
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
import Pagination from '@/Components/Pagination.vue';
const { tienda } = usePage().props
const titulo =  `ML Ventas "${tienda}"`
const ruta = 'mercadolibre.ventas'

const { client_id } = usePage().props
const buscar = ref(props.filtro.buscar ?? "");
const inicio = ref(props.filtro.inicio ?? null);
const fin = ref(props.filtro.fin ?? null);
const estado = ref(props.filtro.estado ?? null);
let date = ref([props.filtro.inicio, props.filtro.fin]);

//*datepicker  */
const props = defineProps({
	datos: {
		type: Object,
		default: () => ({}),
	},
	filtro: {
		type: Object,
		default: () => ({}),
	},
});
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

const btnDetalle = (client_id, venta_id, tipo) => {
	router.get(route(ruta + '.detalle', [client_id, venta_id, tipo]));

};

const selectedEstado = ref({ name: 'Todos', code: null });
const estados = ref([
	{ name: 'Todos', code: null },
	{ name: 'Pagado', code: 'paid' },
	{ name: 'Cancelado', code: 'cancelled' },
]);

const setEstado = (e) => {
	if (selectedEstado.value.code == estado.value)
		return;
	estado.value = selectedEstado.value.code;
}



const colorEstado = (estado) => {
	switch (estado) {
		case 'paid':
			return 'text-green-600'

		case 'cancelled':
			return 'text-red-600'
		default:
			return 'text-black'
	}
}

const bgEstado = (estado) => {
	switch (estado) {
		case 'paid':
			return 'bg-green-600'

		case 'cancelled':
			return 'bg-red-600'
		default:
			return 'bg-white'
	}
}

const textEstado = (estado) => {
	switch (estado) {
		case 'paid':
			return 'Pagado'

		case 'cancelled':
			return 'Cancelado'
		default:
			return ''
	}
}

onMounted(() => {

});


const funcBuscar = () => {
	router.get(
		route(ruta + '.index', [client_id]),
		{
			buscar: buscar.value,
			inicio: inicio.value,
			estado: estado.value,
			fin: fin.value,
		},
		{
			preserveState: true,
			replace: true,
		}
	);
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
	setTimeout(function () {
		router.get(
			route(ruta + '.index', [client_id]),
			{
				buscar: buscar.value,
				inicio: inicio.value,
				estado: estado.value,
				fin: fin.value,
			},
			{
				preserveState: true,
				replace: true,
			}


		);
	}, 100);

}


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
							:debounce-events="['keyup']" placeholder="Numero de venta" :pt="{
								root: { class: 'col-span-6 lg:col-span-2 w-full' }
							}" />
					</div>
					<div class="col-span-2">
						<InputLabel for="estado" value="Estado" class="text-base font-medium leading-1 text-gray-900" />
						<Dropdown id="estado" v-debounce:500ms="funcBuscar" v-model="selectedEstado"
							@change="setEstado(); funcBuscar()" :options="estados" optionLabel="name" :pt="{
								root: { class: 'w-full' },
								trigger: { class: 'fas fa-caret-down text-gray-200 my-auto' },
								item: ({ props, state, context }) => ({
									class: context.selected ? 'text-white bg-primary-900' : context.focused ? 'bg-blue-100' : undefined
								})
							}" placeholder="Seleccione estado" />

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

				<!-- Card with list of reclamos -->
				<div class="bg-white  shadow-md border border-gray-300 overflow-hidden">
					<!-- Reclamo row 1 -->
					<div v-for="(item, index) in datos.data"
						class="flex flex-col md:flex-row items-stretch border-b border-dashed border-gray-300">

						<!-- left status strip -->
						<div class="w-2 hidden md:block" :class="bgEstado(item.estado)">
						</div>
						<!-- content -->
						<div class="flex-1 p-4">
							<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
								<!-- left column: id, title, meta -->
								<div class="flex-1">
									<div class="ml-6 my-1 text-lg text-gray-900">Venta <span
											class="font-semibold text-gray-800">#{{ item.venta_id }}</span> <span
											class="ml-3 px-2 py-0.5 text-[15px] rounded font-semibold"
											:class="colorEstado(item.estado)">{{ textEstado(item.estado) }}</span></div>
									<div class="flex flex-row items-center gap-2" v-for="it in item.productos">

										<div class="w-10 h-10 flex-shrink-0 ">
											<img :src="it.imagen" :alt="it.titulo"
												class="w-10 h-10 object-cover rounded border border-gray-800" />
										</div>
										<div class="px-2">
											<div class="mt-1 text-gray-800 font-semibold">{{ it.cantidad }} × {{
												it.titulo
											}}</div>

										</div>

									</div>
									<!--

								<div class="ml-6 my-1 text-sm  font-bold text-gray-800 flex items-center gap-3">
									<span class="inline-flex items-center gap-2"><i
										class="fa-solid fa-money-bill"></i> Total: $ {{ item.total }} </span>

									</div>
									-->
								</div>

								<!-- right column: status -->
								<div class="max-w-56 md:w-56 flex flex-col items-start md:items-start justify-between">
									<div class="font-semibold text-gray-700">Comprador</div>
									<div class="flex items-center gap-2"><i class="fa-regular fa-user"></i>
										{{ item.comprador?.first_name }} {{ item.comprador?.last_name }}</div>


									<div class="font-semibold text-gray-700">Fecha</div>
									<div class="flex items-center gap-2"><i class="fa-regular fa-calendar"></i>
										{{ item.created_at }}
									</div>
								</div>
								<!-- button -->
								<div class="w-50 h-full flex  px-5 items-center justify-center py-auto">
									<button
										class="rounded bg-blue-700  border border-white px-3 py-2 text-base font-normal text-white m-1 hover:bg-blue-600"
										v-tooltip.top="{ value: `Ver Detalle`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
										@click.prevent="btnDetalle(client_id, item.venta_id, item.tipo)">Ver
										Detalle</button>

								</div>
							</div>
						</div>
					</div>

				</div> <!-- end card -->
				<!-- Paginación -->

				<Pagination :elements="datos.links"></Pagination>


			</div>

		</div>

	</AppLayout>
</template>


<style type="text/css" scoped></style>
 
