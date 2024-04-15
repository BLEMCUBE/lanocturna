<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, router } from '@inertiajs/vue3';
import moment from 'moment';
const { permissions } = usePage().props.auth

const titulo =ref("");
const editable =ref(false);
const ruta = 'rmas'

const form = ref([]);
const btnEditar = (id) => {
    router.get(route(ruta + '.rma-edit', id));

};

onMounted(() => {
    form.value = usePage().props.venta.data;
    editable.value=usePage().props.editable;
    titulo.value="DETALLE "+ form.value.tipo;

});

const formatDate = (dat) => {
    return moment(dat).format("DD/MM/YYYY");
}
</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': 'Rmas', link: true, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
        <div
            class="card px-4 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->

            <div class="px-0 py-1 m-2 mt-0 text-white  col-span-full  flex justify-end items-center">
                <span v-if="form.id"
                    class="w-10 h-10 p-0 flex justify-center items-center rounded bg-sky-300 text-base font-semibold text-white mr-2 hover:bg-sky-400">
                    <a  :href="route('rmas.generar_ticket', form.id)" target="_blank"><i class="fas fa-print"></i></a>
                </span>


                    <Button label="Editar" v-if="permissions.includes('editar-rma') && editable ==true"
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


            </div>
            <div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>

            </div>

            <div
                class="mx-auto grid max-w-2xl grid-cols-1  gap-x-1 gap-y-1 px-4 py-2 sm:px-6 lg:max-w-7xl lg:grid-cols-3 lg:px-8">

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            Usuario:
                        </b>
                        {{ form.vendedor }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                        Fecha Ingreso:
                        </b>
                        {{formatDate( form.fecha_ingreso )}}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            N° de Servicio:
                        </b>
                        {{ form.nro_servicio }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            Estado
                        </b>
                        {{ form.estado }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            Ingresado/Entregado
                        </b>
                        {{ form.modo }}
                    </p>
                </div>

                <div class="col-span-1" v-if="form.fecha_compra">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                        Fecha Compra:
                        </b>
                        {{formatDate( form.fecha_compra )}}
                    </p>
                </div>

                <div class="col-span-1" v-if="form.nro_factura">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            N° de Factura:
                        </b>
                        {{ form.nro_factura }}
                    </p>
                </div>

                 <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            Cliente:
                        </b>
                        {{ form.cliente }}
                    </p>
                </div>

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            Télefono:
                        </b>
                        {{ form.telefono }}
                    </p>
                </div>
                <div class="col-span-1" v-if="form.costo_presupuestado">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                        Costo Presupuestado:
                        </b>
                        {{ form.costo_presupuestado }}
                    </p>
                </div>
                <div class="col-span-3">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            Defecto:
                        </b>
                        {{ form.defecto }}
                    </p>
                </div>
                <div class="col-span-3">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
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
                            <th v-if="form.prod_serie" class="border border-gray-300 ">Serie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="font-sans  text-center font-normal text-gray-800 border border-gray-300">
                            <td class="border border-gray-300 p-2">{{ form.prod_cantidad }}</td>
                            <td class="border border-gray-300 p-2">{{ form.prod_origen }}</td>
                            <td class="border border-gray-300 p-2">{{ form.prod_nombre}}</td>
                            <td v-if="form.prod_serie" class="border border-gray-300 p-2">{{ form.prod_serie}}</td>

                        </tr>
                    </tbody>

                </table>
            </div>



            <!--Contenido-->
        </div>
    </AppLayout>
</template>

<style type="text/css" scoped></style>
