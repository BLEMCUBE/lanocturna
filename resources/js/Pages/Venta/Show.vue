<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm } from '@inertiajs/vue3';

const previewImage = ref('/images/productos/sin_foto.png');

const titulo = "Detalle Venta"
const ruta = 'ventas'

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
    <AppLayout :pagina="[{ 'label': 'Ventas', link: true, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>
            </div>

            <div
                class="mx-auto grid max-w-2xl grid-cols-1  gap-x-1 gap-y-1 px-4 py-2 sm:px-6 lg:max-w-7xl lg:grid-cols-3 lg:px-8">

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-2 text-gray-700 dark:text-gray-300"><b>
                            Fecha:
                        </b>
                        {{ form.fecha }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-2 text-gray-700 dark:text-gray-300"><b>
                            N° de Venta:
                        </b>
                        {{ form.codigo }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-2 text-gray-700 dark:text-gray-300"><b>
                            Estado
                        </b>
                        {{ form.estado }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-2 text-gray-700 dark:text-gray-300"><b>
                            Total:
                        </b>
                        {{ form.total.toFixed(2) }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-2 text-gray-700 dark:text-gray-300"><b>
                            Total sin IVA:
                        </b>
                        {{ form.total_sin_iva.toFixed(2) }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-2 text-gray-700 dark:text-gray-300"><b>
                            Moneda:
                        </b>
                        {{ form.moneda }}
                    </p>
                </div>

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-2 text-gray-700 dark:text-gray-300"><b>
                            Vendedor:
                        </b>
                        {{ form.vendedor }}
                    </p>
                </div>

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-2 text-gray-700 dark:text-gray-300"><b>
                            Destino:
                        </b>
                        {{ form.destino }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-2 text-gray-700 dark:text-gray-300"><b>
                            Cliente:
                        </b>
                        {{ form.cliente }}
                    </p>
                </div>
            </div>
            <div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                <h5 class="text-2xl font-medium">Productos</h5>
            </div>
            <div
                class="mx-auto grid max-w-2xl grid-cols-1  overflow-auto gap-x-1 gap-y-1 px-2 py-2 sm:px-6 lg:max-w-7xl lg:grid-cols-3 lg:px-1">
                <table class="table-auto mx-2 border border-gray-300 col-span-12">
                            <thead>
                                <tr class="p-2 bg-secondary-900 border">
                                    <th class="border border-gray-300 w-24">Cantidad</th>
                                    <th class="border border-gray-300 p-2 w-24">Origen</th>
                                    <th class="border border-gray-300 ">Producto</th>
                                    <th class="border border-gray-300 w-24">Código de Barras</th>
                                    <th class="border border-gray-300 w-24">Precio</th>
                                    <th class="border border-gray-300 w-24">Total</th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in form.productos" :key="index"
                                    class="font-sans  text-center font-normal text-gray-800 border border-gray-300">
                                    <td class="border border-gray-300 p-2">{{ item.cantidad }}</td>
                                    <td class="border border-gray-300 p-2">{{ item.producto.origen }}</td>
                                    <td class="border border-gray-300 p-2">{{ item.producto.nombre }}</td>
                                    <td class="border border-gray-300 p-2">{{ item.producto.codigo_barra }}</td>
                                    <td class="border border-gray-300 p-2">{{ item.precio.toFixed(2) }}</td>
                                    <td class="border border-gray-300 p-2">{{ item.total.toFixed(2) }}</td>

                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end"><b>Total: </b></td>
                                    <td class="text-center"><b> {{ form.moneda=='Pesos'?'$ ':'USD ' }} {{ form.total.toFixed(2) }} </b></td>
                                </tr>

                            </tfoot>
                        </table>
</div>



            <!--Contenido-->
        </div>
    </AppLayout>
</template>

<style type="text/css" scoped></style>
