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

        ? ' bg-secondary-100 text-primary-900 dark:bg-gray-700 hover:bg-secondary-100 hover:text-primary-900 rounded'
        : '  hover:bg-secondary-100 hover:text-primary-900 rounded text-white rounded'
);
const icon = computed(() =>
    props.active
        ? props.iconClass + ' w-5 h-5 text-primary-900 fill-white hover:text-primary-900 fill-primary-900 hover:fill-primary-900 transition duration-75 hover:text-white dark:text-white dark:hover:text-white'
        : props.iconClass + ' '
);
</script>
<template>
    <Link :href="href" :class="classes">
    <i :class="icon"></i>
    <slot />
    </Link>
</template>
