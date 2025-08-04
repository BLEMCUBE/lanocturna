<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
const { permissions } = usePage().props.auth

const titulo = "Detalle Compra"
const ruta = 'compras'

const form = useForm({
	id: '',
	fecha: '',
	observaciones: '',
	proveedor: '',
	nro_factura: '',
	total: 0.0,
	total_sin_iva: 0.0,
	moneda: '',
	tipo_cambio: '',
	productos: [],


})
const btnEditar = (id) => {
	router.get(route(ruta + '.edit', id));

};
onMounted(() => {
	var datos = usePage().props.compra.data;
	form.id = datos.id
	form.fecha = datos.fecha
	form.facturador = datos.facturador
	form.observaciones = datos.observaciones
	form.nro_factura = datos.nro_factura
	form.proveedor = datos.proveedor
	form.moneda = datos.moneda
	form.tipo_cambio = datos.tipo_cambio
	form.total = datos.total
	form.total_sin_iva = datos.total_sin_iva
	form.estado = datos.estado
	form.productos = datos.productos
});


</script>
<template>

	<Head :title="titulo" />
	<AppLayout
		:pagina="[{ 'label': 'Compras', link: true, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
		<div
			class="card px-4 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
			<!--Contenido-->


			<div class="px-0 py-1 m-2 mt-0 text-white  col-span-full  flex justify-end items-center">
				<Button label="Editar" v-if="permissions.includes('ventas-editar') && form.estado !== 'ANULADO'"
					@click="btnEditar(form.id)" :pt="{
						root: {
							class: 'flex items-center  bg-primary-900 justify-center font-medium w-10'
						},
						label: {
							class: 'hidden'
						}
					}" v-tooltip.top="{ value: `Editar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"><i
						class="fas fa-edit"></i></Button>

			</div>
			<div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>

			</div>

			<div
				class="mx-auto grid max-w-2xl grid-cols-1  gap-x-1 gap-y-1 px-4 py-2 sm:px-6 lg:max-w-7xl lg:grid-cols-3 lg:px-8">

				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Fecha:
						</b>
						{{ form.fecha }}
					</p>
				</div>
				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							N° de Factura:
						</b>
						{{ form.nro_factura }}
					</p>
				</div>
				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Proveedor:
						</b>
						{{ form.proveedor }}
					</p>
				</div>


				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Facturado por:
						</b>
						{{ form.facturador }}
					</p>
				</div>

				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Estado:
						</b>
						{{ form.estado }}
					</p>
				</div>
				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Total:
						</b>
						{{ form.total.toFixed(2) }}
					</p>
				</div>
				<!--
					<div class="col-span-1">
						<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Total sin IVA:
						</b>
						{{ form.total_sin_iva.toFixed(2) }}
					</p>
				</div>
				-->
				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Moneda:
						</b>
						{{ form.moneda }}
					</p>
				</div>


				<div class="col-span-3">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Observaciones:
						</b>
						{{ form.observaciones }}
					</p>
				</div>
			</div>
			<div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
				<h5 class="text-2xl font-medium">Productos</h5>
			</div>
			<div
				class="mx-auto grid max-w-2xl grid-cols-1  overflow-auto gap-x-1 gap-y-1 px-2 py-2 sm:px-6 lg:max-w-7xl lg:grid-cols-3 lg:px-1">
				<table class="table-auto mx-2 border border-gray-300 col-span-12">
					<thead>
						<tr class="p-2 bg-secondary-900 border">
							<th class="border border-gray-300 w-24">Cantidad</th>
							<th class="border border-gray-300 p-2 w-24">Origen</th>
							<th class="border border-gray-300 ">Producto</th>
							<th class="border border-gray-300">Código de Barras</th>
							<th class="border border-gray-300">Precio</th>
							<th class="border border-gray-300">Total</th>


						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, index) in form.productos" :key="index"
							class="font-sans  text-center font-normal text-gray-800 border border-gray-300">
							<td class="border border-gray-300 p-2">{{ item.cantidad }}</td>
							<td class="border border-gray-300 p-2">{{ item.producto.origen }}</td>
							<td class="border border-gray-300 p-2">{{ item.producto.nombre }}</td>
							<td class="border border-gray-300 p-2">{{ item.producto.codigo_barra }}</td>
							<td class="border border-gray-300 p-2">{{ item.precio.toFixed(2) }}</td>
							<td class="border border-gray-300 p-2">{{ item.total.toFixed(2) }}</td>

						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="5" class="text-end"><b>Total: </b></td>
							<td class="text-center"><b> {{ form.moneda == 'Pesos' ? '$ ' : 'USD ' }} {{
								form.total.toFixed(2) }}
								</b></td>
						</tr>

					</tfoot>
				</table>
			</div>



			<!--Contenido-->
		</div>
	</AppLayout>
</template>

<style type="text/css" scoped></style>
