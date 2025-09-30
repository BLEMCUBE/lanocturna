<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from "primevue/usetoast";
import { useLoaderStore } from "@/stores/loader";
import CrearEditarValores from '@/Pages/Atributos/Partials/CrearEditarValores.vue';
import { FilterMatchMode } from 'primevue/api';
import { useConfirm } from "primevue/useconfirm";
//Variables
const loader = useLoaderStore();
const toast = useToast();
const confirm = useConfirm();
const titulo = "Atributo"
const ruta = "atributos-valores"
const rutaAtributo = "atributos"
const isShowModal = ref(false);
const formDelete = useForm({
	id: '',
});
const form = useForm({
	id: '',
	nombre: '',
	valores: []
})

const props = defineProps({
	itemId: {
		type: Number,
		default: null,
	},
	atributoId: {
		type: Number,
		default: null,
	},
	atributoNombre: {
		type: String,
		default: null,
	},


});

//Funciones

const addCliente = () => {
	dataEdit(props.itemId);

};

const updateData = (obj) => {
	dataEdit (obj.atributoId)
};

const dataEdit = (id) => {
	loader.show()
	axios.get(route(ruta + '.index', id))
		.then(res => {
			isShowModal.value = true;
			var datos = res.data.atributo
			form.id = datos.id
			form.nombre = datos.nombre
			form.valores = datos
		}).finally(() => {

			loader.hide()
		})


};

//modal eliminar
const eliminar = (id, name) => {
	confirm.require({
		group: 'templating1',
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
				//router.get(route(ruta + '.index'));
				dataEdit(props.itemId);
			}, 500);
		},
		reject: () => {

		}
	});

}

const closeModal = () => {
	form.reset();
	form.clearErrors()
	isShowModal.value = false;
};

const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const filters = ref({
	global: { value: null, matchMode: FilterMatchMode.CONTAINS },

});

</script>

<template>
	<section>
		<button type="button" @click="addCliente">Valores</button>
		<Dialog v-model:visible="isShowModal" modal :header="'Detalle ' + titulo" :style="{ width: '30vw' }"
			:breakpoints="{ '1199px': '75vw', '575px': '90vw' }" position="top" :pt="{
				header: {
					class: 'mt-6 p-2 lg:p-4 '
				},
				content: {
					class: 'p-4 lg:p-4'
				},
			}">

			<div class="w-full flex flex-col md:flex-row py-1">
				<div class="w-full md:w-1/3 xl:w-1/3 mr-2">
					<h3 class="font-semibold text-gray-800 text-base"> Atributo:</h3>
				</div>
				<div class="w-full md:w-2/3">
					<h3 class="font-normal text-gray-800 text-base">{{ props.atributoNombre ?? '-' }}
					</h3>
				</div>
			</div>
			<div class=" px-5 pb-2 col-span-full flex justify-between items-center">
				<CrearEditarValores @updated="updateData" :atributo-id="props.atributoId"></CrearEditarValores>
			</div>
			<div class="align-middle">
				<DataTable size="small" v-model:filters="filters" :value="form.valores" :paginator="true" :rows="10"
					paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
					tableStyle="width: 100%">
					<template #header size="small" class="bg-secondary-900">
						<div class="flex justify-content-end text-md">
							<InputText v-model="filters['global'].value" placeholder="Buscar" />
						</div>
					</template>
					<template #empty> No existe Resultado </template>
					<template #loading> Cargando... </template>

					<Column field="valor" header="Nombre" sortable :pt="{
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
									<CrearEditarValores @updated="updateData"  :item-id="slotProps.data.id" :atributo-id="slotProps.data.atributo_id" :atributo-nombre="slotProps.data.nombre"></CrearEditarValores>
								</span>
							</div>
							<div>
								<span v-if="slotProps.data.productos == 0" class="inline-block rounded bg-red-700 px-2 py-1 text-base font-medium text-white mr-1
								mb-1 hover:bg-red-600">
									<button @click.prevent="eliminar(slotProps.data.id, slotProps.data.valor)"><i
											class="fas fa-trash-alt"></i></button>
								</span>
							</div>
						</template>
					</Column>
				</DataTable>
				<div class="flex justify-end py-3">
					<Button label="Aceptar"
						:pt="{ root: 'mr-5 py-1 inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-primary-100' }"
						size="small" @click="closeModal" type="button" />

				</div>

			</div>
			<ConfirmDialog group="templating1">
				<template #message="slotProps">
					<div class="flex flex-column align-items-center w-full gap-3 border-bottom-1 surface-border">
						<i :class="slotProps.message.icon" class="text-6xl text-primary-500"></i>
						<p>{{ slotProps.message.message }}</p>
					</div>
				</template>
			</ConfirmDialog>
		</Dialog>

	</section>
</template>
