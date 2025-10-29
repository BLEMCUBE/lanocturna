<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import CheckboxName from '@/Components/CheckboxName.vue';
import ModalRepuestaRapidas from '@/Pages/MercadoLibre/Partials/ModalRepuestaRapidas.vue';
import Swal from 'sweetalert2';
import { useCustomToast } from '@/composables/customToast';

const { setShow } = useCustomToast()
const { items } = usePage().props
const { saludo } = usePage().props
const { firma } = usePage().props
const titulo = "Preguntas"
const ruta = 'mercadolibre.preguntas'
const respuestas = ref([]);
const listaPreguntas = ref([]);
const inputSelect = ref(null);
const formDelete = useForm({
	id: '',
});
const formBloquearUsuario = useForm({
	id: '',
});


const formResponder = useForm({
	mercadolibre_pregunta_id: null,
	from_user_id: null,
	text: null,
	payload: null,
});


onMounted(() => {
	items.data.forEach(el => {
		listaPreguntas.value.push(
			{
				id: el.id,
				mercadolibre_pregunta_id: el.mercadolibre_pregunta_id,
				pregunta: el.pregunta,
				publicado: el.publicado,
				from_user_id: el.from_user_id,
				haveSaludo: true,
				haveFirma: true,
				saludo: saludo.value,
				firma: firma.value,
				respuesta: '',
				producto:
				{
					thumbnail: el.producto.thumbnail,
					title: el.producto.title,
					id: el.producto.id,
					sku: el.producto.sku,
					permalink: el.producto.permalink,
					base_price: el.producto.base_price,
					listing_type_id: el.producto.listing_type_id,
				},
				usuario:
				{
					nickname: el.usuario.nickname,
					permalink: el.usuario.permalink,
					city: el.usuario.city,
					state: el.usuario.state,

				}

			}
		)
	})
	getRepuestas();
});

const getRepuestas = () => {
	respuestas.value = usePage().props.repuesta_rapidas
}

const selectTextArea = (index) => {
	inputSelect.value = index
}

const enviarRespuesta = (index) => {
	let rr = listaPreguntas.value[index].respuesta
	if (rr.trim().length === 0) {
		setShow('error', 'Error', 'Por favor ingresá una respuesta.')
		return;
	}
	setDatosResponder(index)

	formResponder.clearErrors()
	formResponder.post(route(ruta + '.responder'), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			setShow('success', 'Mensaje', 'Respuesta enviada')
			setTimeout(() => {
				router.get(route(ruta + '.lista'));
			}, 500);
		},
		onFinish: () => {
		},
		onError: () => {

		}
	});
}

const bloquearUsuario = (index,name) => {
	const alerta = Swal.mixin({ buttonsStyling: true });
	let user_id=listaPreguntas.value[index].from_user_id
	formBloquearUsuario.id=user_id;

	alerta.fire({
		width: 350,
		title: "Seguro de Bloquear al usuario ",
		text: 'Se procedera a bloquear a '+name,
		icon: 'question',
		showCancelButton: true,
		confirmButtonText: 'Eliminar',
		cancelButtonText: 'Cancelar',
		cancelButtonColor: 'red',
	}).then((result) => {
		if (result.isConfirmed) {
			formBloquearUsuario.post(route(ruta + '.bloquear-usuario'),
				{
					preserveScroll: true,
					onSuccess: () => {
						setShow('success', 'Eliminado', 'Se ha Bloqueado')
						setTimeout(() => {
							router.get(route(ruta + '.lista'));
						}, 300);
					}
				});
		}
	});
	}

const setRespuesta = (obj) => {
	if (inputSelect.value !== null) {
		listaPreguntas.value[inputSelect.value].respuesta = obj.respuesta
		setTexto(inputSelect.value);
		inputSelect.value=null;
	}

}

const setDatosResponder = (index) => {
	if (index !== null) {
		let item = listaPreguntas.value[index]
		formResponder.mercadolibre_pregunta_id = item.mercadolibre_pregunta_id
		formResponder.from_user_id = item.from_user_id
		formResponder.payload = item
		setTexto(index);
	}

}

//modal eliminar
const eliminar = (id, name) => {
	const alerta = Swal.mixin({ buttonsStyling: true });
	alerta.fire({
		width: 350,
		title: "Seguro de eliminar " + name,
		text: 'Se eliminará definitivamente',
		icon: 'question',
		showCancelButton: true,
		confirmButtonText: 'Eliminar',
		cancelButtonText: 'Cancelar',
		cancelButtonColor: 'red',
	}).then((result) => {
		if (result.isConfirmed) {
			formDelete.delete(route(ruta + '.destroy', id),
				{
					preserveScroll: true,
					onSuccess: () => {
						setShow('success', 'Eliminado', 'Se ha eliminado')
						setTimeout(() => {
							router.get(route(ruta + '.lista'));
						}, 300);
					}
				});
		}
	});
}

const checkSaludo = (optionId, checked) => {
	if (optionId !== null) {
		let item = listaPreguntas.value[optionId]
		if (checked) {
			item.haveSaludo = true;
			setTexto(optionId);

		} else {
			item.haveSaludo = false;
			setTexto(optionId);
		}
	}
}

const checkFirma = (optionId, checked) => {
	if (optionId !== null) {
		let item = listaPreguntas.value[optionId]
		if (checked) {
			item.haveFirma = true;
			setTexto(optionId);

		} else {
			item.haveFirma = false;
			setTexto(optionId);
		}
	}
}

