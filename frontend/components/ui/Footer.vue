<template>
  <footer class="footer">
    <div class="footer__top">
      <UiAppBrand class="footer__brand" />

      <nav class="footer__nav" aria-label="Карта сайта">
        <NuxtLink to="/" class="footer__link" :class="{ active: isActive('/') }">
          Главная
        </NuxtLink>
        <NuxtLink to="/statistics" class="footer__link" :class="{ active: isActive('/statistics') }">
          Статистика
        </NuxtLink>
        <NuxtLink to="/detox" class="footer__link" :class="{ active: isActive('/detox') }">
          Детокс
        </NuxtLink>
        <NuxtLink to="/about" class="footer__link" :class="{ active: isActive('/about') }">
          О проекте
        </NuxtLink>
      </nav>
    </div>

    <p class="footer__disclaimer">
      AlcoTracker — инструмент самонаблюдения. Он не ставит диагнозов и не заменяет медицинскую консультацию.
    </p>
  </footer>
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
