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
const titulo = "Mercado Libre"
const ruta = 'envios'
const isShowModalProducto = ref(false);
const isLoad = ref(false);
const errorsFilas = ref();
const errorsCompras = ref();
const errorsStock = ref();
const inputArchivo = ref(null);
const { tipo_cambio } = usePage().props
const { lista_destinos } = usePage().props
const uploadMercado = useForm({
    destino: '',
    archivo: ''
})
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS }
});
const setDestino = (e) => {
    var tipo = lista_destinos.find(prod => prod.value === e);
    form.destino = e;

}

const setDestinoUpload = (e) => {
    uploadMercado.destino = e;

}
const form = useForm({
    vendedor_id: '',
    destino: '',
    total: 0.0,
    total_sin_iva: 0.0,
    moneda: 'Pesos',
    tipo: 'ENVIO',
    nro_compra: '',
    tipo_cambio: '',
    estado: 'FACTURADO',
    observaciones: '',
    productos: [],
    cliente: {
        nombre: '',
        direccion: ''
    },

})

const { productos } = usePage().props
const lista_destino = ref({
    value: '',
    closeOnSelect: true,
    placeholder: "Seleccione",
    searchable: false,
    options: [],
});

const lista_moneda = ref({
    value: 'Pesos',
    closeOnSelect: true,
    placeholder: "Seleccione",
    searchable: false,
    options: [
        { "value": "Pesos", "label": "Pesos" },
        { "value": "Dólares", "label": "Dólares" },
    ],
});
onMounted(() => {
    lista_destino.value.options = lista_destinos
    form.tipo_cambio = tipo_cambio
    form.moneda = "Pesos"
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

    form.total_sin_iva = (form.total / 1.22).toFixed(2)
}

const sumaTotalProducto = ($event, id) => {
    var precio_temp = (form.productos[id].precio === null) ? 1 : form.productos[id].precio
    if ($event.target.value > 0) {

        if (form.productos[id].stock >= form.productos[id].cantidad) {
            form.productos[id].total = (parseFloat(form.productos[id].cantidad) * parseFloat(precio_temp).toFixed(2))
            form.productos[id].total_sin_iva = (parseFloat(form.productos[id].cantidad) * parseFloat(precio_temp / 1.22).toFixed(2))
            form.productos[id].precio_sin_iva = (form.productos[id].precio / 1.22).toFixed(2)
            sumaTotal()
            calculoSinIva()
        } else {
            form.productos[id].cantidad = 1
            form.productos[id].precio_sin_iva = form.productos[id].precio / 1.22
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
    form.post(route(ruta + '.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            show('success', 'Mensaje', 'Envio creado')
            setTimeout(() => {
                router.get(route(ruta + '.create'));
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
    router.get(route('inicio'))
};

//subir excel mercado
const pickFile = (e) => {
    e.preventDefault();
    uploadMercado.archivo = e.target.files[0]

}

//descarga formato Excel
const descargarFormatoExcel = (nombre) => {
if (nombre.length>0) {
    window.open(route('plantillas.importar', nombre), '_blank');
} else {

   return;
}
}

//envio de excel
const submitExcel = () => {

    isLoad.value = true;
    uploadMercado.clearErrors()
    uploadMercado.post(route(ruta + '.uploadexcel'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            isLoad.value = false;
            show('success', 'Mensaje', 'Excel Importado')
            setTimeout(() => {
                router.get(route(ruta + '.create'));
            }, 1000);
        },
        onFinish: () => {

        },
        onError: (er) => {
            isLoad.value = false;
            inputArchivo.value.value = null
            uploadMercado.reset('archivo');
            if (er.filas != undefined || er.compras != undefined || er.stock != undefined) {
                if (er.filas.length > 0 || er.compras.length > 0 || er.stock.length > 0) {
                    //errorsFilas.value = er.filas.slice(1);
                    errorsFilas.value = er.filas;
                    //errorsCompras.value = er.compras.slice(1);
                    errorsCompras.value = er.compras;
                    errorsStock.value = er.stock;
                    //errorsStock.value = er.stock.slice(1);
                    isShowModalProducto.value = true;

                }
            }
        }
    }
    );
};

const closeModalProducto = () => {
    inputArchivo.value.value = null //reset input type file
    uploadMercado.reset('archivo');
    isShowModalProducto.value = false;
};
//subir excel mercado


</script>
<template>

    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">
        <!--Contenido-->

        <div class="col-span-12  md:col-span-6 shadow-default lg:col-span-6 mx-2">

            <InputLabel for="file_input1" value="Importar Excel"
                class="block text-base font-medium leading-6 text-gray-900" />
            <input ref="inputArchivo" @input="pickFile" type="file" class="block w-full text-xs
             text-gray-500
                                file:mr-4 file:py-1.5 file:px-3
                                file:rounded file:border-0
                                file:text-sm file:font-medium
                                file:bg-primary-900 file:text-white
                                hover:file:bg-primary-900/80
                                hover:file:cursor-pointer
                                file:disabled::opacity-75
                                file:disabled:cursor-no-drop
                                disabled:opacity-75
                                disabled:cursor-no-drop" :disabled="uploadMercado.processing"
                accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
            <InputError class="mt-1 text-xs" :message="uploadMercado.errors.archivo" />


        </div>

        <div class="col-span-12   md:col-span-6  mx-2 py-0 shadow-default xl:col-span-3 w-full">
            <InputLabel for="destino1" value="Destino" class="text-base font-medium leading-1 text-gray-900" />
            <Multiselect id="destino1" :disabled="uploadMercado.processing" v-model="uploadMercado.destino"
                v-bind="lista_destino" @select="setDestinoUpload">
            </Multiselect>
            <InputError class="mt-1 text-xs" :message="uploadMercado.errors.destino" />
        </div>

        <div class="col-span-12 shadow-default xl:col-span-3 flex h-auto items-end justify-center pb-1">
            <div class="h-10">
                <Button label="Importar" type="button" class="text-normal"
                :class="{ 'opacity-50': form.processing }" :disabled="uploadMercado.processing"
                @click.prevent="submitExcel" />
            </div>
        </div>

        <div class="col-span-12 xl:col-span-3 flex h-auto items-end justify-start px-1">
            <div class="h-8">
                <Button label="Descargar formato"  size="md" severity="success" type="button" 
                class="p-1 text-xs font-light ring-0" @click="descargarFormatoExcel('formato_importar_mercadolibre.xlsx')" />
            </div>
        </div>

        <div
            class="grid grid-cols-12 p-0 m-0 gap-2 mb-4 bg-white col-span-12 py-2 rounded-lg shadow-lg lg:col-span-12 dark:border-gray-700  dark:bg-gray-800">

            <Toast />
            <div class="mt-0 mb-4 col-span-12 lg:col-span-8">

                <div
                    class="px-0 py-1 m-2 mt-0 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
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
                                            class="p-inputtext p-component font-sans  font-normal text-gray-700 bg-white  border-0 appearance-none rounded-none text-sm px-2 py-0 p-inputnumber-input h-9 m-0 w-full text-end"
                                            @input.prevent="sumaTotalProducto($event, index)" />

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
                            <InputError v-for="error in form.errors.campos_productos" class="mt-1 mb-0 text-lg"
                                :message="error" />
                        </div>
                        <!--Tabla-->
                        <!--Datos Ventas-->
                        <div
                            class="px-0 py-1 m-2 bg-primary-900 text-white  col-span-full  flex justify-center items-center">
                            <h5 class="text-lg font-medium">Datos venta</h5>
                        </div>

                        <div class="col-span-12 mx-2 py-0 shadow-default xl:col-span-6">
                            <InputLabel for="nro_compra" value="Nro Compra"
                                class="text-base font-medium leading-6 text-gray-900" />
                            <InputText type="text" id="nro_compra" v-model="form.nro_compra"
                                placeholder="ingrese nro compra" :pt="{
        root: { class: 'h-9 w-full' }
    }" />
                            <InputError class="mt-1 text-xs" :message="form.errors.nro_compra" />

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

                        <!--Datos Ventas-->

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
                <DataTable :filters="filters" scrollable scrollHeight="550px" :globalFilterFields="['origen', 'nombre']"
                    :value="productos.data" :virtualScrollerOptions="{ itemSize: 46 }" size="small">
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
                                            <p
                                                class="text-gray-800 mb-2 text-xs whitespace-pre-line font-bold leading-1">
                                                {{
        slotProps.data.nombre }}</p>
                                            <div class="font-bold leading-none text-xs text-gray-800 pb-1">Origen: <span
                                                    class="px-1 py-0  font-normal">{{ slotProps.data.origen
                                                    }}</span></div>
                                            <div class="font-bold leading-none text-xs text-gray-800">Stock :<span
                                                    class="px-1 py-0 font-normal">{{ slotProps.data.stock }}</span>
                                            </div>
                                            <div class="leading-none text-xs text-gray-800">
                                                <b class="text-xs leading-2 mt-0 text-gray-700 dark:text-gray-300">
                                                    En camino:
                                                </b>
                                                <ul class="list-disc list-outside">
                                                    <template v-for="item in slotProps.data.importacion_detalles">
                                                        <li class="ml-3"
                                                            v-show="item.importacion.estado == 'En camino'">
                                                            <p>
                                                                <b>
                                                                    {{ item.importacion.nro_carpeta }}

                                                                </b> :
                                                                {{ item.cantidad_total }}
                                                            </p>

                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-auto">
                                        <Button severity="success" aria-label="Add"
                                            @click="addToCart(slotProps.data.id)" icon="fas fa-cart-plus" :pt="{
        root: {
            class: 'flex items-center justify-center font-medium w-10'
        },
        label: {
            class: 'hidden'
        }
    }" :disabled="form.productos.filter(e => e.producto_id === slotProps.data.id).length > 0"></Button>
                                    </div>

                                </div>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>

        </div>

        <!--Contenido-->

        <!--Modal productos-->
        <Dialog v-model:visible="isShowModalProducto" modal :style="{ width: '30vw' }" :pt="{
        header: {
            class: 'mt-5 pb-2 px-5'
        },
        content: {
            class: 'p-4'
        },
    }">

            <div v-if="errorsFilas.length > 0">

                <p class="mb-2 font-bold text-md">
                    Los siguientes productos no estan registrado , por favor registre y vuelva a intentar.
                </p>

                <table class="w-full border">
                    <thead>
                        <tr class="w-full border">
                            <th class="w-26 text-center border">
                                Fila
                            </th>
                            <th class="text-center border">
                                Sku
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="w-full text-center border" v-for="item in errorsFilas">
                            <td class="text-center border">
                                {{ item.fila }}
                            </td>
                            <td class="text-center border">
                                {{ item.sku }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="errorsCompras.length > 0">
                <p class="mb-2 mt-4 font-bold text-md">
                    Las siguientes compras ya existen en el sistema, por favor corriga e intente nuevamente.
                </p>

                <table class="w-full border">
                    <thead>
                        <tr class="w-full border">
                            <th class="w-26 text-center border">
                                Fila
                            </th>
                            <th class="text-center border">
                                Número Compra
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="w-full text-center border" v-for="item in errorsCompras">
                            <td class="text-center border">
                                {{ item.fila }}
                            </td>
                            <td class="text-center border">
                                {{ item.nro_compra }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="errorsStock.length > 0">
                <p class="mb-2 mt-4 font-bold text-md">
                    Los siguientes productos no disponen de stock.
                </p>

                <table class="w-full border">
                    <thead>
                        <tr class="w-full border">
                            <th class="w-26 text-center border">
                                Fila
                            </th>
                            <th class="text-center border">
                                SKU
                            </th>
                            <th class="text-center border">
                                Stock
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="w-full text-center border" v-for="item in errorsStock">
                            <td class="text-center border">
                                {{ item.fila }}
                            </td>
                            <td class="text-center border">
                                {{ item.sku }}
                            </td>
                            <td class="text-center border">
                                {{ item.stock }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>


            <template #header>
                <div class="flex flex-column align-items-center" style="flex: 1">
                    <div class="text-center">
                        <i class="pi pi-exclamation-triangle text-yellow-500" style="font-size: 3rem"></i>
                    </div>
                    <div class="font-bold text-2xl m-3">No se ha podido importar</div>
                </div>
            </template>


            <div class="flex justify-end py-3">
                <Button label="Aceptar" size="small" type="button" @click="closeModalProducto()" />

            </div>

        </Dialog>
        <!--Modal productos-->
    </AppLayout>
</template>



<style type="text/css" scoped></style>
