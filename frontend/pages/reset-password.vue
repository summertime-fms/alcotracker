<template>
  <div class="auth-page">
    <div class="auth-container">
      <AuthBrand />
      <AuthResetPasswordForm />
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'guest',
  layout: false,
})
</script>
