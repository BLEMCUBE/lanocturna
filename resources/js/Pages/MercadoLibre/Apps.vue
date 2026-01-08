<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
import CrearModal from '@/Pages/MercadoLibre/Partials/CrearModal.vue';
import EditarModal from '@/Pages/MercadoLibre/Partials/EditarModal.vue';
import { useToast } from "primevue/usetoast";
import Swal from 'sweetalert2';
import { useLoaderStore } from "@/stores/loader";

const loader = useLoaderStore();
const toast = useToast();
const tabla_datos = ref()
const titulo = "ML Apps"
const ruta = 'mercadolibre.apps'

const formDelete = useForm({
	id: '',
});
onMounted(() => {
	tabla_datos.value = usePage().props.items.data;
});

const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const filters = ref({
	global: { value: null, matchMode: FilterMatchMode.CONTAINS }
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
			formDelete.delete(route(ruta + '.destroy', id),
				{
					preserveScroll: true,
					onSuccess: () => {
						show('success', 'Eliminado', 'Se ha eliminado')
						setTimeout(() => {
							router.get(route(ruta + '.index'));
						}, 500);

					}
				});
		}
	});
}

const desconectar = (id, name) => {
	const alerta = Swal.mixin({ buttonsStyling: true });
	alerta.fire({
		width: 350,
		title: "Seguro de Desconectar " + name,
		icon: 'question',
		showCancelButton: true,
		confirmButtonText: 'Desconectar',
		cancelButtonText: 'Cancelar',
		cancelButtonColor: 'red',


	}).then((result) => {
		if (result.isConfirmed) {
			formDelete.get(route(ruta + '.desconectar', id),
				{
					preserveScroll: true,
					onSuccess: () => {
						show('success', 'Desconectado', 'Se ha desconectado')
						setTimeout(() => {
							router.get(route(ruta + '.index'));
						}, 500);

					}
				});
		}
	});
}


const refreshToken = (id) => {

	formDelete.get(route(ruta + '.refresh-token', id),
		{
			preserveScroll: true,
			onSuccess: () => {
				show('success', 'Mensaje', 'Token refrescado')
				setTimeout(() => {
					router.get(route(ruta + '.index'));
				}, 500);

			}
		});


}

const conectar = (id) => {
	let url = route(ruta + '.conectar', id);
	window.open(url, '_blank')
};


</script>
<template>

	<Head title="Mercado Libre-Apps" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">

		<div class="px-4 py-3 mb-4 bg-white col-span-12   dark:border-gray-700  dark:bg-gray-800">
			<div class=" px-5 pb-2 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
				<CrearModal></CrearModal>
			</div>
			<div class="align-middle">
				<DataTable size="small" v-model:filters="filters" :value="tabla_datos" :paginator="true" :rows="10"
					:rowsPerPageOptions="[5, 10, 20, 50]"
					paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
					tableStyle="width: 100%">
					<template #header size="small" class="bg-secondary-900">
						<div class="flex justify-content-end text-md">
							<InputText v-model="filters['global'].value" placeholder="Buscar" />
						</div>
					</template>
					<template #empty> No existe Resultado </template>
					<template #loading> Cargando... </template>
					<Column field="nombre" header="Nombre" sortable></Column>
					<Column field="client_id" header="Cliente ID" sortable></Column>
					<Column field="client_secret" header="Cliente Secret"></Column>
					<Column field="access_token" header="Token Acceso"></Column>
					<Column field="expires_at" header="Expira"></Column>
					<Column header="Acciones" style="width:200px">
						<template #body="slotProps">
							<div class="flex justify-end justify-items-center ">
								<span v-if="slotProps.data.usuario == 0"
									class="mx-2 inline-block rounded bg-green-700 p-2 text-xs font-medium text-white mb-0 hover:bg-green-600">
									<button class="h-5" @click.prevent="conectar(slotProps.data.client_id)">Conectar </button>
								</span>
								<div class="flex" v-else>


									<span
										class="mx-2  rounded bg-red-700 p-2 text-xs font-medium text-white mb-0 hover:bg-red-600">
										<button class="h-5 font-semibold"
											@click.prevent="desconectar(slotProps.data.id, slotProps.data.nombre)">Desconectar
										</button>
									</span>
									<span v-if="slotProps.data.is_expired==1"
										class="mx-2 inline-block rounded bg-blue-700 p-2 text-xs font-medium text-white mb-0 hover:bg-blue-600">
										<button class="h-5"
											@click.prevent="refreshToken(slotProps.data.id)">Refrescar token
										</button>
									</span>
								</div>
								<div class="mx-2">
									<span
										class="inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mb-0 hover:bg-primary-100">
										<EditarModal :cliente-id="slotProps.data.id"></EditarModal>
									</span>
								</div>
								<span v-if="slotProps.data.is_default == 0"
									class="mx-2 inline-block rounded bg-red-700 px-2 py-1 text-base font-medium text-white mb-0 hover:bg-red-600">
									<button @click.prevent="eliminar(slotProps.data.id, slotProps.data.nombre)"><i
											class="fas fa-trash-alt"></i></button>
								</span>
							</div>
						</template>
					</Column>
				</DataTable>
			</div>

		</div>

	</AppLayout>
</template>


<style type="text/css" scoped></style>
