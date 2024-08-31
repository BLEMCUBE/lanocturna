<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from "primevue/usetoast";
import { useFileUpdate } from '@/composition-api/useFileUpdate'

const toast = useToast();

const ruta = "catalogo"

//Variables
const isShowModal = ref(false);

// imagen
const { setFile, previewMap } = useFileUpdate();

// input DOM
const inputDOM = ref(null);

const fileChange = (e) => {
	setFile(e.target.files);
	form.imagen = e.target.files[0]
};

const uploadImages = () => {
	inputDOM.value.click();
};
const limpiarForm = () => {
	setFile([])
	form.reset();
	form.clearErrors()
}

const form = useForm({
	id: '',
	imagen: '',
	nombre: '',
})

const props = defineProps({
	productoId: {
		type: Number,
		default: null,
	},
	nombre: {
		type: String,
		default: "",
	},


});

//Funciones
const addImg = () => {
	dataEdit(props.productoId);

};

const dataEdit = (id) => {
	isShowModal.value = true;
	form.id = id
	form.nombre = props.nombre

};

const closeModal = () => {
	form.reset();
	form.clearErrors()
	isShowModal.value = false;
};

//envio de formulario
const submit = () => {

	form.clearErrors()
	form.post(route(ruta + '.update', form.id), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			isShowModal.value = false
			show('success', 'Mensaje', 'Imagen Actualizada')
			/*setTimeout(() => {
				router.get(route(ruta + '.index'));
			}, 1000);*/
		},
		onFinish: () => {
		},
		onError: () => {

		}
	});

};

const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};


</script>

<template>
	<section>
		<!--Boton-->
		<button type="button" @click="addImg"
			v-tooltip.top="{ value: `Cambiar Imagen`, pt: { text: 'bg-gray-700 p-1 text-[12px] text-white rounded' } }"><i
				class="fas fa-image"></i></button>
		<!--Boton-->

		<Dialog v-model:visible="isShowModal" @hide="limpiarForm" :modal="true" :header="form.nombre"
			:style="{ width: '30rem' }" position="top" :pt="{
				header: {
					class: 'mt-3 py-2 px-3 text-[10px]'
				},
				content: {
					class: 'py-2 px-4 text-base'
				},

				closeButton: {
					class: 'border-none'
				}
			}">
			<form @submit.prevent="submit">
				<div class="px-2 grid grid-cols-6 gap-4 mb-2">

					<div class="col-span-6  text-center xl:col-span-6">
						<div v-show="Object.values(previewMap).length !== 0" class="pb-2 flex justify-center mx-auto"
							v-for="item in previewMap" :key="item">
							<img :src="item" alt="" class="w-auto h-60" />
						</div>
						<input ref="inputDOM" type="file" class="upload" name="imagen" @change="fileChange"
							accept="image/x-png,image/gif,image/jpeg" />
						<button @click.prevent="uploadImages"
							class="text-white bg-green-700 hover:bg-green-600 px-2 py-1 rounded-md text-xs">Seleccionar
							imagen</button>
						<InputError class="mt-0 pt-0 text-xs" :message="form.errors.imagen" />
					</div>

				</div>
				<div class="flex justify-end py-2">
					<Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small"
						@click="closeModal" type="button" />

					<Button label="Enviar" size="small" type="submit" :class="{ 'opacity-50': form.processing }"
						:disabled="form.processing" />
				</div>
			</form>
		</Dialog>
	</section>
</template>

<style scoped>
.upload {
	position: fixed;
	top: -500px;
	left: -500;
	z-index: -100;
	opacity: 0;
}
</style>
