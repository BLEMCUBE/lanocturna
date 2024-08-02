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
const titulo = "Importación"
const ruta = "importaciones"

//Variables
const isShowModal = ref(false);

const form = useForm({
    id: '',
    estado: '',
    nro_carpeta: '',
    nro_contenedor: '',
    fecha_arribado: '',
    fecha_camino: '',
    costo_cif: '',
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
            var datos = res.data.importacion
            form.id = datos.id
            form.nro_carpeta = datos.nro_carpeta
            form.nro_contenedor = datos.nro_contenedor
            form.estado = datos.estado
            form.costo_cif = datos.costo_cif
            selectedEstado.value = lista_estado.value.find(pr => pr.code === datos.estado);
            form.fecha_arribado = moment(datos.fecha_arribado).format('YYYY-MM-DD');
            form.fecha_camino = moment(datos.fecha_camino).format('YYYY-MM-DD');
            isShowModal.value = true;

        })

};


const closeModal = () => {
    form.reset();
    form.clearErrors()
    isShowModal.value = false;
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

					<div class="col-span-12 shadow-default my-auto">
							<InputLabel for="costo_cif" value="Costo CIF"
								class="block text-base font-medium leading-6 text-gray-900" />
							<input type="number" v-model="form.costo_cif" class="p-inputtext p-component text-gray-700 bg-white
                            border appearance-none rounded text-basw h-9 m-0 w-full text-end" />
							<InputError class="mt-1 text-xs" :message="form.errors.costo_cif" />
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
