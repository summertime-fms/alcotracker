<template>
  <component :is="tag" class="app-logo" :class="[`app-logo--${size}`]">
    <img
      src="/logo.svg"
      alt=""
      class="app-logo__image"
      width="40"
      height="40"
    />
    <span v-if="showText" class="app-logo__text">AlcoTracker</span>
  </component>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  tag?: 'h1' | 'div' | 'span'
  size?: 'sm' | 'md' | 'lg'
  showText?: boolean
}>(), {
  tag: 'div',
  size: 'md',
  showText: true,
})
</script>
