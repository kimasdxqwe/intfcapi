<template>
    <div>
        <h1 class="text-2xl font-bold">Not Found</h1>
        <p>Sorry, the page you're looking for doesn't exist.</p>
        <RouterLink to="/home" class="hover:underline">Back to home</RouterLink>
    </div>
</template>
<script setup>

import {RouterLink} from "vue-router";
</script>

<style scoped>

</style>
