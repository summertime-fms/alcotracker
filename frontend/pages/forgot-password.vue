<template>
  <div class="auth-page">
    <div class="auth-container">
      <AuthForgotPasswordForm />
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'guest',
  layout: false,
})
</script>
