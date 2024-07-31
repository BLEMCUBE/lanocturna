<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

import { FilterMatchMode } from 'primevue/api';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";
const toast = useToast();
const tabla_productos = ref([])
const { permissions } = usePage().props.auth
const titulo = "Pagos Importaciones"
const ruta = 'pagos-importaciones'

onMounted(() => {
	tabla_productos.value = usePage().props.productos.data;

});

const btnVer = (id) => {
	router.get(route(ruta + '.show', id));

};

const clickDetalle = (e) => {
	btnVer(e.data.id)
}

//descarga excel
const btnDescargar = () => {

	if (date.value[0] != null && date.value[1] != null) {
		window.open(route('rotacion-stock.exportproductoventas', [
			{
				'categoria': categorias.value,
				'inicio': date.value[0],
				'fin': date.value[1]
			}]), '_blank');
	} else {

		return;
	}
}

const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const ok = (icono, mensaje) => {

	Swal.fire({
		width: 350,
		title: mensaje,
		icon: icono
	})
}

const filters = ref({
	'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
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


				<Button @click="btnDescargar"
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

			</div>
			<!--Contenido-->

		</div>

	</AppLayout>
</template>


<style type="text/css"></style>
