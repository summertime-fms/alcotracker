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

<style scoped>
.navigation {
  display: flex;
  gap: var(--spacing-xs);
  padding: var(--spacing-xs);
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(12px);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-full);
  box-shadow: var(--shadow-sm);
  width: fit-content;
  margin-bottom: var(--spacing-xl);
  flex-shrink: 0;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm) var(--spacing-lg);
  border-radius: var(--radius-full);
  text-decoration: none;
  color: var(--color-text-light);
  transition: all var(--transition-fast);
  font-weight: var(--font-weight-medium);
  font-size: var(--font-size-sm);
}

.nav-link:hover {
  color: var(--color-accent);
  background: var(--color-accent-light);
}

.nav-link.active {
  background: var(--gradient-primary);
  color: #FFFFFF;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.nav-link.active:hover {
  color: #FFFFFF;
  opacity: 0.92;
}

.nav-icon {
  font-size: var(--font-size-base);
}

.nav-text {
  font-family: var(--font-family-display);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
}

@media (max-width: 767px) {
  .navigation {
    width: 100%;
    justify-content: center;
  }

  .nav-link {
    flex: 1;
    justify-content: center;
    padding: var(--spacing-sm) var(--spacing-md);
  }

  .nav-text {
    display: none;
  }
}
</style>
