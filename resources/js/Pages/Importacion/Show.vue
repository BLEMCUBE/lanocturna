<template>

	<Head :title="titulo" />
	<AppLayout
		:pagina="[{ 'label': 'Importaciones', link: true, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
		<div
			class="card px-4 mb-0 bg-white col-span-12 justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
			<!--Contenido-->
			<div class="mb-5 px-3 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
			</div>
			<div class="px-0 py-1 m-2 mt-0 text-white  col-span-full  flex justify-end items-center">
				<span
					v-tooltip.top="{ value: 'Descargar Formato', pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
					class="px-3 py-1.5 w-auto h-8 rounded bg-yellow-500 flex justify-center items-center text-xs font-normal text-black mr-2 hover:bg-yellow-600">
					<a :href="route('importaciones.exportarcostoreal', importacion.id)" target="_blank"
						class="py-auto"><i class="fa-solid fa-file-arrow-down mr-1"></i>
						Descargar formato
					</a>
				</span>
				<Button
					v-tooltip.top="{ value: 'Subir Costo Real', pt: { text: 'bg-gray-500 p-1  text-xs text-white rounded' } }"
					size="small" @click="importCosto" icon="fa-solid fa-file-arrow-up" type="button"
					:label="'Subir Costo Real'"
					:pt="{ root: 'ouline-none bg-green-600 mr-2 border-0 h-8 hover:bg-green-700 text-white', label: 'text-xs font-normal' }">

				</Button>
				<span
					class="inline-block rounded bg-primary-900 px-2 my-0 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-primary-100">
					<EditarModal :clienteId="importacion.id">

					</EditarModal>
				</span>
			</div>

			<div class="grid grid-cols-6 my-2 col-span-12 ">
				<div class="mx-5 col-span-6 gap-2 m-1 lg:col-span-3 flex">
					<b> No. de Carpeta:</b>
					<p>{{ importacion.nro_carpeta }}</p>
				</div>
				<div class="mx-5 col-span-6 gap-2 m-1 lg:col-span-3 flex">
					<b> BL o No. de Contenedor:</b>
					<p>{{ importacion.nro_contenedor }}</p>
				</div>
				<div class="mx-5 col-span-6 gap-2 m-1 lg:col-span-3 flex">
					<b> Estado:</b>
					<p>{{ importacion.estado }}</p>
				</div>
				<div class="mx-5 col-span-6 gap-2 m-1 lg:col-span-3 flex">
					<b> Mueve Stock:</b>
					<p>{{ (importacion.mueve_stock) ? ' SI ' : ' NO ' }}</p>
				</div>
				<div class="mx-5 col-span-6 gap-2 m-1 lg:col-span-3 flex">
					<b>Total:</b>
					<p>{{ importacion.total }}</p>
				</div>
				<div class="mx-5 col-span-6 gap-2 m-1 lg:col-span-3 flex">
					<b>Fecha En Camino :</b>
					<p>{{ moment(importacion.fecha_camino).format('DD/MM/YYYY') }}</p>
				</div>
				<div class="mx-5 col-span-6 gap-2 m-1 lg:col-span-3 flex">
					<b>Fecha Arribado :</b>
					<p>{{ moment(importacion.fecha_arribado).format('DD/MM/YYYY') }}</p>
				</div>
				<div class="mx-5 col-span-6 gap-2 m-1 lg:col-span-3 flex">
					<b>Cantidad productos :</b>
					<p>{{ importacion_detalle.length }}</p>
				</div>
				<div class="mx-5 col-span-6 gap-2 m-1 lg:col-span-3 flex">
					<b>Costo CIF :</b>
					<p>{{ importacion.costo_cif }}</p>
				</div>

			</div>

			<div class="bg-white mr-0 ml-0 mt-6  shadow rounded-lg border border-gray-300">
				<div class="bg-gray-100 rounded-t-lg py-2 px-3">
					<h2 class="text-2xl font-medium pb-0 pl-2">Productos ({{ importacion_detalle.length }}) </h2>
				</div>
				<div class="bg-gradient-to-r from-primary-900 to-primary-100  h-1 mb-0"></div>
				<!-- Línea con gradiente -->
				<div class="align-middle">

					<DataTable sortField="id" :sortOrder="1" :filters="filters" :value="import_detalle" scrollable
						scrollHeight="700px" resizableColumns columnResizeMode="expand" paginator :rows="50"
						@row-click="clickDetalle" :pt="{
							bodyRow: { class: 'hover:cursor-pointer hover:bg-gray-100 hover:text-black' },

						}" size="small">
						<template #header>
							<div class="flex justify-content-end text-md">
								<InputText v-model="filters['global'].value" placeholder="Buscar" />
							</div>
						</template>
						<template #empty> No existe Resultado </template>
						<template #loading> Cargando... </template>
						<Column field="sku" header="Origen" sortable :pt="{
							bodyCell: {
								class: 'text-center'
							},
							headerContent: { class: 'break-words ' }

						}"></Column>
						<Column field="imagen" header="Referencia" :pt="{
							bodyCell: {
								class: 'flex items-center'
							},
							bodyCellContent: {
								class: 'flex items-center'
							}

						}">
							<template #body="slotProps">

								<div class="flex  items-center">
									<div class="w-20">
										<img class="rounded  bg-white shadow-2xl text-center w-20 h-14 object-contain mr-2"
											:src="slotProps.data.imagen" alt="image">
									</div>
									<p class="text-xs text-center flex-wrap">{{ slotProps.data.nombre }}</p>
								</div>
							</template>
						</Column>

						<Column field="precio" header="Precio" sortable :pt="{
							bodyCell: {
								class: 'text-center'
							}
						}"></Column>
						<Column field="unidad" header="Unidad" sortable :pt="{
							bodyCell: {
								class: 'text-center'
							}
						}"></Column>
						<Column field="pcs_bulto" header="PCS Bulto" sortable :pt="{
							bodyCell: {
								class: 'text-center'
							},
							headerContent: {
								class: 'text-center break-all'
							}

						}"></Column>
						<Column field="bultos" sortable header="Bultos" :pt="{
							bodyCell: {
								class: 'text-center'
							},
							headerCell: {
								class: 'text-center'
							}
						}"></Column>
						<Column field="cantidad_total" sortable header="Cantidad Total" :pt="{
							bodyCell: {
								class: 'text-center'
							}
						}"></Column>
						<Column field="valor_total" sortable header="Valor Total" :pt="{
							bodyCell: {
								class: 'text-center'
							}
						}"></Column>
						<Column field="cbm_bulto" sortable header="CBM/Bulto" :pt="{
							bodyCell: {
								class: 'text-center'
							},
							headerContent: {
								class: 'text-center break-all'
							}
						}"></Column>
						<Column field="cbm_total" sortable header="Total CBM" :pt="{
							bodyCell: {
								class: 'text-center'
							}
						}"></Column>

						<Column :pt="{
							bodyCell: {
								class: 'hidden'
							},
							headerCell: {
								class: 'hidden'
							}
						}">

							<template #body="slotProps">

								<EditarProductoModal :clienteId="slotProps.data.id"></EditarProductoModal>
							</template>
						</Column>
					</DataTable>

				</div>
			</div>


			<!--Contenido-->
			<!--Modal productos-->
	<Dialog v-model:visible="isShowModalProducto" modal :style="{ width: '30vw' }" :pt="{
				header: {
					class: 'mt-5 pb-2 px-5'
				},
				content: {
					class: 'p-4'
				},
			}">
				<p class="mb-2 font-bold text-md">
					Listado
				</p>

				<table class="w-full border">
					<thead>
						<tr class="w-full border">
							<th class="w-26 text-center border">
								Fila
							</th>
							<th class="text-center border">
								Sku
							</th>
						</tr>
					</thead>
					<tbody>
						<tr class="w-full text-center border" v-for="item in errorsFilas">
							<td class="text-center border">
								{{ item.fila }}
							</td>
							<td class="text-center border">
								{{ item.sku }}
							</td>
						</tr>
					</tbody>
				</table>

				<template #header>
					<div class="flex flex-column align-items-center" style="flex: 1">
						<div class="text-center">
							<i class="pi pi-exclamation-triangle text-yellow-500" style="font-size: 3rem"></i>
						</div>
						<div class="font-bold text-2xl m-3">No se ha podido importar</div>
					</div>
				</template>


				<div class="flex justify-end py-3">
					<Button label="Aceptar" size="small" type="button" @click="closeModalProducto" />

				</div>

			</Dialog>
			<!--Modal productos-->

	<Dialog v-model:visible="modalImport" modal :header="'Subir Costo Real'" :style="{ width: '30vw' }" position="top"
		:pt="{
			header: {
				class: 'mt-6 p-2 lg:p-4 '
			},
			content: {
				class: 'p-4 lg:p-4'
			},
		}">
		<form @submit.prevent="submitImport">
			<div class="px-2 grid grid-cols-6 gap-4 md:gap-3 2xl:gap-6 mb-2">


						<div class="col-span-12 shadow-default lg:col-span-12">
							<InputLabel for="file_input1" value="Archivo Excel"
								class="block text-base font-medium leading-6 text-gray-900" />
							<input ref="inputArchivo" @input="pickFile" type="file" class="block w-full text-xs text-gray-500
                                file:mr-4 file:py-1 file:px-2
                                file:rounded file:border-0
                                file:text-xs file:font-medium
                                file:bg-gray-200 file:text-gray-700
                                hover:file:bg-gray-300
                                hover:file:cursor-pointer
                                "
								accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
							<InputError class="mt-1 text-xs" :message="formImport.errors.archivo" />
						</div>


			</div>
			<div class="flex justify-end py-3">
				<Button label="Cancelar" :pt="{ root: 'mr-5 py-1 text-xs font-normal' }" severity="danger" size="small" @click="closeModal"
					type="button" />

				<Button label="Subir" :pt="{ root: 'mr-5 py-1 text-xs font-normal' }" size="small" type="submit" :class="{ 'opacity-50': formImport.processing }"
					:disabled="formImport.processing" />
			</div>
		</form>
	</Dialog>
		</div>


	</AppLayout>

