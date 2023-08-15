<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';

import TextInput from '@/Components/TextInput.vue';
import { useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref,  onMounted, nextTick } from 'vue';
import Swal from 'sweetalert2';
import Multiselect from '@vueform/multiselect';

const isShowModal = ref(false);

const titulo = "Cliente"
const ruta = "clientes"

onMounted(() => {

})


const addCliente = () => {
    isShowModal.value = true;

};


const closeModal = () => {
    isShowModal.value = false;
    form.reset();
};


const form = useForm({
    nombre: '',
    telefono: '',
    email: '',
    localidad: '',
    direccion: '',
    empresa: '',
    rut: '',
})



//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta + '.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            isShowModal.value = false
            ok( titulo+' Creado')
            router.get(route(ruta + '.index'));
        },
        onFinish: () => {

            //form.reset()
        },
        onError: () => {

        }
    });



};

const ok = (mensaje) => {
    form.reset();

    Swal.fire({
        width: 350,
        title: mensaje,
        icon: 'success'
    })
}
nextTick(function () {


});
//eliminar espacios
const deleteSpaces = (e) => {
    e.target.value = e.target.value.replace(/[^a-z0-9]/gi, '');
    e.target.value = ("" + e.target.value).replace(/\s+/g, '')
};
</script>

<template>
    <section class="space-y-4">

        <button type="button" @click="addCliente"
            class="m-1 px-2 py-1 text-sm font-medium rounded text-center text-white bg-green-500 hover:bg-green-600 dark:bg-green-500 dark:hover:bg-green-700">
            Agregar {{ titulo }}
        </button>

        <Modal :show="isShowModal" @close="closeModal" maxWidth="lg">
            <div class="p-2">

                <div
                    class="p-4 mb-4 rounded-t flex justify-between items-center border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-lg font-medium text-gray-900">
                        Crear {{ titulo }}
                    </h2>

                </div>

                <form @submit.prevent="submit">
                    <div class="px-2 grid grid-cols-6 gap-4 md:gap-3 2xl:gap-6 mb-2">

                        <div class="col-span-6 shadow-default xl:col-span-6">
                            <InputLabel for="nombre" value="Nombre"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="nombre" type="text" v-model="form.nombre"
                                placeholder="Ingrese Nombre" />
                            <InputError class="mt-1 text-xs" :message="form.errors.nombre" />
                        </div>



                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="telefono" value="Telefono"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="telefono" type="text" v-model="form.telefono"
                                placeholder="Ingrese telefono" />
                            <InputError class="mt-1 text-xs" :message="form.errors.telefono" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="localidad" value="Localidad"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="localidad" type="text" v-model="form.localidad"
                                placeholder="Ingrese Localidad" />
                            <InputError class="mt-1 text-xs" :message="form.errors.localidad" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="direccion" value="Dirección"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="direccion" type="text" v-model="form.direccion"
                                placeholder="Ingrese Dirección" />
                            <InputError class="mt-1 text-xs" :message="form.errors.direccion" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="empresa" value="Empresa"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="empresa" type="text" v-model="form.empresa"
                                placeholder="Ingrese Empresa" />
                            <InputError class="mt-1 text-xs" :message="form.errors.empresa" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="rut" value="RUT"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="rut" type="text" v-model="form.rut"
                                placeholder="Ingrese RUT" />
                            <InputError class="mt-1 text-xs" :message="form.errors.rut" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="email" value="Email"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="email" type="email" v-model="form.email"
                                placeholder="Ingrese Email" />
                            <InputError class="mt-1 text-xs" :message="form.errors.email" />
                        </div>

                    </div>
                    <div class="flex justify-end pt-3">
                        <button @click="closeModal" type="button"
                            class="inline-block rounded bg-red-600 py-1 px-2 text-sm font-semibold text-white mr-4 mb-1 hover:bg-red-700">
                            Cancelar
                        </button>
                        <PrimaryButton
                            class="inline-block rounded bg-blue-600 py-1 px-2 text-sm font-semibold text-white mr-1 mb-1 hover:bg-blue-700"
                            :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                            Guardar
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </section>
</template>
