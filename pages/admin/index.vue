<template>
  <div class="login-page">
    <LoginForm />
  </div>
</template>

<script setup lang="ts">
import LoginForm from '~/components/admin/LoginForm.vue'
import { useAuth } from '~/composables/useAuth'
import { useRouter } from 'vue-router'
import { onMounted } from 'vue'

const { isAuthenticated, checkAuth } = useAuth()
const router = useRouter()

onMounted(() => {
  // Si déjà authentifié, rediriger vers le dashboard
  if (checkAuth()) {
    router.push('/admin/dashboard')
  }
})
</script>

<style scoped>
.login-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
}
</style>
