<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
const { tienda } = usePage().props
const titulo =  `Historial de preguntas "${tienda}"`
const datosTodos = ref([]);
const ruta = 'mercadolibre.preguntas'

const filters = ref({
	global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});



const btnVer = (id) => {
	router.get(route(ruta + '.showMensajes', id));

};


const clickDetalle = (e) => {

	btnVer(e.data.pack_id)
}
const sinLeer = () => {
	router.get(route(ruta + '.sinLeer'))
};

onMounted(() => {
	datosTodos.value = usePage().props.datos.data.map(item => ({
		...item,
		productoDisplay:
			`<div class="flex flex-col justify-center items-center py-3 w-56">
									<img src="${item.producto.thumbnail}"
										class="w-24 h-24 object-contain rounded-md border" />
									<div class="w-full">
										<h2 class="text-gray-800 text-sm text-center font-semibold leading-tight">
											${item.producto.title}
										</h2>
										<p class="text-xs text-center text-gray-500 mt-1"># ${item.producto.id}</p>
									</div>
			</div>`,
		pregunta: `<div class="bg-gray-100 border border-gray-200 w-full p-4 rounded-lg">
										<i class="fas fa-comment fa-3x text-gray-500"></i>
										<p class="text-gray-800">
											${item.pregunta}
										</p>
										<p class="text-xs text-gray-500 mt-3">
											Preguntado el ${item.date_created} — Usuario: <span
												class="font-semibold">${item.usuario.nickname}</span>
																<span v-if="item.usuario.city" class="px-1">

											| <i class="fa fa-map-marker-alt text-gray-500"></i>  ${item.usuario.city}
											,<span v-if="item.usuario.state">
												 ${item.usuario.state}
											</span>
										</span>
										</p>

									</div>
									</div>`
	}));
});

</script>
<template>

	<Head :title="titulo" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">
		<div class="px-4 py-3 mb-4 bg-white col-span-12   dark:border-gray-700  dark:bg-gray-800">
			<div class=" px-5 pb-2 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
			</div>
			<div class="align-middle">


				<DataTable size="small" showGridlines :filters="filters" :value="datosTodos" :paginator="true"
					:rows="50" :pt="{
						bodyRow: { class: '' }
					}" :rowsPerPageOptions="[5, 10, 20, 50]" :globalFilterFields="['productoDisplay', 'pregunta']"
					paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
					tableStyle="width: 100%">
					<template #header size="small" class="bg-secondary-900">
						<div class="flex justify-content-end text-md">
							<InputText class="w-1/2" v-model="filters['global'].value" placeholder="Usuario/Producto" />
						</div>
					</template>
					<template #empty> No existe Resultado </template>
					<template #loading> Cargando... </template>
					<Column field="productoDisplay" header="Producto">
						<template #body="slotProps" style="width:200px">
							<div v-html="slotProps.data.productoDisplay">
							</div>
						</template>
					</Column>
					<Column class="px-5" field="pregunta" header="Pregunta">
						<template #body="slotProps">
							<div class="text-xs text-start">
								<div class="mt-6 flex gap-3" v-html="slotProps.data.pregunta">
								</div>

								<!-- RESPUESTA -->
								<div class="my-4 flex gap-3">
									<div class="bg-blue-500 text-white p-4 rounded-lg shadow w-full">
										<i class="fas fa-comment fa-flip-horizontal fa-3x"></i>
										<p  class="text-xs">
											{{ slotProps.data.respuesta }}

										</p>

										<p class="text-xs text-blue-100 mt-3">
											Respondido el {{ slotProps.data.fecha_respuesta }}
										</p>
									</div>
								</div>


							</div>
						</template>
					</Column>
				</DataTable>
			</div>

		</div>

	</AppLayout>
</template>


<style type="text/css" scoped></style>
