<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, computed } from 'vue'
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import ModalRepuestaRapidas from '@/Pages/MercadoLibre/Partials/ModalRepuestaRapidas.vue';

import { useCustomToast } from '@/composables/customToast';

const { setShow } = useCustomToast()
const { datos } = usePage().props
const { tienda } = usePage().props
const titulo = `Reclamos Mensajes "${tienda}"`
const ruta = 'mercadolibre.reclamos'
const { client_id } = usePage().props
const formResponder = useForm({
	reclamoId: null,
	sellerId: null,
	clientId: null,
	buyerId: null,
	text: '',
	files: []
});

const formatDeadlineDate = (dateString) => {
	if (!dateString) return ''
	const date = new Date(dateString)
	const options = {
		weekday: 'long',
		day: 'numeric',
		month: 'long',
		year: 'numeric',
		//hour: '2-digit',
		//minute: '2-digit',
	}
	return date.toLocaleDateString('es-ES', options)
}

const onFiles = (e) => {
	formResponder.files = Array.from(e.target.files)
}


const enviarRespuesta = () => {
	let rr = formResponder.text
	if (rr.length === 0) {
		setShow('error', 'Error', 'Por favor ingresá un Mensaje.')
		return;
	}
	formResponder.clearErrors()
	formResponder.post(route(ruta + '.responder'), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			setShow('success', 'Mensaje', 'Mensaje enviado')
			setTimeout(() => {
				router.get(route(ruta + '.index', { client_id }));
			}, 500);
		},
		onFinish: () => {
		},
		onError: () => {

		}
	});
}
onMounted(() => {
	formResponder.reclamoId = datos.id
	formResponder.sellerId = datos.comprador.seller
	formResponder.buyerId = datos.comprador.id
	formResponder.clientId = client_id
});


const setRespuesta = (obj) => {
	formResponder.text = obj.respuesta
}

// Convierte fecha completa → solo hora (19:08)
const formatHora = (str) => {
	const d = new Date(str.replace(" ", "T"));
	return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
};

const convertirMB = (size) => {
	return (size / 1024 / 1024).toFixed(2)

}
const formatHoraWsp = (fechaString) => {
	const d = new Date(fechaString.replace(" ", "T"));
	return d.toLocaleTimeString([], {
		hour: "2-digit",
		minute: "2-digit",
		hour12: true
	}).toLowerCase()
		// eliminar los puntos: "a. m." → "am"
		.replace(/\./g, '')
		// eliminar espacios extra: "a m" → "am"
		.replace(/\s+/g, ''); // convierte AM → am
};

// Los keys ya vienen como "17/11/2025", no necesita convertir
const parsedMessages = computed(() => datos.mensajes);


