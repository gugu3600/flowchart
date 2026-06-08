<script setup>
import { reactive, ref } from 'vue'
import apiClient from '../api/apiClient.js'
import { AppButton, AppInput, AppCard } from '../components'

const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const error = ref('')
const loading = ref(false)

async function handleRegister() {
  error.value = ''
  if (form.password !== form.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }
  loading.value = true
  try {
    const res = await apiClient.post('/register', {
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })
    if (res.success) {
      window.location.href = '/canvas'
    }
  } catch (err) {
    error.value = err.message || 'Registration failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-surface-50 dark:bg-surface-950">
    <AppCard title="Create Account" class="w-full max-w-md">
      <form @submit.prevent="handleRegister" class="flex flex-col gap-4">
        <div v-if="error" class="error-msg">{{ error }}</div>

        <AppInput
          id="name"
          v-model="form.name"
          label="Name"
          type="text"
          placeholder="Your name"
        />
        <AppInput
          id="email"
          v-model="form.email"
          label="Email"
          type="email"
          placeholder="you@example.com"
        />
        <AppInput
          id="password"
          v-model="form.password"
          label="Password"
          type="password"
          placeholder="Min 8 characters"
        />
        <AppInput
          id="password_confirmation"
          v-model="form.password_confirmation"
          label="Confirm Password"
          type="password"
          placeholder="Repeat password"
        />

        <AppButton
          type="submit"
          label="Register"
          :loading="loading"
          class="mt-2"
        />
        <p class="text-center text-sm text-surface-500 dark:text-surface-400">
          Already have an account?
          <a href="/login" class="text-primary-500 hover:underline">Login</a>
        </p>
      </form>
    </AppCard>
  </div>
</template>
