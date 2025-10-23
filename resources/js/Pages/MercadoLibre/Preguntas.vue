<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import CheckboxName from '@/Components/CheckboxName.vue';
import ModalRepuestaRapidas from '@/Pages/MercadoLibre/Partials/ModalRepuestaRapidas.vue';
import Swal from 'sweetalert2';

const { items } = usePage().props
const { saludo } = usePage().props
const { firma } = usePage().props
const { repuesta_rapidas } = usePage().props
const toast = useToast();
const titulo = "Preguntas"
const ruta = 'mercadolibre'
const respuestasrapidas = 'respuestasrapidas'
const formDelete = useForm({
	id: '',
});

onMounted(() => {


});
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
			formDelete.delete(route(ruta + '.preguntas-destroy', id),
				{
					preserveScroll: true,
					onSuccess: () => {
						show('success', 'Eliminado', 'Se ha eliminado')
						setTimeout(() => {
							router.get(route(ruta + '.preguntas'));
						}, 200);

					}
				});
		}
	});
}

const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

</script>
<template>

	<Head title="Mercado Libre-Clientes" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">

		<div class="mb-4 e col-span-12   dark:border-gray-700  dark:bg-gray-800">
			<div class=" px-5 pb-2 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
				<!-- Columna izquierda -->
				<div class="md:col-span-8 xl:col-span-9">
					<div class="bg-white rounded-xl shadow p-6 my-5" v-for="item, index in items.data">
						<!-- Header producto -->
						<div
							class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 border-b pb-4">
							<div class="flex items-start gap-2 w-full">
								<img :src="item.product.thumbnail" alt="Producto"
									class="w-16 h-16 rounded object-cover">
								<div class="w-full">
									<span>
										<a :href="item.product.permalink"
											class="text-gray-800 font-medium text-sm hover:text-blue-600"
											target="_blank">
											{{ item.product.title }}
										</a>
									</span>
									<span class="mx-3 text-xs text-gray-500">{{ item.product.id }}</span>
									<span
										class="inline-block bg-blue-700 text-white text-xs font-normal px-2 py-0.5 rounded">{{
											item.product.listing_type_id }}</span>
									<div v-if="item.product.sku" class="text-xs text-gray-700 mt-1">
										SKU: <span class="font-medium">{{ item.product.sku }}</span>
									</div>
									<div class="text-base font-semibold text-blue-600 mt-1">${{ item.product.base_price
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
								<button class="text-gray-600 hover:text-gray-800" title="Bloquear usuario">
									<i class="fa fa-ban"></i>
								</button>
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
						<form class="mt-6 space-y-4">
							<label class="flex items-start gap-2 text-gray-700 font-normal text-base">
								<input type="checkbox" class="mt-1 w-6 h-6 accent-blue-500">
								{{ saludo.value }}
							</label>

							<textarea rows="2" placeholder="Ingresá tu respuesta"
								class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"></textarea>

							<label class="flex items-start gap-2 text-gray-700 font-normal text-base">
								<input type="checkbox" class="mt-1 w-18 h-18 accent-blue-500">
								{{ firma.value }}
							</label>

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
							<button v-for="item in repuesta_rapidas"
								class="px-3 py-1 m-1 text-sm  text-white rounded  text-medium"
								:style="{ backgroundColor: item.color }">
								{{ item.titulo }}</button>
						</div>
						<ModalRepuestaRapidas></ModalRepuestaRapidas>
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
