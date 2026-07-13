<template>
  <div></div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const { isAuthenticated } = useAuth()

if (isAuthenticated.value) {
  navigateTo('/', { replace: true })
}
</script>