</template>
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, router, useForm } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
import moment from 'moment';
import Button from 'primevue/button'
import EditarModal from '@/Pages/Importacion/EditarModal.vue';
import EditarProductoModal from '@/Pages/Importacion/EditarProductoModal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useToast } from "primevue/usetoast";
const toast = useToast();
const { importacion } = usePage().props
const { importacion_detalle } = usePage().props
const titulo = "Detalle Importación"
const ruta = 'importaciones'
const modalImport = ref(false);
const isShowModalProducto = ref(false);
const inputArchivo = ref(null);
const errorsFilas = ref();
const import_detalle = ref()
const filters = ref({
	'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const closeModalProducto = () => {
	//inputArchivo.value.value = null
	formImport.reset('archivo');
	isShowModalProducto.value = false;
};
const pickFile = (e) => {
	e.preventDefault();
	formImport.archivo = e.target.files[0]
}
const closeModal = () => {
    formImport.reset();
    formImport.clearErrors()
    modalImport.value = false;
};
const formImport = useForm({
	archivo: '',
	importacion_id: importacion.id

})
const importCosto = () => {
	modalImport.value = true;
};


//envio de formulario
const submitImport = () => {
	formImport.clearErrors()
	formImport.post(route(ruta + '.costoreal'), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			modalImport.value = false;
			show('success', 'Mensaje', 'Costo real creado')
			setTimeout(() => {
				//router.get(route(ruta + '.index'));
				location.reload();
			}, 1000);
		},
		onFinish: () => {

		},
		onError: (er) => {
			if (er.filas != undefined) {
				modalImport.value=false;
				if (er.filas.length > 0) {
					//errorsFilas.value = er.filas.slice(1);
					errorsFilas.value = er.filas;
					isShowModalProducto.value = true;
				}
			}
			if (er.error_sku != undefined) {
				modalImport.value=false;
				if (er.error_sku.length > 0) {
					//errorsFilas.value = er.filas.slice(1);
					errorsFilas.value = er.error_sku;
					isShowModalProducto.value = true;
				}
			}
		}
	});
};


const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};
onMounted(() => {
	import_detalle.value = Array.from(importacion_detalle, (x) => x);
});

const clickDetalle = (e) => {
	document.getElementById("show-" + e.data.id).click();
	//btnVer(e.data.id)
}
</script>
<style type="text/css" scoped></style>
