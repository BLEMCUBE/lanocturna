<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextInputNumber from '@/Components/TextInputNumber.vue';

const previewImage = ref('/images/productos/sin_foto.png');
const toast = useToast();

const titulo = "Detalle Producto"
const ruta = 'productos'

const form = useForm({
    id: '',
    origen: '',
    nombre: '',
    aduana: '',
    codigo_barra: '',
    stock: 0,
    stock_minimo: 0,
    imagen: '',
    photo: ''
})

onMounted(() => {
    var datos = usePage().props.producto;
    form.id = datos.id
    form.nombre = datos.nombre
    form.origen = datos.origen
    form.aduana = datos.aduana
    form.codigo_barra = datos.codigo_barra
    form.stock = datos.stock
    form.stock_minimo = datos.stock_minimo
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
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12  justify-center md:col-span-12 pb-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->

            <div class=" px-3 col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div class="md:flex justify-center py-5 2xl:px-10 md:px-6 px-4">

                <div class="xl:w-64 justify-center items-center w-80">
                    <img class="w-full text-center" :alt="form.nombre" :src="form.imagen" />

                </div>

                <div class="xl:w-100 md:w-100 lg:ml-8 md:ml-6 md:mt-0 mt-6">
                    <div class="border-b border-gray-200 pb-4">
                        <h1
                            class="lg:text-2xl text-lg font-semibold lg:leading-6 leading-7 text-gray-800 dark:text-white mt-2">
                            {{ form.nombre }}</h1>
                    </div>
                    <div >
                        <p class="text-xl leading-5 mt-2 text-gray-600 dark:text-gray-300"><b>
                                Origen:
                            </b>
                            {{ form.origen }}
                        </p>
                        <p class="text-xl leading-5 mt-2 text-gray-600 dark:text-gray-300"><b>
                                Aduana:
                            </b>
                            {{ form.aduana }}
                        </p>
                        <p class="text-xl leading-5 mt-2 text-gray-600 dark:text-gray-300"><b>
                                Código barra:
                            </b>
                            {{ form.codigo_barra }}
                        </p>
                        <p class="text-xl leading-5 mt-2 text-gray-600 dark:text-gray-300"><b>
                                Stock:
                            </b>
                            {{ form.stock }}
                        </p>
                        <p class="text-xl leading-5 mt-2 text-gray-600 dark:text-gray-300"><b>
                                Stock Mínimo:
                            </b>
                            {{ form.stock_minimo }}
                        </p>
                        <p class="text-xl leading-5 mt-2 text-gray-600 dark:text-gray-300"><b>
                                Stock Futuro:
                            </b>
                            {{ form.stock_futuro }}
                        </p>

                    </div>

                </div>
            </div>

            <div class="mt-8 bg-white p-4 shadow rounded">
                <h2 class="text-2xl font-medium pb-0 pl-2">Pedidos</h2>
                <div class="my-1"></div> <!-- Espacio de separación -->
                <div class="bg-gradient-to-r from-primary-900 to-primary-100 h-1 mb-4"></div> <!-- Línea con gradiente -->


            </div>
            <div class="mt-8 bg-white p-4 shadow rounded">
                <h2 class="text-2xl font-medium pb-0 pl-2">Importaciones</h2>
                <div class="my-1"></div> <!-- Espacio de separación -->
                <div class="bg-gradient-to-r from-secondary-900 to-secondary-100 h-1 mb-4"></div> <!-- Línea con gradiente -->


            </div>
            <!--Contenido-->

        </div>

    </AppLayout>
</template>

<style type="text/css" scoped></style>
