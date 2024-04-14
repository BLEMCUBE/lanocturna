<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { FilterMatchMode } from 'primevue/api';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useToast } from "primevue/usetoast";
const toast = useToast();
const tabla_productos = ref()
const { permissions } = usePage().props.auth
const titulo = "Productos"
const isLoad = ref(false);
const inputArchivo = ref(null);
const ruta = 'productos'
const uploadStock = useForm({
    archivo: ''
})
const isShowModalProducto = ref(false);
const isShowModalNroCompra = ref(false);
const errorsFilas = ref();
const errorsSku = ref();
const formDelete = useForm({
    id: '',
});

const rowClass = (data) => {
    //Si stock = < stock minimo Y stock_futuro = stock
    if (parseFloat(data.stock) <= parseFloat(data.stock_minimo) && parseFloat(data.stock) == parseFloat(data.stock_futuro)) {
        //return "text-red-700 text-xs"
        return ["bg-red-700 text-xs text-white"]
    }
    //Si stock = < stock mínimo y stock_futuro > stock
    if (parseFloat(data.stock) <= parseFloat(data.stock_minimo) && parseFloat(data.stock_futuro) > parseFloat(data.stock)) {
        //return ["text-orange-500 text-xs"]
        return "bg-orange-500 text-xs text-white"
    }
};


const btnVer = (id) => {
    router.get(route(ruta + '.show', id));

};
const btnEditar = (id) => {
    router.get(route(ruta + '.edit', id));

};
const btnEliminar = (id, name) => {

    const alerta = Swal.mixin({ buttonsStyling: true });
    alerta.fire({
        width: 350,
        title: "Seguro de eliminar " + name,
        text: 'Se eliminará definitivamente',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        cancelButtonColor: 'red',
        confirmButtonColor: '#2563EB',

    }).then((result) => {
        if (result.isConfirmed) {
            formDelete.delete(route(ruta + '.destroy', id),
                {
                    preserveScroll: true,
                    forceFormData: true,
                    onSuccess: () => {
                        show('success', 'Eliminado', 'Se ha eliminado')
                        setTimeout(() => {
                            router.get(route(ruta + '.index'));
                        }, 1000);

                    }
                });
        }
    });
}

onMounted(() => {

    tabla_productos.value = Array.from(usePage().props.productos.data, (x) => x);

});

const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

const BtnCrear = () => {
    router.get(route(ruta + '.create'));
}
const clickDetalle = (e) => {
    btnVer(e.data.id)
}

//upload stock
//selecionar excel
const pickFile = (e) => {
    e.preventDefault();
    uploadStock.archivo = e.target.files[0]

}

const closeModalProducto = () => {
    inputArchivo.value.value = null //reset input type file
    uploadStock.reset('archivo');
    isShowModalProducto.value = false;
};
const closeModalNroCompra = () => {
    inputArchivo.value.value = null //reset input type file
    uploadStock.reset('archivo');
    isShowModalNroCompra.value = false;
};

//descarga formato Excel
const descargarFormatoExcel = (nombre) => {
    if (nombre.length > 0) {
        window.open(route('plantillas.importar', nombre), '_blank');
    } else {

        return;
    }
}

//envio de excel
const submitExcel = () => {

isLoad.value = true;
uploadStock.clearErrors()
uploadStock.post(route(ruta + '.importarstock'), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
        isLoad.value = false;
        show('success', 'Mensaje', 'Stock Importado')
        setTimeout(() => {
            router.get(route(ruta + '.index'));
        }, 1000);
    },
    onFinish: () => {

    },
    onError: (er) => {
        isLoad.value = false;
        inputArchivo.value.value = null
        uploadStock.reset('archivo');
        if (er.filas != undefined ) {
            if (er.filas.length > 0) {
                errorsFilas.value = er.filas;
                isShowModalProducto.value = true;
            }
        } else if (er.error_sku != undefined) {
            if (er.error_sku.length > 0) {
                errorsSku.value = er.error_sku;
                isShowModalNroCompra.value = true;
            }
        }


    }
}
);
};

