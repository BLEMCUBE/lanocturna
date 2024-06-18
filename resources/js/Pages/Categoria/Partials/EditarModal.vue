<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from "primevue/usetoast";

const toast = useToast();
const titulo = "Categoría"
const ruta = "categorias"

//Variables
const isShowModal = ref(false);

const form = useForm({
    id: '',
    name: '',
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
            var datos = res.data.categoria
            form.id = datos.id
            form.name = datos.name

        })

};


const closeModal = () => {
    form.clearErrors()
    form.reset();
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

        <Dialog v-model:visible="isShowModal" modal :header="'Crear ' + titulo" :style="{ width: '30vw' }" position="top"
            :pt="{
                header: {
                    class: 'mt-6 p-2 lg:p-4 '
                },
                content: {
                    class: 'p-4 lg:p-4'
                },
            }">
            <form @submit.prevent="submit">
                <div class="px-2 grid grid-cols-6 gap-4 md:gap-3 2xl:gap-6 mb-1">

                    <div class="col-span-6 shadow-default xl:col-span-6">
                            <InputLabel for="name" value="Nombre"
                            class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="name" type="text" v-model="form.name" autocomplete="nombre"
                            placeholder="Ingrese Nombre" />
                        <InputError class="mt-1 text-xs" :message="form.errors.name" />
                    </div>

                </div>
                <div class="flex justify-end py-3">
                    <Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small" @click="closeModal"
                        type="button" />

                    <Button label="Guardar" size="small" type="submit" :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing" />
                </div>
            </form>
        </Dialog>
    </section>
</template>
