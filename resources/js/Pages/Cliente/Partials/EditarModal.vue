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

const titulo = "Cliente"
const ruta = "clientes"

//Variables
const isShowModal = ref(false);
const fec_nac = ref()

const form = useForm({
    id: '',
    nombre: '',
    telefono: '',
    email: '',
    localidad: '',
    direccion: '',
    empresa: '',
    rut: '',
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
    var datos=res.data.cliente
    form.id=datos.id
    form.nombre=datos.nombre
    form.telefono=datos.telefono
    form.localidad=datos.localidad
    form.direccion=datos.direccion
    form.empresa=datos.empresa
    form.rut=datos.rut
    form.email=datos.email
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

        <Modal :show="isShowModal" @close="closeModal" maxWidth="lg">
            <div class="p-2">

                <div
                    class="p-4 mb-4 rounded-t flex justify-between items-center border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-lg font-medium text-gray-900">
                        Editar {{titulo}}
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
