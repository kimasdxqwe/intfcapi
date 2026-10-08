<template>
     <h1 class="text-2xl font-bold">Dashboard</h1>
    <p class="mt-2 text-gray-600">Signed in as {{ auth.user.name }} ({{ auth.user.email }}).</p>
</template>

<script setup>
import {useAuthStore} from "@/stores/auth.js";

const auth = useAuthStore();
</script>

<style scoped>

</style>
