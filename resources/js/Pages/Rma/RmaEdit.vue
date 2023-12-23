<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import 'vue-datepicker-next/locale/es.es.js';
const toast = useToast();
const dateHoy = ref(null);
const dateCompra = ref(null);
const titulo =ref("");
const ruta = 'rmas'
const lista_productos = ref();
const productoSelect = ref();

const setEstado = (e) => {

    if (selectedEstado.value.code == form.estado)
        return;

    form.estado = selectedEstado.value.code;

}
const setModo = (e) => {

    if (selectedModo.value.code == form.modo)
        return;

    form.modo = selectedModo.value.code;

}
const setTipo = (e) => {
    form.clearErrors();
    if (selectedTipo.value.code == form.tipo)
        return;

    form.tipo = selectedTipo.value.code;

}

const setProducto = (e) => {
    form.clearErrors('producto_id');
    if (form.producto_id == productoSelect.value.id)
        return;
    form.producto_id = productoSelect.value.id;
    form.prod_origen = productoSelect.value.origen;
    form.prod_nombre = productoSelect.value.nombre;


}
//filtrado
const filtrado = (value) => {
    form.fecha_compra = value;
}

const form = useForm({
    id:'',
    vendedor_id: '',
    fecha_ingreso: '',
    fecha_compra: '',
    nro_factura: '',
    vendedor_id: '',
    nro_servicio: '',
    costo_presupuestado: '',
    modo: '',
    estado: '',
    tipo: '',
    producto_id: '',
    prod_origen: '',
    prod_cantidad: '',
    prod_nombre: '',
    prod_serie: '',
    observaciones: '',
    defecto: '',
    cliente: {
        nombre: '',
        telefono: ''
    },

})

onMounted(() => {
    var datos=usePage().props.venta.data
    lista_productos.value = Array.from(usePage().props.productos.data, (x) => x);
    titulo.value= "EDITAR "+ datos.tipo
    form.id=datos.id
    form.nro_servicio=datos.nro_servicio
    form.cliente= {
        nombre: datos.cliente,
        telefono: datos.telefono,
    }

    form.costo_presupuestado = datos.costo_presupuestado
    form.observaciones = datos.observaciones
    form.defecto = datos.defecto
    form.producto_id = datos.producto_id
    form.prod_origen = datos.prod_origen
    form.prod_nombre = datos.prod_nombre
    form.prod_cantidad = datos.prod_cantidad
    form.prod_serie = datos.prod_serie
    form.vendedor_id = datos.vendedor_id
    form.nro_factura = datos.nro_factura
    form.fecha_ingreso=datos.fecha_ingreso
    form.fecha_compra=datos.fecha_compra

    dateHoy.value=datos.fecha_ingreso
    dateCompra.value=datos.fecha_compra

    productoSelect.value={'id':datos.producto_id,'origen':datos.prod_origen,'nombre':datos.prod_nombre,'imagen':datos.imagen}


    form.tipo = datos.tipo
    selectedTipo.value.code = datos.tipo
    selectedTipo.value.name = datos.tipo

    form.estado = datos.estado
    selectedEstado.value.code = datos.estado
    selectedEstado.value.name = datos.estado

    form.modo = datos.modo
    selectedModo.value.code = datos.modo
    selectedModo.value.name = datos.modo

})



const selectedModo = ref({ name: '', code: '' });
const selectedEstado = ref({ name: '', code: '' });
const selectedTipo = ref({ name: '', code: '' });

const estados = ref([
    { name: 'RECIBIDO', code: 'RECIBIDO' },
    { name: 'PRESUPUESTADO', code: 'PRESUPUESTADO' },
    { name: 'REPARADO', code: 'REPARADO' },
    { name: 'CAMBIO PRODUCTO', code: 'CAMBIO PRODUCTO' },
    { name: 'ESPERANDO STOCK', code: 'ESPERANDO STOCK' },
]);

const modos = ref([
    { name: 'INGRESADO', code: 'INGRESADO' },
    { name: 'ENTREGADO', code: 'ENTREGADO' },
]);

const tipos = ref([
    { name: 'RMA', code: 'RMA' },
    { name: 'PRESUPUESTO', code: 'PRESUPUESTO' },
]);


//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta + '.rma-update', form.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            show('success', 'Mensaje', 'Actualizado')
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

const cancelCrear = () => {
    router.get(route('rmas.index'))
};


