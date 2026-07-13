<template>
  <AuthBrand />
  <AuthForgotPasswordForm />
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'guest',
  layout: 'auth',
})
</script>
