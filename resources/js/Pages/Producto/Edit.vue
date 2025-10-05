<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Multiselect from '@vueform/multiselect';

const previewImage = ref('/images/productos/sin_foto.png');
const toast = useToast();
const { permissions } = usePage().props.auth;
const { atributos } = usePage().props
const { lista_atributos } = usePage().props
const { lista_valores } = usePage().props
const expandedRows = ref([]);
const titulo = "Editar Producto"
const ruta = 'productos'
const showAtributo = ref(false)
const form = useForm({
	id: '',
	origen: '',
	nombre: '',
	aduana: '',
	codigo_barra: '',
	stock: 0,
	precio: 0,
	stock_minimo: 0,
	imagen: '',
	photo: '',
	costo_origen: null,
	costo_fecha: null,
	costo_real: 0,
	costo_id: null,
	categorias: [],
	atributos: []
})

onMounted(() => {
	lista_categorias.value.options = usePage().props.lista_categorias
	var datos = usePage().props.producto;
	form.id = datos.id
	form.nombre = datos.nombre
	form.origen = datos.origen
	form.aduana = datos.aduana
	form.codigo_barra = datos.codigo_barra
	form.precio = datos.precio
	form.stock = datos.stock
	form.stock_minimo = datos.stock_minimo
	form.stock_futuro = datos.stock_futuro
	form.costo_real = datos.costos_reales.length > 0 ? datos.costos_reales[0].monto : 0
	form.costo_origen = datos.costos_reales.length > 0 ? datos.costos_reales[0].origen : null
	form.costo_id = datos.costos_reales.length > 0 ? datos.costos_reales[0].id : null
	form.costo_fecha = datos.costos_reales.length > 0 ? datos.costos_reales[0].fecha : null
	form.atributos = atributos

	if (datos.categorias.length > 0) {
		datos.categorias.forEach((ele) => {
			form.categorias.push(ele.id)
		})
	}
	previewImage.value = datos.imagen
	form.imagen = datos.imagen

});

//envio de formulario
const submit = () => {

	form.clearErrors()
	form.post(route(ruta + '.update', form.id), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			show('success', 'Mensaje', 'Producto Actualizado')
			setTimeout(() => {
				router.get(route(ruta + '.show', form.id));
			}, 1000);
		},
		onFinish: () => {
		},
		onError: () => {
		}
	});

};

const lista_categorias = ref({
	value: '',
	closeOnSelect: true,
	placeholder: "Categorías",
	mode: 'tags',
	searchable: true,
	options: [],
});

const setStock = (e) => {
	if (e.target.value.length > 0)
		form.stock_futuro = parseFloat(form.stock_futuro) + parseFloat(e.target.value);
}

const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const cancelCrear = () => {
	router.get(route(ruta + '.index'))
};

const pickFile = (e) => {
	e.preventDefault();
	form.photo = e.target.files[0]
	let file = e.target.files
	if (file && file[0]) {
		let reader = new FileReader
		reader.onload = e => {
			previewImage.value = e.target.result
		}
		reader.readAsDataURL(file[0])
	}
}


const expandEvent = (event) => {
	//expandedRows.value = lista_depositos.value.filter((p) => p.id == event.data.id);
	//formDelete.reset();

};
const collapseEvent = (event) => {
	// expandedRows.value = null;
	//formDelete.reset();

};


const setAtributo = (e) => {
	if (form.atributos.length > 0) {
		const indice = form.atributos.findIndex(producto => producto.atributo_id === e.atributo_id);
		if (indice !== -1) { // Asegurarse de que el elemento fue encontrado
			form.atributos.splice(indice, 1);
		}
	}
	var atri = lista_valores.find(pr => pr.id === e.id);
	form.atributos.push(
		{
			id: atri.id,
			valor: atri.valor,
			nombre: atri.nombre,
			atributo_id: atri.atributo_id,
			producto_id: form.id,
		}
	)
	showAtributo.value = false;
}

const removerAtributo = (index) => {
	form.atributos.splice(index, 1);
}

const addAtributo = () => {
	showAtributo.value = true;
}

onMounted(() => {
	expandedRows.value = null;
});

