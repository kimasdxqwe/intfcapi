import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useLaraFetch } from '@/composables/useLaraFetch.js';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const ready = ref(false); // true once the server has been asked

    const isAuthenticated = computed(() => user.value !== null);

    function resetUser() {
        user.value = null;
    }

    async function fetchUser() {
        const { data, error } = await useLaraFetch('/user').get().json();
        user.value = error.value ? null : data.value;
        ready.value = true;
    }

    function redirectToLogin(returnTo = window.location.pathname + window.location.search) {
        window.location.href = `/login?redirect=${encodeURIComponent(returnTo)}`;
    }

    async function logout() {
        await useLaraFetch('/logout').post(); // 204, so no .json()

        user.value = null;
    }

    return { user, ready, isAuthenticated, resetUser, fetchUser, redirectToLogin, logout };
});
