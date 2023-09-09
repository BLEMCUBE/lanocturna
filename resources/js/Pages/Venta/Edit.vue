<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useToast } from "primevue/usetoast";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Multiselect from '@vueform/multiselect';
import Swal from 'sweetalert2';
import { FilterMatchMode } from 'primevue/api';
const toast = useToast();
const titulo = "Venta"
const ruta = 'ventas'

const { lista_destinos } = usePage().props
const { productos } = usePage().props
const prod = useForm({
    producto_id: '',
    nombre: '',
    origen: '',
    imagen: '',
    cantidad: '',
    precio_sin_iva: '',
    precio: '',
    total_sin_iva: '',
    total: '',
})
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS }
});
const setDestino = (e) => {
    form.destino = e;

}
const setMoneda = (e) => {

if (selectedMoneda.value.code == form.moneda)
    return;
if (selectedMoneda.value.code == 'Pesos') {
    form.productos.forEach((item, index) => {
        item['precio'] = roundNumber(parseFloat(item['precio'] * form.tipo_cambio).toFixed(2), 0.5, 'round')
        item['total'] = item['cantidad'] * item['precio']
        item['total_sin_iva'] = item['cantidad'] * item['precio_sin_iva']
    })
    form.moneda = selectedMoneda.value.code;
} else {
    form.productos.forEach((item, index) => {
        item['precio'] = parseFloat(item['precio'] / form.tipo_cambio).toFixed(2)
        item['total'] = item['cantidad'] * item['precio']
        item['total_sin_iva'] = item['cantidad'] * item['precio_sin_iva']
    })
    form.moneda = selectedMoneda.value.code;
}
sumaTotal()
calculoSinIva()
}

const selectedMoneda = ref();
const monedas = ref([
    { name: 'Pesos', code: 'Pesos' },
    { name: 'Dólares', code: 'Dólares' },
]);


const form = useForm({
    id:'',
    vendedor_id: '',
    destino: '',
    total: 0.0,
    vendedor: 0.0,
    codigo:'',
    total_sin_iva: 0.0,
    moneda: '',
    tipo_cambio: '',
    estado: '',
    observaciones: '',
    productos: [],
    cliente: {
        nombre: '',
        direccion: ''
    },

})

const lista_destino = ref({
    value: '',
    closeOnSelect: true,
    placeholder: "Seleccione",
    searchable: false,
    options: [],
});

const lista_moneda = ref({
    value: '',
    closeOnSelect: true,
    placeholder: "Seleccione",
    searchable: false,
    options: [
        { "value": "Pesos", "label": "Pesos" },
        { "value": "Dólares", "label": "Dólares" },
    ],
});
onMounted(() => {
    //productos.value=usePage().props.productos.data
    lista_destino.value.options = lista_destinos
    var dato=usePage().props.venta
    form.tipo_cambio = parseFloat(dato.tipo_cambio).toFixed(2)
    form.moneda=dato.moneda
    form.destino=dato.destino
    form.id=dato.id
    form.vendedor_id=dato.vendedor_id
    form.vendedor=dato.vendedor.name
    form.observaciones=dato.observaciones
    lista_moneda.value.value=dato.moneda
    form.cliente=JSON.parse( dato.cliente)
    form.estado=dato.estado
    form.codigo=dato.codigo
    selectedMoneda.value= monedas.value.find(pr => pr.code === dato.moneda);
    dato.detalles_ventas.forEach(el => {
    var produ2 = productos.data.find(pr => pr.id === el.producto_id);
    if(produ2!=undefined){
        form.productos.push(
            {
                producto_id: el.producto_id,
                nombre: produ2.nombre,
                origen: produ2.origen,
                cantidad: el.cantidad,
                precio: el.precio,
                precio_sin_iva: el.precio_sin_iva,
                total_sin_iva: el.total_sin_iva,
                stock: produ2.stock,
                total: el.total
            }
            )
        }
        sumaTotal()
        calculoSinIva()
    });
    //setMoneda(dato.moneda);



})


const addToCart = (id) => {
    form.clearErrors();
    var produ = productos.data.find(pr => pr.id === id);
    if (produ.stock > 0) {
        form.productos.push(
            {
                producto_id: produ.id,
                nombre: produ.nombre,
                origen: produ.origen,
                cantidad: 1,
                precio: null,
                stock: produ.stock,
                total: 1
            }
        )
        sumaTotal()
        calculoSinIva()

    } else {
        alerta('No hay stock disponible', 'error')
    }

};
const roundNumber = (value, step = 1.0, type = 'round') => {
    step || (step = 1.0);
    const inv = 1.0 / step;
    const mathFunc = 'ceil' === type ? Math.ceil : ('floor' === type ? Math.floor : Math.round);

    return mathFunc(value * inv) / inv;
}


const sumaTotal = () => {
    form.total = (form.productos.reduce((acc, cur) => acc + parseFloat(cur['total']), 0)).toFixed(2)
    form.total_sin_iva = (form.productos.reduce((acc, cur) => acc + parseFloat(cur['total_sin_iva']), 0)).toFixed(2)
    calculoSinIva()

}
const removerProducto = (index) => {
    form.productos.splice(index, 1);
    sumaTotal()
    calculoSinIva()

}


const calculoSinIva = () => {

    form.total_sin_iva = (form.total /1.22).toFixed(2)
}

