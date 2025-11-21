<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from "primevue/usetoast";
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';

const toast = useToast();
const titulo = "Producto Importación"
const ruta = "importaciones"

//Variables
const isShowModal = ref(false);

const form = useForm({
    id: '',
    precio: '',
    unidad: '',
    pcs_bulto: '',
    bultos: '',
    cantidad_total: '',
    valor_total: '',
    codigo_barra: '',
    cbm_bulto:'',
    cbm_total:'',
    sku:'',
    producto:'',
    producto_id:'',
    importacion_id:'',
    estado:''

})

const props = defineProps({
    clienteId: {
        type: Number,
        default: null,
    },


});


//Funciones

const addCliente = () => {
    dataEdit(props.clienteId);

};



const dataEdit = (id) => {
    axios.get(route(ruta + '.showproductomodal', id))
        .then(res => {
            var datos = res.data.importacion_detalle
            form.id = datos.id
            form.unidad = datos.unidad
            form.precio = datos.precio
            form.pcs_bulto = datos.pcs_bulto
            form.bultos = datos.bultos
            form.cantidad_total = datos.cantidad_total
            form.valor_total = datos.valor_total
            form.codigo_barra = datos.codigo_barra
            form.cbm_bulto = datos.cbm_bulto
            form.cbm_total = datos.cbm_total
            form.sku = datos.sku
            form.producto = datos.producto.nombre
            form.producto_id = datos.producto.id
            form.codigo_barra = datos.codigo_barra
            form.importacion_id = datos.importacion.id
            form.estado = datos.importacion.estado
            isShowModal.value = true;

        })

};


const closeModal = () => {
    form.reset();
    form.clearErrors()
    isShowModal.value = false;
};


//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta + '.updateproducto', form.id), {
        //preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            isShowModal.value = false
            show('success', 'Mensaje', 'Se ha editado')
            setTimeout(() => {
                router.get(route(ruta + '.show', form.importacion_id));
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
</script>

<template>
    <section>
        <button type="button" @click="addCliente" :id="'show-' + props.clienteId"><i class="fas fa-edit"></i></button>
        <Dialog v-model:visible="isShowModal" modal :header="'Editar ' + titulo" :style="{ width: '40vw' }" position="top"
            :pt="{
                header: {
                    class: 'mt-6 p-2'
                },
                content: {
                    class: 'p-2'
                },
            }">
            <form>
                <div class="px-2 pb-0 grid grid-cols-12 gap-x-4 mb-2">

                    <div class="col-span-12 shadow-default lg:col-span-12">
                        <InputLabel for="precio" :value="'SKU: '+form.sku"
                            class="block text-base font-medium leading-6 text-gray-900" />
                    </div>

                    <div class="col-span-12 shadow-default lg:col-span-12 mb-4">
                        <InputLabel for="precio" :value="'PRODUCTO: '+form.producto"
                            class="block text-base font-medium leading-6 text-gray-900" />
                    </div>


                    <div class="col-span-12 shadow-default lg:col-span-4">
                        <InputLabel for="precio" value="precio"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <input type="number" v-model="form.precio" step="0.1" class="p-inputtext p-component text-gray-700 bg-white
                            border appearance-none rounded text-sm px-2
                            p-inputnumber-input h-9 px-0 py-0 m-0 w-full text-end" />
                        <InputError class="mt-1 text-xs" :message="form.errors.precio" />
                    </div>

                    <div class="col-span-12 shadow-default lg:col-span-4">
                        <InputLabel for="unidad" value="Unidad"
                        class="block text-base font-medium leading-6 text-gray-900" />

                        <InputText type="text" id="unidad" v-model="form.unidad" placeholder="Ingrese unidad" :pt="{
                            root: { class: 'h-9 w-full' }
                        }" />
                        <InputError class="mt-1 text-xs" :message="form.errors.unidad" />
                    </div>


                    <div class="col-span-12 shadow-default lg:col-span-4">
                        <InputLabel for="pcs_bulto" value="PCS Bulto"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <input type="number" v-model="form.pcs_bulto" class="p-inputtext p-component text-gray-700 bg-white
                            border appearance-none rounded text-sm px-2 py-0
                            p-inputnumber-input h-9 px-0 py-0 m-0 w-full text-end text-sm" />
                        <InputError class="mt-1 text-xs" :message="form.errors.pcs_bulto" />
                    </div>

                    <div class="col-span-12 shadow-default lg:col-span-4">
                        <InputLabel for="bultos" value="Bultos"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <input type="number" v-model="form.bultos" class="p-inputtext p-component text-gray-700 bg-white
                            border appearance-none rounded text-sm px-2 py-0
                            p-inputnumber-input h-9 px-0 py-0 m-0 w-full text-end text-sm" />
                        <InputError class="mt-1 text-xs" :message="form.errors.bultos" />
                    </div>

                    <div class="col-span-12 shadow-default lg:col-span-4">
                        <InputLabel for="cantidad_total" value="Cantidad Total"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <input type="number" v-model="form.cantidad_total" class="p-inputtext p-component text-gray-700 bg-white
                            border appearance-none rounded text-sm px-2 py-0
                            p-inputnumber-input h-9 px-0 py-0 m-0 w-full text-end text-sm" />
                        <InputError class="mt-1 text-xs" :message="form.errors.cantidad_total" />
                    </div>

                    <div class="col-span-12 shadow-default lg:col-span-4">
                        <InputLabel for="valor_total" value="Valor Total"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <input type="number" v-model="form.valor_total" step="0.1" class="p-inputtext p-component text-gray-700 bg-white
                            border appearance-none rounded text-sm px-2 py-0
                            p-inputnumber-input h-9 px-0 py-0 m-0 w-full text-end text-sm" />
                        <InputError class="mt-1 text-xs" :message="form.errors.valor_total" />
                    </div>

                    <div class="col-span-12 shadow-default lg:col-span-4">
                        <InputLabel for="cbm_bulto" value="CBM/Bulto"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <input type="number" v-model="form.cbm_bulto" step="0.1" class="p-inputtext p-component text-gray-700 bg-white
                            border appearance-none rounded text-sm px-2 py-0
                            p-inputnumber-input h-9 px-0 py-0 m-0 w-full text-end text-sm" />
                        <InputError class="mt-1 text-xs" :message="form.errors.cbm_bulto" />
                    </div>

                    <div class="col-span-12 shadow-default lg:col-span-4">
                        <InputLabel for="cbm_total" value="Total CBM"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <input type="number" v-model="form.cbm_total" step="0.1" class="p-inputtext p-component text-gray-700 bg-white
                            border appearance-none rounded text-sm px-2 py-0
                            p-inputnumber-input h-9 px-0 py-0 m-0 w-full text-end text-sm" />
                        <InputError class="mt-1 text-xs" :message="form.errors.cbm_total" />
                    </div>

                </div>
                <div class="flex justify-end py-3">
                    <Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small" @click="closeModal"
                        type="button" />

                    <Button label="Guardar" size="small" type="button" @click.prevent="submit"
                        :class="{ 'opacity-50': form.processing }" :disabled="form.processing" />
                </div>
            </form>
        </Dialog>
    </section>
</template>
