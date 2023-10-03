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

        ? ' bg-secondary-100 text-primary-900 dark:bg-gray-700 hover:bg-secondary-100 tracking-wide rounded-none hover:text-primary-900 w-full'
        : '  hover:bg-secondary-100 hover:text-primary-900 rounded-none text-white w-full tracking-wide'
);

</script>
<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
