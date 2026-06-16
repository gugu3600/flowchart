<script setup>
import { reactive, ref, computed, watch } from 'vue'
import apiClient from '../api/apiClient.js'
import { AppButton, FloatingInput, TierSelector, PaymentMethodPicker } from '../components'

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  tier: 'free',
  payment_method: '',
})

const error = ref('')
const loading = ref(false)
const paymentMethods = ref([])
const paymentMethodsLoading = ref(false)

const tiers = [
  { id: 'free', label: 'Free', price: 0, color: '#64748b', desc: 'Basic canvas access' },
  { id: 'silver', label: 'Silver', price: 3000, color: '#94a3b8', desc: 'Unlimited saves + customization' },
  { id: 'gold', label: 'Gold', price: 6000, color: '#eab308', desc: 'Schema compiler + all Silver features' },
  { id: 'platinum', label: 'Platinum', price: 7500, color: '#a855f7', desc: 'Visual folder mapping + all features' },
]

const needsPayment = computed(() => form.tier !== 'free')

async function fetchPaymentMethods() {
  paymentMethodsLoading.value = true
  try {
    const res = await apiClient.get('/payment-methods')
    paymentMethods.value = res.data?.methods ?? []
  } catch {
    paymentMethods.value = []
  } finally {
    paymentMethodsLoading.value = false
  }
}

watch(() => form.tier, (tier) => {
  form.payment_method = ''
  if (tier !== 'free') {
    fetchPaymentMethods()
  }
})

async function handleRegister() {
  error.value = ''
  if (form.password !== form.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }
  loading.value = true
  try {
    const payload = {
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      tier: form.tier,
    }
    if (needsPayment.value) {
      payload.payment_method = form.payment_method
    }
    const res = await apiClient.post('/register', payload)
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
  <div class="login-page">
    <div class="register-card">
      <div class="login-header">
        <h1 class="login-title">Create Account</h1>
        <p class="login-subtitle">Choose your plan to get started</p>
      </div>

      <form @submit.prevent="handleRegister" class="login-form">
        <div v-if="error" class="error-msg">{{ error }}</div>

        <TierSelector v-model="form.tier" :tiers="tiers" />

        <div class="register-divider"></div>

        <FloatingInput v-model="form.name" id="reg-name" label="Name" />
        <FloatingInput v-model="form.email" id="reg-email" type="email" label="Email" autocomplete="email" />
        <FloatingInput v-model="form.password" id="reg-password" type="password" label="Password" autocomplete="new-password" show-password-toggle />
        <FloatingInput v-model="form.password_confirmation" id="reg-password-confirm" type="password" label="Confirm Password" autocomplete="new-password" show-password-toggle />

        <PaymentMethodPicker v-if="needsPayment" v-model="form.payment_method" :methods="paymentMethods" :loading="paymentMethodsLoading" />

        <AppButton type="submit" :label="needsPayment ? 'Register & Pay' : 'Create Free Account'" :loading="loading" class="login-btn" />

        <p class="register-login-link">
          Already have an account?
          <router-link to="/login">Login</router-link>
        </p>
      </form>
    </div>
  </div>
</template>
