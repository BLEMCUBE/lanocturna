<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted} from 'vue'
import { Head, usePage, useForm,router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from "primevue/usetoast";

const titulo = "Datos Web"
const toast = useToast();
const { lista_configuracion } = usePage().props
const ruta="configuraciones"

const formDatos = useForm({
    config: []
})

const cancelCrear = () => {
    router.get(route('inicio'))
};

//envio de formulario
const submit = () => {

    formDatos.post(route(ruta + '.updateweb'), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
        show('success', 'Mensaje', 'Editado')
        setTimeout(() => {
            router.get(route('inicio'));
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
onMounted(() => {
    formDatos.config =lista_configuracion
});



</script>
<template>
    <div>

        <Head :title="titulo" />
        <AppLayout :pagina="[{ 'label': titulo, link: false }]">

            <div
                class="card p-4 mb-4 bg-white col-span-12  rounded-lg shadow-lg lg:col-span-6 dark:border-gray-700  dark:bg-gray-800">
                <form @submit.prevent="submit">
                <!--Contenido-->
                <div class="p-1 col-span-full flex justify-between items-center">
                    <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">{{ titulo }}</h1>
                </div>
                <div class="pb-2 bg-white col-span-4 lg:col-span-12 rounded-lg shadow-sm"
                v-for="(item, index) in formDatos.config">

                    <div :key="index" class="col-span-6 mt-3 shadow-default xl:col-span-6"
                   >
                        <InputLabel :for="formDatos.config[index].slug" :value="formDatos.config[index].key"
                            class="text-base font-bold leading-6 text-gray-900" />
                        <TextInput :id="formDatos.config[index].key" type="text" class="mt-2" v-model="formDatos.config[index].value" />
                    </div>
                </div>
                <div class="flex justify-end py-1">
                        <Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small" @click="cancelCrear"
                            type="button" />
                        <Button label="Guardar" size="small" type="submit" :class="{ 'opacity-50': formDatos.processing }"
                            :disabled="formDatos.processing" />
                    </div>
                </form>
            </div>

        </AppLayout>

    </div>
</template>


<style type="text/css" scoped></style>
