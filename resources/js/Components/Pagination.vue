<template>
  <div id="pagination" class="w-full flex justify-center border-t border-gray-100 pt-4 items-center">
      <div class="flex flex-wrap -mb-1">
          <template v-for="(link, key) in elements.links" :key="key">
              <div v-if="link.url === null"
                  class="mr-1 mb-1 px-3 py-3 text-sm leading-4 text-gray-400 border rounded" v-html="link.label" />
              <Link v-else
                  class="mr-1 mb-1 px-3 py-3 text-sm leading-4 border rounded hover:bg-gray-200 focus:border-primary-900 focus:text-gray-400"
                  :class="{ 'bg-primary-900 text-gray-400': link.active }" :href="link.url" v-html="link.label" />
          </template>
      </div>
  </div>
</template>

<script setup>

import { Link } from '@inertiajs/vue3';
defineProps(['elements']);
</script>