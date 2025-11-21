<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, watch } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";
import Multiselect from '@vueform/multiselect';
import Pagination from '@/Components/Pagination.vue';
import CrearMasivoModal from '@/Pages/Producto/CrearMasivoModal.vue';

const props = defineProps({
	productos: {
		type: Object,
		default: () => ({}),
	},
	filtro: {
		type: Object,
		default: () => ({}),
	},
});
const toast = useToast();
const tabla_productos = ref()
const fotos = ref(false)
const { permissions } = usePage().props.auth
const titulo = "Productos"
const ruta = 'productos'
const formDelete = useForm({
	id: '',
});

let categorias = ref([props.filtro.categoria])
let buscar = ref(props.filtro.buscar);


const funcBuscar = () => {
	router.get(
		route(ruta + '.index'),
		{
			buscar: buscar.value,
			categoria: categorias.value
		},
		{
			preserveState: true,
			replace: true,
		}
	);
}

const form = useForm({
	id: ''
})


watch(categorias, (value) => {
	router.get(
		route(ruta + '.index'),
		{
			categoria: value,
			buscar: buscar.value,
		},
		{
			preserveState: true,
			replace: true,
		}
	);
});

const rowClass = (stock, stock_minimo, stock_futuro) => {

	if (parseFloat(stock) <= parseFloat(stock_minimo) && parseFloat(stock) == parseFloat(stock_futuro)) {
		return "bg-red-700 text-white"
	}
	if (parseFloat(stock) <= parseFloat(stock_minimo) && parseFloat(stock_futuro) > parseFloat(stock)) {

		return "bg-orange-500 text-black"
	}
};


const btnVer = (id) => {
	router.get(route(ruta + '.show', id));
};

const duplicar = (id) => {
	form.clearErrors()
	form.post(route(ruta + '.duplicar', id), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			show('success', 'Mensaje', 'Producto Duplicado')
		},
		onFinish: () => {

		},
		onError: () => {

		}
	});

};

const btnEliminar = (id, name) => {

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
					forceFormData: true,
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
const lista_categorias = ref({
	value: '',
	closeOnSelect: true,
	placeholder: "Categorías",
	mode: 'tags',
	searchable: true,
	options: [],
});


onMounted(() => {
	tabla_productos.value = usePage().props.productos.data;
	lista_categorias.value.options = usePage().props.lista_categorias
});


const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const BtnCrear = () => {
	router.get(route(ruta + '.create'));
}
const clickDetalle = (id) => {
	btnVer(id)
}
</script>

<template>

	<Head :title="titulo" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">

		<div class="card px-4 mb-4 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12">

			<!--Contenido-->
			<div class="px-3 p-2 col-span-full flex justify-start items-center">
				<h5 class="text-2xl font-medium pr-5">{{ titulo }}</h5>
			</div>

			<div class="px-3 pb-2 col-span-full flex justify-end items-center">
				<input type="checkbox"
					class="rounded-md border-gray-300 text-xl text-primary-900 bg-primary-900 hover:bg-primary-100 shadow-sm w-5 h-5 cursor-pointer"
					v-model="fotos" />
				<label class="mx-2 font-normal text-md text-gray-800 dark:text-white"> Exportar imagenes en excel
				</label>
				<CrearMasivoModal></CrearMasivoModal>
				<div v-tooltip.top="{ value: 'Descargar Excel', pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
					class=" w-8 h-8 rounded bg-green-600 flex justify-center mx-5 items-center text-base font-semibold text-white hover:bg-green-600">
					<a :href="route(ruta + '.exportar', { categoria: categorias, foto: fotos })" target="_blank"
						class="py-auto"><i class="fas fa-file-excel text-white"></i>
					</a>
				</div>
				<Button size="small" :label="'Crear Producto'" severity="success" @click="BtnCrear"></Button>
			</div>

			<!--tabla-->
			<div class="align-middle py-4">

				<div class="grid grid-cols-12 gap-4  m-3">

					<div class="flex justify-content-end text-md col-span-12 lg:col-span-4 2xl:col-span-3">
						<InputText v-debounce:500ms="funcBuscar" :debounce-events="['keyup']" class="w-full"
							v-model="buscar" placeholder="Buscar" />
					</div>
					<div class="flex justify-content-end text-md col-span-12 lg:col-span-8 2xl:col-span-9">
						<Multiselect id="categorias" v-model="categorias" class="w-full" v-bind="lista_categorias">
						</Multiselect>
					</div>
				</div>
				<div style="overflow:auto; max-height: 700px;">

					<table class="w-full text-md bg-white shadow-md rounded mb-4">
						<thead style="position: sticky;" class="top-0 z-[1]">
							<tr class="bg-secondary-100">
								<th class="p-1.5">Stock</th>
								<th>Stock futuro</th>
								<th class="p-2">Imagen</th>
								<th>Origen</th>
								<th>Nombre</th>
								<th>Categoria</th>
								<th>Acciones</th>
							</tr>
						</thead>

						<tbody>
							<tr v-for="post in productos.data"
								class="border text-center hover:cursor-pointer hover:bg-gray-100 hover:text-black"
								:class="rowClass(post.stock, post.stock_minimo, post.stock_futuro)">
								<td @click="clickDetalle(post.id)">{{ post.stock ?? "" }}</td>
								<td @click="clickDetalle(post.id)">{{ post.stock_futuro }}</td>
								<td @click="clickDetalle(post.id)">
									<img class="rounded  bg-white shadow-2xl border-2 text-center w-10 h-10 object-contain"
										:src="post.imagen" alt="image">
								</td>
								<td @click="clickDetalle(post.id)">{{ post.origen }}</td>
								<td @click="clickDetalle(post.id)">{{ post.nombre }}</td>
								<td @click="clickDetalle(post.id)">
									{{post.categorias.map(entry => entry.name).join(',')}}</td>
								<td>
									<div class="flex justify-end">
										<div>
											<button v-if="permissions.includes('productos-eliminar')"
												class="w-8 h-8 rounded bg-green-700  border border-white px-2 py-1 text-base font-normal text-white m-1 hover:bg-green-600"
												v-tooltip.top="{ value: `Duplicar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
												@click.prevent="duplicar(post.id)"><i
													class="fa-regular fa-copy"></i></button>
										</div>
										<div>

											<button v-if="permissions.includes('productos-eliminar')"
												class="w-8 h-8 rounded bg-red-700  border border-white px-2 py-1 text-base font-normal text-white m-1 hover:bg-red-600"
												v-tooltip.top="{ value: `Eliminar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
												@click.prevent="btnEliminar(post.id, post.nombre)"><i
													class="fas fa-trash-alt"></i></button>
										</div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<Pagination :elements="productos"></Pagination>

			</div>
			<!--tabla-->
			<!--Contenido-->

		</div>

	</AppLayout>
</template>
