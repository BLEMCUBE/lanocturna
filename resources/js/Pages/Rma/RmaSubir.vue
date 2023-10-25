<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Multiselect from '@vueform/multiselect';
import { FilterMatchMode } from 'primevue/api';
import axios from 'axios';
const toast = useToast();
const titulo = "Subir Envio RMA"
const ruta = 'rmas'

const { lista_destinos } = usePage().props
const { lista_rmas } = usePage().props
const { vendedor } = usePage().props
const { vendedor_id } = usePage().props


const setDestino = (e) => {
    var tipo = lista_destinos.find(prod => prod.value === e);
    form.destino = e;

}

const form = useForm({
    vendedor_id: '',
    destino: '',
    total: 0.0,
    total_sin_iva: 0.0,
    tipo: 'ENVIO',
    nro_compra: '',
    estado: 'RMA',
    observaciones: '',
    parametro:
    {
        rma: {},
        opt: {
            mueve_stock:"NO",
        }
    },
    productos: [],
    cliente: {
        nombre: '',
        telefono: '',
        localidad: '',
        direccion: ''
    },

})

const setMueveStock = (e) => {
    if (e.value.code == form.parametro.rma.id)
        return;
    form.parametro.opt.mueve_stock=e.value.code

}
const selectedMueveStock = ref({ name: 'NO', code: 'NO' });

const mueveStock = ref([
    { name: 'SI', code: 'SI' },
    { name: 'NO', code: 'NO' },
]);
const lista_destino = ref({
    value: '',
    closeOnSelect: true,
    placeholder: "Seleccione",
    searchable: false,
    options: [],
});

onMounted(() => {
    lista_destino.value.options = lista_destinos
    lista_rmas.value = lista_rmas
    form.vendedor_id=vendedor_id
})

const selectedRma = ref({ name: '', code: '' });
const setRma = (e) => {

    if (e.value.code == form.parametro.rma.id)
        return;

    axios.get(route(ruta + '.showsubir', e.value.code))
        .then(res => {
            var datos = res.data.venta
            form.parametro.rma = datos;
            form.productos = [{
                producto_id: datos.producto_id,
                nombre: datos.prod_nombre,
                origen: datos.prod_origen,
                cantidad: datos.prod_cantidad,
                precio: null
            }];
            form.cliente = {
                nombre: datos.cliente,
                telefono: datos.telefono,
                localidad: datos.localidad,
                direccion: datos.direccion
            };


        })




}

