<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from "primevue/usetoast";
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import 'vue-datepicker-next/locale/es.es.js';
import Button from 'primevue/button'

//Variables
const selectedConcepto = ref();
const toast = useToast();
const lista_conceptos = ref();
const titulo = "Pago"
const ruta = "pago-servicio"
const isShowModal = ref(false);
const selectedMoneda = ref({ name: 'Pesos', code: 'Pesos' });
const monedas = ref([
	{ name: 'Pesos', code: 'Pesos' },
	{ name: 'Dólares', code: 'Dólares' },
]);

const form = useForm({
	id: '',
	fecha_pago: '',
	moneda: '',
	nro_factura: '',
	monto: '',
	concepto_pago_id: '',
	observacion: ''
})
const setMoneda = (e) => {
	if (selectedMoneda.value.code == form.moneda)
		return;
	form.moneda = selectedMoneda.value.code;
}

const props = defineProps({
	itemId: {
		type: Number,
		default: null,
	},

});

const getConceptos = () => {
	axios.get(route(ruta + '.conceptos'))
		.then(res => {
			lista_conceptos.value = res.data.conceptos
		})
};
const setConcepto = (e) => {
	form.concepto_pago_id = selectedConcepto.value.code;
}
//Funciones

const addCliente = () => {
	dataEdit(props.itemId);

};

const dataEdit = (id) => {
	axios.get(route(ruta + '.show', id))
		.then(res => {
			isShowModal.value = true;
			var datos = res.data.item
			form.id = datos.id
			form.fecha_pago = datos.fecha_pago
			form.moneda = datos.moneda
			form.nro_factura = datos.nro_factura
			form.monto = datos.monto
			form.concepto_pago_id = datos.concepto_pago_id
			form.observacion = datos.observacion
			selectedMoneda.value= monedas.value.find(pr => pr.code === datos.moneda);
			selectedConcepto.value= lista_conceptos.value.find(pr => pr.code === datos.concepto_pago_id);
		})
};

onMounted(() => {
	getConceptos();
})


const mayuscula = (event) => {
	form.nombre = event.target.value.toUpperCase();

};
const closeModal = () => {
	form.reset();
	form.clearErrors()
	isShowModal.value = false;
};


//envio de formulario
const submit = () => {

	form.clearErrors()
	form.post(route(ruta + '.update', form.id), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			isShowModal.value = false
			show('success', 'Mensaje', 'Se ha editado')
			setTimeout(() => {
				router.get(route(ruta + '.index'));
			}, 1000);
		},
		onFinish: () => {
		},
		onError: () => {

		}
	});

};

const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};
</script>

<template>
	<section>
		<button type="button" @click="addCliente"><i class="fas fa-edit"></i></button>

		<Dialog v-model:visible="isShowModal" modal :header="'Editar ' + titulo" :style="{ width: '30vw' }"
			position="top" :pt="{
				header: {
					class: 'mt-6 p-2 lg:p-4 '
				},
				content: {
					class: 'p-4 lg:p-4'
				},
			}">
			<form @submit.prevent="submit">
				<div class="px-2 grid grid-cols-6 gap-4 md:gap-2 2xl:gap-6 mb-2">

					<div class="col-span-12">
						<InputLabel for="fecha_pago" value="Fecha"
							class="block text-base font-medium leading-6 text-gray-900" />
						<date-picker :clearable="false" :editable="true" type="date" value-type="YYYY-MM-DD"
							format="DD/MM/YYYY"
							class="p-component col-span-12  text-gray-700  bg-white  transition-colors duration-200 border-0 px-0 py-0"
							v-model:value="form.fecha_pago" lang="es" placeholder="Seleccione Fecha"></date-picker>
						<InputError class="mt-1 text-xs" :message="form.errors.fecha_pago" />
					</div>
					<div class="col-span-12">
						<InputLabel for="moneda" value="Moneda" class="text-base font-medium leading-1 text-gray-900" />
						<Dropdown v-model="selectedMoneda" @change="setMoneda" :options="monedas" optionLabel="name"
							:pt="{
								root: { class: 'w-full' },
								trigger: { class: 'fas fa-caret-down text-gray-200 my-auto' },
								item: ({ props, state, context }) => ({
									class: context.selected ? 'text-white bg-primary-900' : context.focused ? 'bg-blue-100' : undefined
								})
							}" placeholder="Seleccione Moneda" />
						<InputError class="mt-1 text-xs" :message="form.errors.moneda" />
					</div>
					<div class="col-span-12">
						<InputLabel for="concepto" value="Concepto"
							class="text-base font-medium leading-1 text-gray-900" />

						<Dropdown v-model="selectedConcepto" @change="setConcepto" :options="lista_conceptos"
							optionLabel="name" :pt="{
								root: { class: 'w-full' },
								trigger: { class: 'fas fa-caret-down text-gray-200 my-auto' },
								item: ({ props, state, context }) => ({
									class: context.selected ? 'text-white bg-primary-900' : context.focused ? 'bg-blue-100' : undefined
								})
							}" placeholder="Seleccione Concepto" />
						<InputError class="mt-1 text-xs" :message="form.errors.concepto_pago_id" />
					</div>
					<div class="col-span-12">
						<InputLabel for="nro_factura" value="Nro. Factura"
							class="block text-base font-medium leading-6 text-gray-900" />
						<input type="text" v-model="form.nro_factura"
							class="p-inputtext p-component h-9 w-full font-sans  font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1">

						<InputError class="mt-1 text-xs" :message="form.errors.nro_factura" />
					</div>

					<div class="col-span-12">
						<InputLabel for="monto" value="Monto"
							class="block text-base font-medium leading-6 text-gray-900" />
						<input type="number" v-model="form.monto" step="0.1" class="p-inputtext p-component text-gray-700 bg-white
                            border appearance-none rounded text-basw h-9 m-0 w-full text-end" />
						<InputError class="mt-1 text-xs" :message="form.errors.monto" />
					</div>

					<div class="col-span-12">
						<InputLabel for="observacion" value="Observación"
							class="block text-base font-medium leading-6 text-gray-900" />
						<input type="text" v-model="form.observacion"
							class="p-inputtext p-component h-9 w-full font-sans  font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1">

						<InputError class="mt-1 text-xs" :message="form.errors.observacion" />
					</div>
				</div>
				<div class="flex justify-end py-3">
					<Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small"
						@click="closeModal" type="button" />
					<Button label="Guardar" size="small" type="submit" :class="{ 'opacity-50': form.processing }"
						:disabled="form.processing" />
				</div>
			</form>
		</Dialog>
	</section>
</template>
