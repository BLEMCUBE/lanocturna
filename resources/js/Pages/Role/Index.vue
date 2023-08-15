

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, reactive, computed, watch } from 'vue'
import Pagination from '@/Components/Pagination.vue';
import { Breadcrumb, BreadcrumbItem } from 'flowbite-vue'
import { Head, usePage, Link } from '@inertiajs/vue3';

const tabla_categorias = ref({})
const searchQuery = ref('');


//buscador
const filteredItems = computed(() => {
    let filteredItems1 = usePage().props.roles;
    if (searchQuery.value !== "") {
        pagination.currentPage = 1;
        filteredItems1 = tabla_categorias.value.filter(bet => {
            return bet.nombre.toLowerCase().includes(searchQuery.value.toLowerCase())
        })
    }
    return filteredItems1;

})

const pagination = reactive({
    currentPage: 1,
    perPage: 10,
    totalPages: computed(() =>
        Math.ceil(filteredItems.value.length / pagination.perPage)
    ),
});

watch(
    () => pagination.totalPages,
    () => (pagination.currentPage = 1)
);

const paginatedItems = computed(() => {
    const { currentPage, perPage } = pagination;
    const start = (currentPage - 1) * perPage;
    const stop = start + perPage;

    return filteredItems.value.slice(start, stop);
});


const clickCallback = (page) => {
    pagination.currentPage = page;
}


onMounted(() => {
    tabla_categorias.value = usePage().props.roles.data;
});



</script>
<template>
    <div>

        <Head title="Roles" />
        <AuthenticatedLayout>
            <div class="ml-4 col-span-full">

                <Breadcrumb>
                    <BreadcrumbItem href="/inicio" home>
                        Inicio
                    </BreadcrumbItem>
                    <BreadcrumbItem>
                        Roles
                    </BreadcrumbItem>
                </Breadcrumb>
            </div>
            <div class=" px-5 col-span-full flex justify-between items-center">

                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Roles</h1>

            </div>
            <div
                class="px-4 py-0 mb-4 bg-white col-span-6 pb-5 rounded-lg shadow-sm 2xl:col-span-12 dark:border-gray-700  dark:bg-gray-800">
                <div class="overflow-x-auto">

                    <div class="inline-block min-w-full align-middle">
                        <div class="overflow-visible">


                            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                                <thead
                                    class="text-md text-gray-700 bg-gray-200/80 border-2 border-gray-300  dark:border-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>

                                        <th scope="col"
                                            class="p-1 text-center border-2 border-gray-300 dark:border-gray-500 w-20">
                                            ID
                                        </th>
                                        <th scope="col"
                                            class="p-1 text-center border-2 border-gray-300 dark:border-gray-500">
                                            Nombre
                                        </th>


                                        <th scope="col"
                                            class="p-1 text-center border-2 border-gray-300 dark:border-gray-500 w-20">
                                            <div class="flex justify-center">
                                                Acciones
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr :key="id" v-for="{ id, name }, index in paginatedItems"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">

                                        <th scope="row"
                                            class="px-3 py-1 border-2  text-center border-gray-300 dark:border-gray-700 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ index + 1 }}
                                        </th>
                                        <th scope="row"
                                            class="px-3 py-1 border-2  border-gray-300 dark:border-gray-700 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ name }}
                                        </th>

                                        <td scope="row"
                                            class="p-1 w-20 border-2  border-gray-300 dark:border-gray-700 text-center">

                                            <Link :href="route('roles.edit', id)"
                                                class="inline-block rounded bg-blue-600 px-2 py-1 text-sm font-semibold text-white mr-1 mb-1 hover:bg-blue-700">
                                            Permisos
                                            </Link>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                            <pagination :pageCount="pagination.totalPages" class="my-3"
                                :containerClass="'flex items-center -space-x-px h-10 text-base'"
                                :prevLinkClass="'flex items-center  cursor-pointer justify-center px-2  h-10 ml-0 leading-tight text-gray-500  border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white'"
                                :nextLinkClass="'flex items-center cursor-pointer justify-center px-2 h-10 leading-tight text-gray-500  border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white'"
                                :pageLinkClass="'flex items-center hover:cursor-pointer justify-center px-5 h-10 leading-tight text-gray-500  border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white'"
                                :prevText="'«&nbsp; Anterior'" :nextText="'Siguiente &nbsp;»'"
                                :disabledClass="'bg-gray-100 cursor-not-allowed'"
                                :activeClass="'bg-gray-200 cursor-pointer'" :clickHandler="clickCallback">
                            </pagination>
                        </div>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>

    </div>
</template>


<style type="text/css" scoped></style>
