<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { useToast } from "primevue/usetoast";

const toast = useToast();
const isShowModal = ref(false);

const titulo = "Cliente"
const ruta = "clientes"

onMounted(() => {

})


const addCliente = () => {
    isShowModal.value = true;

};

const closeModal = () => {
    form.reset();
    form.clearErrors()
    isShowModal.value = false;
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
            show('success', 'Mensaje', 'Se ha creado')
            setTimeout(() => {
                router.get(route(ruta + '.index'));
            }, 1000);
        },
        onFinish: () => {

            //form.reset()
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
    <section class="space-y-4">

        <Button size="small" @click="addCliente" type="button" :label="'Agregar ' + titulo" severity="success"></Button>

        <Modal :show="isShowModal" @close="closeModal" maxWidth="lg">
            <div class="p-2">

                <div
                    class="p-4 mb-4 rounded-t flex justify-between items-center border-b border-gray-200 dark:border-gray-600">
                    <h5 class="text-xl font-normal">Crear {{ titulo }}</h5>

                </div>

                <form @submit.prevent="submit">
                    <div class="px-2 grid grid-cols-6 gap-4 md:gap-3 2xl:gap-6 mb-2">

                        <div class="col-span-6 shadow-default xl:col-span-6">
                            <InputLabel for="nombre" value="Nombre"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="nombre" type="text" v-model="form.nombre" placeholder="Ingrese Nombre" />
                            <InputError class="mt-1 text-xs" :message="form.errors.nombre" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="telefono" value="Telefono"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="telefono" type="text" v-model="form.telefono" placeholder="Ingrese telefono" />
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
                            <TextInput id="empresa" type="text" v-model="form.empresa" placeholder="Ingrese Empresa" />
                            <InputError class="mt-1 text-xs" :message="form.errors.empresa" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="rut" value="RUT" class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="rut" type="text" v-model="form.rut" placeholder="Ingrese RUT" />
                            <InputError class="mt-1 text-xs" :message="form.errors.rut" />
                        </div>

                        <div class="col-span-6 shadow-default xl:col-span-3">
                            <InputLabel for="email" value="Email"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <TextInput id="email" type="email" v-model="form.email" placeholder="Ingrese Email" />
                            <InputError class="mt-1 text-xs" :message="form.errors.email" />
                        </div>

                    </div>
                    <div class="flex justify-end py-3">
                        <Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small" @click="closeModal"
                            type="button" />
                        <Button label="Guardar" size="small" type="submit" :class="{ 'opacity-50': form.processing }"
                            :disabled="form.processing" />
                    </div>
                </form>
            </div>
        </Modal>
    </section>
</template>
