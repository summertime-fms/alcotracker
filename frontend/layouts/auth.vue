<template>
  <div class="auth-page">
    <div class="auth-container">
      <slot />
    </div>
  </div>
</template>

<style>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-md);
  background: var(--gradient-auth-bg);
  position: relative;
  overflow: hidden;
}

.auth-page::before {
  content: '';
  position: absolute;
  width: 480px;
  height: 480px;
  top: -160px;
  right: -120px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%);
  pointer-events: none;
}

.auth-page::after {
  content: '';
  position: absolute;
  width: 360px;
  height: 360px;
  bottom: -100px;
  left: -80px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
  pointer-events: none;
}

.auth-container {
  width: 100%;
  max-width: 440px;
  padding: var(--spacing-2xl);
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.9);
  border-radius: var(--radius-2xl);
  box-shadow: var(--shadow-xl);
  animation: slideUp 0.4s ease-out;
  position: relative;
  z-index: 1;
}
</style>
