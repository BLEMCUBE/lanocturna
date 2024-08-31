<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, router } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
import Column from 'primevue/column';
import Button from 'primevue/button';
import DetalleModal from '@/Pages/PagosImportacion/DetalleModal.vue';
import AgregarModal from '@/Pages/PagosImportacion/AgregarModal.vue';

const tabla_productos = ref([])
const importacionId = ref(null)
const isShowModalDetalle = ref(false);
const isShowModalAgregar = ref(false);
const titulo = "Pagos Importaciones"
const ruta = 'pagos-importaciones'

onMounted(() => {
	tabla_productos.value = usePage().props.productos.data;

});

const clickDetalle = (e) => {
	importacionId.value = e.data.id;
	isShowModalDetalle.value = true;
}

//descarga excel
const btnDescargar = () => {


		window.open(route(ruta+'.exportar'), '_blank');

}

const filters = ref({
	'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const getInfoDetalle = (obj) => {
	isShowModalDetalle.value = false;
	switch (obj.store) {
		case 'CANCELAR':
			isShowModalDetalle.value = false;
			break;
		case 'AGREGAR':
			importacionId.value = obj.importacionId
			isShowModalAgregar.value = true;
			break;
		case 'ELIMINADO':
			setTimeout(() => {
				router.get(route(ruta + '.index'));
			}, 1000);
			break;
		default:
			break;
	}


}
const getInfoAgregar = (obj) => {
	switch (obj.store) {
		case 'CANCELAR':
			isShowModalAgregar.value = false;
			break;
		case 'AGREGAR':
			break;
		case 'ELIMINADO':
			setTimeout(() => {
				router.get(route(ruta + '.index'));
			}, 1000);
			break;
		default:
			break;
	}


}
</script>
<template>

	<Head :title="titulo" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">
		<div
			class="card p-3 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

			<!--Contenido-->
			<Toast />
			<div class="py-1 px-3 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
				<Button @click.prevent="btnDescargar"
					v-tooltip.top="{ value: `Exportar Excel`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
					:pt="{
						root: { class: 'py-auto px-2.5 py-2 text-lg bg-green-600 border-none hover:bg-green-500' }
					}"><i class="fas fa-file-excel text-white text-lg"></i>
				</Button>
			</div>

			<div class="align-middle  py-1 px-3 ">
				<DataTable :filters="filters" :value="tabla_productos" scrollable scrollHeight="700px" paginator
					:rows="50" columnResizeMode="expand" :pt="{
						bodyRow: { class: 'hover:cursor-pointer hover:bg-gray-100 hover:text-black' },
						root: { class: 'text-base' }
					}" @row-click="clickDetalle" size="small">
					<template #header>
						<div class="flex justify-content-end text-md">
							<div class="w-72">

								<InputText v-model="filters['global'].value" placeholder="Buscar" class="w-full" />
							</div>
						</div>
					</template>
					<template #empty> No existe Resultado </template>
					<template #loading> Cargando... </template>

					<Column field="nro_carpeta" header="No. de Carpeta" sortable :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
					</Column>

					<Column field="nro_contenedor" header="No. de Contenedor" sortable :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
					</Column>
					<Column field="fecha_arribado" sortable header="Fecha Arribado" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">

					</Column>
					<Column field="estado" header="Estado de pedido" sortable :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
					</Column>
					<Column sortable header="%" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
						<template #body="slotProps">
							<span>
								{{ $numberFormat(slotProps.data.porcentaje) }}
							</span>
						</template>
					</Column>
					<Column sortable header="Saldo" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
						<template #body="slotProps">
							<span>
								{{ $numberFormat((slotProps.data.costo_cif - slotProps.data.pagado)) }}
							</span>
						</template>
					</Column>
					<Column sortable header="Total" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
						<template #body="slotProps">
							<span>
								{{ $numberFormat(slotProps.data.costo_cif) }}
							</span>
						</template>
					</Column>

					<!--
	<Column header="Acciones" style="width:130px" :pt="{
		bodycell: { class: 'px-auto text-center' }
	}">


	<template #body="slotProps">
							<div class="text-white flex justify-center items-center" v-if="slotProps.data != undefined">
								<span
									v-tooltip.top="{ value: 'Descargar Excel', pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
									class=" w-8 h-8 rounded bg-green-600 flex justify-center items-center text-base font-semibold text-white mr-1 hover:bg-green-600">
									<a :href="route('importaciones.exportar', slotProps.data.id)" target="_blank"
										class="py-auto"><i class="fas fa-file-excel text-white"></i>
									</a>

								</span>
							</div>
						</template>
					</Column>
					-->
				</DataTable>
				<!--Contenido-->
			</div>
		</div>
		<DetalleModal v-if="isShowModalDetalle" @pass-info="getInfoDetalle" :importacion-id="importacionId"
			:show-detalle="isShowModalDetalle">
		</DetalleModal>
		<AgregarModal v-if="isShowModalAgregar" @pass-info="getInfoAgregar" :importacion-id="importacionId"
			:show-detalle="isShowModalAgregar"></AgregarModal>

	</AppLayout>
</template>


<style type="text/css"></style>
