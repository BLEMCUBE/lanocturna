<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from "primevue/usetoast";
import Button from 'primevue/button'
const toast = useToast();
const isShowModal = ref(false);
const titulo = "Producto Masivo"
const ruta = "productos"
const isLoad = ref(false);
const inputArchivo = ref(null);
const errorsFilas = ref();
const isShowModalProducto = ref(false);
const uploadMasivo = useForm({
	archivo: ''
})

//subir excel mercado
const pickFile = (e) => {
	uploadMasivo.clearErrors()
	e.preventDefault();
	uploadMasivo.archivo = e.target.files[0]
}

const closeModalProducto = () => {
	uploadMasivo.reset('archivo');
	isShowModalProducto.value = false;
};

const addCliente = () => {
	isShowModal.value = true;
};

const closeModal = () => {
	inputArchivo.value.value = null //reset input type file
	uploadMasivo.reset('archivo');
	isShowModal.value = false;
};


//descarga formato Excel
const descargarFormatoExcel = (nombre) => {
	if (nombre.length > 0) {
		window.open(route('plantillas.importar', nombre), '_blank');
	} else {

		return;
	}
}
//envio de excel
const submitExcel = () => {

	isLoad.value = true;
	uploadMasivo.clearErrors()
	uploadMasivo.post(route(ruta + '.storemasivo'), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			isLoad.value = false;
			show('success', 'Mensaje', 'Productos creados')
			setTimeout(() => {
				router.get(route(ruta + '.index'));
			}, 1000);
		},
		onFinish: () => {

		},
		onError: (er) => {
			isLoad.value = false;
			inputArchivo.value.value = null
			uploadMasivo.reset('archivo');
			if (er.filas != undefined) {
				if (er.filas.length > 0) {
					errorsFilas.value = er.filas;
					closeModal();
					isShowModalProducto.value = true;
				}
			}
		}
	}
	);
};

const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

</script>

<template>
	<section class="space-y-4">
		<Button size="small" @click="addCliente" type="button" :label="'Crear Producto Masivo'"
			severity="success"></Button>

		<Dialog v-model:visible="isShowModal" modal :header="'Crear ' + titulo" :style="{ width: '40vw' }"
			position="top" :pt="{
				header: {
					class: 'mt-6 px-4 py-2'
				},
				content: {
					class: 'px-4 py-2'
				},
			}">
			<div class="h-8">
				<Button label="Descargar formato" severity="success" type="button"
					class="p-1 text-xs ring-0 font-medium"
					@click="descargarFormatoExcel('formato_crear_masivo_productos.xlsx')"></Button>
			</div>

			<div class="px-2 grid grid-cols-6 gap-2 md:gap-3 mb-2">

				<div class="col-span-12 md:col-span-6 shadow-default lg:col-span-6 mx-2 py-2">
					<InputLabel for="file_input1" value="Importar Excel"
						class="block text-base font-medium leading-6 text-gray-900" />
					<input ref="inputArchivo" @input="pickFile" type="file" class="block w-full text-xs text-gray-500
					file:mr-4 file:py-1 file:px-3
					file:rounded file:border-0
					file:text-sm file:font-medium
					file:bg-primary-900 file:text-white
					hover:file:bg-primary-900/80
					hover:file:cursor-pointer
					file:disabled::opacity-75
					file:disabled:cursor-no-drop
					disabled:opacity-75
					disabled:cursor-no-drop" :disabled="uploadMasivo.processing"
						accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
					<InputError class="mt-1 text-xs" :message="uploadMasivo.errors.archivo" />
				</div>

			</div>
			<div class="flex justify-end py-2">
				<Button label="Cancelar" :pt="{ root: 'mr-5 py-0' }" severity="danger" @click="closeModal"
					type="button"></Button>
				<div class="h-8">
					<Button label="Crear Productos" type="button" class="text-normal"
						:class="{ 'opacity-50': uploadMasivo.processing }" :disabled="uploadMasivo.processing"
						@click.prevent="submitExcel"></Button>
				</div>
			</div>
		</Dialog>

		<!--Modal productos-->
		<Dialog v-model:visible="isShowModalProducto" modal :style="{ width: '40vw' }" :pt="{
			header: {
				class: 'mt-5 pb-2 px-5'
			},
			content: {
				class: 'p-4'
			},
		}">

			<div v-if="errorsFilas.length > 0">

				<p class="mb-2 font-semibold text-md">
					Los siguientes productos ya existen registrado.
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
							<th class="text-center border">
								Código de barra
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
							<td class="text-center border">
								{{ item.codigo_barra }}
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<template #header>
				<div class="flex flex-column align-items-center" style="flex: 1">
					<div class="text-center">
						<i class="pi pi-exclamation-triangle text-yellow-500" style="font-size: 3rem"></i>
					</div>
					<div class="font-semibold text-xl m-3">No se ha podido importar</div>
				</div>
			</template>

			<div class="flex justify-end py-3">
				<Button label="Aceptar" size="small" type="button" @click="closeModalProducto()" />
			</div>
		</Dialog>
		<!--Modal productos-->
	</section>
</template>
