<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from "primevue/usetoast";

const toast = useToast();
const titulo = "Bultos"
const ruta = "depositos"
const maxBultos = ref();
//Variables
const isShowModal = ref(false);

const form = useForm({
    origen_id:'',
    origen_nombre:'',
    destino_id:'',
    productos:[
    ]


})

const props = defineProps({
    origenId: {
        type: Number,
        default: null,
    },
    origenNombre: {
        type: String,
        default: '',
    },
    disabledStatus: {
        type: Boolean,
        default: false,
    },
    productos: {
        type: Array,
        default: [],
    },


});


//Funciones

const addCliente = () => {
    dataEdit(props.origenId);

};

const dataEdit = (id) => {

    axios.post(route(ruta + '.showproductos'),{
    productos: props.productos,
    origen_id:props.origenId
  })
        .then(res => {
            form.productos = res.data.detalle_productos


            form.origen_nombre =props.origenNombre
            form.origen_id = props.origenId
            depositos.value=res.data.lista_depositos



            isShowModal.value = true;

        })

};
const setDeposito = (e) => {

    form.destino_id = selectedDeposito.value.code;
}

const selectedDeposito = ref();
const depositos = ref();

const closeModal = () => {
    form.reset();
    form.clearErrors()
    isShowModal.value = false;
};


//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta + '.updatedeposito', form.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            isShowModal.value = false
            show('success', 'Mensaje', 'Se ha editado')
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
</script>

<template>

        <Button  :disabled="props.disabledStatus" class="w-auto rounded bg-primary-900 px-2  text-base font-normal text-white m-1 hover:bg-primary-100" type="button" @click="addCliente">
            <i class="fas fa-exchange-alt w-6 h-4"></i></Button>



        <Dialog v-model:visible="isShowModal" modal :header="'Mover ' + titulo"
        :style="{ width: '30vw' }" :breakpoints="{ '960px': '30vw', '641px': '30vw' }"
         position="top"
            :pt="{
                header: {
                    class: 'mt-6 p-2'
                },
                content: {
                    class: 'p-4 lg:p-4'
                },
            }">
            <form @submit.prevent="submit">
                <div class="px-2 grid grid-cols-6 gap-4 md:gap-3 lg:gap-4 mb-2">


                    <table class="col-span-6 border">
                    <thead>
                        <tr class="w-full border">
                            <th class="w-26 text-center border">
                                SKU
                            </th>
                            <th class="text-center border">
                                PRODUCTO
                            </th>
                            <th class="text-center border" v-if="props.productos.length>1">
                                BULTOS
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr  class="w-full text-center border" v-for="item in form.productos">
                            <td class="text-center border">
                                {{item.sku}}
                            </td>
                            <td class="text-center border">
                                {{item.nombre_producto}}
                            </td>
                            <td class="text-center border" v-if="props.productos.length>1">
                                {{item.bultos}}
                            </td>
                        </tr>
                    </tbody>
                </table>


                        <div class="col-span-6 shadow-default">
                            <InputLabel for="origen" value="Origen"
                            class="block text-base font-medium leading-6 text-gray-900" />
                            <input type="text" v-model="form.origen_nombre" disabled
                            class="p-inputtext p-component h-9 w-full font-sans  font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1">

                        </div>

                    <div class="col-span-6 shadow-default">
                        <InputLabel for="destino" value="Destino"
                            class="block text-base font-medium leading-6 text-gray-900" />
                            <Dropdown v-model="selectedDeposito" @change="setDeposito" :options="depositos" optionLabel="name"
                                :pt="{
                                    root: { class: 'w-full' },
                                    trigger: { class: 'fas fa-caret-down text-gray-200 my-auto' },
                                    item: ({ props, state, context }) => ({
                                        class: context.selected ? 'text-white bg-primary-900' : context.focused ? 'bg-blue-100' : undefined
                                    })
                                }" placeholder="Seleccione destino" />
                            <InputError class="mt-1 text-xs" :message="form.errors.destino_id" />

                    </div>
{{ form }}
                    <div class="col-span-6 shadow-default" v-if="props.productos.length==1">
                        <InputLabel for="bultos" value="Bultos" v-if="form.productos.length>0"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <input type="number" v-if="form.productos.length>0" v-model="form.productos[0].bultos" step="1" min="0" :max="form.productos[0].maxBultos"
                            class="p-inputtext p-component h-9 w-full text-end
                            font-normal text-gray-700  border border-gray-300 transition-colors
                             duration-200 appearance-none rounded-md text-sm px-3 py-1">

                        <!--
                            <InputError v-if="form.productos.length>0" class="mt-1 text-xs" :message="form.errors.productos[0].bultos" />

                        -->
                    </div>
                </div>
                <div class="flex justify-end py-3">
                    <Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small" @click="closeModal"
                        type="button" />

                    <Button label="Mover" size="small" type="submit" :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing" />
                </div>
            </form>
        </Dialog>

</template>
