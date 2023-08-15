<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
    label: String,
    fieldId: {
        type: String,
        required: true,
      },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
     <input
        type="checkbox"
        :value="value"
        v-model="proxyChecked"
        class="rounded-md border-gray-300 text-xl text-blue-600 shadow-sm w-5 h-5 cursor-pointer"
    />
     <label
      :for="fieldId"
      class="ml-2 font-normal text-md text-gray-800 dark:text-white"
    >  {{ label }}
    </label>

</template>
