<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Swal from 'sweetalert2';
import { FilterMatchMode } from 'primevue/api';
const toast = useToast();
const titulo = "Editar Compra"
const ruta = 'compras'

const { productos } = usePage().props
const { tipo_cambio } = usePage().props
const { permissions } = usePage().props.auth;
const filters = ref({
	'global': { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const lista_moneda = ref({
	value: '',
	closeOnSelect: true,
	placeholder: "Seleccione",
	searchable: false,
	options: [
		{ "value": "Pesos", "label": "Pesos" },
		{ "value": "Dólares", "label": "Dólares" },
	],
});

const form = useForm({
	id: '',
	nro_factura: '',
	proveedor: '',
	observaciones: '',
	productos: [],
	total: 0.0,
	total_sin_iva: 0.0,
	moneda: '',
	tipo_cambio: '',

})



onMounted(() => {

	//lista_destino.value.options = lista_destinos
	var dato = usePage().props.compra
	form.id = dato.id
	form.nro_factura = dato.nro_factura
	form.proveedor = dato.proveedor
	form.facturador_id = dato.facturador_id
	form.observaciones = dato.observaciones
	form.moneda = dato.moneda
	form.total = dato.total
	form.total_sin_iva = dato.total_sin_iva
	lista_moneda.value.value = dato.moneda
	selectedMoneda.value = monedas.value.find(pr => pr.code === dato.moneda);
	form.tipo_cambio = dato.tipo_cambio
	dato.detalles_compras.forEach(el => {
		var produ2 = productos.data.find(pr => pr.id === el.producto_id);
		if (produ2 != undefined) {
			var monto=el.costo_reales!==null?el.costo_reales.monto:0;
			form.productos.push(
				{
					producto_id: el.producto_id,
					nombre: produ2.nombre,
					origen: produ2.origen,
					cantidad: el.cantidad,
					total: el.total,
					precio: el.precio,
					precio_sin_iva: el.precio_sin_iva,
					total_sin_iva: el.total_sin_iva,
					stock: produ2.stock,
					costo_real: monto,
					costo_origen:'COMPRA',

				}
			)
		}

	});

})


const addToCart = (id) => {
	form.clearErrors();
	var produ = productos.data.find(pr => pr.id === id);
	if (produ.stock >= 0) {
		form.productos.push(
			{
				producto_id: produ.id,
				nombre: produ.nombre,
				origen: produ.origen,
				cantidad: 1,
				stock: produ.stock,
				costo_real: 0,
				costo_origen:'COMPRA',
			}
		)


	} else {
		alerta('No hay stock disponible', 'error')
	}

};



//envio de formulario
const submit = () => {

	form.clearErrors()
	form.post(route(ruta + '.update', form.id), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			show('success', 'Mensaje', 'Compra Actualizada')
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
//modal advertencia
const alerta = (mensaje, icono) => {
	Swal.fire({
		width: 350,
		title: mensaje,
		icon: icono
	})
}
const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};
const selectedMoneda = ref();
const monedas = ref([
	{ name: 'Pesos', code: 'Pesos' },
	{ name: 'Dólares', code: 'Dólares' },
]);
const cancelCrear = () => {
	router.get(route(ruta + '.index'))
};
const removerProducto = (index) => {
	form.productos.splice(index, 1);
	sumaTotal()
	calculoSinIva()
}

const roundNumber = (value, step = 1.0, type = 'round') => {
	step || (step = 1.0);
	const inv = 1.0 / step;
	const mathFunc = 'ceil' === type ? Math.ceil : ('floor' === type ? Math.floor : Math.round);

	return mathFunc(value * inv) / inv;
}


const setMoneda = (e) => {

	if (selectedMoneda.value.code == form.moneda)
		return;
	if (selectedMoneda.value.code == 'Pesos') {
		form.productos.forEach((item, index) => {
			item['precio'] = roundNumber(parseFloat(item['precio'] * form.tipo_cambio).toFixed(2), 0.5, 'round')
			item['total'] = (item['cantidad'] * item['precio']).toFixed(2)
			item['precio_sin_iva'] = (parseFloat(item['precio']) / 1.22).toFixed(2)
			item['total_sin_iva'] = (item['cantidad'] * item['precio_sin_iva']).toFixed(2)
		})
		form.moneda = selectedMoneda.value.code;
	} else {
		form.productos.forEach((item, index) => {
			item['precio'] = parseFloat(item['precio'] / form.tipo_cambio).toFixed(2)
			item['total'] = (item['cantidad'] * item['precio']).toFixed(2)
			item['precio_sin_iva'] = (parseFloat(item['precio']) / 1.22).toFixed(2)
			item['total_sin_iva'] = (item['cantidad'] * item['precio_sin_iva']).toFixed(2)
		})
		form.moneda = selectedMoneda.value.code;
	}
	sumaTotal()
	calculoSinIva()
}

const sumaTotal = () => {
	form.total = (form.productos.reduce((acc, cur) => acc + parseFloat(cur['total']), 0)).toFixed(2)
	form.total_sin_iva = (form.productos.reduce((acc, cur) => acc + parseFloat(cur['total_sin_iva']), 0)).toFixed(2)
	calculoSinIva()

}

const sumaTotalProducto = ($event, id) => {
	var precio_temp = (form.productos[id].precio === null) ? 1 : form.productos[id].precio

	form.productos[id].total = (parseFloat(form.productos[id].cantidad) * parseFloat(precio_temp).toFixed(2))
	form.productos[id].total_sin_iva = (parseFloat(form.productos[id].cantidad) * parseFloat(precio_temp / 1.22).toFixed(2))
	form.productos[id].precio_sin_iva = (form.productos[id].precio / 1.22).toFixed(2)
	sumaTotal()
	calculoSinIva()

}

const calculoSinIva = () => {

	form.total_sin_iva = (form.total / 1.22).toFixed(2)
}

</script>
<template>

	<Head :title="titulo" />
	<AppLayout
		:pagina="[{ 'label': 'Compras', link: false, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
		<!--Contenido-->
		<div
			class="grid grid-cols-12 p-0 m-0 gap-2 mb-4 bg-white col-span-12 py-2 rounded-lg shadow-lg lg:col-span-12 dark:border-gray-700  dark:bg-gray-800">

			<Toast />
			<div class="mt-0 mb-4 col-span-12 lg:col-span-8">

				<div
					class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
					<h5 class="text-2xl font-medium">{{ titulo }}</h5>
				</div>
				<form>

					<div class="grid grid-cols-12 gap-1 py-0">
						<!--Tabla-->
						<table class="table-auto mx-2 border border-gray-300 col-span-12">
							<thead>
								<tr class="p-2 bg-secondary-900 border">
									<th class="border border-gray-300 p-2 w-24">Origen</th>
									<th class="border border-gray-300 ">Producto</th>
									<th class="border border-gray-300 w-24">Cantidad</th>
									<th class="border border-gray-300 w-24">Precio</th>
										<th v-if="permissions.includes('costoreal-productos')"
										class="border border-gray-300 w-24">Costo real</th>
									<th class="border border-gray-300 w-24">Total</th>
									<th class="border border-gray-300 w-8"></th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="producto, index in form.productos" :key="index"
									class="font-sans  font-normal text-gray-800 border border-gray-300">
									<td class="border border-gray-300 p-2">{{ producto.origen }}</td>
									<td class="border border-gray-300 p-2">{{ producto.nombre }}</td>
									<td class="border border-gray-300"><input type="number" v-model="producto.cantidad"
											min="1" step="1"
											class="p-inputtext p-component font-sans  font-normal text-gray-700 bg-white  border-0 appearance-none rounded-none  p-inputnumber-input h-9 px-0 py-0 m-0 w-full text-end text-sm"
											@input.prevent="sumaTotalProducto($event, index)" />

									</td>
									<td class="border border-gray-300"><input type="number" required
											v-model="producto.precio" min="0" step="1"
											@input="sumaTotalProducto($event, index)"
											class="p-inputtext pr-2 p-component font-sans  font-normal text-gray-700 bg-white  border-0 appearance-none rounded-none p-inputnumber-input h-9 px-0 py-0 m-0 w-full text-end text-sm" />

									</td>
									<td v-if="permissions.includes('costoreal-productos')" class="border border-gray-300"><input type="number" required
											v-model="producto.costo_real" min="0" step="1"
											class="p-inputtext pr-2 p-component font-sans  font-normal text-gray-700 bg-white  border-0 appearance-none rounded-none text-sm px-2 py-0 p-inputnumber-input h-9 m-0 w-full text-end" />

									</td>
									<td class="border border-gray-300 p-2">{{ producto.total }} </td>
									<td class="border-none  border-gray-300 p-1 ">
										<div
											class="rounded-md p-1 flex justify-center items-center bg-red-600 py-auto  text-base font-semibold text-white hover:bg-red-700">
											<button type="button" @click.prevent="removerProducto(index)" class="w-6"
												v-tooltip.top="{ value: `Eliminar`, pt: { text: 'bg-gray-500 p-1 m-0 text-xs text-white rounded' } }"><i
													class="fas fa-trash"></i></button>
										</div>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="4" class="text-end"><b>Total: </b></td>
									<td class="text-end"><b> {{ form.moneda == 'Pesos' ? '$ ' : 'USD ' }} {{ form.total
											}}
										</b></td>
								</tr>

							</tfoot>
						</table>
						<div class="col-span-12  p-2 xl:col-span-12">
							<InputError class="mt-1 text-lg w-full " :message="form.errors.productos" />
							<InputError v-for="error in form.errors.campos_productos" class="mt-1 mb-0 text-lg"
								:message="error" />
						</div>
						<!--Tabla-->

						<!--Datos Compras-->
						<div
							class="px-0 py-1 m-2 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
							<h5 class="text-lg font-medium">Datos Compra</h5>
						</div>
						<div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
							<InputLabel for="tipo_cambio" value="Tipo de cambio"
								class="text-base font-medium leading-1 text-gray-900" />
							<InputText type="text" id="tipo_cambio" v-model="form.tipo_cambio" readonly :pt="{
								root: { class: 'h-9 w-full' }
							}" />
							<InputError class="mt-1 text-xs" :message="form.errors.tipo_cambio" />
						</div>
						<div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
							<InputLabel for="moneda" value="Moneda"
								class="text-base font-medium leading-1 text-gray-900" />
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

						<div class="col-span-12 mx-2 py-0 shadow-default lg:col-span-6">
							<InputLabel for="nro_factura" value="Nro Factura"
								class="text-base font-medium leading-6 text-gray-900" />
							<InputText type="text" id="nro_factura" v-model="form.nro_factura"
								placeholder="ingrese nro factura" :pt="{
									root: { class: 'h-9 w-full' }
								}" />
							<InputError class="mt-1 text-xs" :message="form.errors.nro_factura" />

						</div>


						<div class="col-span-12 mx-2 py-0 shadow-default lg:col-span-6">
							<InputLabel for="proveedor" value="Proveedor"
								class="text-base font-medium leading-6 text-gray-900" />
							<InputText type="text" id="proveedor" v-model="form.proveedor"
								placeholder="ingrese proveedor" :pt="{
									root: { class: 'h-9 w-full' }
								}" />
							<InputError class="mt-1 text-xs" :message="form.errors.proveedor" />

						</div>
						<div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-12">
							<InputLabel for="rut" value="Observaciones:"
								class="text-base font-medium leading-6 text-gray-900" />

							<Textarea v-model="form.observaciones" :pt="{
								root: {
									rows: '2',
									class: 'w-full'
								}
							}" />

						</div>

						<!--Datos Compras-->
					</div>
					<div class="flex justify-end py-3">
						<Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small"
							@click="cancelCrear" type="button" />

						<Button label="Guardar" size="small" type="button" :class="{ 'opacity-50': form.processing }"
							:disabled="form.processing" @click.prevent="submit" />
					</div>

				</form>


			</div>


			<!--Productos-->
			<div class="p-0 mb-0 col-span-12  lg:col-span-4 px-2">
				<DataTable :filters="filters" scrollable scrollHeight="550px" :globalFilterFields="['origen', 'nombre']"
					:value="productos.data"
					:virtualScrollerOptions="{ itemSize: 46, lazy: true, numToleratedItems: 20 }">
					<template #header>
						<div class="flex justify-content-end text-sm">
							<InputText class="w-full mx-1" v-model="filters['global'].value" placeholder="Buscar" />
						</div>
					</template>
					<template #empty> No existe Resultado </template>
					<template #loading> Cargando... </template>

					<Column field="nombre" header="Productos" :pt="{
						bodyCell: {
							class: 'flex justify-start text-center p-0 mx-0'
						}
					}">
						<template #body="slotProps">
							<div class="w-full my-0">
								<div class="bg-white border-2 overflow-hidden">
									<ul>
										<li class="py-2 px-2">
											<div class="flex items-center space-x-3">
												<div class="flex-shrink-0">
													<img class="w-12 h-12 rounded-full" :src="slotProps.data.imagen"
														alt="">
												</div>
												<div class="flex-1 min-w-0 text-start py-1">
													<div
														class="text-xs md:text-[14px] font-medium whitespace-pre-line leading-4 text-gray-900">
														{{ slotProps.data.nombre }}
													</div>
													<div class="font-bold leading-4 text-xs text-gray-800 py-1">
														Origen:
														<span class="px-1 py-0 font-normal">{{ slotProps.data.origen
														}}</span>
													</div>
													<div class="font-bold leading-4 text-xs text-gray-800 py-1">
														Stock:
														<span class="px-1 py-0 font-normal text-xs">{{
															slotProps.data.stock }}</span>
													</div>

												</div>
												<div
													class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white pr-2">
													<button @click="addToCart(slotProps.data.id)"
														class="bg-green-500 disabled:bg-green-400 disabled:text-gray-500 hover:bg-green-600 text-white rounded px-2 py-1.5"
														:disabled="form.productos.filter(e => e.producto_id === slotProps.data.id).length > 0"><i
															class="fas fa-cart-plus"></i></button>
												</div>
											</div>
										</li>

									</ul>
								</div>
							</div>
						</template>
					</Column>
				</DataTable>
			</div>


		</div>

		<!--Contenido-->

	</AppLayout>
</template>



<style type="text/css" scoped></style>
