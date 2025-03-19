<template>
  <NuxtLayout name="default">
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup>
import { useTranslation } from '~/composables/useTranslation';

const { t } = useTranslation();
</script>

<style>
:root {
  --primary-color: #87664Bff; /* Raw Umber */
  --secondary-color: #BD2B23ff; /* Fire Brick */
  --accent-color: #BD2B23ff; /* Fire Brick */
  --light-color: #FCFEFFff; /* White */
  --text-color: #87664Bff; /* Raw Umber */
  --success-color: #87664Bff; /* Raw Umber */
  --warning-color: #87664Bff; /* Raw Umber */
  --danger-color: #BD2B23ff; /* Fire Brick */
  --white-color: #FBFDFEff; /* White 2 */
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'EB Garamond', serif, -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
  color: var(--text-color);
  line-height: 1.6;
  background-color: var(--light-color);
}

.app-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.container {
  width: 100%;
  max-width: 1200px;
  padding: 0 1rem;
  margin: 0 auto;
}

.btn {
  display: inline-block;
  font-weight: 500;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  cursor: pointer;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  line-height: 1.5;
  border-radius: 0.5rem;
  transition: all 0.15s ease-in-out;
  text-decoration: none;
}

.btn-primary {
  color: white;
  background-color: var(--primary-color);
  border: 1px solid var(--primary-color);
}

.btn-primary:hover {
  background-color: #0a2540;
}

.btn-secondary {
  color: white;
  background-color: var(--secondary-color);
  border: 1px solid var(--secondary-color);
}

.btn-secondary:hover {
  background-color: #3b6a87;
}

.btn-accent {
  color: white;
  background-color: var(--accent-color);
  border: 1px solid var(--accent-color);
}

.btn-accent:hover {
  background-color: #d62b3a;
}

.alert {
  position: relative;
  padding: 1.5rem;
  margin-bottom: 1rem;
  border: 1px solid transparent;
  border-radius: 0.5rem;
}

.alert-warning {
  color: #856404;
  background-color: #fff3cd;
  border-color: #ffeeba;
}

.alert-success {
  color: #155724;
  background-color: #d4edda;
  border-color: #c3e6cb;
}

.alert-danger {
  color: #721c24;
  background-color: #f8d7da;
  border-color: #f5c6cb;
}

.card {
  position: relative;
  display: flex;
  flex-direction: column;
  min-width: 0;
  word-wrap: break-word;
  background-color: #fff;
  background-clip: border-box;
  border: 1px solid rgba(0, 0, 0, 0.125);
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-body {
  flex: 1 1 auto;
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-control {
  display: block;
  width: 100%;
  padding: 0.75rem;
  font-size: 1rem;
  line-height: 1.5;
  color: #495057;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-check {
  position: relative;
  display: block;
  padding-left: 1.25rem;
}

.form-check-input {
  position: absolute;
  margin-top: 0.3rem;
  margin-left: -1.25rem;
}

.text-center {
  text-align: center;
}

.mt-3 {
  margin-top: 1rem;
}

.mt-4 {
  margin-top: 1.5rem;
}

.mb-3 {
  margin-bottom: 1rem;
}

.mb-4 {
  margin-bottom: 1.5rem;
}
</style>