</script>
<template>

	<Head title="Mercado Libre-Mensajes" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">

		<div class="mb-4 col-span-12   dark:border-gray-700  dark:bg-gray-800">
			<div class="w-2/3 flex justify-between">
				<div class=" px-5 pb-2">
					<h5 class="text-2xl font-medium">Reclamo: </h5>
					<p class="text-lg font-medium">#{{ datos.id }}</p>
					<p>
						{{ datos.date_created }}
					</p>
				</div>
				<span
					class=" h-8 p-2 rounded bg-green-600 flex justify-center items-center text-base font-semibold text-white mr-1 hover:bg-green-600">
					<a :href="'https://www.mercadolibre.com.uy/ventas/' + datos.orden_id + '/detalle'" target="_blank"
						class="py-auto">Ver ficha de venta</a>
				</span>

			</div>

			<div class="grid grid-cols-12 gap-6">
				<!-- Columna izquierda -->
				<div class="col-span-8">
					<div class="bg-white rounded-xl shadow p-6">

						<div class="w-full max-h-screen flex flex-col ">
							<!-- Contenedor scroll -->
							<div class="flex-1 overflow-y-auto p-4 space-y-2 ">
								<!-- Loop por fechas -->
								<template v-for="(messages, fechaVisual) in parsedMessages" :key="fechaVisual">

									<!-- Fecha centrada -->
									<div class="text-center text-gray-500 text-xs">
										{{ fechaVisual }}
									</div>

									<!-- Mensajes -->
									<div class="space-y-2">

										<div v-for="msg in messages" :key="msg.raw.id" class="flex"
											:class="msg.sender_role == 'complainant' ? 'justify-start' : 'justify-end'">
											<div class="p-2 rounded-xl max-w-lg shadow-md"
												:class="msg.sender_role == 'complainant' ? ' bg-white text-gray-800' : 'bg-blue-100  text-gray-800'">
												<!-- Mensaje con HTML permitido -->
												<p class="text-xs" v-html="msg.raw.message"></p>
												<div v-if="msg.attachment_path !== null" class="w-auto text-xs">
													<a class="text-blue-700" :href="route(ruta + '.descargarAdjunto', {
														filename: msg.raw.attachments[0].filename,
														original_filename: msg.raw.attachments[0].original_filename,
														client_id: client_id,
														reclamo_id: datos.id
													})" target="_blank">
														<i class="fa fa-paperclip"></i>
														{{ msg.raw.attachments[0].original_filename }}
													</a>
													<span class="ml-1" style="font-size: 12px;">({{
														convertirMB(msg.raw.attachments[0].size) }}MB)</span>
												</div>
												<!-- Hora -->
												<div class="flex items-center justify-end gap-1 mt-1">
													<span class=" text-[12px] text-gray-500">
														{{ formatHoraWsp(msg.fecha) }}
													</span>

												</div>
											</div>
										</div>
									</div>

								</template>

							</div>
							<!-- Input abajo -->
							<!-- Formulario de respuesta -->
							<div class="border-t  py-3 bg-white">
								<!--
									<b class="text-xs my-4">
										{{ datos.motivo.title ?? '' }}
									</b>
									<p class="text-xs mb-3">
									{{ datos.motivo.description ?? '' }}

								</p>
								<div class="flex justify-start items-center my-4">
									<button type="button" v-for="(item, index) in datos.displayActions"
									class="bg-blue-300 mx-3 px-2 py-2 text-black text-xs font-medium rounded hover:bg-blue-200">
									{{ item.label }}
								</button>

							</div>
							-->
								<form class=" flex items-center gap-3" @submit.prevent="enviarRespuesta()">

									<!-- Icono adjuntar -->
									<label class="cursor-pointer text-gray-500 hover:text-gray-700"
										v-if="datos.estado !== 'closed'">
										<i class="fa-solid fa-paperclip fa-xl"></i>
										<input type="file" multiple class="hidden" @change="onFiles" />
									</label>

									<textarea rows="1" maxlength="340" v-model="formResponder.text"
										:readOnly="datos.estado === 'closed'" placeholder="Ingresá tu mensaje..."
										class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"></textarea>
									<div class="flex justify-end" v-if="datos.estado !== 'closed'">
										<button type="submit"
											class="bg-blue-600 text-white font-medium px-4 py-2 rounded hover:bg-blue-700"
											:class="{ 'opacity-50': formResponder.processing }"
											:disabled="formResponder.processing">
											Enviar
										</button>
									</div>
								</form>
							</div>
							<!-- errores Laravel -->
							<div v-if="formResponder.errors.text" class="text-red-500">
								{{ formResponder.errors.text }}
							</div>
							<div v-if="formResponder.errors['files.0']" class="text-red-500">
								{{ formResponder.errors['files.0'] }}
							</div>
						</div>
					</div>
				</div>

				<!-- Columna derecha-->
				<div class="col-span-4">
					<TabView class="py-2">

						<TabPanel>
							<template #header>
								<div class="flex align-items-center gap-2">
									<span class="font-bold white-space-nowrap">Reclamo</span>
								</div>
							</template>
							<div>
								<p>
									<b>Tipo: </b>
									Reclamo
								</p>
								<p>
									<b>Motivo: </b>
									{{ datos.motivo.problem }}
								</p>

							</div>
						</TabPanel>
						<TabPanel>
							<template #header>
								<div class="flex align-items-center gap-2">
									<span class="font-bold white-space-nowrap">Comprador</span>
								</div>
							</template>
							<div>
								<p>
									<b>Nombre: </b>
									{{ datos.comprador.first_name }} {{ datos.comprador.last_name }}
								</p>
								<p>
									<b>Usuario: </b>
									{{ datos.comprador.nickname }}
								</p>
							</div>
						</TabPanel>
					</TabView>

					<div class="bg-white rounded-xl shadow p-5 mb-5">
						<div class="text-lg font-semibold pb-2">
							Compra
						</div>
						<div class="flex gap-3 items-center" v-for="compra in datos.compra">
							<!-- Imagen pequeña -->
							<img :src="compra.producto.thumbnail" class="w-10 h-10 object-cover rounded">

							<div class="text-sm text-gray-700">
								<a target="_blank" :href="compra.producto.permalink">

									<p class="font-medium">
										{{ compra.producto.title }}
									</p>
								</a>
								<p class="text-gray-600" v-if="compra.color">Color {{ compra.color }}</p>
								<p class="text-gray-500 text-xs">SKU: {{ compra.seller_sku }}</p>
								<p class="font-semibold mt-1">{{ compra.cantidad }} × ${{ compra.precio }}</p>
							</div>
						</div>

					</div>
					<div class="bg-white rounded-xl shadow p-5 overflow-y-auto">
						<h5 class="text-lg font-semibold mb-4">Respuestas rápidas</h5>
						<div class="hidden sm:block flex-wrap gap-2 mb-4">
							<ModalRepuestaRapidas @add-texto="setRespuesta" tipo="reclamo">
							</ModalRepuestaRapidas>
						</div>

					</div>
				</div>
			</div>

		</div>

	</AppLayout>
</template>


<style type="text/css" scoped></style>
