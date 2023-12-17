<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { useToast } from "primevue/usetoast";
import moment from 'moment';
import Swal from 'sweetalert2';

const isShowModal = ref(false);
const toast = useToast();
const titulo = "Detalle Envio Rma"
const ruta = 'rmas'
const closeModal = () => {
    cod_maestro.reset();
    cod_maestro.clearErrors()
    isShowModal.value = false;
};
const openModal = (index) => {

    cod_maestro.index = index;
    isShowModal.value = true;
};

const cod_maestro = useForm({
    id: '',
    codigo: '',
    index: ''
})


//envio de formulario
const validarCodigoMaestro = () => {

    cod_maestro.post(route(ruta + '.maestro'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            //form.productos[cod_maestro.index].producto_validado = true;
            show('success', 'Mensaje', 'Se ha Validado')
            //BotonConfirmar()
             setTimeout(() => {
                 router.get(route(ruta + '.index'));
             }, 1000);
            closeModal()

        },
        onFinish: () => {

        },
        onError: () => {

        }
    });

};

const ok = (icono, mensaje) => {

    Swal.fire({
        width: 350,
        title: mensaje,
        icon: icono
    })
}


const BotonConfirmar = () => {
    /*
    var total = form.productos.length;
    var total_valido = 0;
    form.productos.forEach(el => {
        if (el.producto_validado) {
            total_valido += 1
        }
    })
    if (total == total_valido) {
        isConfirm.value = true;
    } else {
        isConfirm.value = false;

    }*/
}
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
    facturado: '',
    estado: '',
    observaciones: '',
    productos: [],
    cliente: '',
    direccion: '',
    parametro: [],
    rma: [],
    localidad: '',
    telefono: '',

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
            show('success', 'Mensaje', 'Se ha Validado')
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
const formatDate = (dat) => {
    return moment(dat).format("DD/MM/YYYY");
}
const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};
onMounted(() => {
    var datos = usePage().props.venta.data;
    form.id = datos.id
    form.fecha = datos.fecha
    form.vendedor = datos.vendedor
    form.facturado = datos.facturado
    form.facturador = datos.facturador
    form.validador = datos.validador
    form.observaciones = datos.observaciones
    form.destino = datos.destino
    form.cliente = datos.cliente
    form.empresa = datos.empresa
    form.rut = datos.rut
    form.direccion = datos.direccion
    form.localidad = datos.localidad
    form.telefono = datos.telefono
    form.moneda = datos.moneda
    form.estado = datos.estado
    form.productos = datos.productos
    form.parametro = datos.parametro
    form.rma = datos.parametro.rma
    form.total_sin_iva = datos.total_sin_iva
    form.total = datos.total
    form.codigo = datos.codigo
    form.tipo_cambio = datos.tipo_cambio.toFixed(2)

});


</script>
<template>
    <Head :title="titulo" />
    <AppLayout
        :pagina="[{ 'label': 'Validación Rma', link: true, url: route(ruta + '.validacion') }, { 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <Toast />
            <div class="px-0 py-1 m-2 mt-0 col-span-full  flex justify-start items-center">
                <!--

                    <Button @click="btnEditar(form.id)" v-if="form.estado!='FACTURADO'"
                    class="rounded border-0 bg-yellow-500 px-2 py-0.5 text-base font-normal  m-2 hover:bg-yellow-600">
                    <span class="text-black font-semibold">Editar</span>
                </Button>
            -->
                <Button v-if="form.facturado == '0'"
                    class="rounded border-0 bg-green-700 px-2 py-0.5 text-base font-normal  m-2 hover:bg-green-600"
                    @click.prevent="openModal(form.id)"> <span class="text-white font-semibold">Validar</span></Button>


            </div>


            <div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                <h5 class="text-2xl font-medium">{{ titulo }}</h5>

            </div>


            <div
                class="mx-auto grid max-w-2xl grid-cols-1  gap-x-1 gap-y-1 px-4 py-2 sm:px-6 lg:max-w-7xl lg:grid-cols-3 lg:px-8">

                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            Fecha Ingreso:
                        </b>
                        {{ formatDate(form.rma.fecha_ingreso) }}
                    </p>
                </div>
                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            N° de Servicio:
                        </b>
                        {{ form.rma.nro_servicio }}
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
                        {{ form.rma.modo }}
                    </p>
                </div>

                <div class="col-span-1" v-if="form.fecha_compra">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            Fecha Compra:
                        </b>
                        {{ formatDate(form.rma.fecha_compra) }}
                    </p>
                </div>

                <div class="col-span-1" v-if="form.rma.nro_factura">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            N° de Factura:
                        </b>
                        {{ form.rma.nro_factura }}
                    </p>
                </div>

                <div class="col-span-1" v-if="form.rma.costo_presupuestado > 0">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            Costo Presupuestado:
                        </b>
                        {{ form.rma.costo_presupuestado }}
                    </p>
                </div>


                <div class="col-span-1">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                            Vendedor:
                        </b>
                        {{ form.rma.vendedor }}
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
                <div class="col-span-3">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            Defecto:
                        </b>
                        {{ form.rma.defecto }}
                    </p>
                </div>
                <div class="col-span-3">
                    <p class="text-lg leading-6 mt-0 text-gray-700"><b>
                            Observaciones:
                        </b>
                        {{ form.rma.observaciones }}
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
                            <th class="border border-gray-300 ">Nombre</th>
                            <th v-if="form.rma.prod_serie" class="border border-gray-300 ">Serie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="font-sans  text-center font-normal text-gray-800 border border-gray-300">
                            <td class="border border-gray-300 p-2">{{ form.rma.prod_cantidad }}</td>
                            <td class="border border-gray-300 p-2">{{ form.rma.prod_origen }}</td>
                            <td class="border border-gray-300 p-2">{{ form.rma.prod_nombre }}</td>
                            <td v-if="form.rma.prod_serie" class="border border-gray-300 p-2">{{ form.rma.prod_serie }}</td>

                        </tr>
                    </tbody>

                </table>
            </div>


            <!--Modal codigo maestro-->
            <Dialog v-model:visible="isShowModal" modal header="Código Maestro" :style="{ width: '50vw' }" position="top"
                :pt="{
                    header: {
                        class: 'mt-6 p-2 lg:p-4 '
                    },
                    content: {
                        class: 'p-4 lg:p-4'
                    },
                }">
                <form @submit.prevent="validarCodigoMaestro">
                    <div class="px-2 grid grid-cols-6 gap-4 md:gap-3 2xl:gap-6 mb-2">

                        <div class="col-span-6 shadow-default xl:col-span-6">
                            <InputLabel for="codigo" value="Código"
                                class="block text-base font-medium leading-6 text-gray-900" />
                            <input type="password" v-model="cod_maestro.codigo"
                                class="p-inputtext text-end p-component h-9 w-full font-sans  font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1" />

                            <InputError class="mt-1 text-xs" :message="cod_maestro.errors.codigo" />
                        </div>

                    </div>
                    <div class="flex justify-end py-3">
                        <Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small"
                            @click="closeModal" type="button" />

                        <Button label="Guardar" size="small" type="submit" :class="{ 'opacity-50': cod_maestro.processing }"
                            :disabled="cod_maestro.processing" />
                    </div>
                </form>
            </Dialog>
            <!--Modal codigo maestro-->

            <!--Contenido-->
        </div>
    </AppLayout>
</template>

<style type="text/css" scoped></style>
