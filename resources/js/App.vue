<template>
    <nav class="nav flex items-center gap-4 border-b border-gray-200 px-6 py-3">
        <RouterLink to="/home">Home</RouterLink>
        <RouterLink to="/about">About</RouterLink>
        <RouterLink to="/dashboard">Dashboard</RouterLink>
        <RouterLink v-if="!isProduction" to="/dev">Dev</RouterLink>

        <div class="ml-auto flex items-center gap-4">
            <template v-if="auth.isAuthenticated">
                <span class="text-gray-500">{{ auth.user.email }}</span>
                <a
                    href="#"
                    class="logout cursor-pointer px-3 py-1 hover:underline"
                    @click="handleLogout"
                >
                    Logout
                </a>
            </template>

            <a
                v-else
                href="/login"
                class="inline-block cursor-pointer px-3 py-1 hover:underline"
            >
                Log in
            </a>
        </div>
    </nav>

    <ModalHost/>

    <main class="mx-auto max-w-5xl p-6">
        <RouterView />
    </main>
</template>

<script setup>
import ModalHost from "@/components/modal/ModalHost.vue";
import { RouterLink, RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth';
import { useEnv } from '@/composables/useEnv';
import { useRouter } from 'vue-router';

const { isProduction } = useEnv();
const auth = useAuthStore();

async function handleLogout() {
    await auth.logout();

    auth.redirectToLogin();
}

</script>

<style>
.nav a { text-decoration: none; color: #333; }
.nav a.router-link-exact-active { font-weight: bold; color: #2563eb; }
.content { max-width: 720px; margin: 24px auto; padding: 0 16px; }

.logout { font-weight: bold; color: #c11726 !important; }
</style>
