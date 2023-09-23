<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from "primevue/usetoast";

const toast = useToast();
const titulo = "Mover Bultos"
const ruta = "depositos"
const maxBultos = ref();
//Variables
const isShowModal = ref(false);

const form = useForm({
    id: '',
    bultos:'',
    origen_id:'',
    destino_id:'',
    sku:'',


})

const props = defineProps({
    detalleId: {
        type: Number,
        default: null,
    },


});


//Funciones

const addCliente = () => {
    dataEdit(props.detalleId);

};

const dataEdit = (id) => {

    axios.get(route(ruta + '.showcambiarproducto', id))
        .then(res => {
            var datos = res.data.deposito_detalle
            form.id = datos.id
            form.sku = datos.sku
            form.bultos = datos.bultos
            form.nombre_deposito = datos.deposito_lista.nombre
            form.nombre_producto = datos.producto.nombre
            form.origen_id = datos.deposito_lista.id

            monedas.value=res.data.lista_depositos
            maxBultos.value=datos.bultos
            isShowModal.value = true;

        })

};
const setMoneda = (e) => {

    form.destino_id = selectedMoneda.value.code;
}

const selectedMoneda = ref();
const monedas = ref();

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
    <section>
        <button  type="button" @click="addCliente"><i class="fas fa-exchange-alt"></i></button>

        <Dialog v-model:visible="isShowModal" modal :header="'Mover ' + titulo"
        :style="{ width: '20vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }"
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
                    <div class="col-span-6 shadow-default">
                        <InputLabel  :value="'SKU: '+ form.sku"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <InputLabel  :value="'PRODUCTO: '+ form.nombre_producto"
                            class="block text-base font-medium leading-6 text-gray-900" />

                    </div>
                    <div class="col-span-6 shadow-default">
                        <InputLabel for="origen" value="Origen"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <input type="text" v-model="form.nombre_deposito" disabled
                            class="p-inputtext p-component h-9 w-full font-sans  font-normal text-gray-700 dark:text-white/80 bg-white dark:bg-gray-900 border border-gray-300 dark:border-blue-900/40 transition-colors duration-200 appearance-none rounded-md text-sm px-2 py-1">


                    </div>

                    <div class="col-span-6 shadow-default">
                        <InputLabel for="destino" value="Destino"
                            class="block text-base font-medium leading-6 text-gray-900" />
                            <Dropdown v-model="selectedMoneda" @change="setMoneda" :options="monedas" optionLabel="name"
                                :pt="{
                                    root: { class: 'w-full' },
                                    trigger: { class: 'fas fa-caret-down text-gray-200 my-auto' },
                                    item: ({ props, state, context }) => ({
                                        class: context.selected ? 'text-white bg-primary-900' : context.focused ? 'bg-blue-100' : undefined
                                    })
                                }" placeholder="Seleccione destino" />
                            <InputError class="mt-1 text-xs" :message="form.errors.destino_id" />

                    </div>
                    <div class="col-span-6 shadow-default">
                        <InputLabel for="bultos" value="Bultos"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <input type="number" v-model="form.bultos" step="1" :max="maxBultos"
                            class="p-inputtext p-component h-9 w-full text-end
                            font-normal text-gray-700  border border-gray-300 transition-colors
                             duration-200 appearance-none rounded-md text-sm px-3 py-1">

                        <InputError class="mt-1 text-xs" :message="form.errors.bultos" />
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
    </section>
</template>
