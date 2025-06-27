<script setup>

import { useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import 'vue-datepicker-next/index.css';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';

const { permissions } = usePage().props.auth
const formDelete = useForm({
	id: '',
});
const emit = defineEmits(['pass-info']);
const ruta = "pagos-importaciones"
//Variables
const isShowModal = ref(false);
const store = ref('CANCELAR')
const form = useForm({
	id: '',
	nro_carpeta: '',
	costo_cif: '',
	pagos: [],
	pagado: '',
	saldo: ''
})

const props = defineProps({
	importacionId: {
		type: Number,
		default: null,
	},
	showDetalle: {
		type: Boolean,
		default: false,
	},

});

const btnEliminar = (id) => {
	formDelete.delete(route(ruta + '.destroy', id),
		{
			preserveScroll: true,
			forceFormData: true,
			onSuccess: () => {
				dataEdit(props.importacionId)
				store.value = "ELIMINADO"
				passInfo();
			}
		});
}

const agregarPago = () => {
	store.value = "AGREGAR";
	passInfo();
}
//Funciones
onMounted(() => {
	dataEdit(props.importacionId)
});

function passInfo() {
	emit('pass-info', { store: store.value, importacionId: props.importacionId })
}

const dataEdit = (id) => {
	axios.get(route(ruta + '.showdetalle', id))
		.then(res => {
			var datos = res.data.importacion
			form.id = datos.id
			form.nro_carpeta = datos.nro_carpeta
			form.costo_cif = datos.costo_cif
			form.pagos = datos.importaciones_pagos
			form.pagado = (datos.importaciones_pagos.reduce((acc, cur) => acc + parseFloat(cur['monto']), 0)).toFixed(2)
			form.saldo = datos.costo_cif - form.pagado;
			isShowModal.value = true;
		})
};


const closeModal = () => {
	form.reset();
	form.clearErrors()
	store.value = "CANCELAR"
	passInfo();
};

</script>

<template>
	<section>

		<Dialog v-model:visible="isShowModal" @hide="passInfo" modal :header="`Pagos importación: ${form.nro_carpeta}`"
			:style="{ width: '40vw' }" position="top" :pt="{
				header: {
					class: 'mt-4 p-2'
				},
				content: {
					class: 'p-2'
				},
			}">
			<form>
				<div class="px-2 pt-0 pb-0 grid grid-cols-12 gap-2 mb-2">

					<div class="col-span-12">
						<p><b>Costo CIF: </b> {{ $numberFormat(form.costo_cif) }}</p>
					</div>
					<div class="col-span-12">
						<p><b>Pagos Ingresados: </b></p>
					</div>
					<div class="col-span-12">

						<table class="w-full border">
							<thead>
								<tr class="w-full border text-[14px]">
									<th class="w-26 text-center border">
										Fecha
									</th>
									<th class="text-center border">
										Banco
									</th>
									<th class="text-center border">
										Nro. de Transacción
									</th>
									<th class="text-center border">
										Monto
									</th>
									<th class="text-center border">
										Acciones
									</th>
								</tr>
							</thead>
							<tbody>
								<tr class="w-full text-center border text-xs" v-for="item in form.pagos">
									<td class="text-center border text-[14px]">
										{{ moment(item.fecha_pago).format('DD/MM/YYYY') }}
									</td>
									<td class="text-center border text-[14px]">
										{{ item.banco }}
									</td>
									<td class="text-center border text-[14px]">
										{{ item.nro_transaccion }}
									</td>
									<td class="text-center border text-[14px]">
										{{ $numberFormat(item.monto) }}
									</td>
									<td class="text-center border text-[14px]">
										<Button v-if="permissions.includes('eliminar-pagos')"
											class="w-8 h-8 rounded bg-red-700 border-0 flex justify-center text-center text-base font-normal text-white hover:bg-red-600"
											v-tooltip.top="{ value: `Eliminar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
											@click.prevent="btnEliminar(item.id)"><i
												class="fas fa-trash-alt text-[14px] text-center"></i></Button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-span-12 pt-2">
						<p><b>Saldo: </b> {{ $numberFormat(form.saldo) }}</p>
					</div>
				</div>
				<div class="flex justify-end py-3">
					<Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small"
						@click="closeModal" type="button" />
					<Button label="Agregar Pago" v-if="form.saldo > 0" size="small" type="button"
						@click.prevent="agregarPago" :class="{ 'opacity-50': form.processing }"
						:disabled="form.processing" />
				</div>
			</form>
		</Dialog>
	</section>
</template>