</script>
<template>

	<Head :title="titulo" />
	<AppLayout
		:pagina="[{ 'label': 'Productos', link: true, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
		<div
			class="card px-4  mb-4 bg-white col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
			<!--Contenido-->
			<Toast />
			<div class=" px-3 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
			</div>
			<div class="align-middle">
				<form @submit.prevent="submit">
					<div class="px-2 pt-4 pb-0 grid grid-cols-12 gap-4 mb-2">
						<div class="col-span-12 shadow-default xl:col-span-3">
							<InputLabel for="origen" value="Origen"
								class="block text-base font-medium leading-6 text-gray-900" />
							<InputText type="text" id="origen" v-model="form.origen" placeholder="Ingrese origen" :pt="{
								root: { class: 'h-9 w-full' }
							}" />
							<InputError class="mt-1 text-xs" :message="form.errors.origen" />
						</div>

						<div class="col-span-12 shadow-default xl:col-span-5">
							<InputLabel for="nombre" value="Nombre"
								class="block text-base font-medium leading-6 text-gray-900" />
							<InputText type="text" id="nombre" v-model="form.nombre" placeholder="Ingrese nombre" :pt="{
								root: { class: 'h-9 w-full' }
							}" />
							<InputError class="mt-1 text-xs" :message="form.errors.nombre" />
						</div>
						<div class="col-span-12 shadow-default xl:col-span-4">
							<InputLabel for="categorias" value="Categoría"
								class="block text-base font-medium leading-6 text-gray-900" />
							<Multiselect id="categorias" v-model="form.categorias" class="w-full"
								v-bind="lista_categorias">
							</Multiselect>
						</div>

						<div class="col-span-12 shadow-default xl:col-span-3">
							<InputLabel for="aduana" value="Aduana"
								class="block text-base font-medium leading-6 text-gray-900" />
							<InputText type="text" id="aduana" v-model="form.aduana" placeholder="Ingrese aduana" :pt="{
								root: { class: 'h-9 w-full' }
							}" />
							<InputError class="mt-1 text-xs" :message="form.errors.aduana" />
						</div>

						<div class="col-span-12 shadow-default xl:col-span-3">
							<InputLabel for="codigo_barra" value="Código barra"
								class="block text-base font-medium leading-6 text-gray-900" />

							<InputText type="text" id="codigo_barra" v-model="form.codigo_barra"
								placeholder="Ingrese Código barra" :pt="{
									root: { class: 'h-9 w-full' }
								}" />
							<InputError class="mt-1 text-xs" :message="form.errors.codigo_barra" />
						</div>

						<div class="col-span-12 shadow-default xl:col-span-3">
							<InputLabel for="stock" value="Stock"
								class="block text-base font-medium leading-6 text-gray-900" />
							<input type="number" required v-model="form.stock" step="1" min="0"
								@keyup="setStock($event)"
								class="p-inputtext text-end p-component h-9 w-full font-sans  font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1" />

							<InputError class="mt-1 text-xs" :message="form.errors.stock" />
						</div>

						<div class="col-span-12 shadow-default xl:col-span-3">
							<InputLabel for="stock_minimo" value="Stock Minimo"
								class="block text-base font-medium leading-6 text-gray-900" />
							<input type="number" required v-model="form.stock_minimo" step="1" min="0"
								class="p-inputtext text-end p-component h-9 w-full font-sans  font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1" />
							<InputError class="mt-1 text-xs" :message="form.errors.stock_minimo" />
						</div>

						<div v-if="permissions.includes('productos-editar_precio') == false"
							class="col-span-12 shadow-default xl:col-span-3">
							<InputLabel for="costo_real" value="Precio"
								class="block text-base font-medium leading-6 text-gray-900" />
							<h3 class="font-normal text-gray-800 text-base">{{ form.precio }}</h3>
						</div>

						<div v-if="permissions.includes('productos-editar_precio')"
							class="col-span-12 shadow-default xl:col-span-3">
							<InputLabel for="costo_real" value="Precio"
								class="block text-base font-medium leading-6 text-gray-900" />
							<input type="number" required v-model="form.precio" step="0.02" min="0"
								class="p-inputtext text-end p-component h-9 w-full font-sans  font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1" />
							<InputError class="mt-1 text-xs" :message="form.errors.precio" />
						</div>

						<div v-if="permissions.includes('productos-costoreal') == false"
							class="col-span-12 shadow-default xl:col-span-3">
							<InputLabel for="costo_real" value="Costo Real"
								class="block text-base font-medium leading-6 text-gray-900" />
							<h3 class="font-normal text-gray-800 text-base">{{ form.costo_real }}</h3>
						</div>

						<div v-if="permissions.includes('productos-costoreal')"
							class="col-span-12 shadow-default xl:col-span-3">
							<InputLabel for="costo_real" value="Costo Real"
								class="block text-base font-medium leading-6 text-gray-900" />
							<input type="number" required v-model="form.costo_real" step="0.02" min="0"
								class="p-inputtext text-end p-component h-9 w-full font-sans  font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1" />
							<InputError class="mt-1 text-xs" :message="form.errors.costo_real" />
						</div>

						<input type="hidden" id="stock_futuro" v-model="form.stock_futuro">
						<div class="col-span-12 shadow-default xl:col-span-6">
							<InputLabel for="file_input1" value="Imagen"
								class="block text-base font-medium leading-6 text-gray-900" />
							<input @input="pickFile" type="file" class="block w-full text-xs text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded file:border-0
                                file:text-sm file:font-medium
                                file:bg-gray-200 file:text-gray-700
                                hover:file:bg-gray-300
                                hover:file:cursor-pointer
                                " accept="image/x-png,image/gif,image/jpeg" />
							<p class="mt-1 text-xs text-gray-500 dark:text-gray-300" id="imagen">Peso
								máximo de la
								foto 2MB</p>
							<InputError class="mt-1 text-xs" :message="form.errors.imagen" />
						</div>
						<div class="col-span-12 shadow-default xl:col-span-3">
							<div class="imagePreviewWrapper" :style="{ 'background-image': `url(${previewImage})` }">
							</div>
						</div>
						<div class=" col-span-12 w-full flex flex-col md:flex-row pt-2">
							<div class="bg-gray-200 w-full text-start">
								<h3 class="font-semibold text-gray-800 text-lg px-5">ATRIBUTOS: </h3>
							</div>
						</div>
						<div class=" col-span-12 w-full flex flex-col md:flex-row pt-2">
							<Button size="small" @click="addAtributo" type="button" :label="'Agregar Atributo'"
								severity="success"></Button>
						</div>

						<div v-if="form.atributos.length > 0"
							class="flex justify-start col-span-12 w-full flex-col md:flex-row py-1 px-5"
							v-for="(atributo, index) in form.atributos">
							<div class="flex items-center w-full md:w-1/3 xl:w-1/3 mr-2">
								<h3 class="font-semibold text-gray-800 text-base">{{ atributo.nombre }}: </h3>
								<h3 class="font-normal text-gray-800 text-base px-3">{{ atributo.valor }}
								</h3>
								<div
									class="rounded-md p-1 flex justify-center items-center bg-red-600 py-auto  text-base font-semibold text-white hover:bg-red-700">
									<button type="button" @click.prevent="removerAtributo(index)" class="w-6"
										v-tooltip.top="{ value: `Eliminar`, pt: { text: 'bg-gray-500 p-1 m-0 text-xs text-white rounded' } }"><i
											class="fas fa-trash"></i></button>
								</div>
							</div>
						</div>
					</div>

					<div class="flex justify-end pt-2">
						<Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small"
							@click="cancelCrear" type="button" />
						<Button label="Guardar" size="small" type="submit" :class="{ 'opacity-50': form.processing }"
							:disabled="form.processing" />
					</div>
				</form>
			</div>
			<!--Contenido-->
		</div>

		<!--Modal Atributo-->
		<Dialog v-model:visible="showAtributo" modal :style="{ width: '50vw' }"
			:breakpoints="{ '1199px': '75vw', '575px': '90vw' }" position="top" :pt="{
				header: {
					class: 'mt-6 p-2 lg:p-4 flex justify-around text-end'
				},
				content: {
					class: 'p-4 lg:p-4'
				},
			}">
			<div class="card flex  w-full">
				<DataTable v-model:expandedRows="expandedRows" size="small" v-on:row-collapse="collapseEvent"
					v-on:row-expand="expandEvent" :value="lista_atributos.data" scrollable scrollHeight="800px"
					pagination :rows=50 :pt="{
						root: { class: 'w-full' }
					}">
					<template #empty> No existe Resultado </template>
					<template #loading> Cargando... </template>
					<Column expander style="width: 4rem" :pt="{
						bodyCell: { class: 'bg-secondary-900/30 font-bold m-2 text-2xl text-start' },
						headerCell: { class: 'uppercase bg-secondary-900 text-md ' },
					}" />
					<Column sortable field="nombre" header="Atributos" :pt="{
						bodyCell: { class: 'bg-secondary-900/30 font-bold text-start' },
						headerCell: { class: 'uppercase bg-secondary-100 text-md' },
					}"></Column>
					<template #expansion="slotProps">
						<div class="px-1">
							<DataTable :value="slotProps.data.valores" scrollable scrollHeight="300px" paginator
								:rows="100" :pt="{
									header: { class: 'text-center pb-0 mb-0 text-start' },
									headerContent: { class: 'bg-white pb-0 mb-0 text-start' },
								}">
								<Column :pt="{
									bodyCell: { class: 'text-start p-0 m-0' },
									headerCell: { class: 'bg-white p-0 m-0' },
									headerContent: {
										class: 'text-start stickyToTopTableHeaders'
									},
									bodyCellContent: {
										class: 'text-start'
									},
								}">
									<template #body="slotProps">
										<div class="flex justify-between items-center mx-2 w-1/2">
											<div class="mx-3">
												{{ slotProps.data.valor }}
											</div>
											<div class="px-5 text-end">
												<button @click="setAtributo(slotProps.data)"
													class="bg-green-500 disabled:bg-gray-800 disabled:text-white hover:bg-green-600 text-white rounded px-2 py-1.5"
													:disabled="form.atributos.filter(e => e.id === slotProps.data.id).length > 0"><i
														class="fas fa-plus"></i></button>
											</div>
										</div>
									</template>
								</Column>
							</DataTable>
						</div>
					</template>

				</DataTable>
			</div>
		</Dialog>
		<!--Modal Atributo-->

	</AppLayout>
</template>



<style type="text/css" scoped>
.imagePreviewWrapper {
	background-repeat: no-repeat;
	width: 120px;
	height: 120px;
	display: block;
	margin: 0 auto;
	background-size: contain;
	background-position: center center;
}
</style>
