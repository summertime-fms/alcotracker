<template>
  <div class="app-root">
    <div class="app-bg" aria-hidden="true">
      <div class="app-bg__orb app-bg__orb--1" />
      <div class="app-bg__orb app-bg__orb--2" />
      <div class="app-bg__orb app-bg__orb--3" />
    </div>
    <NuxtPage />
  </div>
</template>

<style>
@import './assets/css/variables.css';

/* Глобальные стили встроены */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html {
  font-size: 16px;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

body {
  font-family: var(--font-family-base);
  font-size: var(--font-size-base);
  font-weight: var(--font-weight-normal);
  line-height: var(--line-height-normal);
  color: var(--color-text);
  background-color: #FFFFFF;
}

.app-root {
  position: relative;
  min-height: 100vh;
}

.app-bg {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
  background: #FFFFFF;
}

.app-bg__orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.45;
}

.app-bg__orb--1 {
  width: 520px;
  height: 520px;
  top: -120px;
  right: -80px;
  background: radial-gradient(circle, rgba(99, 102, 241, 0.18) 0%, transparent 70%);
}

.app-bg__orb--2 {
  width: 400px;
  height: 400px;
  bottom: 10%;
  left: -100px;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.14) 0%, transparent 70%);
}

.app-bg__orb--3 {
  width: 300px;
  height: 300px;
  top: 40%;
  right: 20%;
  background: radial-gradient(circle, rgba(167, 139, 250, 0.1) 0%, transparent 70%);
}

.app-root > :not(.app-bg) {
  position: relative;
  z-index: 1;
}

/* Заголовки */
h1, h2, h3, h4, h5, h6 {
  font-family: var(--font-family-display);
  font-weight: var(--font-weight-semibold);
  line-height: var(--line-height-tight);
  color: var(--color-text);
  letter-spacing: -0.02em;
}

h1 { font-size: var(--font-size-4xl); }
h2 { font-size: var(--font-size-3xl); }
h3 { font-size: var(--font-size-2xl); }
h4 { font-size: var(--font-size-xl); }
h5 { font-size: var(--font-size-lg); }
h6 { font-size: var(--font-size-base); }

/* Ссылки */
a {
  color: var(--color-accent);
  text-decoration: none;
  transition: color var(--transition-fast);
}
a:hover { color: var(--color-accent-hover); }

/* Параграфы */
p { margin-bottom: var(--spacing-md); }

/* Списки */
ul, ol {
  margin-bottom: var(--spacing-md);
  padding-left: var(--spacing-lg);
}

/* Кнопки */
button {
  font-family: var(--font-family-base);
  cursor: pointer;
}
button:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

/* Поля ввода */
input, textarea, select {
  font-family: var(--font-family-base);
  font-size: var(--font-size-base);
}

/* Utility классы */
.container {
  width: 100%;
  max-width: var(--container-lg);
  margin: 0 auto;
  padding: 0 var(--spacing-md);
}

.container-sm { max-width: var(--container-sm); }
.container-xs { max-width: var(--container-xs); }

.auth-loading {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #FFFFFF;
}

.auth-loading--overlay {
  position: fixed;
  inset: 0;
  z-index: 100;
}

.auth-loading__spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

/* Flexbox */
.flex { display: flex; }
.flex-col { flex-direction: column; }
.items-center { align-items: center; }
.justify-center { justify-content: center; }
.justify-between { justify-content: space-between; }

/* Spacing */
.gap-sm { gap: var(--spacing-sm); }
.gap-md { gap: var(--spacing-md); }
.gap-lg { gap: var(--spacing-lg); }

/* Text */
.text-center { text-align: center; }
.text-sm { font-size: var(--font-size-sm); }
.text-lg { font-size: var(--font-size-lg); }
.text-muted { color: var(--color-text-muted); }
.text-error { color: var(--color-error); }
.text-success { color: var(--color-success); }

/* Анимации */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from {
    transform: translateY(10px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes slideDown {
  from {
    transform: translateY(-10px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-fadeIn { animation: fadeIn var(--transition-base); }
.animate-slideUp { animation: slideUp var(--transition-base); }
.animate-slideDown { animation: slideDown var(--transition-base); }
.animate-spin { animation: spin 1s linear infinite; }

/* Формы */
.form-group { margin-bottom: var(--spacing-lg); }

.form-label {
  display: block;
  margin-bottom: var(--spacing-sm);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  color: var(--color-text);
}

.form-error {
  display: block;
  margin-top: var(--spacing-xs);
  font-size: var(--font-size-sm);
  color: var(--color-error);
}

/* Card */
.card {
  background: var(--color-secondary);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  padding: var(--spacing-xl);
  box-shadow: var(--shadow-sm);
}

/* Auth layout */
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

.card-hover {
  transition: box-shadow var(--transition-base);
}
.card-hover:hover {
  box-shadow: var(--shadow-md);
}
</style>
