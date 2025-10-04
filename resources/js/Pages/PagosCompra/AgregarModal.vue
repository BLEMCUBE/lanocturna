<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import 'vue-datepicker-next/locale/es.es.js';


const ruta = "pagos-compras"
const emit = defineEmits(['pass-info']);
const selectedMoneda = ref({ name: 'Pesos', code: 'Pesos' });
const monedas = ref([
	{ name: 'Pesos', code: 'Pesos' },
	{ name: 'Dólares', code: 'Dólares' },
]);
const setMoneda = (e) => {
	if (selectedMoneda.value.code == form.moneda)
		return;
	form.moneda = selectedMoneda.value.code;
}
const store = ref('CANCELAR')
//Variables
const isShowModal = ref(false);
const form = useForm({
	id: '',
	nro_factura: '',
	banco: '',
	moneda: '',
	fecha_pago: '',
	nro_transaccion: '',
	monto: '',
	saldo: 0,
	tpagado: ''


})
const props = defineProps({
	importacionId: {
		type: Number,
		default: null,
	},
	showAgregar: {
		type: Boolean,
		default: false,
	},
});

//Funciones
function passInfo() {
	emit('pass-info', { store: store.value, importacionId: props.importacionId })
}
const setBanco = (e) => {

	if (selectedBanco.value.code == form.banco)
		return;
	form.banco = selectedBanco.value.code;

}

onMounted(() => {

	dataEdit(props.importacionId)
});

const selectedBanco = ref();
const lista_banco = ref([
	{ name: 'BROU', code: 'BROU' },
	{ name: 'ITAU', code: 'ITAU' },
	{ name: 'OTROS', code: 'OTROS' }

]);

const dataEdit = (id) => {
	axios.get(route(ruta + '.showdetalle', id))
		.then(res => {
			var datos = res.data.importacion
			form.id = datos.id
			form.moneda = datos.moneda
			form.tpagado = (datos.compra_pagos.reduce((acc, cur) => acc + parseFloat(cur['monto']), 0)).toFixed(2)
			form.saldo =  datos.total-form.tpagado
			selectedMoneda.value = monedas.value.find(pr => pr.code === datos.moneda);
			form.nro_factura = datos.nro_factura
			isShowModal.value = true;

		})
};

const closeModal = () => {
	form.reset();
	form.clearErrors()
	store.value = "CANCELAR"
	passInfo();
};

//envio de formulario
const submit = () => {
	form.clearErrors()
	form.post(route(ruta + '.store'), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			isShowModal.value = false;
			store.value = "AGREGAR"
			setTimeout(() => {
				router.get(route(ruta + '.index'));
			}, 1000);
		},
		onFinish: () => {
		},
		onError: (er) => {
			console.log(er)
		}
	});

};

</script>

<template>
	<section>
		<Dialog v-model:visible="isShowModal" @hide="passInfo" modal
			:header="`Agregar pago a compra: ${form.nro_factura}`" :style="{ width: '20vw' }" position="top" :pt="{
				header: {
					class: 'mt-4 p-2'
				},
				content: {
					class: 'p-2'
				},
			}">
			<form>
				<div class="px-2 pt-0 pb-0 grid grid-cols-12 gap-2 mb-2">
					<div class="col-span-12 shadow-default my-auto ">
						<InputLabel for="fecha_pago" value="Fecha"
							class="block text-base font-medium leading-6 text-gray-900" />
						<date-picker :clearable="false" :editable="true" type="date" value-type="YYYY-MM-DD"
							format="DD/MM/YYYY"
							class="p-component col-span-6  text-gray-700  bg-white  transition-colors duration-200 border-0 px-0 py-0"
							v-model:value="form.fecha_pago" lang="es" placeholder="Seleccione Fecha"></date-picker>
						<InputError class="mt-1 text-xs" :message="form.errors.fecha_pago" />
					</div>
					<div class="col-span-12">
						<InputLabel for="moneda" value="Moneda" class="text-base font-medium leading-1 text-gray-900" />
						<Dropdown v-model="selectedMoneda" @change="setMoneda" disabled :options="monedas" optionLabel="name"
							:pt="{
								root: { class: 'w-full' },
								trigger: { class: 'fas fa-caret-down text-gray-200 my-auto' },
								item: ({ props, state, context }) => ({
									class: context.selected ? 'text-white bg-primary-900' : context.focused ? 'bg-blue-100' : undefined
								})
							}" placeholder="Seleccione Moneda" />
						<InputError class="mt-1 text-xs" :message="form.errors.moneda" />
					</div>
					<div class="col-span-12 shadow-default">
						<InputLabel for="banco" value="Banco"
							class="block text-base font-medium leading-6 text-gray-900" />
						<Dropdown v-model="selectedBanco" @change="setBanco" :options="lista_banco" optionLabel="name"
							:pt="{
								root: { class: 'w-full' },
								trigger: { class: 'fas fa-caret-down text-gray-200 my-auto' },
								item: ({ props, state, context }) => ({
									class: context.selected ? 'text-white bg-primary-900' : context.focused ? 'bg-blue-100' : undefined
								})
							}" placeholder="Seleccione banco" />
						<InputError class="mt-1 text-xs" :message="form.errors.banco" />
					</div>
					<div class="col-span-12 shadow-default lg:col-span-12">
						<InputLabel for="nro_transaccion" value="Nro. de Transacción"
							class="block text-base font-medium leading-6 text-gray-900" />

						<InputText type="text" id="nro_transaccion" v-model="form.nro_transaccion"
							placeholder="Ingrese Nro. de Transacción" :pt="{
								root: { class: 'h-9 w-full' }
							}" />
						<InputError class="mt-1 text-xs" :message="form.errors.nro_transaccion" />
					</div>
					<div class="col-span-12 shadow-default my-auto">
						<InputLabel for="monto" value="Monto"
							class="block text-base font-medium leading-6 text-gray-900" />
						<input type="number" v-model="form.monto" class="p-inputtext p-component text-gray-700 bg-white
                            border appearance-none rounded text-basw h-9 m-0 w-full text-end" />
						<InputError class="mt-1 text-xs" :message="form.errors.monto" />
					</div>
				</div>
				<div class="flex justify-end py-3">
					<Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small"
						@click="closeModal" type="button" />

					<Button label="Guardar" size="small" type="button" @click.prevent="submit"
						:class="{ 'opacity-50': form.processing }" :disabled="form.processing" />
				</div>
			</form>
		</Dialog>
	</section>
</template>
