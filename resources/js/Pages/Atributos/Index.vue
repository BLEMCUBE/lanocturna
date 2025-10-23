<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import CrearModal from '@/Pages/Atributos/Partials/CrearModal.vue';
import EditarModal from '@/Pages/Atributos/Partials/EditarModal.vue';
import DetalleModal from '@/Pages/Atributos/Partials/DetalleModal.vue';
import { FilterMatchMode } from 'primevue/api';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

const tabla_datos = ref()
const confirm = useConfirm();
const toast = useToast();
const titulo = "Atributos"
const ruta = 'atributos'


const formDelete = useForm({
	id: '',
});

onMounted(() => {
	tabla_datos.value = usePage().props.atributos.data;
});


//modal eliminar
const eliminar = (id, name) => {
	confirm.require({
		group: 'templating',
		header: 'Eliminar',
		message: "Seguro de eliminar " + name,
		icon: 'pi pi-exclamation-circle',
		acceptIcon: 'pi pi-check',
		rejectIcon: 'pi pi-times',
		rejectClass: 'bg-red-700 px-2 py-1 text-base border-none font-medium text-white mr-1 b-1 hover:bg-red-600',
		acceptClass: 'button-sm',
		rejectLabel: 'Cancelar',
		acceptLabel: 'Eliminar',
		accept: () => {
			formDelete.delete(route(ruta + '.destroy', id))
			show('success', 'Eliminado', 'Se ha eliminado')
			setTimeout(() => {
				router.get(route(ruta + '.index'));
			}, 500);
		},
		reject: () => {

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
			class="px-4 py-3 mb-4 bg-white col-span-12  lg:col-span-6 rounded-lg shadow-lg 2xl:col-span-6 dark:border-gray-700  dark:bg-gray-800">
			<!--Contenido-->
			<div class=" px-5 pb-2 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
				<CrearModal></CrearModal>
			</div>
			<div class="align-middle">
				<DataTable size="small" v-model:filters="filters" :value="tabla_datos" :paginator="true" :rows="100"
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
							class: 'text-start px-5'
						}
					}"></Column>

					<Column header="Acciones" style="width:80px" :pt="{
						bodyCell: {
							class: 'text-center flex justify-end'
						}
					}">
						<template #body="slotProps">
							<div>
								<span
									class="inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-primary-100">
									<DetalleModal :item-id="slotProps.data.id" :atributo-nombre="slotProps.data.nombre" :atributo-id="slotProps.data.id"></DetalleModal>
								</span>
							</div>
							<div>
								<span
									class="inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-primary-100">
									<EditarModal :item-id="slotProps.data.id"></EditarModal>
								</span>
							</div>
							<div>

								<span v-if="slotProps.data.valores == 0" class="inline-block rounded bg-red-700 px-2 py-1 text-base font-medium text-white mr-1
								mb-1 hover:bg-red-600">
									<button @click.prevent="eliminar(slotProps.data.id, slotProps.data.nombre)"><i
											class="fas fa-trash-alt"></i></button>
								</span>
							</div>

						</template>
					</Column>
				</DataTable>
			</div>
			<ConfirmDialog group="templating">
				<template #message="slotProps">
					<div class="flex flex-column align-items-center w-full gap-3 border-bottom-1 surface-border">
						<i :class="slotProps.message.icon" class="text-6xl text-primary-500"></i>
						<p>{{ slotProps.message.message }}</p>
					</div>
				</template>
			</ConfirmDialog>
			<!--Contenido-->
		</div>

	</AppLAyout>
</template>


<style type="text/css"></style>
