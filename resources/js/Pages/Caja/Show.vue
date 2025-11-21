<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";

const toast = useToast();
const titulo = "Detalle Venta"
const ruta = 'cajas'

const form = useForm({
	id: '',
	vendedor: '',
	destino: '',
	codigo: '',
	total: 0.0,
	fecha: '',
	total_sin_iva: 0.0,
	moneda: '',
	tipo_cambio: '',
	estado: '',
	observaciones: '',
	productos: [],
	cliente: '',
	direccion: '',
	localidad: '',
	nro_casa: '',
	telefono: '',
	mp_id: '',
	auth_id: ''

})
const btnEditar = (id) => {
	router.get(route(ruta + '.edit', id));

};
const btnFacturar = (id) => {
	form.get(route(ruta + '.facturar', id), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			show('success', 'Mensaje', 'Se ha Facturado')
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
	var datos = usePage().props.venta.data;
	form.id = datos.id
	form.fecha = datos.fecha
	form.vendedor = datos.vendedor
	form.facturador = datos.facturador
	form.validador = datos.validador
	form.observaciones = datos.observaciones
	form.destino = datos.destino
	form.cliente = datos.cliente
	form.empresa = datos.empresa
	form.rut = datos.rut
	form.direccion = datos.direccion
	form.localidad = datos.localidad
	form.telefono = datos.telefono
	form.moneda = datos.moneda
	form.estado = datos.estado
	form.productos = datos.productos
	form.nro_compra = datos.nro_compra
	form.nro_orden = datos.nro_orden
	form.total_sin_iva = datos.total_sin_iva
	form.total = datos.total
	form.codigo = datos.codigo
	form.mp_id = datos.mp_id
	form.auth_id = datos.auth_id
	form.nro_casa = datos.nro_casa
	form.tipo_cambio = datos.tipo_cambio.toFixed(2)

});


</script>
<template>

	<Head :title="titulo" />
	<AppLayout
		:pagina="[{ 'label': 'Caja', link: true, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
		<div
			class="card px-4 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
			<!--Contenido-->
			<div class="px-0 py-1 m-2 mt-0 col-span-full  flex justify-start items-center">
				<Button @click="btnEditar(form.id)" v-if="form.estado != 'FACTURADO'"
					class="rounded border-0 bg-yellow-500 px-2 py-0.5 text-base font-normal  m-2 hover:bg-yellow-600">
					<span class="text-black font-semibold">Editar</span>
				</Button>
				<Button @click="btnFacturar(form.id)" v-if="form.estado != 'FACTURADO'"
					class="rounded border-0 bg-green-700 px-2 py-0.5 text-base font-normal  m-2 hover:bg-green-600">
					<span class="text-white font-semibold">Facturar</span>
				</Button>
			</div>

			<div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
				<h5 class="text-2xl font-medium">Cliente: {{ form.cliente }}</h5>
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
							Moneda:
						</b>
						{{ form.moneda }}
					</p>
				</div>
				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Tipo Cambio:
						</b>
						{{ form.tipo_cambio }}
					</p>
				</div>

				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Vendedor:
						</b>
						{{ form.vendedor }}
					</p>
				</div>

				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Destino:
						</b>
						{{ form.destino }}
					</p>
				</div>
				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Cliente:
						</b>
						{{ form.cliente }}
					</p>
				</div>
				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Empresa:
						</b>
						{{ form.empresa }}
					</p>
				</div>
				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Rut:
						</b>
						{{ form.rut }}
					</p>
				</div>
				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Localidad:
						</b>
						{{ form.localidad }}
					</p>
				</div>
				<div class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Direccion:
						</b>
						{{ form.direccion }} {{ form.nro_casa }}
					</p>
				</div>
				<div v-if="form.nro_orden" class="col-span-1">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Nro. Orden:
						</b>
						{{ form.nro_orden }}
					</p>
				</div>
				<div class="col-span-1" v-if="form.mp_id !== ''">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							MP:
						</b>
						{{ form.mp_id }}
					</p>
				</div>
				<div class="col-span-1" v-if="form.auth_id !== ''">
					<p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
							Auth ID:
						</b>
						{{ form.auth_id }}
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
				class="mx-auto grid max-w-2xl grid-cols-12  overflow-auto gap-x-1 gap-y-0 px-2 py-0 sm:px-6 lg:max-w-7xl lg:grid-cols-3 lg:px-1">
				<table class="table-auto mx-2 border border-gray-300 col-span-full">
					<thead>
						<tr class="p-2 bg-secondary-900 border">
							<th class="border border-gray-300 p-1">Cantidad</th>
							<th class="border border-gray-300">Origen</th>
							<th class="border border-gray-300">Imagen</th>
							<th class="border border-gray-300 ">Producto</th>
							<th class="border border-gray-300">Código de Barras</th>
							<th class="border border-gray-300 ">Precio</th>
							<th class="border border-gray-300">Total</th>

						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, index) in form.productos" :key="index"
							class="font-sans  text-center font-normal text-gray-800 border border-gray-300">
							<td class="border border-gray-300 p-1">{{ item.cantidad }}</td>
							<td class="border border-gray-300 p-1">{{ item.producto.origen }}</td>
							<td class="flex justify-center items-center pt-1">
								<img class="rounded  bg-white shadow-2xl border-2 text-center w-10 h-10 object-contain"
									:src="usePage().props.base_url + item.producto.imagen" alt="image">
							</td>
							<td class="border border-gray-300 p-1">{{ item.producto.nombre }}</td>
							<td class="border border-gray-300 p-1">{{ item.producto.codigo_barra }}</td>
							<td class="border border-gray-300 p-1">{{ item.precio_sin_iva.toFixed(2) }}</td>
							<td class="border border-gray-300 p-1">{{ item.total_sin_iva.toFixed(2) }}</td>

						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="5" class="border border-gray-300 p-1 text-end pr-2"><b>Subtotal: </b></td>
							<td class="border border-gray-300 p-1 text-center"><b> {{ form.moneda == 'Pesos' ? '$ ' :
									'USD ' }} {{ form.total_sin_iva.toFixed(2) }}
								</b></td>
						</tr>
						<tr>
							<td colspan="5" class="border border-gray-300 p-1 text-end pr-2"><b>Total (con impuesto):
								</b></td>
							<td class="border border-gray-300 p-1 text-center"><b> {{ form.moneda == 'Pesos' ? '$ ' :
									'USD ' }} {{ form.total.toFixed(2) }}
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
