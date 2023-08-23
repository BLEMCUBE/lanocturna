<template>
    <nav class="p-2 dark:bg-gray-800 dark:text-gray-100 text-base">
        <ol class="flex h-8 space-x-2">
            <li class="flex items-center space-x-2 hover:text-primary-100">
                <Link class="hover:fill-primary-100 flex" href="/inicio">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="w-5 h-5 pr-1 dark:text-gray-400">
                    <path
                        d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                    </path>
                </svg>
                <span>
                    Inicio
                </span>
                </Link>
            </li>
            <li class="flex items-center hover:fill-primary-100 space-x-2" v-for="item in pagina">
                <div v-if="item.link==true">
                    <i class="fas fa-angle-right"></i>
                    <Link class="hover:text-primary-100 pl-2" :href="item.url">
                    {{item.label}}
                </Link>
                </div>
                <div v-if="item.link==false" class="text-primary-900/70">
                    <i class="fas fa-angle-right"></i>
                    {{item.label}}
                </div>
            </li >

        </ol>
    </nav>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
const props = defineProps({
    pagina: {
        type: Array,
        default: [],
    },

});
</script>
