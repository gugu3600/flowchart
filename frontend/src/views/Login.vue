<script setup>
import { reactive, ref } from 'vue'
import apiClient from '../api/apiClient.js'
import { AppButton, AppInput, AppCard } from '../components'

const form = reactive({ email: '', password: '' })
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true

  try {
    const res = await apiClient.post('/login', form)
    if (res.success) {
      window.location.href = '/dashboard'
    }
  } catch (err) {
    error.value = err.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-surface-50 dark:bg-surface-950">
    <AppCard title="Flowchart Login" class="w-full max-w-md">
      <form @submit.prevent="handleLogin" class="flex flex-col gap-4">
        <div v-if="error" class="error-msg">{{ error }}</div>

        <AppInput
          id="email"
          v-model="form.email"
          label="Email"
          type="email"
          placeholder="admin@flowchart.dev"
        />
        <AppInput
          id="password"
          v-model="form.password"
          label="Password"
          type="password"
          placeholder="••••••••"
        />

        <AppButton
          type="submit"
          label="Login"
          :loading="loading"
          class="mt-2"
        />
      </form>
    </AppCard>
  </div>
</template>
