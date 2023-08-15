

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CheckboxName from '@/Components/CheckboxName.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { Breadcrumb, BreadcrumbItem } from 'flowbite-vue'

const { role } = usePage().props;
const { rolePermissions } = usePage().props;
const { permissions } = usePage().props;


onMounted(() => {
    form.id = role.id
    form.permisos = rolePermissions
});
const form = useForm({
    id: '',
    permisos: [],
})
//envio de formulario
const submit = () => {

    form.post(route('roles.update', form.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            ok('Rol  Editado')
            //form.reset()
            router.get(route('roles.index'));
        },
        onFinish: () => {
        },
        onError: () => {

        }
    });

}
const ok = (mensaje) => {
    //form.reset();
    Swal.fire({
        width: 350,
        title: mensaje,
        icon: 'success'
    })
}


//check permisos
const check = (optionId, checked) => {
    if (checked) {
        rolePermissions.push(optionId);
    } else {
        rolePermissions.splice(rolePermissions.indexOf(optionId), 1);
    }
};
</script>
<template>
    <div>

        <Head title="Permisos" />
        <AuthenticatedLayout>
            <div class="ml-4 col-span-full">

                <Breadcrumb>
                    <BreadcrumbItem href="/inicio" home>
                        Inicio
                    </BreadcrumbItem>
                    <BreadcrumbItem>
                        Permisos
                    </BreadcrumbItem>
                </Breadcrumb>
            </div>

            <div
                class="px-4 py-2 mb-4 bg-white col-span-12 pb-5 rounded-lg shadow-sm 2xl:col-span-12 dark:border-gray-700 sm:p-2 dark:bg-gray-800">
                <div class="px-5 col-span-full flex justify-between items-center">
                    <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Permisos de {{ role.name }}
                    </h1>
                </div>

                <form @submit.prevent="submit">

                    <div class="grid grid-cols-12 gap-2  mt-5 col-span-12  xl:col-span-6">
                        <div :key="permiso.id" class="col-span-12  xl:col-span-3 m-0.5 flex"
                            v-for="permiso, index in permissions">
                            <CheckboxName :checked="rolePermissions.includes(permiso.id)"
                                @update:checked="check(permiso.id, $event)" :fieldId="'permiso.id'" :label="permiso.name"
                                :key="permiso.id">
                            </CheckboxName>
                        </div>


                    </div>

                    <div class="flex justify-start pt-5">
                        <PrimaryButton
                            class="inline-block rounded bg-blue-600 p-2 text-sm font-semibold text-white mr-1 mb-1 hover:bg-blue-700"
                            :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                            Guardar
                        </PrimaryButton>
                    </div>
                </form>



            </div>


        </AuthenticatedLayout>

    </div>
</template>


<style type="text/css" scoped></style>