const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
</script>
<template>

    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">

        <div class="col-span-12   sm:col-span-12 md:col-span-6 shadow-default lg:col-span-5 2xl:col-span-4 mx-2">

            <InputLabel for="file_input1" value="Importar Stock"
                class="block text-base font-medium leading-6 text-gray-900" />
            <input ref="inputArchivo" @input="pickFile" type="file" class="block w-full text-xs text-gray-500
                    file:mr-4 file:py-1.5 file:px-3
                    file:rounded file:border-0
                    file:text-sm file:font-medium
                    file:bg-primary-900 file:text-white
                    hover:file:bg-primary-900/80
                    hover:file:cursor-pointer
                    file:disabled::opacity-75
                    file:disabled:cursor-no-drop
                    disabled:opacity-75
                    disabled:cursor-no-drop" :disabled="uploadStock.processing"
                accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
            <InputError class="mt-1 text-xs" :message="uploadStock.errors.archivo" />


            
        </div>
        <div class="col-span-12  sm:col-span-12 shadow-default xl:col-span-3  2xl:col-span-2 flex h-auto items-end justify-center pb-1">
            <div class="h-8">
                <Button label="Importar" type="button" class="text-normal" :class="{ 'opacity-50': uploadStock.processing }"
                    :disabled="uploadStock.processing" @click.prevent="submitExcel" />
            </div>
        </div>

        <div class="col-span-12 shadow-default xl:col-span-3 flex h-auto items-end 2xl:col-span-2 justify-center pb-1">
            <div class="h-8">
                <Button label="Descargar formato" size="md" severity="success" type="button"
                    class="p-1 text-xs font-light ring-0"
                    @click="descargarFormatoExcel('importar_stock_productos.xlsx')" />
            </div>
        </div>

        <div class="card px-4 mb-4 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-12">

            <!--Contenido-->
            <Toast />
            <div class="px-3 p-2 col-span-full flex justify-start items-center">
                <h5 class="text-2xl font-medium pr-5">{{ titulo }}</h5>
            </div>

            <div class="px-3 pb-2 col-span-full flex justify-end items-center">
                <span
                    v-tooltip.top="{ value: 'Descargar Excel', pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
                    class=" w-8 h-8 rounded bg-green-600 flex justify-center mr-5 items-center text-base font-semibold text-white hover:bg-green-600">
                    <a :href="route('productos.exportar')" target="_blank" class="py-auto"><i
                            class="fas fa-file-excel text-white"></i>
                    </a>

                </span>
                <Button size="small" :label="'Agregar Producto'" severity="success" @click="BtnCrear"></Button>

            </div>

            <div class="align-middle">

                <DataTable :rowClass="rowClass" :filters="filters" :value="tabla_productos" :pt="{
                    bodyRow: { class: 'hover:cursor-pointer hover:bg-gray-100 hover:text-black' },
                    root: { class: 'w-auto' }
                }" :globalFilterFields="['codigo_barra', 'origen', 'nombre']" scrollable scrollHeight="700px" paginator :rows="50"
                    @row-click="clickDetalle" size="small">
                    <template #header>
                        <div class="flex justify-content-end text-md">
                            <InputText v-model="filters['global'].value" placeholder="Buscar" />
                        </div>
                    </template>
                    <template #empty> No existe Resultado </template>
                    <template #loading> Cargando... </template>

                    <Column field="stock" sortable header="Stock" :pt="{
                        bodyCell: {
                            class: 'text-center w-12'
                        }
                    }"></Column>
                    <Column field="stock_futuro" sortable header="Stock futuro" :pt="{
                        bodyCell: {
                            class: 'text-center w-12'
                        }
                    }"></Column>
                    <Column field="imagen" header="Imagen" :pt="{
                        bodyCell: {
                            class: 'flex justify-center text-center w-12'
                        },
                        headerCell: {
                            class: 'w-10'
                        }
                    }">
                        <template #loading>
                        </template>
                        <template #body="slotProps">
                            <img class="rounded  bg-white shadow-2xl border-2 text-center w-10 h-10 object-contain"
                                :src="slotProps.data.imagen" alt="image">
                        </template>
                    </Column>
                    <Column field="origen" header="Origen" sortable :pt="{
                        bodyCell: {
                            class: 'text-center w-36'
                        }
                    }"></Column>
                    <Column field="nombre" header="Nombre" sortable :pt="{
                        bodyCell: {
                            class: 'text-center'
                        }
                    }"></Column>



                    <Column header="Acciones" style="width:80px" class="px-auto">
                        <template #loading>
                        </template>
                        <template #body="slotProps">
                            <!--

                                <button v-if="permissions.includes('editar-productos')"
                                class="w-8 h-8 rounded bg-yellow-500  px-2 py-1 text-base font-normal text-black m-1 hover:bg-yellow-400"
                                v-tooltip.top="{ value: `Ver`, pt: { text: 'bg-gray-500 p-1 m-0 text-xs text-white rounded' } }"
                                @click.prevent="btnVer(slotProps.data.id)"><i class="fas fa-eye"></i></button>
                            -->

                            <button v-if="permissions.includes('eliminar-productos')"
                                class="w-8 h-8 rounded bg-red-700  border border-white px-2 py-1 text-base font-normal text-white m-1 hover:bg-red-600"
                                v-tooltip.top="{ value: `Eliminar`, pt: { text: 'bg-gray-500 p-1 text-xs text-white rounded' } }"
                                @click.prevent="btnEliminar(slotProps.data.id, slotProps.data.nombre)"><i
                                    class="fas fa-trash-alt"></i></button>

                        </template>
                    </Column>
                </DataTable>

            </div>
            <!--Contenido-->

        </div>
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

                <p class="mb-2 font-semibold text-md">
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

            <template #header>
                <div class="flex flex-column align-items-center" style="flex: 1">
                    <div class="text-center">
                        <i class="pi pi-exclamation-triangle text-yellow-500" style="font-size: 3rem"></i>
                    </div>
                    <div class="font-semibold text-xl m-3">No se ha podido importar</div>
                </div>
            </template>


            <div class="flex justify-end py-3">
                <Button label="Aceptar" size="small" type="button" @click="closeModalProducto()" />

            </div>

        </Dialog>
        <!--Modal productos-->

        <!--Modal nro compra-->
        <Dialog v-model:visible="isShowModalNroCompra" modal :style="{ width: '30vw' }" :pt="{
            header: {
                class: 'mt-5 pb-2 px-5'
            },
            content: {
                class: 'p-4'
            },
        }">
            <div>
                <p class="mb-2 mt-0 font-semibold text-center text-md">
                    {{ errorsSku }}
                </p>

            </div>
            <template #header>
                <div class="flex flex-column align-items-center" style="flex: 1">
                    <div class="text-center">
                        <i class="pi pi-exclamation-triangle text-yellow-500" style="font-size: 3rem"></i>
                    </div>
                    <div class="font-semibold text-xl m-3">No se ha podido importar</div>
                </div>
            </template>


            <div class="flex justify-end py-3">
                <Button label="Aceptar" size="small" type="button" @click="closeModalNroCompra()" />

            </div>

        </Dialog>
        <!--Modal nro compra-->
    </AppLayout>
</template>


<style type="text/css"></style>
