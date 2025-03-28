<template>
  <v-card class="mx-auto my-12" max-width="500">
    <v-card-title class="text-center text-h4 py-4">
      Administration - Roue de la Fortune
    </v-card-title>
    <v-card-text>
      <v-alert v-if="loginError" type="error" variant="tonal" closable>
        Identifiants incorrects. Veuillez réessayer.
      </v-alert>

      <v-form ref="form" v-model="valid" @submit.prevent="handleLogin">
        <v-text-field
          v-model="username"
          label="Nom d'utilisateur"
          prepend-icon="mdi-account"
          :rules="[rules.required]"
          required
        ></v-text-field>

        <v-text-field
          v-model="password"
          label="Mot de passe"
          prepend-icon="mdi-lock"
          :append-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
          :type="showPassword ? 'text' : 'password'"
          :rules="[rules.required]"
          required
          @click:append="showPassword = !showPassword"
        ></v-text-field>

        <v-btn block color="primary" class="mt-4" size="large" type="submit" :loading="loading">
          Connexion
        </v-btn>
      </v-form>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '~/composables/useAuth'

const router = useRouter()
const { login } = useAuth()

const username = ref('')
const password = ref('')
const showPassword = ref(false)
const valid = ref(false)
const loginError = ref(false)
const loading = ref(false)

const rules = {
  required: (v: string) => !!v || 'Ce champ est requis'
}

const handleLogin = async () => {
  loginError.value = false
  loading.value = true

  try {
    const success = await login(username.value, password.value)
    
    if (success) {
      router.push('/admin/dashboard')
    } else {
      loginError.value = true
    }
  } catch (error) {
    console.error(error)
    loginError.value = true
  } finally {
    loading.value = false
  }
}
</script>

<style lang="scss" scoped>
.v-card {
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>
