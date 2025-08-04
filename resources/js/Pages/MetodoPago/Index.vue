<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import CrearModal from '@/Pages/MetodoPago/Partials/CrearModal.vue';
import EditarModal from '@/Pages/MetodoPago/Partials/EditarModal.vue';

import { FilterMatchMode } from 'primevue/api';
const tabla_items = ref()
import { useToast } from "primevue/usetoast";
const toast = useToast();
const titulo = "Métodos de Pago"
const ruta = 'metodo-pago'


const formDelete = useForm({
	id: '',
});

onMounted(() => {
	tabla_items.value = usePage().props.items.data;
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
		confirmButtonColor: '#2563EB',

	}).then((result) => {
		if (result.isConfirmed) {
			formDelete.delete(route(ruta + '.destroy', id),
				{
					preserveScroll: true,
					onSuccess: () => {
						show('success', 'Eliminado', 'Se ha eliminado')
						setTimeout(() => {
							router.get(route(ruta + '.index'));
						}, 1000);

					}
				});
		}
	});
}


const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const filters = ref({
	global: { value: null, matchMode: FilterMatchMode.CONTAINS },

});
</script>
<template>

	<Head :title="titulo" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">
		<div
			class="px-4 py-3 mb-4 bg-white col-span-12  lg:col-span-5 rounded-lg shadow-lg 2xl:col-span-5 dark:border-gray-700  dark:bg-gray-800">
			<!--Contenido-->
			<Toast />
			<div class=" px-5 pb-2 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
				<CrearModal></CrearModal>
			</div>
			<div class="align-middle">
				<DataTable size="small" v-model:filters="filters" :value="tabla_items" :paginator="true" :rows="100"
					paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
					tableStyle="width: 100%">
					<template #header size="small" class="bg-secondary-900">
						<div class="flex justify-content-end text-md">
							<InputText v-model="filters['global'].value" placeholder="Buscar" />
						</div>
					</template>
					<template #empty> No existe Resultado </template>
					<template #loading> Cargando... </template>
					<Column field="nombre" header="Nombre" sortable :pt="{
						bodyCell: {
							class: 'text-center'
						}
					}"></Column>
					<Column header="Acciones" style="width:100px" :pt="{
						bodyCell: {
							class: 'text-center'
						}
					}">
						<template #body="slotProps">
							<div class="flex justify-end justify-items-center">
								<div
									class="h-8 inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-primary-100">
									<EditarModal :cliente-id="slotProps.data.id"></EditarModal>
								</div>
								<div v-if="slotProps.data.pagos==false"
								class="h-8 inline-block rounded bg-red-700 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-red-600">
								<button @click.prevent="eliminar(slotProps.data.id, slotProps.data.nombre)"><i
									class="fas fa-trash-alt"></i></button>
								</div>
							</div>

						</template>
					</Column>
				</DataTable>
			</div>

			<!--Contenido-->
		</div>

	</AppLAyout>
</template>


<style type="text/css"></style>