const sumaTotalProducto = ($event, id) => {
    var precio_temp = (form.productos[id].precio === null) ? 1 : form.productos[id].precio
    if ($event.target.value > 0) {

        if (form.productos[id].stock >= form.productos[id].cantidad) {
            form.productos[id].total = (parseFloat(form.productos[id].cantidad) * parseFloat(precio_temp).toFixed(2))
            form.productos[id].total_sin_iva = (parseFloat(form.productos[id].cantidad) * parseFloat(precio_temp/1.22).toFixed(2))
            form.productos[id].precio_sin_iva=(form.productos[id].precio/1.22).toFixed(2)
            sumaTotal()
            calculoSinIva()
        } else {
            form.productos[id].cantidad = 1
            form.productos[id].precio_sin_iva=form.productos[id].precio/1.22
            form.productos[id].total = (parseFloat(form.productos[id].cantidad) * parseFloat(precio_temp).toFixed(2))
            alerta('La cantidad supera el Stock', 'error')
        }
    } else {
        return;
    }
}

//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta + '.update', form.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            show('success', 'Mensaje', 'Venta Actualizada')
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
    <AppLayout :pagina="[{ 'label': 'Ventas', link: false, url: route(ruta + '.index') }, { 'label': titulo, link: false }]">
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
                                    <th class="border border-gray-300 w-24">Precio</th>
                                    <th class="border border-gray-300 w-24">Total</th>
                                    <th class="border border-gray-300 w-8"></th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="producto, index in form.productos" :key="index"
                                    class="font-sans  font-normal text-gray-800 border border-gray-300">
                                    <td class="border border-gray-300 p-2">{{ producto.origen }}</td>
                                    <td class="border border-gray-300 p-2">{{ producto.nombre }}</td>
                                    <td class="border border-gray-300"><input type="number" v-model="producto.cantidad"
                                            min="1" step="1"
                                            class="p-inputtext p-component font-sans  font-normal text-gray-700 bg-white  border-0 appearance-none rounded-none text-sm px-2 py-0 p-inputnumber-input h-9 px-0 py-0 m-0 w-full text-end text-sm"
                                            @input.prevent="sumaTotalProducto($event, index)" />

                                    </td>
                                    <td class="border border-gray-300"><input type="number" required v-model="producto.precio"
                                            min="0" step="1" @input="sumaTotalProducto($event, index)"
                                            class="p-inputtext pr-2 p-component font-sans  font-normal text-gray-700 bg-white  border-0 appearance-none rounded-none text-sm px-2 py-0 p-inputnumber-input h-9 px-0 py-0 m-0 w-full text-end text-sm" />

                                    </td>
                                    <td class="border border-gray-300 p-2">{{ producto.total }}  </td>
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
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><b>Total: </b></td>
                                    <td class="text-end"><b> {{ form.moneda=='Pesos'?'$ ':'USD ' }} {{ form.total }} </b></td>
                                </tr>

                            </tfoot>
                        </table>
                        <div class="col-span-12  p-2 xl:col-span-12">
                            <InputError class="mt-1 text-xs w-full " :message="form.errors.productos" />
                        </div>
                        <!--Tabla-->
                        <!--Datos Ventas-->
                        <div
                            class="px-0 py-1 m-2 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                            <h5 class="text-lg font-medium">Datos venta</h5>
                        </div>
                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="tipo_cambio" value="Tipo de cambio"
                                class="text-base font-medium leading-1 text-gray-900" />
                            <InputText type="text" id="tipo_cambio" v-model="form.tipo_cambio" readonly :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.tipo_cambio" />
                        </div>
                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="moneda" value="Moneda" class="text-base font-medium leading-1 text-gray-900" />
                            <Dropdown v-model="selectedMoneda" @change="setMoneda" :options="monedas" optionLabel="name"
                                :pt="{
                                    root: { class: 'w-auto' },
                                    trigger: { class: 'fas fa-caret-down text-gray-200 my-auto' },
                                    item: ({ props, state, context }) => ({
                                        class: context.selected ? 'text-white' : context.focused ? 'bg-blue-100' : undefined
                                    })
                                }" placeholder="Seleccione Moneda" />
                            <InputError class="mt-1 text-xs" :message="form.errors.moneda" />
                        </div>

                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="vendedor" value="Vendedor"
                                class="text-base font-medium leading-1 text-gray-900" />
                            <InputText type="text" id="vendedor" v-model="form.vendedor" readonly :pt="{
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
                            <InputText type="text" id="cliente" v-model="form.cliente.nombre"
                                placeholder="ingrese nombre cliente" :pt="{
                                    root: { class: 'h-9 w-full' }
                                }" />
                            <InputError class="mt-1 text-xs" :message="form.errors['cliente.nombre']" />

                        </div>

                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="telefono" value="Telefono"
                                class="text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="telefono" v-model="form.cliente.telefono"
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

                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="empresa" value="Empresa"
                                class="text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="empresa" v-model="form.cliente.empresa" placeholder="ingrese Empresa"
                                :pt="{
                                    root: { class: 'h-9 w-full' }
                                }" />

                        </div>

                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="rut" value="RUT" class="text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="rut" v-model="form.cliente.rut" placeholder="ingrese RUT" :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />

                        </div>
                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-12">
                            <InputLabel for="rut" value="Observaciones:"
                                class="text-base font-medium leading-6 text-gray-900" />

                            <Textarea v-model="form.observaciones" :pt="{
                                root: {
                                    rows: '1',
                                    class: 'w-full'
                                }
                            }" />

                        </div>

                        <!--Datos Ventas-->

                    </div>
                    <div class="flex justify-end py-3">
                        <Button label="Cancelar" :pt="{ root: 'mr-5 py-1' }" severity="danger" size="small" @click="cancelCrear"
                            type="button" />

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

                                    <div class="">
                                        <Button severity="success" aria-label="Add" @click="addToCart(slotProps.data.id)"
                                            icon="fas fa-cart-plus" :pt="{
                                                root: {
                                                    class: 'flex items-center justify-center font-medium w-8'
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
