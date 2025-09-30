import { defineStore } from "pinia";

export const useLoaderStore = defineStore("loaderStore", {
  state: () => ({
    visible: false,
  }),
  actions: {
    show() {
      this.visible = true;
    },
    hide() {
      this.visible = false;
    },
  },
});
