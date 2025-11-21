<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue'
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useToast } from "primevue/usetoast";
const toast = useToast();
const titulo = "Ajuste Stock"
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

onMounted(() => {

});

const show = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};


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
            show('success', 'Mensaje', 'Stock Ajustado')
            setTimeout(() => {
                router.get(route(ruta + '.ajuste-stock'));
            }, 1000);
        },
        onFinish: () => {

        },
        onError: (er) => {
            isLoad.value = false;
            inputArchivo.value.value = null
            uploadStock.reset('archivo');
            if (er.filas != undefined) {
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

</script>
<template>

    <Head :title="titulo" />
    <AppLayout :pagina="[{ 'label': titulo, link: false }]">

        <div class="card px-4 mb-4 bg-white col-span-12  rounded-lg shadow-lg 2xl:col-span-8">

            <!--Contenido-->
            <div class="px-3 p-2 col-span-full flex justify-start items-center">
                <h5 class="text-2xl font-medium pr-5">{{ titulo }}</h5>
            </div>
        <div class="grid grid-cols-12 gap-4 py-3">

            <div class="col-span-7 sm:col-span-12 md:col-span-6 shadow-default lg:col-span-5 2xl:col-span-6 mx-2">

                <InputLabel for="file_input1" value="Importar excel"
                    class="block text-base font-semibold leading-6 text-gray-900" />
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
            <div
                class="col-span-3  font-medium sm:col-span-6 shadow-default xl:col-span-3  2xl:col-span-3 flex h-auto items-end justify-center">
                <div class="h-8">
                    <Button label="Importar" type="button" class="text-normal font-medium"
                        :class="{ 'opacity-50': uploadStock.processing }" :disabled="uploadStock.processing"
                        @click.prevent="submitExcel" />
                </div>
            </div>

            <div class="col-span-3 shadow-default xl:col-span-3 flex h-auto items-end 2xl:col-span-2 justify-center">
                <div class="h-8">
                    <Button label="Descargar formato" size="md" severity="success" type="button"
                        class="p-1 text-xs font-medium ring-0"
                        @click="descargarFormatoExcel('importar_stock_productos.xlsx')" />
                </div>
            </div>
        </div>
        </div>
        <!--/Contenido-->

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
