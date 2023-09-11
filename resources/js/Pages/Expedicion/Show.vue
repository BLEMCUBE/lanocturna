<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import InputError from '@/Components/InputError.vue';
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import Swal from 'sweetalert2';
import InputLabel from '@/Components/InputLabel.vue';
const { permissions } = usePage().props.auth
const toast = useToast();
const titulo = "Detalle"
const ruta = 'expediciones'
const isShowModal = ref(false);
const isConfirm = ref(false);
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
const cod_maestro = useForm({
    id: '',
    codigo: '',
    index: ''
})
const btnValidar = () => {

    form.clearErrors()
    form.post(route(ruta + '.update', form.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            isShowModal.value = false
            show('success', 'Mensaje', 'Pedido confirmado')
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
const validarCodigo = ($event, id) => {

    var codigo = form.productos[id].codigo_barra;
    var texto = $event.target.value;

    if (texto.length > 0) {
        if (texto == codigo) {
            form.productos[id].producto_validado = true;
            BotonConfirmar()
        } else {
            ok('error', 'Código incorrecto. Llamar a supervisor.')

        }
    }

}


const BotonConfirmar = () => {

    var total=form.productos.length;
    var total_valido=0;
    form.productos.forEach(el => {
        if(el.producto_validado){
            total_valido+=1
        }
    })
    if(total==total_valido){
        isConfirm.value=true;
    }else{
        isConfirm.value=false;

    }
}

const ok = (icono, mensaje) => {

    Swal.fire({
        width: 350,
        title: mensaje,
        icon: icono
    })
}

const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};
const closeModal = () => {
    cod_maestro.reset();
    cod_maestro.clearErrors()
    isShowModal.value = false;
};
const openModal = (index) => {

    cod_maestro.index = index;
    isShowModal.value = true;
};

//envio de formulario
const validarCodigoMaestro = () => {

    cod_maestro.post(route(ruta + '.maestro'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.productos[cod_maestro.index].producto_validado = true;
            show('success', 'Mensaje', 'Producto Validado')
            BotonConfirmar()
            /* setTimeout(() => {
                 router.get(route(ruta + '.index'));
             }, 1000);*/
            closeModal()

        },
        onFinish: () => {

        },
        onError: () => {

        }
    });

};

onMounted(() => {
    var datos = usePage().props.venta.data;
    form.id = datos.id
    form.fecha = datos.fecha
    form.codigo = datos.codigo
    form.cliente = datos.cliente
    form.estado = datos.estado
    form.observaciones = datos.observaciones
    datos.productos.forEach(el => {
        form.productos.push(
            {
                detalle_id: el.id,
                cantidad: el.cantidad,
                producto_id: el.producto_id,
                origen: el.producto.origen,
                nombre: el.producto.nombre,
                codigo_barra: el.producto.codigo_barra,
                producto_validado: el.producto.producto_validado,
            }
        )
    })

});


</script>
<template>
    <Head :title="titulo" />
    <AppLayout
        :pagina="[{ 'label': 'Expediciones', link: true, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
        <div
            class="card px-4 py-3 mb-4 bg-white col-span-12  justify-center md:col-span-12 py-5 rounded-lg shadow-lg 2xl:col-span-10 dark:border-gray-700  dark:bg-gray-800">
            <!--Contenido-->
            <Toast />

            <div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                <h5 class="text-2xl font-medium">Cliente: {{ form.cliente }}</h5>
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
                            N° de Pedido:
                        </b>
                        {{ form.codigo }}
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
                            Cliente:
                        </b>
                        {{ form.cliente }}
                    </p>
                </div>
                <div class="col-span-3">
                    <p class="text-lg leading-6 mt-0 text-gray-700 dark:text-gray-300"><b>
                        Observaciones:
                        </b>
                        {{ form.observaciones }}
                    </p>
                </div>
            </div>

            <div
                class="mx-auto grid max-w-2xl grid-cols-1  overflow-auto gap-x-1 gap-y-0 px-2 py-0 sm:px-6 lg:max-w-7xl lg:grid-cols-3 lg:px-1">
                <table class="table-auto mx-2 border border-gray-300 col-span-12">
                    <thead>
                        <tr class="p-2 bg-secondary-900 border">
                            <th class="border border-gray-300">Cantidad</th>
                            <th class="border border-gray-300">Origen</th>
                            <th class="border border-gray-300 ">Producto</th>
                            <th class="border border-gray-300 w-48">Validacion</th>
                            <th class="border border-gray-300 w-16 text-xs font-medium">Código maestro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item, index in form.productos" :key="index"
                            class="font-sans  text-center font-normal text-gray-800 border border-gray-300">
                            <td class="border border-gray-300 p-2">{{ item.cantidad }}</td>
                            <td class="border border-gray-300 p-2">{{ item.origen }}</td>
                            <td class="border border-gray-300 p-2">{{ item.nombre }}</td>
                            <td class="border border-gray-300 p-2">
                                <input type="text" v-if="form.estado == 'FACTURADO' && !item.producto_validado"
                                    v-on:keyup.enter="validarCodigo($event, index)"
                                    class="p-inputtext text-end p-component h-8 w-full font-sans
                                    font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1" />

                                <span v-if="item.producto_validado == true" class="text-green-600 font-bold">
                                    Validado
                                </span>

                            </td>
                            <td>
                                <Button v-if="form.estado == 'FACTURADO' && !item.producto_validado"
                                    class="w-8 h-8 rounded bg-primary-700   px-2 py-1 text-xs font-normal text-white m-1 hover:bg-primary-600"
                                    @click.prevent="openModal(index)"><i class="fas fa-key"></i></Button>
                            </td>


                        </tr>
                    </tbody>

                </table>

            </div>
            <div class="flex justify-center">
                <Button @click="btnValidar" v-if="isConfirm"
                    class="rounded border-0 bg-green-700 px-2 py-0.5 text-base font-normal  m-2 hover:bg-green-600">
                    <span class="text-white font-semibold">Confirmar</span>
                </Button>
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
                    <div class="flex justify-end py-3" >
                        <Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small" @click="closeModal"
                            type="button" />

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
