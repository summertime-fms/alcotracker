<template>
  <AuthBrand />
  <AuthLoginForm />
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'guest',
  layout: 'auth',
})
</script>
