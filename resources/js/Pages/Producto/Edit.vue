<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Multiselect from '@vueform/multiselect';
const previewImage = ref('/images/productos/sin_foto.png');
const toast = useToast();

const titulo = "Editar Producto"
const ruta = 'productos'

const form = useForm({
    id: '',
    origen: '',
    nombre: '',
    aduana: '',
    codigo_barra: '',
    stock: 0,
    stock_minimo: 0,
    //stock_futuro:'',
    imagen: '',
    photo: '',
    categorias:[]
})

onMounted(() => {
    lista_categorias.value.options = usePage().props.lista_categorias
    var datos = usePage().props.producto;
    form.id = datos.id
    form.nombre = datos.nombre
    form.origen = datos.origen
    form.aduana = datos.aduana
    form.codigo_barra = datos.codigo_barra
    form.stock = datos.stock
    form.stock_minimo = datos.stock_minimo
    form.stock_futuro = datos.stock_futuro
    if(datos.categorias.length>0){
        datos.categorias.forEach((ele)=>{
            form.categorias.push(ele.id)
        })
        //form.categorias= [datos.categorias.map(entry => entry.id).join(',')]
    }
    //previewImage.value= usePage().props.base_url+datos.imagen
    previewImage.value = datos.imagen
    form.imagen = datos.imagen

});
//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta + '.update', form.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            show('success', 'Mensaje', 'Producto Actualizado')
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
const lista_categorias = ref({
    value: '',
    closeOnSelect: true,
    placeholder: "Categorías",
    mode: 'tags',
    searchable: true,
    options: [],
});
const setStock = (e) => {
    if(e.target.value.length>0)

    form.stock_futuro =  parseFloat(form.stock_futuro) +parseFloat( e.target.value);
}
const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const cancelCrear = () => {
    router.get(route(ruta + '.index'))
};
const pickFile = (e) => {
    e.preventDefault();

    form.photo = e.target.files[0]

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
    <AppLayout
        :pagina="[{ 'label': 'Productos', link: true, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
        <div
            class="card px-4  mb-4 bg-white col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <!--Contenido-->
            <Toast />
            <div class=" px-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div class="align-middle">

                <form @submit.prevent="submit">
                    <div class="px-2 pt-4 pb-0 grid grid-cols-12 gap-4 mb-2">

                        <div class="col-span-12 shadow-default xl:col-span-3">
                            <InputLabel for="origen" value="Origen"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="origen" v-model="form.origen" placeholder="Ingrese origen" :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.origen" />
                        </div>

                        <div class="col-span-12 shadow-default xl:col-span-5">
                            <InputLabel for="nombre" value="Nombre"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="nombre" v-model="form.nombre" placeholder="Ingrese nombre" :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.nombre" />
                        </div>
                        <div class="col-span-12 shadow-default xl:col-span-4">
                            <InputLabel for="categorias" value="Categoría"
                                class="block text-base font-medium leading-6 text-gray-900" />
                        <Multiselect id="categorias" v-model="form.categorias" class="w-full" v-bind="lista_categorias">
                        </Multiselect>
                        </div>

                        <div class="col-span-12 shadow-default xl:col-span-3">
                            <InputLabel for="aduana" value="Aduana"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="aduana" v-model="form.aduana" placeholder="Ingrese aduana" :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.aduana" />
                        </div>

                        <div class="col-span-12 shadow-default xl:col-span-3">
                            <InputLabel for="codigo_barra" value="Código barra"
                                class="block text-base font-medium leading-6 text-gray-900" />

                            <InputText type="text" id="codigo_barra" v-model="form.codigo_barra"
                                placeholder="Ingrese Código barra" :pt="{
                                    root: { class: 'h-9 w-full' }
                                }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.codigo_barra" />
                        </div>

                        <div class="col-span-12 shadow-default xl:col-span-3">
                            <InputLabel for="stock" value="Stock"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <input type="number" required
                                v-model="form.stock" step="1" min="0" @keyup="setStock($event)"
                                class="p-inputtext text-end p-component h-9 w-full font-sans  font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1"/>

                            <InputError class="mt-1 text-xs" :message="form.errors.stock" />
                        </div>

                        <div class="col-span-12 shadow-default xl:col-span-3">
                            <InputLabel for="stock_minimo" value="Stock Minimo"
                                class="block text-base font-medium leading-6 text-gray-900" />
                                <input type="number" required
                                v-model="form.stock_minimo" step="1" min="0"
                                class="p-inputtext text-end p-component h-9 w-full font-sans  font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1"/>
                            <InputError class="mt-1 text-xs" :message="form.errors.stock_minimo" />
                        </div>
                        <input type="hidden" id="stock_futuro" v-model="form.stock_futuro">
                        <div class="col-span-12 shadow-default xl:col-span-6">
                            <InputLabel for="file_input1" value="Imagen"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <input @input="pickFile" type="file" class="block w-full text-xs text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded file:border-0
                                file:text-sm file:font-medium
                                file:bg-gray-200 file:text-gray-700
                                hover:file:bg-gray-300
                                hover:file:cursor-pointer
                                " accept="image/x-png,image/gif,image/jpeg" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-300" id="imagen">Peso
                                máximo de la
                                foto 2MB</p>
                            <InputError class="mt-1 text-xs" :message="form.errors.imagen" />
                        </div>
                        <div class="col-span-12 shadow-default xl:col-span-3">
                            <div class="imagePreviewWrapper" :style="{ 'background-image': `url(${previewImage})` }"></div>
                        </div>

                    </div>

                    <div class="flex justify-end pt-2">
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
