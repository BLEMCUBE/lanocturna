<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CheckboxName from '@/Components/CheckboxName.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { onMounted, ref } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';

import { useToast } from "primevue/usetoast";
const { role } = usePage().props;
const { rolePermissions } = usePage().props;
const { namedGroups } = usePage().props;
const titulo = 'Permisos'
const toast = useToast();

const selected = ref([])
const toggleGroup = (group) => {
	const all = group.permissions.map(p => p.id)
	const allSelected = all.every(id => selected.value.includes(id))
	selected.value = allSelected
		? selected.value.filter(id => !all.includes(id))
		: [...new Set([...selected.value, ...all])]
}

const isGroupChecked = (group) => {
	return group.permissions.every(p => selected.value.includes(p.id))
}

const form = useForm({
	id: '',
	permisos: [],
})

onMounted(() => {
	form.id = role.id
	form.permisos = rolePermissions
	selected.value = rolePermissions
});
//envio de formulario
const submit = () => {
	form.permisos = selected.value
	form.post(route('roles.update', form.id), {
		preserveScroll: true,
		forceFormData: true,
		onSuccess: () => {
			show('success', 'Confirmado', 'Rol Editado');
		},
		onFinish: () => {
		},
		onError: () => {

		}
	});

}
//check permisos
const check = (optionId, checked) => {
	if (checked) {
		rolePermissions.push(optionId);
	} else {
		rolePermissions.splice(rolePermissions.indexOf(optionId), 1);
	}
};

const show = (tipo, titulo, mensaje) => {
	toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 2000 });
};
</script>
<template>

	<Head title="Permisos" />
	<AppLayout :pagina="[{ 'label': titulo, link: false }]">
		<Toast />
		<div
			class="px-4 bg-white col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700 sm:p-2 dark:bg-gray-800">

			<form @submit.prevent="submit">
				<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
				<div class="px-5 pb-2 col-span-full flex justify-between items-center">
					<h5 class="text-2xl font-medium">Permisos de {{ role.name }}</h5>
				</div>
				<div v-for="group in namedGroups" :key="group.key" class="flex items-start justify-center">
					<!-- Card Container -->
					<div class="w-full max-w-md mx-4 bg-white rounded-xl shadow-lg overflow-hidden md:max-w-2xl">
						<!-- Card Header with Gradient Background -->
						<div class="h-2 bg-gradient-to-r from-blue-500 to-cyan-400"></div>

						<!-- Card Content -->
						<div class="p-3">
							<!-- Profile Section -->
							<div class="flex items-center">
								<!-- Name and Title -->
								<div>
									<h2 class="text-xl pb-2 font-bold text-gray-800">{{ group.name }}</h2>
									<hr>
									<label class="flex items-center gap-2 text-sm text-blue-600 cursor-pointer">
										<input type="checkbox"
											class="w-5 h-5 rounded-md border-gray-300 text-xl text-primary-900 bg-primary-900 hover:bg-primary-100 shadow-sm cursor-pointer"
											:checked="isGroupChecked(group)" @change="toggleGroup(group)" />
										Seleccionar todo
									</label>
								</div>
							</div>

							<!-- Divider -->
							<div class="my-2 border-t border-gray-200"></div>

							<!-- Contact Information -->
							<div class="space-y-2">
								<!-- Phone -->
							<div class="grid grid-cols-1 gap-2">

									<label v-for="perm in group.permissions" :key="perm.id"
										class="flex items-center gap-2 text-sm text-gray-700">
										<CheckboxName :checked="selected.includes(perm.id)"
											@update:checked="check(perm.id, $event)" :fieldId="'perm.id'"
											:label="perm.description" :key="perm.id">
										</CheckboxName>
									</label>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

				<div class="flex justify-start pt-5">
					<PrimaryButton
						class="inline-block rounded bg-primary-900 p-2 text-sm font-medium text-white mr-1 mb-1 hover:bg-primary-100"
						:class="{ 'opacity-50': form.processing }" :disabled="form.processing">
						Guardar
					</PrimaryButton>
				</div>
			</form>

		</div>

	</AppLayout>
</template>


<style type="text/css" scoped></style>