</script>
<template>
    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': 'Rmas', link: false, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
        <!--Contenido-->
        <div
            class="grid grid-cols-12 p-0 m-0 gap-4 mb-4 bg-white col-span-12 py-2 rounded-lg shadow-lg lg:col-span-12 2xl:col-span-10">

            <Toast />
            <div class="m-4 col-span-12 lg:col-span-12">

                <div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                    <h5 class="text-2xl font-medium">{{ titulo }}</h5>
                </div>
                <form>

                    <div class="grid grid-cols-12 gap-2 py-0">
                        <!--Datos-->

                        <!--Fecha ingreso-->
                        <div class="col-span-12 mx-2 py-1 shadow-default xl:col-span-4">
                            <InputLabel for="fecha_ingreso" value="Fecha Ingreso"
                                class="text-base font-medium leading-1 text-gray-900" />
                            <date-picker id="fecha_ingreso" type="date" value-type="YYYY-MM-DD" format="DD/MM/YYYY"
                                class="col-span-6 lg:col-span-2 text-2xl text-gray-700  bg-white  transition-colors duration-200 border-0"
                                v-model:value="dateHoy" lang="es" placeholder="Seleccione" disabled
                                :clearable="false"></date-picker>
                        </div>

                        <!--nro_servicio-->
                        <div class="col-span-12 mx-2 py-1 shadow-default xl:col-span-4">
                            <InputLabel for="nro_servicio" value="Número servicio"
                                class="text-base font-medium leading-1 text-gray-900" />
                            <InputText type="text" id="nro_servicio" v-model="form.nro_servicio" readonly :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.nro_servicio" />
                        </div>
                        <!--tipo-->
                        <div class="col-span-12 mx-2 py-1 shadow-default xl:col-span-4">
                            <InputLabel for="tipo" value="Tipo" class="text-base font-medium leading-1 text-gray-900" />

                            <Dropdown v-model="selectedTipo" id="tipo" @change="setTipo" :options="tipos" optionLabel="name"
                                :pt="{
                                    root: { class: 'w-full' },
                                    trigger: { class: 'fas fa-caret-down text-gray-400 my-auto' },
                                    item: ({ props, state, context }) => ({
                                        class: context.selected ? 'text-white bg-primary-900 p-2' : context.focused ? 'bg-blue-100 p-2' : undefined
                                    })
                                }" placeholder="Seleccione" />
                            <InputError class="mt-1 text-xs" :message="form.errors.tipo" />
                        </div>

                        <div class="col-span-12 mx-2 py-1 shadow-default xl:col-span-4">
                            <InputLabel for="modo" value="Ingresado/Entregado" class="text-base font-medium leading-1 text-gray-900" />

                            <Dropdown v-model="selectedModo" id="modo" @change="setModo" filter
                                :options="modos" optionLabel="name" :pt="{
                                    root: { class: 'w-full' },
                                    trigger: { class: 'fas fa-caret-down text-gray-400 my-auto' },
                                    item: ({ props, state, context }) => ({
                                        class: context.selected ? 'text-white bg-primary-900 p-2' : context.focused ? 'bg-blue-100 p-2' : undefined
                                    })
                                }" placeholder="Seleccione" />
                            <InputError class="mt-1 text-xs" :message="form.errors.modo" />
                        </div>

                        <div class="col-span-12 mx-2 py-1 shadow-default xl:col-span-4">
                            <InputLabel for="estado" value="Estado" class="text-base font-medium leading-1 text-gray-900" />

                            <Dropdown v-model="selectedEstado" id="estado" @change="setEstado" filter
                                :options="estados" optionLabel="name" :pt="{
                                    root: { class: 'w-full' },
                                    trigger: { class: 'fas fa-caret-down text-gray-400 my-auto' },
                                    item: ({ props, state, context }) => ({
                                        class: context.selected ? 'text-white bg-primary-900 p-2' : context.focused ? 'bg-blue-100 p-2' : undefined
                                    })
                                }" placeholder="Seleccione" />
                            <InputError class="mt-1 text-xs" :message="form.errors.estado" />
                        </div>
                        <!--fecha_compra-->
                        <div class="col-span-12 mx-2 py-1 shadow-default xl:col-span-4" v-if="form.tipo=='RMA'">
                            <InputLabel for="fecha_compra" value="Fecha de compra"
                                class="text-base font-medium leading-1 text-gray-900" />
                            <date-picker id="fecha_compra" type="date" value-type="YYYY-MM-DD" @change="filtrado"
                                format="DD/MM/YYYY"
                                class="col-span-6 lg:col-span-2 text-base text-gray-700  bg-white  transition-colors duration-200 border-0"
                                v-model:value="dateCompra" lang="es" placeholder="Seleccione Fecha"
                                :clearable="false"></date-picker>
                            <InputError class="mt-1 text-xs" :message="form.errors.fecha_compra" />
                        </div>
                        <!--nro_factura-->
                        <div class="col-span-12 mx-2 py-1 shadow-default xl:col-span-4" v-if="form.tipo=='RMA'">
                            <InputLabel for="nro_factura" value="Número de factura"
                                class="text-base font-medium leading-1 text-gray-900" />
                            <InputText type="text" id="nro_factura" v-model="form.nro_factura" :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.nro_factura" />
                        </div>


                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-4">
                            <InputLabel for="cliente" value="Cliente"
                                class="text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="cliente" v-model="form.cliente.nombre"
                                placeholder="ingrese nombre cliente" :pt="{
                                    root: { class: 'h-9 w-full' }
                                }" />
                            <InputError class="mt-1 text-xs" :message="form.errors['cliente.nombre']" />

                        </div>

                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-4">
                            <InputLabel for="telefono" value="Telefono"
                                class="text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="telefono" v-model="form.cliente.telefono"
                                placeholder="ingrese telefono" :pt="{
                                    root: { class: 'h-9 w-full' }
                                }" />
                            <InputError class="mt-1 text-xs" :message="form.errors['cliente.telefono']" />
                        </div>
                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-4" v-if="form.tipo=='PRESUPUESTO'">
                            <InputLabel for="costo_presupuestado" value="Costo presupuestado"
                                class="text-base font-medium leading-6 text-gray-900" />
                            <input type="number" v-model="form.costo_presupuestado" step="1" min="0"
                                class="p-inputtext text-end h-9 w-full text-gray-700 bg-white  border border-gray-300 transition-colors duration-200 appearance-none px-1 py-1" />
                            <InputError class="mt-1 text-xs" :message="form.errors.costo_presupuestado" />
                        </div>

                        <!--Datos-->

                        <!--producto-->
                        <div
                            class="px-0 py-1 m-2 mt-3 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                            <h5 class="text-2xl font-medium">Detalle</h5>
                        </div>
                        <div class="col-span-12 mx-2 py-1 shadow-default xl:col-span-8">
                            <InputLabel for="prod_origen" value="Producto"
                                class="text-base font-medium leading-1 text-gray-900" />
                            <Dropdown v-model="productoSelect" :autoOptionFocus="true" id="productos" @change="setProducto" filter
                                :options="lista_productos" optionLabel="nombre" :virtualScrollerOptions="{ itemSize: 46 }"
                                class="w-full md:w-14rem" :pt="{
                                    root: { class: 'w-1/2' },
                                    trigger: { class: 'fas fa-caret-down text-gray-400 my-auto' },
                                    item: ({ props, state, context }) => ({
                                        class: context.selected ? 'text-white bg-primary-900 p-2' : context.focused ? 'bg-blue-100 p-2' : undefined
                                    })
                                }" placeholder="Seleccione Producto">

                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex align-items-center">
                                        <img :alt="slotProps.value.nombre" :src="slotProps.value.imagen"
                                            style="width: 20px; margin-right: 5px;" />
                                        <div> {{ slotProps.value.origen }} - {{ slotProps.value.nombre }} - stock:{{ slotProps.value.stock }}</div>
                                    </div>
                                    <span v-else>
                                        {{ slotProps.placeholder }}
                                    </span>
                                </template>
                                <template #option="slotProps">
                                    <div class="flex align-items-center">
                                        <img :alt="slotProps.option.nombre" :src="slotProps.option.imagen"
                                            style="width: 20px; margin-right: 5px;" />
                                            <div> {{ slotProps.option.origen }} - {{ slotProps.option.nombre }} - stock:{{ slotProps.option.stock }}</div>
                                    </div>
                                </template>
                            </Dropdown>


                            <InputError class="mt-1 text-xs" :message="form.errors.producto_id" />
                        </div>


                        <div class="col-span-12 mx-2 py-1 shadow-default xl:col-span-4" v-if="form.tipo=='RMA'">
                            <InputLabel for="prod_serie" value="Número de Serie"
                                class="text-base font-medium leading-1 text-gray-900" />
                            <InputText type="text" id="prod_serie" v-model="form.prod_serie" :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.origen" />
                        </div>

                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="defecto" value="Defecto"
                                class="text-base font-medium leading-6 text-gray-900" />

                            <Textarea v-model="form.defecto" :pt="{
                                root: {
                                    rows: '2',
                                    class: 'w-full'
                                }
                            }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.defecto" />
                        </div>
                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="observaciones" value="Observaciones"
                                class="text-base font-medium leading-6 text-gray-900" />

                            <Textarea v-model="form.observaciones" :pt="{
                                root: {
                                    rows: '2',
                                    class: 'w-full'
                                }
                            }" />

                        </div>
                        <!--producto-->

                    </div>
                    <div class="flex justify-end py-3">
                        <Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small"
                            @click="cancelCrear" type="button" />

                        <Button label="Guardar" size="small" type="button" :class="{ 'opacity-50': form.processing }"
                            :disabled="form.processing" @click.prevent="submit" />
                    </div>

                </form>

            </div>

            <!--Productos-->

        </div>

        <!--Contenido-->

    </AppLayout>
</template>



<style type="text/css" scoped></style>
