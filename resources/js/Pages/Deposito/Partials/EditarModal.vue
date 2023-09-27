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
const titulo = "Bultos"
const ruta = "depositos"

//Variables
const isShowModal = ref(false);
const isShowModalProducto = ref(false);
const productos_movidos = ref([]);

const form = useForm({
    id: '',
    estado: '',
    nro_carpeta: '',
    nro_carpeta: '',
    nro_contenedor: '',
    estado: '',
    fecha_arribado: '',
    fecha_camino: '',
})

const props = defineProps({
    clienteId: {
        type: Number,
        default: null,
    },


});


//Funciones
const setEstado = (e) => {

    if (selectedEstado.value.code == form.estado)
        return;
    form.estado = selectedEstado.value.code;

}

const addCliente = () => {
    dataEdit(props.clienteId);

};

const selectedEstado = ref();
const lista_estado = ref([
    { name: 'Arribado', code: 'Arribado' },
    { name: 'En camino', code: 'En camino' },
]);


const dataEdit = (id) => {
    axios.get(route(ruta + '.showmodal', id))
        .then(res => {
            if (res.data.status == true) {
                var datos = res.data.deposito
                form.id = datos.id
                form.nro_carpeta = datos.nro_carpeta
                form.nro_contenedor = datos.nro_contenedor
                form.estado = datos.estado
                selectedEstado.value = lista_estado.value.find(pr => pr.code === datos.estado);
                form.fecha_arribado = moment(datos.fecha_arribado).format('YYYY-MM-DD');
                form.fecha_camino = moment(datos.fecha_camino).format('YYYY-MM-DD');
                isShowModal.value = true;
            } else {
                productos_movidos.value = res.data.faltantes
                isShowModalProducto.value = true;
            }


        })

};


const closeModal = () => {
    form.reset();
    form.clearErrors()
    isShowModal.value = false;
};

const closeModalProducto = () => {
    isShowModalProducto.value = false;
};

