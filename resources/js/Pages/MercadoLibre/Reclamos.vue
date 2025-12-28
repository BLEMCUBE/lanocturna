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
const titulo =  `ML Reclamos "${tienda}"`
const ruta = 'mercadolibre.reclamos'

const { client_id } = usePage().props
const buscar = ref(props.filtro.buscar ?? "");
const inicio = ref(props.filtro.inicio ?? null);
const fin = ref(props.filtro.fin ?? null);
const estado = ref(props.filtro.estado ?? null);
let date = ref([props.filtro.inicio, props.filtro.fin]);
const reclamos = ref([])
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

const btnDetalle = (client_id, reclamo_id) => {
	router.get(route(ruta + '.detalle', [client_id,reclamo_id]));

};

const selectedEstado = ref({ name: 'Todos', code: null });
const estados = ref([
	{ name: 'Todos', code: null },
	{ name: 'Reclamo abierto', code: 'opened' },
	{ name: 'Reclamo cerrado', code: 'closed' },
]);

const setEstado = (e) => {
	if (selectedEstado.value.code == estado.value)
		return;
	estado.value = selectedEstado.value.code;
}

const textEstado = (estado) => {
	switch (estado) {
		case 'opened':
			return 'Reclamo abierto'

		case 'closed':
			return 'Reclamo cerrado'
		default:
			return ''
	}
}

const textEspera = (estado) => {
	switch (estado) {
		case 'seller':
			return ['Esperando tu respuesta', 'text-red-600', 'bg-red-500']
		case 'buyer':
			return ['Esperando al comprador', 'text-green-600', 'bg-green-300']
		default:
			return ['Reclamo cerrado', 'text-gray-600', 'bg-gray-500']
	}
}

onMounted(() => {
 reclamos.value = [...props.datos.data].sort((a, b) => {
    const prioridad = (r) => {
      if (r.espera === 'seller') return 1
      if (r.espera === 'buyer') return 2
      return 3
    }

    return prioridad(a) - prioridad(b)
  })
})

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
							:debounce-events="['keyup']" placeholder="Numero de reclamo" :pt="{
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
					<!-- Reclamos -->

					<div v-for="(item, index) in reclamos"
						class="flex flex-col md:flex-row items-stretch border-b border-dashed border-gray-300">

						<!-- left status strip -->
						<div class="w-2 hidden md:block" :class="textEspera(item.espera)[2]">
						</div>
						<!-- content -->
						<div class="flex-1 p-4">
							<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
								<!-- left column: id, title, meta -->
								<div class="flex-1">
									<div class="ml-6 my-1 text-lg text-gray-900">Reclamo <span
											class="font-semibold text-gray-800">#{{ item.reclamo_id }}</span>
											 <span v-if="item.estado!=='closed'"
											class="ml-3 px-2 py-0.5 text-[15px] rounded font-semibold"
											:class="textEspera(item.espera)[1]">{{ textEspera(item.espera)[0] }}</span>
									</div>
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
									<div class="ml-6">
										<i class="fa-solid fa-circle-exclamation"></i>
										{{ item.motivo }}
									</div>
								</div>

								<!-- right column: status -->
								<div class="max-w-56 md:w-56 flex flex-col items-start md:items-start justify-between">
									<div v-if="item.venta_estado == 'cancelled'" class="font-semibold text-gray-700">Venta
										Cancelada</div>
									<div v-else class="font-semibold text-gray-700">
										{{ textEstado(item.estado) }}
									</div>
									<div class="flex items-center gap-2"><i class="fa-regular fa-user"></i>
										{{ item.comprador?.first_name }} {{ item.comprador?.last_name }}</div>
									<div class="flex items-center gap-2"><i class="fa-regular fa-calendar"></i>
										{{ item.fecha_creacion }}
									</div>
								</div>
								<!-- button -->
								<div class="w-50 h-full flex  px-5 items-center justify-center py-auto">

									<button
										class="rounded bg-blue-700  border border-white px-3 py-2 text-base font-normal text-white m-1 hover:bg-blue-600"
										v-tooltip.top="{ value: `Ver Reclamo`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
										@click.prevent="btnDetalle(client_id, item.reclamo_id)">Ver
										Reclamo</button>

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
