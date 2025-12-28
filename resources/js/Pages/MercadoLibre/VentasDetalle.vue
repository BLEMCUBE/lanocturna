<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, computed } from 'vue'
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
const { datos } = usePage().props
const { tienda } = usePage().props
const titulo =  `Venta Detalle "${tienda}"`
const ruta = 'mercadolibre.ventas'
const { client_id } = usePage().props

onMounted(() => {

});



// Convierte fecha completa → solo hora (19:08)
const formatHora = (str) => {
	const d = new Date(str.replace(" ", "T"));
	return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
};

</script>
<template>

	<Head title="Mercado Libre-Ventas" />
	<AppLayout
		:pagina="[{ 'label': 'ML Ventas', link: true, url: route(ruta + '.index', [client_id]) }, { 'label': titulo, link: false }]">

		<div class="px-4 py-3 mb-4 col-span-12 2xl:col-span-9">



			<!-- ENCABEZADO -->
			<div class="bg-white shadow rounded-lg p-6 flex justify-between items-start w-full">
				<div>

					<h1 class="text-xl font-semibold">Orden #{{ datos.data.pack_id?datos.data.pack_id:datos.data.orden_id }}</h1>
					<p class="text-sm text-gray-500 mt-1">{{ datos.data.fecha }}</p>

					<div class="flex gap-2 mt-4">


						<a :href="'https://www.mercadolibre.com.uy/ventas/' + datos.data.orden_id + '/detalle'"
							class="px-3 py-2 text-sm bg-yellow-400 hover:bg-yellow-500 text-black rounded shadow-sm"
							target="_blank">
							Ver en Mercado Libre
						</a>

						<!--
						<button class="px-3 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded shadow-sm">
							Escribirle al comprador
						</button>

						<button class="px-3 py-2 text-sm border rounded hover:bg-gray-100">
							Adjuntar factura de venta
						</button>
						-->

					</div>
				</div>
				<!--
	<button class="text-sm px-3 py-1.5 border rounded hover:bg-gray-100">
		Imprimir
	</button>
	-->
			</div>

			<!-- DETALLES -->
			<div class="bg-white shadow rounded-lg mt-6">
				<div class="p-6">
					<div class="text-xl font-semibold p-3"><i class="fa-solid fa-file-lines fa-1x"></i> Detalle</div>
					<!--
					<div class="flex items-center gap-2 mb-4">
						<span class="text-green-600 font-semibold flex items-center gap-1">
							✔ Entregado
						</span>

					</div>
					-->
					<div class="border rounded-lg overflow-hidden">

						<!-- PRODUCTO -->
						<div class="p-4 border-b">
							<div class="flex justify-between font-semibold">
								<span>Precio del producto</span>
								<span>${{ datos.data.total }}</span>
							</div>
							<div v-for="pr in datos.data.productos" class="flex items-center justify-between">
								<div class="flex justify-start items-center text-sm text-gray-600 mt-1">
									<div class="w-10 h-10 mr-2">
										<img :src="pr.imagen" :alt="pr.titulo"
											class="w-10 h-10 object-cover rounded border border-gray-800" />
									</div>
									<div class="px-2 mx-2">
										<div>Operación #{{ datos.data.orden_id }}</div>
										<div>{{ pr.cantidad }} x {{ pr.titulo }} | {{ pr.sku }}</div>
									</div>
								</div>
								<div>${{ pr.precio }}</div>
							</div>

						</div>

						<!-- CARGO POR VENTA -->
						<div class="p-4 border-b">
							<p class="font-semibold">Cargo por venta total</p>
							<div class="flex justify-between text-sm mt-2" v-for="pa in datos.data.pagos">
								<span>Cargo por vender {{ pa.reason }}</span>
								<span>- $ {{ pa.marketplace_fee }}</span>
							</div>
						</div>

						<!-- ENVÍOS -->
						<div class="p-4 border-b">
							<p class="font-semibold">Envíos</p>
							<div v-if="datos.data.pagado == 'VENDEDOR'" class="flex justify-between text-sm mt-2">
								<span>Cargo por Mercado Envíos (a tu cargo)</span>
								<span>- $ {{ datos.data.envio.list_cost }}</span>
							</div>
							<div v-else class="flex justify-between text-sm mt-2">
								<span>Cargo por Mercado Envíos (a cargo del comprador)</span>
								<span> $ {{ datos.data.envio.list_cost }}</span>
							</div>
						</div>

						<!-- TOTAL -->
						<div class="p-4 bg-gray-50">
							<div class="flex justify-between font-semibold">
								<span>Total</span>
								<span>${{ datos.data.total_final }}</span>
							</div>
							<!--
								<div class="flex justify-between font-semibold  mt-1">
									<span>Debés facturar</span>
									<span>$1.2401</span>
								</div>
								-->
						</div>

					</div>

				</div>
			</div>

			<!-- COMPRADOR -->
			<div class="bg-white shadow rounded-lg p-6 mt-6">
				<h2 class="text-lg font-semibold mb-4">Comprador</h2>
				<div class="grid grid-cols-3 gap-4">
					<div>
						<p class="text-sm text-gray-500">Nombre</p>
						<p class="font-medium">{{ datos.data.comprador.nombre }}</p>
					</div>
					<div>
						<p class="text-sm text-gray-500">Datos para factura</p>
						<p class="font-medium">{{ datos.data.comprador.doc_type }} {{ datos.data.comprador.doc }}</p>
					</div>
					<div>
						<p class="text-sm text-gray-500">Usuario</p>
						<p class="font-medium">{{ datos.data.comprador.user }}</p>
					</div>
				</div>
			</div>

			<!-- PAGOS + ENTREGA -->

			<div class="grid grid-cols-2 gap-6 mt-6">


				<div class="bg-white shadow rounded-lg p-6">
					<h3 class="text-lg font-semibold mb-3">Pagos</h3>
					<p class="text-sm"><strong>Cobro:</strong> #{{ datos.data.pagos[0].id }}</p>
					<p class="text-sm"><strong>Estado:</strong> {{ datos.data.pagos[0].status }}</p>
					<p class="text-sm"><strong>Medio:</strong> {{ datos.data.pagos[0].payment_type }}</p>
					<p class="text-sm"><strong>Total pagado:</strong> ${{ datos.data.pagos[0].total_paid_amount }} </p>
				</div>


				<div class="bg-white shadow rounded-lg p-6">
					<h3 class="text-lg font-semibold mb-3">Entrega</h3>
					<p class="text-sm"><strong>Número de envío:</strong> {{ datos.data.envio.id }}</p>
					<p class="text-sm"><strong>Código de seguimiento:</strong> {{ datos.data.envio.tracking_number }}
					</p>
					<p class="text-sm"><strong>Dirección:</strong> {{ datos.data.envio.address_line }}</p>
					<p v-if="datos.data.envio.comment" class="text-sm"><strong>Referencia:</strong> {{
						datos.data.envio.comment }}</p>
					<p class="text-sm"><strong>Recibe:</strong> {{ datos.data.envio.receiver_name }}</p>
				</div>

			</div>


			<!-- PREGUNTAS / MENSAJES -->
			<!--
				<div class="grid grid-cols-2 gap-6 mt-6">

					<div class="bg-white shadow rounded-lg p-6">
						<h3 class="text-lg font-semibold mb-3">Preguntas previas</h3>
						<p class="text-gray-500 text-sm">Sin preguntas previas</p>
					</div>

					<div class="bg-white shadow rounded-lg p-6">
						<h3 class="text-lg font-semibold mb-3">Mensajes post-venta</h3>
						<p class="text-gray-500 text-sm">Sin mensajes post venta</p>
					</div>

				</div>
				-->

			<!-- NOTAS -->
			<!--
				<div class="bg-white shadow rounded-lg p-6 mt-6">
					<div class="flex justify-between items-center mb-4">
						<h3 class="text-lg font-semibold">Notas</h3>

						<button class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded shadow-sm">
							Crear nota
						</button>
					</div>

					<div class="border p-3 rounded text-sm">
						FAC // armado 5
					</div>
				</div>
				-->






		</div>

	</AppLayout>
</template>


<style type="text/css" scoped></style>
