<template>
  <AuthBrand />
  <AuthResetPasswordForm />
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'guest',
  layout: 'auth',
})
</script>
