<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";

const { permissions } = usePage().props.auth
const toast = useToast();
const titulo = "Detalle"
const ruta = 'expediciones'

const form = useForm({
    id: '',
    vendedor: '',
    destino: '',
    codigo: '',
    total: 0.0,
    fecha: '',
    total_sin_iva: 0.0,
    moneda: '',
    tipo_cambio: '',
    estado: '',
    observaciones: '',
    productos: [],
    cliente: '',

})
const btnEditar = (id) => {
    router.get(route(ruta + '.edit', id));

};
const btnFacturar = (id) => {
    //router.get(route(ruta + '.facturar', id));
    form.get(route(ruta + '.facturar', id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            show('success', 'Mensaje', 'Se ha Facturado')
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
onMounted(() => {
    var datos = usePage().props.venta.data;
    form.id = datos.id
    form.fecha = datos.fecha
    form.vendedor = datos.vendedor
    form.destino = datos.destino
    form.cliente = datos.cliente
    form.moneda = datos.moneda
    form.estado = datos.estado
    form.productos = datos.productos
    form.total_sin_iva = datos.total_sin_iva
    form.total = datos.total
    form.codigo = datos.codigo

});


</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': 'Expediciones', link: true, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <Toast />
<!--

    <div class="px-0 py-1 m-2 mt-0 col-span-full  flex justify-start items-center">
        <Button @click="btnEditar(form.id)" v-if="form.estado!='FACTURADO'"
        class="rounded border-0 bg-yellow-500 px-2 py-0.5 text-base font-normal  m-2 hover:bg-yellow-600">
        <span class="text-black font-semibold">Editar</span>
    </Button>
    <Button @click="btnFacturar(form.id)" v-if="form.estado!='FACTURADO'"
    class="rounded border-0 bg-green-600 px-2 py-0.5 text-base font-normal  m-2 hover:bg-green-500">
    <span class="text-white font-semibold">Facturar</span>
</Button>
</div>
-->

<div class="px-0 py-0 m-0 mt-0 text-white  col-span-full  flex justify-center items-center">
            </div>

            <div
                class="mx-auto grid max-w-2xl grid-cols-1  gap-x-1 gap-y-1 px-4 py-2 sm:px-6 lg:max-w-7xl lg:grid-cols-3 lg:px-8">

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-black dark:text-gray-300"><b>
                            Fecha:
                        </b>
                        {{ form.fecha }}
                    </p>
                </div>

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-black dark:text-gray-300"><b>
                            Estado
                        </b>
                        {{ form.estado }}
                    </p>
                </div>

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-black dark:text-gray-300"><b>
                            Cliente:
                        </b>
                        {{ form.cliente }}
                    </p>
                </div>
            </div>

            <div
                class="mx-auto grid max-w-2xl grid-cols-1  overflow-auto gap-x-1 gap-y-0 px-2 py-0 sm:px-6 lg:max-w-7xl lg:grid-cols-3 lg:px-1">
                <table class="table-auto mx-2 border border-gray-300 col-span-12">
                    <thead>
                        <tr class="p-2 bg-secondary-900 border">
                            <th class="border border-gray-300 w-24">Cantidad</th>
                            <th class="border border-gray-300 p-2 w-24">Origen</th>
                            <th class="border border-gray-300 ">Producto</th>
                            <th class="border border-gray-300 w-24">Validacion</th>


                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in form.productos" :key="index"
                            class="font-sans  text-center font-normal text-gray-800 border border-gray-300">
                            <td class="border border-gray-300 p-2">{{ item.cantidad }}</td>
                            <td class="border border-gray-300 p-2">{{ item.producto.origen }}</td>
                            <td class="border border-gray-300 p-2">{{ item.producto.nombre }}</td>
                            <td class="border border-gray-300 p-2"></td>


                        </tr>
                    </tbody>

                </table>
            </div>



            <!--Contenido-->
        </div>
    </AppLayout>
</template>

<style type="text/css" scoped></style>
