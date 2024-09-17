<script setup>
//importacion
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { useToast } from "primevue/usetoast";
import Button from 'primevue/button'
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import 'vue-datepicker-next/locale/es.es.js';

//variables
const toast = useToast();
const isShowModal = ref(false);
const titulo = "Pago"
const ruta = "pago-servicio"
const lista_conceptos = ref();
const lista_metodos = ref();
const selectedMoneda = ref({ name: 'Pesos', code: 'Pesos' });
const monedas = ref([
	{ name: 'Pesos', code: 'Pesos' },
	{ name: 'Dólares', code: 'Dólares' },
]);
const selectedConcepto = ref();
const selectedMetodo = ref();

//funciones
const addCliente = () => {
	isShowModal.value = true;
};

const getConceptos = () => {
	axios.get(route(ruta + '.conceptos'))
		.then(res => {
			lista_conceptos.value = res.data.conceptos
		})
};
const getMetodos = () => {
	axios.get(route(ruta + '.metodos'))
		.then(res => {
			lista_metodos.value = res.data.metodos
		})
};

const closeModal = () => {
	form.reset();
	form.clearErrors()
	isShowModal.value = false;
};

const form = useForm({
	fecha_pago: '',
	moneda: '',
	nro_factura: '',
	monto: '',
	concepto_pago_id: '',
	metodo_pago_id: '',
	observacion: ''
})

const setMoneda = (e) => {
	if (selectedMoneda.value.code == form.moneda)
		return;
	form.moneda = selectedMoneda.value.code;
}

const setConcepto = (e) => {
	form.concepto_pago_id = selectedConcepto.value.code;
}
const setPago = (e) => {
	form.metodo_pago_id = selectedMetodo.value.code;
}

//envio de formulario
const submit = () => {

	form.clearErrors()
	form.post(route(ruta + '.store'), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			isShowModal.value = false
			show('success', 'Mensaje', 'Se ha creado')
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

onMounted(() => {
	form.moneda = "Pesos"
	getConceptos();
	getMetodos();
})

</script>

<template>
	<section class="space-y-4">

		<Button size="small" @click="addCliente" type="button" :label="'Agregar ' + titulo" severity="success"></Button>

		<Dialog v-model:visible="isShowModal" modal :header="'Crear ' + titulo" @hide="closeModal"
			:style="{ width: '30vw' }" :breakpoints="{ '1199px': '40vw', '575px': '50vw' }" position="top" :pt="{
				header: {
					class: 'mt-5 p-2 '
				},
				content: {
					class: 'p-2 lg:p-4'
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
						<InputLabel for="metodo" value="Método de pago"
							class="text-base font-medium leading-1 text-gray-900" />

						<Dropdown v-model="selectedMetodo" @change="setPago" :options="lista_metodos" optionLabel="name"
							:pt="{
								root: { class: 'w-full' },
								trigger: { class: 'fas fa-caret-down text-gray-200 my-auto' },
								item: ({ props, state, context }) => ({
									class: context.selected ? 'text-white bg-primary-900' : context.focused ? 'bg-blue-100' : undefined
								})
							}" placeholder="Seleccione Método" />
						<InputError class="mt-1 text-xs" :message="form.errors.metodo_pago_id" />
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
						<input type="number" v-model="form.monto" step="0.01" class="p-inputtext p-component text-gray-700 bg-white
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
