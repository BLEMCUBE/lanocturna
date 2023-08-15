<script setup>
import { onMounted, onUnmounted } from 'vue';
import SideBar from '@/Components/SideBar.vue';
import NavBar from '@/Components/NavBar.vue';
import { initFlowbite } from 'flowbite'
import { useConfigStore } from '@/store/config.js'
const configStore = useConfigStore();

onMounted(() => {
    initFlowbite();

})

/*window.addEventListener("beforeunload", () => {
    localStorage.removeItem('currentMenu');
});
*/

onUnmounted(() => {
    //localStorage.removeItem('currentMenu');



})
</script>

<template>
    <NavBar></NavBar>
        <SideBar></SideBar>
        <div class="pt-16 sm:ml-56">
        <div class="grid grid-cols-1 p-4 xl:grid-cols-12 md:grid-span-12 xl:gap-4 dark:bg-gray-900 z-20">
            <slot />
        </div>
    </div>
    <footer class="fixed ml-0 md:ml-56  text-center bottom-0 z-20 w-full p-2 bg-white border-t border-gray-200 shadow md:flex md:items-center md:justify-between md:p-2 dark:bg-gray-800 dark:border-gray-600">
    <span class="text-xs text-gray-500 text-center sm:text-center dark:text-gray-400"><a href="https://walink.co/38ecb3" class="hover:underline">Desarrollado por: Ing. Oscar Jimmy Marquez </a> | +591 69081668
    </span>
</footer>
</template>
