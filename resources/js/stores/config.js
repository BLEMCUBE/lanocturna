import { defineStore } from 'pinia';
import { useLocalStorage,useSessionStorage } from "@vueuse/core"

export const useConfigStore = defineStore("config", {

    state: () => ({
        isOpen: true,
        currentMenu: useSessionStorage('currentMenu', 'inicio'),
    }),
    getters: {
        getIsOpen(state) {
            return state.isOpen
        },
        getCurrentMenu(state) {
            return state.currentMenu
        },

    },
    actions: {

        showSide(boolean) {
            this.isOpen = boolean;
        },
        showMenu(string) {
            this.currentMenu = string;
        },

    },


})
