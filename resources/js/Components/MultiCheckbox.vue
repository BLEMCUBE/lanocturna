<template>
    <div
      class="flex flex-col items-start justify-center w-64 border-2 border-gray-400 p-8 rounded-lg bg-gray-100"
    >

      <Checkbox
        v-for="option in options"
        :checked="value.includes(option.id)"
        @update:checked="check(option.id, $event)"
        :fieldId="option.name"
        :label="option.name"
        :key="option"
      ></Checkbox>

    </div>
  </template>

  <script>
  import CheckboxName from "@/Components/CheckboxName.vue";
  import Checkbox from "@/Components/Checkbox.vue";

  export default {
    emits: ["update:value"],
    props: {
      value: {
        type: Array,
        required: true,
      },
      options: {
        type: Array,
        required: true,
        validator: (value) => {
          const hasNameKey = value.every((option) =>
            Object.keys(option).includes("name")
          );
          const hasIdKey = value.every((option) =>
            Object.keys(option).includes("id")
          );
          return hasNameKey && hasIdKey;
        },
      },
    },
    setup(props, context) {
      const check = (optionId, checked) => {
        let updatedValue = [...props.value];
        if (checked) {
          updatedValue.push(optionId);
        } else {
          updatedValue.splice(updatedValue.indexOf(optionId), 1);
        }
        context.emit("update:value", updatedValue);
      };

      return {
        check,
      };
    },
    components: {
      "CheckboxName": CheckboxName,
    },
  };
  </script>
