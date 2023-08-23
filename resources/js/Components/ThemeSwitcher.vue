<template>
    <div class="card flex justify-end p-2 mb-4">
        <button type="button" class="flex w-2rem h-2rem p-0 align-center justify-center" @click="onThemeToggler">
            <i :class="`dark:text-white pi ${iconClass}`" />
        </button>
    </div>
</template>

<script setup>
import {ref} from 'vue';
const iconClass = ref('pi-moon');

const onThemeToggler = () => {
    const root = document.getElementsByTagName('html')[0];

    //root.classList.toggle('dark');
    iconClass.value = iconClass.value==='pi-moon' ? 'pi-sun': 'pi-moon';
};
</script>
