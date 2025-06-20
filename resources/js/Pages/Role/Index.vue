<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, Link,useForm,router } from '@inertiajs/vue3';
import { FilterMatchMode } from 'primevue/api';
import CrearModal from '@/Pages/Role/Partials/CrearModal.vue';
import EditarModal from '@/Pages/Role/Partials/EditarModal.vue';
import { useToast } from "primevue/usetoast";
import Swal from 'sweetalert2';
const toast = useToast();
const tabla_categorias = ref()
const titulo = "Roles"
const ruta = 'roles'
const formDelete = useForm({
    id: '',
});
onMounted(() => {
	tabla_categorias.value = usePage().props.roles.data;
});

const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const filters = ref({
	global: { value: null, matchMode: FilterMatchMode.CONTAINS },
	name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
	'country.name': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
	representative: { value: null, matchMode: FilterMatchMode.IN },
	status: { value: null, matchMode: FilterMatchMode.EQUALS },
	verified: { value: null, matchMode: FilterMatchMode.EQUALS }
});

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

	<Head title="Roles" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">

		<div
			class="px-4 py-3 mb-4 bg-white col-span-12  lg:col-span-6 rounded-lg shadow-lg 2xl:col-span-6 dark:border-gray-700  dark:bg-gray-800">
			<Toast />
			<div class=" px-5 pb-2 col-span-full flex justify-between items-center">
				<h5 class="text-2xl font-medium">{{ titulo }}</h5>
				<CrearModal></CrearModal>
			</div>
			<div class="align-middle">

				<DataTable size="small" v-model:filters="filters" :value="tabla_categorias" :paginator="true" :rows="10"
					:rowsPerPageOptions="[5, 10, 20, 50]"
					paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
					tableStyle="width: 100%">
					<template #header size="small" class="bg-secondary-900">
						<div class="flex justify-content-end text-md">
							<InputText v-model="filters['global'].value" placeholder="Buscar" />
						</div>

					</template>
					<template #empty> No existe Resultado </template>
					<template #loading> Cargando... </template>
					<Column field="name" header="Nombre" sortable></Column>
					<Column header="Acciones" style="width:200px">
						<template #body="slotProps">
							<div class="flex justify-end justify-items-center ">
								<Link :href="route('roles.edit', slotProps.data.id)"
									class="rounded bg-primary-900 px-2 mx-2 py-1 text-xs text-white hover:bg-primary-100">
								Permisos
								</Link>
								<div class="mx-2" v-if="slotProps.data.user == 0 && slotProps.data.permiso == 0">
									<span
										class="inline-block rounded bg-primary-900 px-2 py-1 text-base font-medium text-white mb-0 hover:bg-primary-100">
										<EditarModal :cliente-id="slotProps.data.id"></EditarModal>
									</span>
								</div>

								<span  v-if="slotProps.data.user == 0 && slotProps.data.permiso == 0"
									class="mx-2 inline-block rounded bg-red-700 px-2 py-1 text-base font-medium text-white mb-0 hover:bg-red-600">
									<button @click.prevent="eliminar(slotProps.data.id, slotProps.data.name)"><i
											class="fas fa-trash-alt"></i></button>
								</span>
							</div>
						</template>
					</Column>
				</DataTable>

			</div>

		</div>

	</AppLayout>
</template>


<style type="text/css" scoped></style>