//envio de formulario
const submit = () => {

    form.clearErrors()
    form.post(route(ruta + '.update', form.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            isShowModal.value = false
            show('success', 'Mensaje', 'Se ha editado')
            setTimeout(() => {
                router.get(route(ruta + '.show', form.id));
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
        <button type="button" @click="addCliente"><i class="fas fa-edit"></i></button>
        <Toast />
        <!--Modal productos-->
        <Dialog v-model:visible="isShowModalProducto" modal  :style="{ width: '50vw' }" :pt="{
            header: {
                class: 'mt-5 pb-2 px-5'
            },
            content: {
                class: 'p-4'
            },
        }">
            <p class="mb-2 font-bold">
                Los siguientes productos deben ser movido al DEPÓSITO TEMPORAL para poder cambiar de estado o ya se ha retirado definitivamente del depósito.
            </p>
            <DataTable :value="productos_movidos" :pt="{

                root: { class: 'text-xs' }
            }" size="small">


                <Column field="sku" header="Sku" :pt="{
                  bodyCell: { class: 'text-center p-0 m-0 w-24' },
                                    headerCell: { class: 'bg-secondary-900 p-0 m-0 w-24' },
                                    headerContent: {

                                        class: 'text-center w-24'
                                    },
                                    bodyCellContent: {

                                        class: 'text-center w-24'
                                    },


                }"></Column>
                <Column field="producto" header="Producto" :pt="{
                  bodyCell: { class: 'text-center p-0 m-0 w-auto' },
                                    headerCell: { class: 'bg-secondary-900 p-0 m-0 w-auto' },
                                    headerContent: {

                                        class: 'text-center w-auto'
                                    },
                                    bodyCellContent: {

                                        class: 'text-center w-auto'
                                    },


                }"></Column>


                <Column field="mover" header="Cantidad a mover"  :pt="{
                  bodyCell: { class: 'text-center p-0 m-0 w-32' },
                                    headerCell: { class: 'bg-secondary-900 p-0 m-0 w-32' },
                                    headerContent: {

                                        class: 'text-center w-32'
                                    },
                                    bodyCellContent: {

                                        class: 'text-center w-32'
                                    },


                }"></Column>


            </DataTable>
            <template #header>
                <div class="flex flex-column align-items-center" style="flex: 1">
                    <div class="text-center">
                        <i class="pi pi-exclamation-triangle text-yellow-500" style="font-size: 3rem"></i>
                    </div>
                    <div class="font-bold text-2xl m-3">No se ha podido cambiar de Estado</div>
                </div>
            </template>


            <div class="flex justify-end py-3">
                <Button label="Aceptar" size="small" type="button" @click="closeModalProducto()" />

            </div>

        </Dialog>
        <!--Modal productos-->
        <Dialog v-model:visible="isShowModal" modal :header="'Editar ' + titulo" :style="{ width: '30vw' }" position="top"
            :pt="{
                header: {
                    class: 'mt-6 p-2'
                },
                content: {
                    class: 'p-2'
                },
            }">
            <form>
                <div class="px-2 pt-4 pb-0 grid grid-cols-12 gap-2 mb-2">

                    <div class="col-span-12 shadow-default lg:col-span-6">
                        <InputLabel for="nro_carpeta" value="No. de Carpeta"
                            class="block text-base font-medium leading-6 text-gray-900" />

                        <InputText type="text" id="nro_carpeta" v-model="form.nro_carpeta"
                            placeholder="Ingrese No. de Carpeta" :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                        <InputError class="mt-1 text-xs" :message="form.errors.nro_carpeta" />
                    </div>

                    <div class="col-span-12 shadow-default lg:col-span-6">
                        <InputLabel for="nro_contenedor" value="BL o No. de Contenedor"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <InputText type="text" id="nro_contenedor" v-model="form.nro_contenedor"
                            placeholder="Ingrese BL o No. de Contenedor" :pt="{
                                root: { class: 'h-9 w-full' }
                            }" />
                        <InputError class="mt-1 text-xs" :message="form.errors.nro_contenedor" />
                    </div>

                    <div class="col-span-12 shadow-default">
                        <InputLabel for="estado" value="Estado"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <Dropdown v-model="selectedEstado" @change="setEstado" :options="lista_estado" optionLabel="name"
                            :pt="{
                                root: { class: 'w-full' },
                                trigger: { class: 'fas fa-caret-down text-gray-200 my-auto' },
                                item: ({ props, state, context }) => ({
                                    class: context.selected ? 'text-white bg-primary-900' : context.focused ? 'bg-blue-100' : undefined
                                })
                            }" placeholder="Seleccione estado" />
                        <InputError class="mt-1 text-xs" :message="form.errors.estado" />
                    </div>


                    <div class="col-span-12 shadow-default my-auto ">
                        <InputLabel for="fecha_camino" value="Fecha En Camino"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <date-picker :clearable="false" :editable="true" type="date" value-type="YYYY-MM-DD"
                            format="DD/MM/YYYY"
                            class="p-component col-span-6  text-gray-700  bg-white  transition-colors duration-200 border-0 px-0 py-0"
                            v-model:value="form.fecha_camino" lang="es" placeholder="Seleccione Fecha"></date-picker>
                        <InputError class="mt-1 text-xs" :message="form.errors.fecha_camino" />
                    </div>

                    <div class="col-span-12 shadow-default my-auto">
                        <InputLabel for="estado" value="Fecha Arribado"
                            class="block text-base font-medium leading-6 text-gray-900" />
                        <date-picker :clearable="false" :editable="true" type="date" value-type="YYYY-MM-DD"
                            format="DD/MM/YYYY"
                            class="p-component col-span-6  text-gray-700  bg-white  transition-colors duration-200 border-0 px-0 py-0"
                            v-model:value="form.fecha_arribado" lang="es" placeholder="Seleccione Fecha"></date-picker>
                        <InputError class="mt-1 text-xs" :message="form.errors.fecha_arribado" />
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
