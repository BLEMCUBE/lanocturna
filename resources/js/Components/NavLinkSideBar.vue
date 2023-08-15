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

        ? ' w-full bg-primary-100 dark:bg-gray-700'
        : ''
);
const icon = computed(() =>
    props.active
        ? props.iconClass + ' w-5 h-5 text-white transition duration-75 group-hover:text-white dark:text-white dark:group-hover:text-white'
        : props.iconClass + ' w-5 h-5 text-white transition duration-75 group-hover:text-white dark:text-gray-400 dark:group-hover:text-white'
);
</script>
<template>
    <Link :href="href" :class="classes">
    <i :class="icon"></i>
    <slot />
    </Link>
</template>
