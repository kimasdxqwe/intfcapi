<template>
    <nav class="nav">
        <RouterLink to="/home">Home</RouterLink>
        <RouterLink to="/about">About</RouterLink>
        <RouterLink to="/contact">Contact</RouterLink>
        <RouterLink to="/dashboard">Dashboard</RouterLink>
    </nav>

    <main class="content">
        <RouterView />
    </main>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<style>
.nav { display: flex; gap: 16px; padding: 16px; border-bottom: 1px solid #ddd; }
.nav a { text-decoration: none; color: #333; }
.nav a.router-link-exact-active { font-weight: bold; color: #2563eb; }
.content { max-width: 720px; margin: 24px auto; padding: 0 16px; }
</style>
