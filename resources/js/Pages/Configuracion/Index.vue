<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from "primevue/usetoast";
import { FireIcon } from '@heroicons/vue/20/solid';

const titulo = "Datos Web"
const toast = useToast();
const { lista_configuracion } = usePage().props
const ruta = "configuraciones"

const formDatos = useForm({
	config: []
})

const cancelCrear = () => {
	router.get(route('inicio'))
};

//envio de formulario
const submit = () => {

	formDatos.post(route(ruta + '.updateweb'), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			show('success', 'Mensaje', 'Editado')
			setTimeout(() => {
				router.get(route('inicio'));
			}, 1000);
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
onMounted(() => {
	formDatos.config = lista_configuracion
});



</script>
<template>
	<div>

		<Head :title="titulo" />
		<AppLayout :pagina="[{ 'label': titulo, link: false }]">

			<div
				class="card p-4 mb-4 bg-white col-span-12  rounded-lg shadow-lg lg:col-span-6 dark:border-gray-700  dark:bg-gray-800">
				<form @submit.prevent="submit">
					<!--Contenido-->
					<div class="p-1 col-span-full flex justify-between items-center">
						<h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">{{ titulo }}</h1>
					</div>
					<div class="pb-2 bg-white col-span-4 lg:col-span-12 rounded-lg shadow-sm"
						v-for="(item, index) in formDatos.config">

						<div :key="index" class="col-span-6 mt-3 shadow-default xl:col-span-6">
							<InputLabel :for="formDatos.config[index].slug" :value="formDatos.config[index].key"
								class="text-base font-bold leading-6 text-gray-900" />
							<input v-if="formDatos.config[index].type == 'text'"
								class="mt-2 shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-md rounded p-1 block w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white  dark:focus:border-primary-500"
								:id="formDatos.config[index].key" type="text" name="" id=""
								v-model="formDatos.config[index].value" />

							<textarea v-if="formDatos.config[index].type == 'textarea'"
								v-model="formDatos.config[index].value" rows="2"
								class="w-full mt-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-2"></textarea>

						</div>
					</div>
					<div class="flex justify-end py-1">
						<Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small"
							@click="cancelCrear" type="button" />
						<Button label="Guardar" size="small" type="submit"
							:class="{ 'opacity-50': formDatos.processing }" :disabled="formDatos.processing" />
					</div>
				</form>
			</div>

		</AppLayout>

	</div>
</template>


<style type="text/css" scoped></style>
