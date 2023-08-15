<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import Multiselect from '@vueform/multiselect';

import TextInput from '@/Components/TextInput.vue';
import { useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import 'vue-datepicker-next/locale/es.es.js';

const isShowModal = ref(false);
const fec_nac = ref()
const of_colonia = ref('')
const casa_colonia = ref('')

const date = ref([new Date(), new Date()]);
watch(of_colonia, () => {
    return form.oficina_colonia = of_colonia.value
})
watch(casa_colonia, () => {
    return form.casa_colonia = casa_colonia.value
})
const setOficinaColonia = (e) => {
    of_colonia.value = e.name
}

const setCasaColonia = (e) => {
    casa_colonia.value = e.name
}
const selectNac = (modelData) => {
    form.fecha_nacimiento = modelData;
    fec_nac.value = modelData;
}

const addCliente = () => {
    isShowModal.value = true;
    //nextTick(() => passwordInput.value.focus());
};


const closeModal = () => {
    isShowModal.value = false;
    form.reset();
};


const form = useForm({
    nombre: '',
    rfc: '',
    email: '',
    telefono: '',
    casa_direccion: '',
    casa_colonia: '',
    oficina_direccion: '',
    oficina_colonia: '',
    fecha_nacimiento: '',
    tipo_cliente: '',

})

const perfiles = ref({
    value: '',
    closeOnSelect: true,
    placeholder: "Seleccione",
    searchable: true,
    options: [
        { "value": "Minorista", "label": "Minorista" },
        { "value": "Mayorista", "label": "Mayorista" }
    ],
});

//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route('clientes.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            isShowModal.value = false
            ok('Cliente Creado')
            router.get(route('clientes.index'));
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
</script>

<template>
    <section class="space-y-4">
        <button type="button" @click="addCliente"
            class="m-1 p-2 text-sm font-medium rounded text-center text-white bg-green-500 hover:bg-green-600 dark:bg-green-500 dark:hover:bg-green-700">
            Agregar Cliente
        </button>

        <Modal :show="isShowModal" @close="closeModal">
            <div class="p-2">

                <div
                    class="p-4 mb-4 rounded-t flex justify-between items-center border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-lg font-medium text-gray-900">
                        Crear Cliente
                    </h2>

                </div>
                <form @submit.prevent="submit">
                    <div class="px-2 grid grid-cols-6 gap-4 md:gap-3 2xl:gap-6 mb-2">

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="nombre" value="Nombre"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="nombre" type="text" v-model="form.nombre" autocomplete="nombre"
                                placeholder="Ingrese nombre" />
                            <InputError class="mt-1 text-xs" :message="form.errors.nombre" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="rfc" value="RFC" class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="rfc" type="text" v-model="form.rfc" autocomplete="rfc"
                                placeholder="Ingrese rfc" />
                            <InputError class="mt-1 text-xs" :message="form.errors.rfc" />
                        </div>


                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="email" value="Email"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="email" type="email" v-model="form.email" placeholder="Ingrese email"
                                class="block w-full text-gray-900 border border-gray-300 rounded bg-gray-50 sm:text-base focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                            <InputError class="mt-1 text-xs" :message="form.errors.email" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="telefono" value="Télefono"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="telefono" type="text" v-model="form.telefono" placeholder="Ingrese telefono"
                                class="block w-full text-gray-900 border border-gray-300 rounded bg-gray-50 sm:text-base focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                            <InputError class="mt-1 text-xs" :message="form.errors.telefono" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3 z-50">
                            <InputLabel for="fecha_nacimiento" value="Fecha de nacimiento"
                                class="block text-base font-medium leading-6 text-gray-900" />

                            <date-picker v-model:value="fec_nac" value-type="YYYY-MM-DD" format="DD/MM/YYYY" type="date"
                            @change="selectNac" lang="es" placeholder="Seleccione fecha"></date-picker>
                            <InputError class="mt-1 text-xs" :message="form.errors.fecha_nacimiento" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="tipo_cliente" value="Tipo de cliente"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <Multiselect id="tipo_cliente" v-model="form.tipo_cliente" v-bind="perfiles">
                            </Multiselect>
                            <InputError class="mt-1 text-xs" :message="form.errors.tipo_cliente" />
                        </div>


                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="casa_direccion" value="Casa dirección"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="casa_direccion" type="text" v-model="form.casa_direccion"
                                autocomplete="casa_direccion" placeholder="Ingrese casa direccion" />
                            <InputError class="mt-1 text-xs" :message="form.errors.casa_direccion" />
                        </div>
                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="casa_colonia" value="Casa colonia"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <GMapAutocomplete
                                class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-md rounded  focus:ring-primary-500 focus:border-primary-500 block w-full p-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Ingrese casa colonia" @place_changed="setCasaColonia"
                                :value="form.casa_colonia" :options="{
                                    types: ['geocode'],
                                    componentRestrictions: { country: 'MX' }
                                }">
                            </GMapAutocomplete>
                            <InputError class="mt-1 text-xs" :message="form.errors.casa_colonia" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="oficina_direccion" value="Oficina dirección"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="oficina_direccion" type="text" v-model="form.oficina_direccion"
                                autocomplete="oficina_direccion" placeholder="Ingrese oficina direccion" />
                            <InputError class="mt-1 text-xs" :message="form.errors.oficina_direccion" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="oficina_colonia" value="Oficina colonia"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <GMapAutocomplete
                                class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-md rounded  focus:ring-primary-500 focus:border-primary-500 block w-full p-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Ingrese oficina colonia" @place_changed="setOficinaColonia"
                                v-model="of_colonia" :value="form.oficina_colonia" :options="{
                                    types: ['geocode'],
                                    componentRestrictions: { country: 'MX' }
                                }">
                            </GMapAutocomplete>

                            <InputError class="mt-1 text-xs" :message="form.errors.oficina_colonia" />
                        </div>

                    </div>
                    <div class="flex justify-end pt-0">
                        <button @click="closeModal" type="button"
                            class="inline-block rounded bg-red-600 p-2 text-sm font-semibold text-white mr-4 mb-1 hover:bg-red-700">
                            Cancelar
                        </button>
                        <PrimaryButton
                            class="inline-block rounded bg-blue-600 p-2 text-sm font-semibold text-white mr-1 mb-1 hover:bg-blue-700"
                            :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                            Guardar
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </section>
</template>
