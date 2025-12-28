<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, computed } from 'vue'
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import ModalRepuestaRapidas from '@/Pages/MercadoLibre/Partials/ModalRepuestaRapidas.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import { useCustomToast } from '@/composables/customToast';

const { setShow } = useCustomToast()
const { datos } = usePage().props
const { tienda } = usePage().props
const titulo = "Venta Mensajes "+ tienda
const ruta = 'mercadolibre.mensajes'
const { client_id } = usePage().props
const formResponder = useForm({
	packId: null,
	sellerId: null,
	clientId: null,
	buyerId: null,
	text: '',
});

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
				router.get(route(ruta + '.showMensajes', {client_id,id:formResponder.packId}));
			}, 500);
		},
		onFinish: () => {
		},
		onError: () => {

		}
	});
}
onMounted(() => {
	formResponder.packId = datos.id
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


const descargar = (attachment) => {
    router.visit(route('ml.descargarAdjunto'), {
        method: 'get',
        data: {
            filename: attachment.filename,
            original_filename: attachment.original_filename,
            client_id: attachment.client_id
        },
        preserveScroll: true,
        preserveState: true,
        onBefore: () => {
            // Forzar que Inertia NO intercepte el binario
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = route('ml.descargarAdjunto');
            form.style.display = 'none';

            // Pasar parámetros
            Object.entries({
                filename: attachment.filename,
                original_filename: attachment.original_filename,
                client_id: attachment.client_id
            }).forEach(([key, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    });
};



</script>
<template>

	<Head title="Mercado Libre-Mensajes" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">

		<div class="mb-4 col-span-12   dark:border-gray-700  dark:bg-gray-800">
			<div class="w-2/3 flex justify-between">
				<div class=" px-5 pb-2">

					<h5 class="text-2xl font-medium">Orden</h5>
					<p class="text-lg font-medium">#{{ datos.id }}</p>
					<p>
						{{ datos.date_created }}
					</p>
				</div>
				<span
					class=" h-8 p-2 rounded bg-green-600 flex justify-center items-center text-base font-semibold text-white mr-1 hover:bg-green-600">
					<a :href="'https://www.mercadolibre.com.uy/ventas/' + datos.orden_id + '/detalle'"
						target="_blank" class="py-auto">Ver ficha de venta</a>
				</span>

			</div>

			<div class="grid grid-cols-12 gap-6">
				<!-- Columna izquierda -->
				<div class="col-span-8">
					<div class="bg-white rounded-xl shadow p-6">

						<div class="w-full h-screen flex flex-col ">

							<!-- Contenedor scroll -->
							<div class="flex-1 overflow-y-auto p-4 space-y-2">

								<!-- Loop por fechas -->
								<template v-for="(messages, fechaVisual) in parsedMessages" :key="fechaVisual">

									<!-- Fecha centrada -->
									<div class="text-center text-gray-500 text-xs">
										{{ fechaVisual }}
									</div>

									<!-- Mensajes -->
									<div class="space-y-2">
										<div v-for="msg in messages" :key="msg.raw.id" class="flex"
											:class="msg.is_from_seller ? 'justify-end' : 'justify-start'">
											<div class="p-2 rounded-xl max-w-lg shadow-md"
												:class="msg.is_from_seller ? ' bg-blue-100 text-gray-800' : 'bg-white  text-gray-800'">
												<!-- Mensaje con HTML permitido -->
												<p class="text-xs" v-html="msg.raw.text"></p>
												<div v-if="msg.attachment_path!==null" class="w-auto text-xs">
													<a class="text-blue-700" :href="route(ruta + '.descargarAdjunto', {
														filename: msg.raw.message_attachments[0].filename,
														original_filename: msg.raw.message_attachments[0].original_filename,
														client_id:client_id
													})" target="_blank">
														<i class="fa fa-paperclip"></i>
														{{ msg.raw.message_attachments[0].original_filename }}
													</a>
													<span class="ml-1" style="font-size: 12px;">({{
														convertirMB(msg.raw.message_attachments[0].size) }}MB)</span>
												</div>
												<!-- Hora -->
												<div class="flex items-center justify-end gap-1 mt-1">
													<span class=" text-[12px] text-gray-500">
														{{ formatHoraWsp(msg.fecha) }}
													</span>
													<!-- Tildes estilo WhatsApp -->

													<svg v-if="msg.raw.message_date.read"
														xmlns="http://www.w3.org/2000/svg" width="15" height="8"
														viewBox="0 0 15 8">
														<path fill="#3483FA" fill-rule="nonzero"
															d="M8.635.646a.5.5 0 1 1 .707.708L3.753 6.942a.5.5 0 0 1-.707 0l-2.4-2.4a.5.5 0 0 1 .708-.707L3.4 5.881 8.635.646zm5 0a.5.5 0 1 1 .707.708L8.753 6.942a.5.5 0 0 1-.707 0l-1.4-1.4a.5.5 0 0 1 .708-.707L8.4 5.881 13.635.646z">
														</path>
													</svg>

													<!-- Un solo tilde (enviado no leído) -->
													<svg v-else xmlns="http://www.w3.org/2000/svg" width="15" height="8"
														viewBox="0 0 15 8">
														<path fill="#9b9b9b" fill-rule="nonzero"
															d="M8.635.646a.5.5 0 1 1 .707.708L3.753 6.942a.5.5 0 0 1-.707 0l-2.4-2.4a.5.5 0 0 1 .708-.707L3.4 5.881 8.635.646zm5 0a.5.5 0 1 1 .707.708L8.753 6.942a.5.5 0 0 1-.707 0l-1.4-1.4a.5.5 0 0 1 .708-.707L8.4 5.881 13.635.646z">
														</path>
													</svg>

												</div>
											</div>
										</div>
									</div>

								</template>

							</div>
							<!-- Input abajo -->
							<!-- Formulario de respuesta -->
							<form class="border-t p-4 bg-white flex items-center gap-3"
								@submit.prevent="enviarRespuesta()">
								<textarea rows="1" maxlength="340" v-model="formResponder.text"
									placeholder="Ingresá tu mensaje..."
									class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"></textarea>
								<div class="flex justify-end">
									<button type="submit"
										class="bg-blue-600 text-white font-medium px-4 py-2 rounded hover:bg-blue-700"
										:class="{ 'opacity-50': formResponder.processing }" :disabled="formResponder.processing">
										Enviar
									</button>
								</div>
							</form>
						</div>
					</div>
				</div>

				<!-- Columna derecha-->
				<div class="col-span-4">
					<div class="bg-white rounded-xl shadow p-5 mb-5">
						<div class="text-lg font-semibold">
							Comprador
						</div>
						<div>
							<p>
								{{ datos.comprador.first_name }} {{ datos.comprador.last_name }}
							</p>
							<p>
								{{ datos.comprador.nickname }}
							</p>
						</div>

					</div>
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
							<ModalRepuestaRapidas @add-texto="setRespuesta" tipo="mensaje">
							</ModalRepuestaRapidas>
						</div>

					</div>
				</div>
			</div>

		</div>

	</AppLayout>
</template>


<style type="text/css" scoped></style>
