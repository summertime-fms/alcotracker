<template>
  <nav class="navigation">
    <NuxtLink to="/" class="nav-link" :class="{ active: isActive('/') }">
      <span class="nav-icon">🏠</span>
      <span class="nav-text">Главная</span>
    </NuxtLink>
    <NuxtLink to="/statistics" class="nav-link" :class="{ active: isActive('/statistics') }">
      <span class="nav-icon">📊</span>
      <span class="nav-text">Статистика</span>
    </NuxtLink>
    <NuxtLink to="/detox" class="nav-link" :class="{ active: isActive('/detox') }">
      <span class="nav-icon">🌿</span>
      <span class="nav-text">Детокс</span>
    </NuxtLink>
    <NuxtLink to="/about" class="nav-link" :class="{ active: isActive('/about') }">
      <span class="nav-icon">ℹ️</span>
      <span class="nav-text">О проекте</span>
    </NuxtLink>
  </nav>
</template>

<script setup lang="ts">
const route = useRoute()

const isActive = (path: string) => {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(path)
}
</script>
