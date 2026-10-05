import { defineStore } from 'pinia';
import {ref} from "vue";

export const useCoreStore = defineStore('core', () => {

    const theme = ref('light');

    return {
        theme
    }

});
