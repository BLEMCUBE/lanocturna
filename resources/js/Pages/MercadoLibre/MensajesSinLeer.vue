<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';

const titulo = "ML Mensajes sin leer"
const datosNoLeido = ref([]);
const ruta = 'mercadolibre.mensajes'

const filters = ref({
	global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const todos = () => {
	router.get(route(ruta + '.lista'))
};
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
	datosNoLeido.value = usePage().props.datos.data.map(item => ({
		...item,
		productoDisplay:
			'<div class="ml-6 text-xs text-gray-500">' + `#${item.pack_id}` + '</div>' +
			'<div class="flex justify-center justify-items-center">' +
			`${item.productos.map(function (x) {
				return '<img src="' + x.thumbnail + '" class="w-6 h-8 rounded object-cover border mx-2" />'
					+ '<div class="font-semibold text-xs text-gray-800">' + x.title + '</div>';
			})
			}` + '</div>'
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
				<Button :class="{ 'opacity-60': route().current('mercadolibre.mensajes.sinLeer') }" @click="todos" label="Todos" />
				<Button class="mx-2" :class="{ 'opacity-60': route().current('mercadolibre.mensajes.lista') }"
					label="Sin leer" @click="sinLeer" />
				<DataTable size="small" :filters="filters" @row-click="clickDetalle" :value="datosNoLeido" :paginator="true" :rows="50" :pt="{
					bodyRow: { class: 'hover:cursor-pointer hover:bg-gray-100' }
				}" :rowsPerPageOptions="[5, 10, 20, 50]" :globalFilterFields="['productoDisplay']"
					paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
					tableStyle="width: 100%">
					<template #header size="small" class="bg-secondary-900">
						<div class="flex justify-content-end text-md">
							<InputText class="w-1/2" v-model="filters['global'].value"
								placeholder="Buscar por Nº de operación..." />
						</div>
					</template>
					<template #empty> No existe Resultado </template>
					<template #loading> Cargando... </template>
					<Column class="w-[210px]" field="productoDisplay" header="Producto">
						<template #body="slotProps" style="width:210px">
							<div class="w-[210px]" v-html="slotProps.data.productoDisplay"></div>
						</template>
					</Column>
					<Column class="px-10" field="mensaje" header="Mensaje">
						<template #body="slotProps">
							<div class="text-xs  text-start">
								<div v-if="slotProps.data.leido > 0"
									class="my-2 px-2 py-1 font-bold rounded-md text-white bg-red-700 border-1 w-fit  border-red-700">
									{{ slotProps.data.leido > 1 ? `${slotProps.data.leido} mensajes sin
									leer`: `${slotProps.data.leido} mensaje sin leer` }}
								</div>
								<div v-html="slotProps.data.mensaje">
								</div>
							</div>
						</template>
					</Column>
					<Column class="text-xs text-center w-[270px]" field="comprador" header="Comprador">
					</Column>
					<Column class="text-xs text-center w-[100px]" header="Fecha">
						<template #body="{ data }" style="width:250px">
							<div class="flex flex-col justify-items-center ">
								<div class="text-bold">
									{{ data.hora }}
								</div>
								<div class="text-bold">
									{{ data.fecha }}
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