const setTexto = (index) => {
	if (index !== null) {
		let item = listaPreguntas.value[index]
		switch ((true)) {
			case (item.haveSaludo == true && item.haveFirma == false):
				formResponder.text = item.saludo + '\n' + item.respuesta
				break;
			case (item.haveSaludo == false) && (item.haveFirma == false):
				formResponder.text = item.respuesta
				break;
			case (item.haveSaludo == false) && (item.haveFirma == true):
				formResponder.text = item.respuesta + '\n' + item.firma
				break;
			default:
				formResponder.text = item.saludo + '\n' + item.respuesta + '\n' + item.firma
				break;
		}
	}
}

</script>
<template>

	<Head title="Mercado Libre-Preguntas" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">

		<div class="mb-4 e col-span-12   dark:border-gray-700  dark:bg-gray-800">
			<div class=" px-5 pb-2 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
				<!-- Columna izquierda -->
				<div class="md:col-span-8 xl:col-span-9">
					<div class="bg-white rounded-xl shadow p-6 my-5" v-for="item, index in listaPreguntas">
						<!-- Header producto -->
						<div
							class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 border-b pb-4">
							<div class="flex items-start gap-2 w-full">
								<img :src="item.producto.thumbnail" alt="Producto"
									class="w-16 h-16 rounded object-cover">
								<div class="w-full">
									<span>
										<a :href="item.producto.permalink"
											class="text-gray-800 font-medium text-sm hover:text-blue-600"
											target="_blank">
											{{ item.producto.title }}
										</a>
									</span>
									<span class="mx-3 text-xs text-gray-500">{{ item.producto.id }}</span>
									<span
										class="inline-block bg-blue-700 text-white text-xs font-normal px-2 py-0.5 rounded">{{
											item.producto.listing_type_id }}</span>
									<div v-if="item.producto.sku" class="text-xs text-gray-700 mt-1">
										SKU: <span class="font-medium">{{ item.producto.sku }}</span>
									</div>
									<div class="text-base font-semibold text-blue-600 mt-1">${{ item.producto.base_price
									}}
										<!--
											<span
											class="text-gray-600 text-sm font-normal">x 5 disponibles</span>
											-->
									</div>
									<div class="text-base text-gray-600 mt-1">
										<a :href="item.usuario.permalink" target="_blank"
											class="text-blue-500 hover:underline">{{ item.usuario.nickname }} </a>
										<!--
										|	Miembro desde: 19/11/2012
											-->

										<span v-if="item.usuario.city" class="px-1">

											| <i class="fa fa-map-marker-alt text-gray-500"></i> {{ item.usuario.city }}
											<span v-if="item.usuario.state">
												, {{ item.usuario.state }}
											</span>
										</span>
									</div>
								</div>
							</div>

							<!-- Botones header -->
							<div class="flex items-center gap-3">
								<button @click.prevent="eliminar(item.id, '')" class="text-red-500 hover:text-red-700"
									title="Borrar pregunta">
									<i class="fa fa-trash"></i>
								</button>
								<!--
								<button  @click.prevent="bloquearUsuario(index,item.usuario.nickname)" class="text-gray-600 hover:text-gray-800" title="Bloquear usuario">
									<i class="fa fa-ban"></i>
								</button>
								-->
							</div>
						</div>

						<!-- Pregunta -->
						<div class="mt-3 flex">
							<span class="font-medium text-gray-800 mb-1">
								{{ item.pregunta }}
							</span>
							<span class="ml-3 text-gray-500 text-xs flex items-center gap-1">
								<i class="far fa-clock"></i> {{ item.publicado }}
							</span>
						</div>

						<!-- Formulario de respuesta -->
						<form class="mt-6 space-y-4" @submit.prevent="enviarRespuesta(index)">

							<CheckboxName @update:checked="checkSaludo(index, $event)" :label="item.saludo"
								:key="item.id" :checked="item.haveSaludo">
							</CheckboxName>
							<textarea rows="2" v-model="item.respuesta" placeholder="Ingresá tu respuesta"
								@focus="selectTextArea(index)"
								class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"></textarea>
							<CheckboxName @update:checked="checkFirma(index, $event)" :label="item.firma" :key="item.id"
								:checked="item.haveFirma">
							</CheckboxName>

							<div class="flex justify-end">
								<button type="submit"
									class="bg-blue-600 text-white font-medium px-4 py-2 rounded hover:bg-blue-700">
									Enviar respuesta
								</button>
							</div>
						</form>
					</div>
				</div>

				<!-- Columna derecha-->
				<div class="md:col-span-4 xl:col-span-3 relative">
					<div class="bg-white rounded-xl shadow p-5 top-5 fixed ">
						<h5 class="text-lg font-semibold mb-4">Respuestas rápidas</h5>
						<div class="hidden sm:block flex flex-wrap gap-2 mb-4">
							<ModalRepuestaRapidas @add-texto="setRespuesta" :lista-respuestas="respuestas">
							</ModalRepuestaRapidas>
						</div>
						<!--
	<p class="text-xs text-gray-500 mt-4">
		Tipeá <span class="text-blue-600">@</span> para respuestas rápidas o
		<span class="text-blue-600">#</span> para insertar un link.
		Usá <span class="text-blue-600 font-semibold">Ctrl + Enter</span> para enviar la respuesta.
	</p>
	-->
					</div>
				</div>
			</div>

		</div>

	</AppLayout>
</template>


<style type="text/css" scoped></style>
