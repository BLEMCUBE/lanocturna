

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage,useForm,router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { useToast } from "primevue/usetoast";

const toast = useToast();
const codi_maestro = ref()
const titulo = "Código Maestro"
const ruta="configuraciones"


const form=useForm({
    id:'',
    slug: 'codigo-maestro',
    codigo: '',
    codigo2: '',
});

const cancelCrear = () => {
    router.get(route('inicio'))
};

//envio de formulario
const submit = () => {

form.clearErrors()
form.post(route(ruta + '.update', form.id), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
        show('success', 'Mensaje', 'Código editado')
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
    codi_maestro.value = usePage().props.codigo_maestro;
    form.id=codi_maestro.value.id
});



</script>
<template>
    <div>

        <Head title="Configuraciones" />
        <AppLayout :pagina="[{ 'label': titulo, link: false }]">
            <div
            class="card p-4 mb-4 bg-white col-span-12  rounded-lg shadow-lg lg:col-span-6 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <div class=" px-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div class="align-middle">

                <form @submit.prevent="submit">
                    <div class="px-2 pt-4 pb-0 grid grid-cols-12 gap-4 mb-2">

                        <div class="col-span-12 shadow-default lg:col-span-6">
                            <InputLabel for="codigo" value="Código"
                                class="block text-base font-medium leading-6 text-gray-900" />

                            <InputText type="text" id="codigo" v-model="form.codigo" placeholder="Ingrese Código" :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                            <input type="hidden" v-model="form.slug">
                            <InputError class="mt-1 text-xs" :message="form.errors.codigo" />
                        </div>
                        <div class="col-span-12 shadow-default lg:col-span-6">
                            <InputLabel for="codigo2" value="Repetir Código"
                                class="block text-base font-medium leading-6 text-gray-900" />

                            <InputText type="text" id="codigo2" v-model="form.codigo2" placeholder="Ingrese Repetir Código" :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.codigo2" />
                        </div>

                    </div>

                    <div class="flex justify-end p-2">
                        <Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small" @click="cancelCrear"
                            type="button" />
                        <Button label="Guardar" size="small" type="submit" :class="{ 'opacity-50': form.processing }"
                            :disabled="form.processing" />
                    </div>
                </form>

            </div>
            <!--Contenido-->

        </div>

    </AppLayout>

    </div>
</template>


<style type="text/css" scoped></style>
