<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from "primevue/usetoast";
import { useLoaderStore } from "@/stores/loader";
const loader = useLoaderStore();
const toast = useToast();
const titulo = "Respuestas Rapidas"
const ruta = "respuestasrapidas"

//Variables
const isShowModal = ref(false);

const form = useForm({
	etiquetas: [],
	saludo: '',
	firma: '',
})
const colores = [
	{ bg: '#ff9191' },
	{ bg: '#ffb15e' },
	{ bg: '#7ad690' },
	{ bg: '#91cdff' },
	{ bg: '#c591ff' },
	{ bg: '#ff91d5' }
]

const hoverIndex = ref(null)
const etiqueta = ref('ETIQUETA')
const color=ref(null)
const descripcion=ref('')
const colorSeleccionado = ref(null)


//Funciones

const addCliente = () => {
	dataEdit();

};

const agregarEtiqueta=()=>{
	if(etiqueta.value.length>0){
	console.log('si')
	}else{
		console.log('no')
			show('error', 'Error', 'Por favor ingresá un nombre a la etiqueta.')
		return;
	}
	if(descripcion.value.length>0){
	console.log('si')
	}else{
		console.log('no')
			show('error', 'Error', 'Por favor ingresá la respuesta rápida.')
		return;
	}
}

onMounted(() => {
	colorSeleccionado.value = colores[0].bg
})

const dataEdit = () => {
	loader.show()
	axios.get(route(ruta + '.index'))
		.then(res => {
			isShowModal.value = true;
			var respuestas = res.data.respuestas
			var firma = res.data.firma
			var saludo = res.data.saludo
			console.log('r ', res.data)
			form.etiquetas = respuestas.map(el => ({
				id: el.id,
				titulo: el.titulo,
				descripcion: el.descripcion,
				color: el.color,
			}));
			/*respuestas.forEach(el => {
				form.etiquetas.push(
					{
						id: el.id,
						titulo: el.titulo,
						descripcion: el.descripcion,
						color: el.color,

					}
				)
			});*/
			form.firma = firma
			form.saludo = saludo
		}).finally(() => {
			loader.hide()
		})


};

const closeModal = () => {
	form.reset();
	form.clearErrors()
	isShowModal.value = false;
};


//envio de formulario
const submit = () => {

	form.clearErrors()
	form.post(route(ruta + '.updateRol', form.id), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			isShowModal.value = false
			show('success', 'Mensaje', 'Se ha editado')
			setTimeout(() => {
				router.get(route(ruta + '.index'));
			}, 1000);
		},
		onFinish: () => {
		},
		onError: () => {

		}
	});

};

const mayuscula = (event) => {
	etiqueta.value = event.target.value.toUpperCase();
};

const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 4000 });
};
</script>

<template>
	<section>
		<button @click="addCliente"
			class="w-full bg-gray-200 hover:bg-gray-300 text-xs p-2 rounded mt-3 flex items-center justify-center gap-2">
			<i class="fas fa-pen"></i> Respuestas rápidas
		</button>

		<Dialog v-model:visible="isShowModal" modal :header="titulo" :style="{ width: '70rem' }"
			:breakpoints="{ '1199px': '75vw', '575px': '90vw' }" position="top" :pt="{
				header: {
					class: 'mt-6 p-2 lg:p-4 '
				},
				content: {
					class: 'p-4 lg:p-4'
				},
			}">

			<div class="grid grid-cols-12 gap-6 p-2">
				<div
					class="w-full flex flex-col items-center col-span-12 sm:col-span-3  md:col-span-3  lg:col-span-3  text-white rounded-lg text-center">
					<input type="text" placeholder="ETIQUETA" @input="mayuscula($event)" v-model="etiqueta"
						class="placeholder:text-gray-500 text-center text-[16px] border font-normal  border-gray-300 h-10 rounded-md w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
						:style="{ backgroundColor: colorSeleccionado || '#ffffff' }" />

					<div class="flex flex-wrap justify-center mt-2">
						<button v-for="(item, i) in colores" :key="i"
							class="m-1 p-2 rounded-full w-4 h-4  border border-gray-300" :class="{
								'scale-110 border-gray-800': colorSeleccionado === item.bg
							}" :style="{
								backgroundColor: item.bg,
								filter: hoverIndex === i ? 'brightness(0.9)' : 'brightness(1)',
							}" @mouseover="hoverIndex = i" @mouseleave="hoverIndex = null" @click="colorSeleccionado = item.bg"></button>
					</div>

				</div>
				<div class="col-span-12 sm:col-span-7  md:col-span-7  lg:col-span-7  rounded-lg text-center">
					<textarea v-model="descripcion" rows="3" placeholder="Ingresá la respuesta rápida"
						class="w-full h-[60px] border border-gray-300 rounded-md p-3 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
				</div>
				<div class="col-span-12 sm:col-span-2  md:col-span-2  lg:col-span-2 text-white  rounded-lg text-center">
					<button type="button" @click.prevent="agregarEtiqueta"
						class="w-full h-[60px] bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md">
						Agregar
					</button>
				</div>
			</div>
			<form @submit.prevent="submit">



				<div class="px-2 grid grid-cols-6 gap-4 md:gap-3 2xl:gap-6 mb-2">
					<div class="col-span-6 shadow-default xl:col-span-6 " v-for="item in form.etiquetas">
						<InputLabel for="etiqueta" value="Etiqueta"
							class="block text-base font-medium leading-6 text-gray-900" />
						<input type="text" v-model="item.titulo"
							class="p-inputtext p-component h-9 w-full font-sans  font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1">
						<InputError class="mt-1 text-xs" :message="form.errors.titulo" />
					</div>

				</div>
				<div class="flex justify-end py-3">
					<Button label="Cancelar" :pt="{ root: 'mr-3 py-1' }" severity="danger" size="small"
						@click="closeModal" type="button" />

					<Button label="Guardar" size="small" type="submit" :class="{ 'opacity-50': form.processing }"
						:disabled="form.processing" />
				</div>
			</form>
		</Dialog>
	</section>
</template>
