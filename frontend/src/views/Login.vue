<script setup>
import { reactive, ref } from 'vue'
import apiClient from '../api/apiClient.js'
import { AppButton, FloatingInput } from '../components'

const form = reactive({ email: '', password: '' })
const error = ref('')
const loading = ref(false)
const remember = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true

  try {
    const res = await apiClient.post('/login', { ...form, remember: remember.value })
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

        <FloatingInput v-model="form.email" id="email" type="email" label="Email" autocomplete="email" />

        <FloatingInput v-model="form.password" id="password" type="password" label="Password" autocomplete="current-password" show-password-toggle />

        <label class="remember-row">
          <input type="checkbox" v-model="remember" class="remember-checkbox" />
          <span class="remember-label">Remember me</span>
        </label>

        <AppButton
          type="submit"
          label="Sign In"
          :loading="loading"
          class="login-btn"
        />

        <p class="register-login-link">
          Don't have an account?
          <router-link to="/register">Register</router-link>
        </p>
      </form>
    </div>
  </div>
</template>
