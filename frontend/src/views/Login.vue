<script setup>
import { reactive, ref } from 'vue'
import apiClient from '../api/apiClient.js'
import { AppButton } from '../components'

const form = reactive({ email: '', password: '' })
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true

  try {
    const res = await apiClient.post('/login', form)
    if (res.success) {
      window.location.href = '/canvas'
    }
  } catch (err) {
    error.value = err.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <h1 class="login-title">Welcome Back</h1>
        <p class="login-subtitle">Sign in to your account</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div v-if="error" class="error-msg">{{ error }}</div>

        <div class="login-field">
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder=" "
            class="login-input"
          />
          <label for="email" class="login-label">Email</label>
          <span class="login-border"></span>
        </div>

        <div class="login-field">
          <input
            id="password"
            v-model="form.password"
            type="password"
            placeholder=" "
            class="login-input"
          />
          <label for="password" class="login-label">Password</label>
          <span class="login-border"></span>
        </div>

        <AppButton
          type="submit"
          label="Sign In"
          :loading="loading"
          class="login-btn"
        />
      </form>
    </div>
  </div>
</template>
