<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Swal from 'sweetalert2';
import { FilterMatchMode } from 'primevue/api';
const toast = useToast();
const titulo = "Compras"
const ruta = 'compras'

const { productos } = usePage().props

const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS }
});





const form = useForm({
    id: '',
    nro_factura: '',
    proveedor: '',
    observaciones: '',
    productos: [],

})



onMounted(() => {

    //lista_destino.value.options = lista_destinos
    var dato = usePage().props.compra
    form.id = dato.id
    form.nro_factura = dato.nro_factura
    form.proveedor = dato.proveedor
    form.facturador_id = dato.facturador_id
    form.observaciones = dato.observaciones
    dato.detalles_compras.forEach(el => {
        var produ2 = productos.data.find(pr => pr.id === el.producto_id);
        if (produ2 != undefined) {
            form.productos.push(
                {
                    producto_id: el.producto_id,
                    nombre: produ2.nombre,
                    origen: produ2.origen,
                    cantidad: el.cantidad,
                    stock: produ2.stock,

                }
            )
        }

    });

})


const addToCart = (id) => {
    form.clearErrors();
    var produ = productos.data.find(pr => pr.id === id);
    if (produ.stock >= 0) {
        form.productos.push(
            {
                producto_id: produ.id,
                nombre: produ.nombre,
                origen: produ.origen,
                cantidad: 1,
                stock: produ.stock,
            }
        )


    } else {
        alerta('No hay stock disponible', 'error')
    }

};



//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta + '.update', form.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            show('success', 'Mensaje', 'Compra Actualizada')
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
//modal advertencia
const alerta = (mensaje, icono) => {
    Swal.fire({
        width: 350,
        title: mensaje,
        icon: icono
    })
}
const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const cancelCrear = () => {
    router.get(route(ruta + '.index'))
};


</script>
<template>
    <Head :title="titulo" />
    <AppLayout
        :pagina="[{ 'label': 'Compras', link: false, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
        <!--Contenido-->
        <div
            class="grid grid-cols-12 p-0 m-0 gap-2 mb-4 bg-white col-span-12 py-2 rounded-lg shadow-lg lg:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <Toast />
            <div class="mt-0 mb-4 col-span-12 lg:col-span-8">

                <div class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                    <h5 class="text-2xl font-medium">{{ titulo }}</h5>
                </div>
                <form>

                    <div class="grid grid-cols-12 gap-1 py-0">
                        <!--Tabla-->

                        <table class="table-auto mx-2 border border-gray-300 col-span-12">
                            <thead>
                                <tr class="p-2 bg-secondary-900 border">
                                    <th class="border border-gray-300 p-2 w-24">Origen</th>
                                    <th class="border border-gray-300 ">Producto</th>
                                    <th class="border border-gray-300 w-24">Cantidad</th>
                                    <th class="border border-gray-300 w-8"></th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(producto, index) in form.productos" :key="index"
                                    class="font-sans  font-normal text-gray-800 border border-gray-300">
                                    <td class="border border-gray-300 p-2">{{ producto.origen }}</td>
                                    <td class="border border-gray-300 p-2">{{ producto.nombre }}</td>
                                    <td class="border border-gray-300"><input type="number" v-model="producto.cantidad"
                                            min="1" step="1"
                                            class="p-inputtext p-component font-sans  font-normal text-gray-700 bg-white  border-0 appearance-none rounded-none text-sm px-2 py-0 p-inputnumber-input h-9 px-0 py-0 m-0 w-full text-end text-sm" />

                                    </td>

                                    <td class="border-none  border-gray-300 p-1 ">
                                        <div
                                            class="rounded-md p-1 flex justify-center items-center bg-red-600 py-auto  text-base font-semibold text-white hover:bg-red-700">
                                            <button type="button" @click.prevent="removerProducto(index)" class="w-6"
                                                v-tooltip.top="{ value: `Eliminar`, pt: { text: 'bg-gray-500 p-1 m-0 text-xs text-white rounded' } }"><i
                                                    class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="col-span-12  p-2 xl:col-span-12">
                            <InputError class="mt-1 text-lg w-full " :message="form.errors.productos" />
                            <InputError v-for="error in form.errors.campos_productos" class="mt-1 mb-0 text-lg" :message="error" />
                        </div>
                        <!--Tabla-->

                        <!--Datos Compras-->
                        <div
                            class="px-0 py-1 m-2 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                            <h5 class="text-lg font-medium">Datos Compra</h5>
                        </div>



                        <div class="col-span-12 mx-2 py-0 shadow-default lg:col-span-6">
                            <InputLabel for="nro_factura" value="Nro Factura"
                                class="text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="nro_factura" v-model="form.nro_factura"
                                placeholder="ingrese nro factura" :pt="{
                                    root: { class: 'h-9 w-full' }
                                }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.nro_factura" />

                        </div>


                        <div class="col-span-12 mx-2 py-0 shadow-default lg:col-span-6">
                            <InputLabel for="proveedor" value="Proveedor"
                                class="text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="proveedor" v-model="form.proveedor" placeholder="ingrese proveedor"
                                :pt="{
                                    root: { class: 'h-9 w-full' }
                                }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.proveedor" />

                        </div>
                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-12">
                            <InputLabel for="rut" value="Observaciones:"
                                class="text-base font-medium leading-6 text-gray-900" />

                            <Textarea v-model="form.observaciones" :pt="{
                                root: {
                                    rows: '2',
                                    class: 'w-full'
                                }
                            }" />

                        </div>

                        <!--Datos Compras-->
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
            <div class="p-0 mb-0 col-span-12  lg:col-span-4 ">
                <DataTable showGridlines :filters="filters" scrollable scrollHeight="550px"
                    :globalFilterFields="['origen', 'nombre']" :value="productos.data"
                    :virtualScrollerOptions="{ itemSize: 46 }" size="small">
                    <template #header>
                        <div class="flex justify-content-end text-sm">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                        </div>
                    </template>
                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>

                    <Column field="nombre" header="Productos" :pt="{
                        bodyCell: {
                            class: 'flex justify-start text-center p-0 mx-0'
                        }
                    }">
                        <template #body="slotProps">
                            <div class="w-full mx-auto px-1">
                                <div class="flex flex-col gap-y-1 mx-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-full object-cover" :src="slotProps.data.imagen"
                                            alt="" />
                                        <div class="ml-6 w-auto  text-start">
                                            <p class="text-gray-800 mb-2 text-xs whitespace-pre-line font-bold leading-1">
                                                {{
                                                    slotProps.data.nombre }}</p>
                                            <div class="font-bold leading-none text-xs text-gray-800 pb-1">Origen: <span
                                                    class="px-1 py-0  font-normal">{{ slotProps.data.origen
                                                    }}</span></div>
                                            <div class="font-bold leading-none text-xs text-gray-800">Stock :<span
                                                    class="px-1 py-0 font-normal">{{ slotProps.data.stock }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-auto">
                                        <Button severity="success" aria-label="Add" @click="addToCart(slotProps.data.id)"
                                            icon="fas fa-cart-plus" :pt="{
                                                root: {
                                                    class: 'flex items-center justify-center font-medium w-10'
                                                },
                                                label: {
                                                    class: 'hidden'
                                                }
                                            }"
                                            :disabled="form.productos.filter(e => e.producto_id === slotProps.data.id).length > 0"></Button>
                                    </div>

                                </div>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>

        </div>

        <!--Contenido-->

    </AppLayout>
</template>



<style type="text/css" scoped></style>
