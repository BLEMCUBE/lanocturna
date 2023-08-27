<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from "primevue/usetoast";

const toast = useToast();
const titulo = "Tipo de Cambio"
const ruta = "tipo_cambio"

//Variables
const isShowModal = ref(false);
const fec_nac = ref()

const form = useForm({
    id: '',
    valor: '',
})

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
    axios.get(route(ruta + '.show', id))
        .then(res => {
            isShowModal.value = true;
            var datos = res.data.tipo_cambio
            form.id = datos.id
            form.valor = datos.valor

        })

};


const closeModal = () => {
    form.reset();
    form.clearErrors()
    isShowModal.value = false;
};


//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta + '.update', form.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            isShowModal.value = false
            show('success', 'Mensaje', 'Se ha editado')
            setTimeout(() => {
                router.get(route(ruta + '.index'));
            }, 1000);
        },
        onFinish: () => {
        },
        onError: () => {

        }
    });

};

const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};
</script>

<template>
    <section>
        <button type="button" @click="addCliente"><i class="fas fa-edit"></i></button>

        <Dialog v-model:visible="isShowModal" modal :header="'Crear ' + titulo" :style="{ width: '50vw' }" position="top"
            :pt="{
                header: {
                    class: 'mt-6 p-2 lg:p-4 '
                },
                content: {
                    class: 'p-4 lg:p-4'
                },
            }">
            <form @submit.prevent="submit">
                <div class="px-2 grid grid-cols-6 gap-4 md:gap-3 2xl:gap-6 mb-2">

                    <div class="col-span-6 shadow-default xl:col-span-6">
                        <InputLabel for="nombre" value="Nombre"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <InputNumber v-model="form.valor" inputId="stock" size="small"
                            mode="currency" :min="1" :max="99999999" :style="{ width: '100vw' }"
                            currency="USD" locale="en-US"
                            :breakpoints="{ '960px': '75vw', '641px': '100vw' }" :pt="{
                                root: { class: 'h-9 p-0 m-0 text-base' },
                                input: { class: 'h-9 px-0 py-0 m-0 w-full text-end text-base' },
                                incrementButton: { class: 'm-0 w-auto  rounded-tl-none rounded-br-lg' },
                                decrementButton: { class: 'm-0 w-auto  rounded-bl-none  rounded-tr-lg' }
                            }" />
                        <InputError class="mt-1 text-xs" :message="form.errors.valor" />
                    </div>

                </div>
                <div class="flex justify-end py-3">
                    <Button label="Cancelar" :pt="{ root: 'mr-5' }" severity="danger" size="small" @click="closeModal"
                        type="button" />

                    <Button label="Guardar" size="small" type="submit" :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing" />
                </div>
            </form>
        </Dialog>
    </section>
</template>
