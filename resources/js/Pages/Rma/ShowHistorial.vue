<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, router } from '@inertiajs/vue3';
import moment from 'moment';
const { permissions } = usePage().props.auth

const titulo =ref("");
const idRma =ref("");
const ruta = 'rmas'

const form = ref([])

onMounted(() => {
    form.value = usePage().props.venta.data;
    idRma.value=form.value.parametro.rma.id;
    titulo.value="DETALLE ENVIO "+ form.value.tipo;

});


</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': 'Historial Rmas', link: true, url: route(ruta + '.historial-envios') }, { 'label': titulo, link: false }]">
        <div
            class="card px-4 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->

            <div class="px-0 py-1 m-2 mt-0 text-white  col-span-full  flex justify-end items-center">
                <!--
                <Button label="Editar" v-if="permissions.includes('editar-rma') && form.estado !== 'COMPLETADO' "
                    @click="btnEditar(form.id)" :pt="{
                        root: {
                            class: 'flex items-center  bg-primary-900 justify-center font-medium w-10'
                        },
                        label: {
                            class: 'hidden'
                        }
                    }"
                    v-tooltip.top="{ value: `Editar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"><i
                        class="fas fa-edit"></i></Button>

                    -->

                    <span v-if="idRma"
                    class="w-10 h-10 flex p-0 justify-center items-center rounded bg-sky-300 text-base font-semibold text-white mr-2 hover:bg-sky-400">
                    <a  :href="route('rmas.generar_ticket', idRma)" target="_blank"><i class="fas fa-print"></i></a>
                </span>
            </div>

            <div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>

            </div>

            <div
                class="mx-auto grid max-w-2xl grid-cols-1  gap-x-1 gap-y-1 px-4 py-2 sm:px-6 lg:max-w-7xl lg:grid-cols-3 lg:px-8">

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Fecha:
                        </b>
                        {{ form.fecha }}
                    </p>
                </div>

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Estado
                        </b>
                        {{ form.estado }}
                    </p>
                </div>

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Vendedor:
                        </b>
                        {{ form.vendedor }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Facturado por:
                        </b>
                        {{ form.facturador }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Fecha Facturado:
                        </b>
                        {{ form.fecha_facturacion }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Validado por:
                        </b>
                        {{ form.validador }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Fecha Validado:
                        </b>
                        {{ form.fecha_validacion }}
                    </p>
                </div>

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Destino:
                        </b>
                        {{ form.destino }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Cliente:
                        </b>
                        {{ form.cliente }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Dirección:
                        </b>
                        {{ form.direccion }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Télefono:
                        </b>
                        {{ form.telefono }}
                    </p>
                </div>
                <div class="col-span-2">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Observaciones:
                        </b>
                        {{ form.observaciones }}
                    </p>
                </div>
            </div>
            <div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                <h5 class="text-2xl font-medium uppercase">Producto</h5>
            </div>
            <div
                class="mx-auto grid max-w-2xl grid-cols-1  overflow-auto gap-x-1 gap-y-1 px-2 py-2 sm:px-6 lg:max-w-7xl lg:grid-cols-3 lg:px-1">
                <table class="table-auto mx-2 border border-gray-300 col-span-12">
                    <thead>
                        <tr class="p-2 bg-secondary-900 border">
                            <th class="border border-gray-300 w-24">Cantidad</th>
                            <th class="border border-gray-300 p-2 w-24">Origen</th>
                            <th class="border border-gray-300 ">Nombre</th>
                        </tr>
                    </thead>
                        <tbody>
                        <tr v-for="(item, index) in form.productos" :key="index"
                            class="font-sans  text-center font-normal text-gray-800 border border-gray-300">
                            <td class="border border-gray-300 p-2">{{ item.cantidad }}</td>
                            <td class="border border-gray-300 p-2">{{ item.producto.origen }}</td>
                            <td class="border border-gray-300 p-2">{{ item.producto.nombre }}</td>
                        </tr>
                    </tbody>

                </table>
            </div>



            <!--Contenido-->
        </div>
    </AppLayout>
</template>

<style type="text/css" scoped></style>
