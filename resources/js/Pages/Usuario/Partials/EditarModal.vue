<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import Multiselect from '@vueform/multiselect';
import TextInput from '@/Components/TextInput.vue';
import { useForm,router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const titulo = "Usuario"
const ruta = "usuarios"

//Variables
const isShowModal = ref(false);
const fec_nac = ref()

const form = useForm({
    id: '',
    name: '',
    username: '',
    password: '',
    rol: '',

})

const roles = ref({
    value: '',
    closeOnSelect: true,
    placeholder: "Seleccione",
    searchable: true,
    options: [],
});

const props = defineProps({
    clienteId: {
        type: Number,
        default: null,
    },


});


//Funciones

const addCliente = () => {
    dataEdit(props.clienteId);

};

const dataEdit = (id) => {
    axios.get(route(ruta+'.show', id))
  .then(res => {
    isShowModal.value = true;
    var datos=res.data.usuario

    form.id=datos.id
    form.name=datos.name
    form.username=datos.username
    form.rol=res.data.id_rol
    roles.value.options=res.data.lista_roles


  })

};


const closeModal = () => {
    isShowModal.value = false;
    form.reset();
};


//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta+'.update',form.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            isShowModal.value = false
            ok(titulo+' Editado')
            //form.reset()
            router.get(route(ruta+'.index'));
        },
        onFinish: () => {
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
</script>

<template>
    <section>
        <button type="button" @click="addCliente"><i class="fas fa-edit"></i></button>

        <Modal :show="isShowModal" @close="closeModal"  maxWidth="lg">
            <div class="p-2">

                <div
                    class="p-4 mb-4 rounded-t flex justify-between items-center border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-lg font-medium text-gray-900">
                        Editar {{titulo}}
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
