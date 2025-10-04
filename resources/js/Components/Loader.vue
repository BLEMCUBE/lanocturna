<template>
  <transition name="fade">
    <div
      v-if="loader.visible"
      class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60"
    >
      <!-- Spinner -->
      <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
    </div>
  </transition>
</template>

<script setup>
import { useLoaderStore } from "@/stores/loader";
const loader = useLoaderStore();
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
