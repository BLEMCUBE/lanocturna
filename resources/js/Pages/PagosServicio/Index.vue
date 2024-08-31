<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, router,useForm } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
import Column from 'primevue/column';
import Button from 'primevue/button';
import CrearModal from '@/Pages/PagosServicio/Partials/CrearModal.vue';
import EditarModal from '@/Pages/PagosServicio/Partials/EditarModal.vue';
import Swal from 'sweetalert2';
import { useToast } from "primevue/usetoast";
const { permissions } = usePage().props.auth
const tabla_items = ref([])
const toast = useToast();
const titulo = "Pagos"
const ruta = 'pago-servicio'
const formDelete = useForm({
	id: '',
});


onMounted(() => {
	tabla_items.value = usePage().props.items.data;
});

//descarga excel
const btnDescargar = () => {


	window.open(route(ruta + '.exportar'), '_blank');

}

const filters = ref({
	'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};
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
</script>
<template>

	<Head :title="titulo" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">
		<div
			class="card p-3 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
			<!--Contenido-->
			<Toast />

			<div class=" px-2.5 pb-2 grid grid-cols-12 gap-4">
				<div class="py-1 px-3 col-span-12 sm:col-span-8 flex justify-between items-center">
					<h5 class="text-2xl font-medium">{{ titulo }}</h5>
				</div>

				<div class="ml-5 flex justify-end col-span-12 sm:col-span-4 ">
					<CrearModal></CrearModal>
					<Button @click.prevent="btnDescargar"
						v-tooltip.top="{ value: `Exportar Excel`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
						:pt="{
							root: { class: 'py-auto px-2.5 ml-5 py-2 text-lg bg-green-600 border-none hover:bg-green-500' }
						}"><i class="fas fa-file-excel text-white text-lg"></i>
					</Button>
				</div>
			</div>
			<div class="align-middle  py-1 px-3 ">
				<DataTable :filters="filters" :value="tabla_items" scrollable scrollHeight="700px" paginator :rows="50"
					columnResizeMode="expand" :pt="{
						bodyRow: { class: 'hover:bg-gray-100 hover:text-black' },
						root: { class: 'text-base' }
					}" size="small">
					<template #header>
						<div class="flex justify-content-end text-md">
							<div class="w-72">
								<InputText v-model="filters['global'].value" placeholder="Buscar" class="w-full" />
							</div>
						</div>
					</template>
					<template #empty> No existe Resultado </template>
					<template #loading> Cargando... </template>
					<Column field="fecha_pago" sortable header="Fecha" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
					</Column>
					<Column field="nro_factura" header="No. de Factura" sortable :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
					</Column>
					<Column field="moneda" header="Moneda" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
					</Column>

					<Column sortable header="Monto" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
						<template #body="slotProps">
							<span>
								{{ $numberFormat(slotProps.data.monto) }}
							</span>
						</template>
					</Column>

					<Column sortable header="Concepto" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
						<template #body="slotProps">
							<span>
								{{ slotProps.data.concepto }}
							</span>
						</template>
					</Column>
					<!--
						<Column sortable header="Agregado por" :pt="{
							bodyCell: { class: 'text-center' },
							headerTitle: { class: 'text-center' },
						}">
						<template #body="slotProps">
							<span>
								{{ slotProps.data.usuario }}
							</span>
						</template>
					</Column>
					-->
					<Column field="observacion" header="Observación" :pt="{
						bodyCell: { class: 'text-center' },
						headerTitle: { class: 'text-center' },
					}">
					</Column>
					<Column header="Acciones" style="width:100px" :pt="{
						bodyCell: {
							class: 'text-center'
						}
					}">
						<template #body="slotProps">
							<div class="flex justify-end justify-items-center" >
								<div v-if="permissions.includes('editar-pagoservicio')"
									class="h-8 inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-primary-100">
									<EditarModal :item-id="slotProps.data.id"></EditarModal>
								</div>
								<div v-if="permissions.includes('eliminar-pagoservicio')"
								class="h-8 inline-block rounded bg-red-700 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-red-600">
								<button @click.prevent="eliminar(slotProps.data.id, slotProps.data.nro_factura)"><i
									class="fas fa-trash-alt"></i></button>
								</div>
							</div>

						</template>
					</Column>

				</DataTable>
				<!--Contenido-->
			</div>
		</div>

	</AppLayout>
</template>


<style type="text/css"></style>
