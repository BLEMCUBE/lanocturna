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

const titulo = "Usuario"
const ruta = "usuarios"

onMounted(() => {

})


const addCliente = () => {
    axios.get(route('opciones.roles'))
  .then(res => {
    isShowModal.value = true;
    var datos=res.data
    roles.value.options=datos.lista_roles
  })

};

const roles = ref({
    value: '',
    closeOnSelect: true,
    placeholder: "Seleccione",
    searchable: true,
    options: [],
});
const closeModal = () => {
    isShowModal.value = false;
    form.reset();
};


const form = useForm({
    name: '',
    username: '',
    password: '',
    rol: '',

})



//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta + '.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            isShowModal.value = false
            ok('Usuario Creado')
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

        <Modal :show="isShowModal" @close="closeModal"  maxWidth="lg">
            <div class="p-2">

                <div
                    class="p-4 mb-4 rounded-t flex justify-between items-center border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-lg font-medium text-gray-900">
                        Crear {{ titulo }}
                    </h2>

                </div>

                <form @submit.prevent="submit">
                    <div class="px-2 grid grid-cols-6 gap-4 md:gap-3 2xl:gap-6 mb-2">

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="name" value="Nombre"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="name" type="text" v-model="form.name" autocomplete="nombre"
                                placeholder="Ingrese Nombre" />
                            <InputError class="mt-1 text-xs" :message="form.errors.name" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="username" value="Usuario"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="username" type="text" @keyup="deleteSpaces" v-model="form.username"
                                autocomplete="username" placeholder="Ingrese usuario" />
                            <InputError class="mt-1 text-xs" :message="form.errors.username" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="password" value="Contraseña"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="password" type="password" v-model="form.password"
                                placeholder="Ingrese Contraseña"
                                class="block w-full text-gray-900 border border-gray-300 rounded bg-gray-50 sm:text-base focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                            <InputError class="mt-1 text-xs" :message="form.errors.password" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="rol" value="Rol"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <Multiselect id="rol" v-model="form.rol" v-bind="roles">
                            </Multiselect>
                            <InputError class="mt-1 text-xs" :message="form.errors.rol" />
                        </div>

                    </div>
                    <div class="flex justify-end pt-3">
                        <button @click="closeModal" type="button"
                        class="inline-block rounded bg-red-700 py-1 px-2 text-sm font-semibold text-white mr-4 mb-1 hover:bg-red-600">
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
