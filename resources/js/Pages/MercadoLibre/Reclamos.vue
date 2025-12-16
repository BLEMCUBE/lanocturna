<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
const titulo = "ML Reclamos"

const ruta = 'mercadolibre.ventas'


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

const btnVer = (id) => {
	router.get(route(ruta + '.showMensajes', id));

};


const clickDetalle = (e) => {

	btnVer(e.data.pack_id)
}

onMounted(() => {

});

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
const funcBuscar = () => {
	router.get(
		route(ruta + '.index'),
		{
			buscar: buscar.value,
			inicio: inicio.value,
			fin: fin.value,
			total: total.value,
			cliente: cliente.value,
			compra: compra.value
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
			route(ruta + '.index'),
			{
				buscar: buscar.value,
				inicio: inicio.value,
				fin: fin.value,
				total: total.value,
				cliente: cliente.value,
				compra: compra.value,
			},
			{
				preserveState: true,
				replace: true,
			}


		);
	}, 100);

}

const buscar = ref(props.filtro.buscar ?? "");
const estado = ref(props.filtro.estado ?? "todos");
//const instancia = ref(props.filtro.instancia ?? "todas");
const inicio = ref(props.filtro.inicio ?? null);
const fin = ref(props.filtro.fin ?? null);
let date = ref([props.filtro.inicio, props.filtro.fin]);

function aplicarFiltros() {
	router.get('/reclamos', {
		buscar: buscar.value,
		estado: estado.value,
		//instancia: instancia.value,
		inicio: inicio.value,
		fin: fin.value,
	}, {
		preserveState: true,
		replace: true
	});
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

				<div class="grid grid-cols-6 gap-4 mx-1.5 my-3">
					<InputText v-model="buscar" v-debounce:500ms="funcBuscar" :debounce-events="['keyup']"
						placeholder="Buscar" :pt="{
							root: { class: 'col-span-6 lg:col-span-2 m-1.5' }
						}" />

					<DatePicker @change.="filtrado" type="date" range value-type="YYYY-MM-DD" format="DD/MM/YYYY"
						class="p-inputtext p-component col-span-6 lg:col-span-2 font-sans  font-normal text-gray-700  bg-white  transition-colors duration-200 border-0 text-sm"
						v-model:value="date" :shortcuts="shortcuts" lang="es" editable="false"
						placeholder="Seleccione Fecha"></DatePicker>

				</div>

				<!-- Card with list of reclamos -->
				<div class="bg-white  shadow-md border border-gray-300 overflow-hidden">

					<!-- Reclamo row 1 -->
					<div v-for="item,index in datos" class="flex flex-col md:flex-row items-stretch border-b border-dashed border-gray-300">
						<!-- left status strip -->
						<div class="w-2 hidden md:block" style="background:linear-gradient(180deg,#ff8aa0,#ffb6c1)">
						</div>
						<!-- content -->
						<div class="flex-1 p-4">
							<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
								<!-- left column: id, title, meta -->
								<div class="flex-1">
									<div class="flex items-center gap-2">
										<div class="w-15 h-15 flex-shrink-0">
											<img src="https://placehold.co/48x48" alt="thumb"
												class="w-15 h-15 object-cover rounded" />
										</div>
										<div>
											<div class="text-xs text-gray-400">Reclamo <span
													class="font-mono text-gray-500">#5437214434</span> <span
													class="ml-3 px-2 py-0.5 text-[13px] rounded text-[#ff5c7a] font-semibold">Esperando
													tu respuesta</span></div>
											<div class="mt-1 text-gray-800 font-semibold">1 × 2 Focos Luz Led Solar,
												Fotocélula, Sensor, Ext, Int, Control</div>
											<div class="mt-2 text-sm text-gray-500 flex items-center gap-3">
												<span class="inline-flex items-center gap-2"><i
														class="fa-solid fa-circle-info text-gray-400"></i> El paquete
													llegó bien</span>
												<span class="inline-flex items-center gap-2"><i
														class="fa-regular fa-file text-gray-400"></i> FAC // armado
													S</span>
											</div>
										</div>
									</div>
								</div>

								<!-- right column: status -->
								<div class="max-w-56 md:w-56 flex flex-col items-start md:items-start justify-between">
									<div class="font-semibold text-gray-700">Reclamo abierto</div>
									<div class="flex items-center gap-2"><i class="fa-regular fa-user"></i> Juan
										Lozano</div>

									<div class="flex items-center gap-2"><i class="fa-regular fa-clock"></i>
										hace 2 horas</div>
									<div class="flex items-center gap-2"><i class="fa-regular fa-calendar"></i>
										2 días
									</div>
								</div>
								<!-- button -->
								<div class="w-50 h-full flex  px-5 items-center justify-center py-auto">
									<button
										class="rounded bg-blue-700  border border-white px-3 py-2 text-base font-normal text-white m-1 hover:bg-blue-600"
										v-tooltip.top="{ value: `Ver Detalle`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
										@click.prevent="duplicar(post.id)">Ver Detalle</button>

								</div>
							</div>
						</div>
					</div>

				</div> <!-- end card -->

				<!-- Paginación -->
				<div class="mt-4 flex justify-center gap-2">
					<button v-for="link in datos.links" :key="link.label" v-html="link.label" :disabled="!link.url"
						@click="router.visit(link.url)" class="px-3 py-1 rounded"
						:class="link.active ? 'bg-blue-600 text-white' : 'bg-gray-200'" />
				</div>

			</div>

		</div>

	</AppLayout>
</template>


<style type="text/css" scoped></style>