//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta + '.subir-store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            show('success', 'Mensaje', 'Creado')
            setTimeout(() => {
                router.get(route(ruta + '.subir-store'));
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
    router.get(route('inicio'))
};


</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <!--Contenido-->
        <div
            class="grid grid-cols-12 p-2 m-0 gap-2 mb-4 bg-white col-span-10 py-2 rounded-lg shadow-lg lg:col-span-10 2xl:col-span-8">

            <Toast />
            <div class="mt-2 mb-4 col-span-12 lg:col-span-12">

                <div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                    <h5 class="text-2xl font-medium">{{ titulo }}</h5>
                </div>
                <form>
                    <div class="grid grid-cols-12 gap-1 py-0">
                        <!--Tabla-->
                        <div class="col-span-12 mx-2 py-1 shadow-default xl:col-span-4">
                            <InputLabel for="estado" value="Rma" class="text-base font-medium leading-1 text-gray-900" />

                            <Dropdown v-model="selectedRma" id="estado" @change="setRma" filter :options="lista_rmas"
                                optionLabel="name" :pt="{
                                    root: { class: 'w-full' },
                                    trigger: { class: 'fas fa-caret-down text-gray-400 my-auto' },
                                    item: ({ props, state, context }) => ({
                                        class: context.selected ? 'text-white bg-primary-900 p-2' : context.focused ? 'bg-blue-100 p-2' : undefined
                                    })
                                }" placeholder="Seleccione" />

                        </div>

                        <div class="col-span-12 mx-2 py-1 shadow-default xl:col-span-4">
                            <InputLabel for="stock" value="Mueve Stock?" class="text-base font-medium leading-1 text-gray-900" />

                            <Dropdown v-model="selectedMueveStock" id="stock" :disabled="form.parametro.rma.stock==0" @change="setMueveStock" filter :options="mueveStock"
                                optionLabel="name" :pt="{
                                    root: { class: 'w-full' },
                                    trigger: { class: 'fas fa-caret-down text-gray-400 my-auto' },
                                    item: ({ props, state, context }) => ({
                                        class: context.selected ? 'text-white bg-primary-900 p-2' : context.focused ? 'bg-blue-100 p-2' : undefined
                                    })
                                }" placeholder="Seleccione" />
                                 <InputError class="mt-1 text-xs" message="No existe stock" v-if="form.parametro.rma.stock==0"/>

                        </div>

                        <table class="table-auto mx-2 border border-gray-300 col-span-12">
                            <thead>
                                <tr class="p-2 bg-secondary-900 border">
                                    <th class="border border-gray-300 w-24">Cantidad</th>
                                    <th class="border border-gray-300 w-24">Origen</th>
                                    <th class="border border-gray-300 ">Nombre</th>
                                    <th v-if="form.parametro.rma.prod_serie" class="border border-gray-300 ">Serie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="font-sans  text-center font-normal text-gray-800 border border-gray-300">
                                    <td class="border border-gray-300 p-1">{{ form.parametro.rma.prod_cantidad }}</td>
                                    <td class="border border-gray-300 p-1">{{ form.parametro.rma.prod_origen }}</td>
                                    <td class="border border-gray-300 p-1">{{ form.parametro.rma.prod_nombre }}</td>
                                    <td v-if="form.parametro.rma.prod_serie" class="border border-gray-300 p-1">{{
                                        form.parametro.rma.prod_serie
                                    }}</td>

                                </tr>
                            </tbody>
                        </table>
                        <!--Tabla-->
                        <div class="col-span-12 mx-2 py-1 shadow-default 2xl:col-span-12">

                            <InputError class="mt-1 text-lg w-full" :message="form.errors.productos" />
                            <InputError v-for="error in form.errors.campos_productos" class="mt-1 mb-0 text-lg" :message="error" />
                        </div>
                        <!--Datos-->
                        <div
                            class="px-0 py-1 m-2 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                            <h5 class="text-lg font-medium uppercase">Datos</h5>
                        </div>

                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="vendedor" value="Vendedor"
                                class="text-base font-medium leading-1 text-gray-900" />
                            <InputText type="text" id="vendedor" v-model="vendedor" readonly :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.vendedor_id" />
                        </div>
                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="destino" value="Destino"
                                class="text-base font-medium leading-1 text-gray-900" />
                            <Multiselect id="rol" v-model="form.destino" v-bind="lista_destino" @select="setDestino">
                            </Multiselect>
                            <InputError class="mt-1 text-xs" :message="form.errors.destino" />
                        </div>

                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="cliente" value="Cliente"
                                class="text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="cliente" readonly v-model="form.cliente.nombre"
                                placeholder="ingrese nombre cliente" :pt="{
                                    root: { class: 'h-9 w-full' }
                                }" />
                            <InputError class="mt-1 text-xs" :message="form.errors['cliente.nombre']" />

                        </div>

                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="telefono" value="Telefono"
                                class="text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="telefono" readonly v-model="form.cliente.telefono"
                                placeholder="ingrese telefono" :pt="{
                                    root: { class: 'h-9 w-full' }
                                }" />
                        </div>

                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="localidad" value="Localidad"
                                class="text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="localidad" v-model="form.cliente.localidad"
                                placeholder="ingrese localidad" :pt="{
                                    root: { class: 'h-9 w-full' }
                                }" />
                        </div>

                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="direccion" value="Dirección"
                                class="text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="direccion" v-model="form.cliente.direccion"
                                placeholder="ingrese direccion" :pt="{
                                    root: { class: 'h-9 w-full' }
                                }" />
                            <InputError class="mt-1 text-xs" :message="form.errors['cliente.direccion']" />
                        </div>

                        <!--Datos-->

                    </div>
                    <div class="flex justify-end py-3">
                        <Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small"
                            @click="cancelCrear" type="button" />

                        <Button label="Guardar" size="small" type="button" :class="{ 'opacity-50': form.processing }"
                            :disabled="form.processing" @click.prevent="submit" />
                    </div>

                </form>


            </div>


        </div>

        <!--Contenido-->

    </AppLayout>
</template>



<style type="text/css" scoped></style>
