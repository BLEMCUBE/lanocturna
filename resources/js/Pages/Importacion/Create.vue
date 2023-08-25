<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Multiselect from '@vueform/multiselect';
const previewImage = ref('/images/productos/sin_foto.png');
const toast = useToast();
const titulo = "Importar"
const ruta = 'importaciones'

const form = useForm({
    nro_carpeta: '',
    nro_contenedor: '',
    estado: '',
    archivo: '',
})

const lista_estado = ref({
    value: '',
    closeOnSelect: true,
    placeholder: "Seleccione",
    searchable: false,
    options: [
        { "value": "Arribado", "label": "Arribado" },
        { "value": "En camino", "label": "En camino" },
     //   { "value": "Compra en Plaza", "label": "Compra en Plaza" }
    ],
});

//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta + '.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            show('success', 'Mensaje', 'Producto creado')
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

const cancelCrear = () => {
    router.get(route(ruta + '.index'))
};
const pickFile = (e) => {
    e.preventDefault();

    form.archivo = e.target.files[0]

    let file = e.target.files
    if (file && file[0]) {
        let reader = new FileReader
        reader.onload = e => {
            previewImage.value = e.target.result
        }
        reader.readAsDataURL(file[0])

    }
}

</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': 'Importaciones', link:true,url: route(ruta + '.index') },{ 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <Toast />
            <div class=" px-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div class="align-middle">

                <form @submit.prevent="submit">
                    <div class="px-2 pt-4 pb-0 grid grid-cols-12 gap-4 mb-2">

                        <div class="col-span-12 shadow-default xl:col-span-4">
                            <InputLabel for="nro_carpeta" value="No. de Carpeta"
                                class="block text-base font-medium leading-6 text-gray-900" />

                            <InputText type="text" id="nro_carpeta" v-model="form.nro_carpeta" placeholder="Ingrese No. de Carpeta" :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.nro_carpeta" />
                        </div>

                        <div class="col-span-12 shadow-default xl:col-span-4">
                            <InputLabel for="nro_contenedor" value="BL o No. de Contenedor"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="nro_carpeta" v-model="form.nro_contenedor" placeholder="Ingrese BL o No. de Contenedor" :pt="{
                                root: { class: 'h-9 w-full' }
                            }"/>
                              <InputError class="mt-1 text-xs" :message="form.errors.nro_contenedor" />
                        </div>

                        <div class="col-span-12 shadow-default xl:col-span-4">
                            <InputLabel for="estado" value="Estado"
                                class="block text-base font-medium leading-6 text-gray-900" />
                                <Multiselect id="rol" v-model="form.estado" v-bind="lista_estado">
                            </Multiselect>
                            <InputError class="mt-1 text-xs" :message="form.errors.estado" />
                        </div>


                        <div class="col-span-12 shadow-default xl:col-span-12">
                            <InputLabel for="file_input1" value="Archivo Excel"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <input @input="pickFile" type="file" class="block w-full text-xs text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded file:border-0
                                file:text-sm file:font-medium
                                file:bg-gray-200 file:text-gray-700
                                hover:file:bg-gray-300
                                hover:file:cursor-pointer
                                "
                                accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
                            <InputError class="mt-1 text-xs" :message="form.errors.archivo" />
                        </div>

                    </div>

                    <div class="flex justify-end pt-2">
                        <Button label="Cancelar" :pt="{ root: 'mr-5' }" severity="danger" size="small" @click="cancelCrear"
                            type="button" />
                        <Button label="Guardar" size="small" type="submit" :class="{ 'opacity-50': form.processing }"
                            :disabled="form.processing" />
                    </div>
                </form>

            </div>
            <!--Contenido-->

        </div>

    </AppLayout>
</template>



<style type="text/css" scoped>
.imagePreviewWrapper {
    background-repeat: no-repeat;
    width: 120px;
    height: 120px;
    display: block;
    margin: 0 auto;
    background-size: contain;
    background-position: center center;
}
</style>
