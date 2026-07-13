<template>
  <div class="app-brand">
    <UiAppLogo :tag="logoTag" :size="size" />
    <p v-if="subtitle" class="app-brand__subtitle">{{ subtitle }}</p>
  </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  logoTag?: 'h1' | 'div' | 'span'
  size?: 'sm' | 'md' | 'lg'
  subtitle?: string
}>(), {
  logoTag: 'div',
  size: 'lg',
  subtitle: 'Учет потребления алкоголя',
})
</script>
