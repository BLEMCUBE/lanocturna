<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
    iconClass: {
        type: String
    }
});

const classes = computed(() =>
    props.active

        ? ' w-full bg-gray-200 dark:bg-gray-700'
        : ''
);
const icon = computed(() =>
    props.active
        ? props.iconClass + ' w-5 h-5 text-gray-900 transition duration-75 group-hover:text-gray-900 dark:text-white dark:group-hover:text-white'
        : props.iconClass + ' w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white'
);
</script>
<template>
    <Link :href="href" :class="classes">
    <i :class="icon"></i>
    <slot />
    </Link>
</template>
