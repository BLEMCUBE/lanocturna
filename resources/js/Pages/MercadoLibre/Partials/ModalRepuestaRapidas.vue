<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import InputError from '@/Components/InputError.vue';
import axios from 'axios';
import { useToast } from "primevue/usetoast";
import { useLoaderStore } from "@/stores/loader";
const loader = useLoaderStore();
const toast = useToast();
const titulo = "Respuestas Rapidas"
const ruta = "respuestasrapidas"
const mlruta = "mercadolibre"
const emit = defineEmits(['addTexto']);
import { useCustomToast } from '@/composables/customToast';
const { setShow } = useCustomToast()

//Variables
const isShowModal = ref(false);

const props = defineProps({
	tipo: {
		type: String,
		default: 'pregunta',
	},

});
const form = useForm({
	etiquetas: [],
	saludo: [],
	firma: [],
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
const respuestas = ref([])
const etiqueta = ref('')
const color = ref(null)
const descripcion = ref('')
const colorSeleccionado = ref(null)


//Funciones

const showData = () => {
	//loader.show()
	isShowModal.value = true;

};
const sendTexto = (index) => {
	let texto = respuestas.value[index].descripcion;
	devolverRespuesta(texto, index);
};

function devolverRespuesta(texto, index) {

	emit('addTexto', { respuesta: texto, index: index })
}

const setColor = (item) => {
	colorSeleccionado.value = item.bg
	color.value = item.bg
};

const agregarEtiqueta = () => {
	form.clearErrors()

	if (etiqueta.value.trim().length === 0) {
		show('error', 'Error', 'Por favor ingresá un nombre a la etiqueta.')
	} else if (descripcion.value.trim().length === 0) {
		show('error', 'Error', 'Por favor ingresá la respuesta rápida.')
	} else {
		const existe = form.etiquetas.some(item => item.titulo === etiqueta.value);
		if (existe) {
			show('error', 'Error', 'Ya existe el nombre de la etiqueta.')
		} else {
			form.etiquetas.push(
				{
					id: null,
					titulo: etiqueta.value,
					descripcion: descripcion.value,
					color: color.value,
				}
			)
			show('success', 'Mensaje', 'Respuesta agregada, no te olvides de guardar los cambios.')
			etiqueta.value = null
			descripcion.value = null
			color.value = null
		}
	}

}

const removerEtiqueta = async (index) => {
	const item = form.etiquetas[index];
	form.etiquetas.splice(index, 1);
	if (item.id !== null) {

		try {
			await axios.delete(route(ruta + '.destroy', item.id))
			//Actualizar lista local sin recargar
			//usuarios.value = usuarios.value.filter(u => u.id !== id)
		} catch (error) {
			console.error('Error al eliminar usuario:', error)
		}
	} else {
		return;
	}

}

onMounted(() => {
	dataEdit();
	colorSeleccionado.value = colores[0].bg
	color.value = colores[0].bg
})

const dataEdit = () => {

	axios.get(route(ruta + '.index',props.tipo))
		.then(res => {

			respuestas.value = res.data.respuestas
			var firma = res.data.firma
			var saludo = res.data.saludo
			form.etiquetas = respuestas.value.map(el => ({
				id: el.id,
				titulo: el.titulo,
				tipo:el.tipo,
				descripcion: el.descripcion,
				color: el.color,
			}));

			form.firma.push(firma);
			form.saludo.push(saludo);
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
	form.post(route(ruta + '.update'), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			isShowModal.value = false
			show('success', 'Mensaje', 'Se ha editado')
			setTimeout(() => {
				router.get(route(mlruta + '.preguntas.lista'));
			}, 500);
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
		<button v-for="(item, index) in respuestas" @click.prevent="sendTexto(index)"
			class="px-3 py-1 m-1 text-sm  text-white rounded  text-medium" :style="{ backgroundColor: item.color }">

			{{ item.titulo }}
		</button>


		<button @click.prevent="showData"
			class="w-full bg-gray-200 hover:bg-gray-300 text-xs p-2 rounded mt-3 flex items-center justify-center gap-2">
			<i class="fas fa-pen"></i> Modificar respuestas rápidas
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

			<div class="grid grid-cols-12 gap-4 p-2">
				<div
					class="w-full flex flex-col items-center col-span-12 sm:col-span-3  md:col-span-3  lg:col-span-3  text-white rounded-lg text-center">
					<input type="text" placeholder="ETIQUETA" @input="mayuscula($event)" v-model="etiqueta"
						class="placeholder:text-white text-center text-[16px] border font-normal  h-10 rounded-md w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
						:style="{ backgroundColor: colorSeleccionado || '#ffffff' }" />

					<div class="flex flex-wrap justify-center mt-2">
						<button v-for="(item, i) in colores" :key="i"
							class="m-1 p-2 rounded-full w-4 h-4  border border-gray-300" :class="{
								'scale-110 border-gray-800': colorSeleccionado === item.bg
							}" :style="{
								backgroundColor: item.bg,
								filter: hoverIndex === i ? 'brightness(0.9)' : 'brightness(1)',
							}" @mouseover="hoverIndex = i" @mouseleave="hoverIndex = null" @click="setColor(item)"></button>
					</div>

				</div>
				<div class="col-span-12 sm:col-span-7  md:col-span-7  lg:col-span-7  rounded-lg text-center">
					<textarea v-model="descripcion" rows="3" placeholder="Ingresá la respuesta rápida"
						class="w-full h-[60px] border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
				</div>
				<div class="col-span-12 sm:col-span-2  md:col-span-2  lg:col-span-2 text-white  rounded-lg text-center">
					<button type="button" @click.prevent="agregarEtiqueta"
						class="w-full h-[60px] bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md">
						Agregar
					</button>
				</div>
			</div>
			<form @submit.prevent="submit">
				<p>
					Repuestas agregadas
				</p>
				<div class="col-span-12 p-2 xl:col-span-12" v-if="form.errors.length > 0">
					<InputError class="mt-1 text-lg w-full " :message="form.errors.etiquetas" />
					<InputError v-for="error in form.errors.campos_etiquetas" class="mt-1 mb-0 text-lg"
						:message="error" />
					<InputError v-for="error in form.errors.campos_saludo" class="mt-1 mb-0 text-lg" :message="error" />
					<InputError v-for="error in form.errors.campos_firma" class="mt-1 mb-0 text-lg" :message="error" />
				</div>

				<div class="grid grid-cols-12 gap-4 p-2 mt-3" v-for="(item, index) in form.etiquetas">
					<div
						class="w-full flex flex-col items-center col-span-12 sm:col-span-3  md:col-span-3  lg:col-span-3  text-white rounded-lg text-center">
						<input :key="index" type="text" placeholder="ETIQUETA" v-model="item.titulo"
							class="placeholder:text-gray-500 text-center text-[16px]  uppercase border font-normal  h-8 rounded-md w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
							:style="{ backgroundColor: item.color || '#ffffff' }" />

					</div>
					<div class="col-span-12 sm:col-span-7  md:col-span-7  lg:col-span-7  rounded-lg text-center">
						<textarea v-model="item.descripcion" rows="3" placeholder="Ingresá la respuesta rápida"
							class="w-full h-[60px] border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
					</div>
					<div
						class="col-span-12 sm:col-span-2  md:col-span-2  lg:col-span-2 text-white  rounded-lg text-center">
						<button type="button" @click.prevent="removerEtiqueta(index)"
							class="w-full h-[34px] bg-red-500 hover:bg-red-600 text-white font-medium rounded-md">
							<i class="fas fa-trash-alt"></i>
							Borrar
						</button>
					</div>
				</div>

				<div class="grid grid-cols-12 gap-4 p-2 mt-3">
					<div
						class=" w-full flex flex-col items-start col-span-12 sm:col-span-6  md:col-span-6  lg:col-span-6   rounded-lg text-center">
						<label class="px-2 text-gray-900 font-medium">Saludo inicial:</label>
						<textarea v-model="form.saludo[0].value" rows="3"
							class="w-full h-[60px] border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
						<p class="px-2 text-gray-400 font-medium text-xs">Lo pondremos al inicio de tus respuestas.</p>
						<InputError class="mt-1 text-xs" :message="form.errors.saludo" />
					</div>
					<div
						class="w-full flex flex-col items-start col-span-12 sm:col-span-6  md:col-span-6  lg:col-span-6">
						<label class="px-2 text-gray-900 font-medium">Firma:</label>
						<textarea v-model="form.firma[0].value" rows="3"
							class="w-full h-[60px] border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
						<p class="px-2 text-gray-400 font-medium text-xs">Lo pondremos al final de tus respuestas.</p>
						<InputError class="mt-1 text-xs" :message="form.errors.firma" />
					</div>

				</div>

				<div class="flex justify-end py-3">
					<Button label="Cancelar" :pt="{ root: 'mr-3 py-1' }" severity="danger" size="small"
						@click="closeModal" type="button" />

					<Button label="Guardar" size="small" severity="success" type="submit"
						:class="{ 'opacity-50': form.processing }" :disabled="form.processing" />
				</div>
			</form>
		</Dialog>
	</section>
</template>
