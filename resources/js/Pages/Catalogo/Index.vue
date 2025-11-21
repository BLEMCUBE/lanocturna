<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, watch } from 'vue'
import { Head, usePage,router } from '@inertiajs/vue3';
import EditarModal from '@/Pages/Catalogo/Partials/EditarModal.vue';
import Multiselect from '@vueform/multiselect';
import Pagination from '@/Components/Pagination.vue';
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

const tabla_productos = ref()
const { permissions } = usePage().props.auth
const titulo = "Catálogo"
const ruta = 'catalogo'

let categorias = ref([props.filtro.categoria])
let buscar = ref(props.filtro.buscar);

watch(buscar, (value) => {
	router.get(
		route(ruta + '.index'),
		{
			buscar: value,
			categoria: categorias.value
		},
		{
			preserveState: true,
			replace: true,
		}
	);
});


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
	//Si stock = < stock minimo Y stock_futuro = stock
	if (parseFloat(stock) <= parseFloat(stock_minimo) && parseFloat(stock) == parseFloat(stock_futuro)) {
		//return "text-red-700 text-xs"
		return "bg-red-700 text-white"
	}
	//Si stock = < stock mínimo y stock_futuro > stock
	if (parseFloat(stock) <= parseFloat(stock_minimo) && parseFloat(stock_futuro) > parseFloat(stock)) {

		return "bg-orange-500 text-black"
	}
};


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


</script>
<template>

	<Head :title="titulo" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">

		<div class="card px-4 mb-4 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12">
			<!--Contenido-->
			<div class="px-3 p-2 col-span-full flex justify-start items-center">
				<h5 class="text-2xl font-medium pr-5">{{ titulo }}</h5>
			</div>

			<!--tabla-->
			<div class="align-middle py-4">

				<div class="grid grid-cols-12 gap-4  m-3">

					<div class="flex justify-content-end text-md col-span-12 lg:col-span-4 2xl:col-span-3">
						<InputText class="w-full" v-model="buscar" placeholder="Buscar" />
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
								<th>Código barra</th>
								<th>Acciones</th>
							</tr>
						</thead>

						<tbody>
							<tr v-for="post in productos.data"
								class="border text-center hover:bg-gray-100 hover:text-black"
								:class="rowClass(post.stock, post.stock_minimo, post.stock_futuro)">
								<td>{{ post.stock ?? "" }}</td>
								<td>{{ post.stock_futuro }}</td>
								<td>
									<img class="rounded bg-white shadow-2xl border-2 text-center w-10 h-10 object-contain"
										:src="post.imagen" alt="image">
								</td>
								<td>{{ post.origen }}</td>
								<td>{{ post.nombre }}</td>
								<td> {{ post.categorias.map(entry => entry.name).join(', ') }}</td>
								<td>{{ post.codigo_barra }}</td>
								<td>
									<div v-if="permissions.includes('catalogo-imagen')">
										<span
											class="inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mr-1 mb-1 hover:bg-primary-100">
											<EditarModal :producto-id="post.id" :nombre="post.nombre"></EditarModal>
										</span>
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
