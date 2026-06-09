<script setup>
import { reactive, ref, computed } from 'vue'
import apiClient from '../api/apiClient.js'
import { AppButton } from '../components'

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

const tiers = [
  { id: 'free', label: 'Free', price: 0, color: '#64748b', desc: 'Basic canvas access' },
  { id: 'silver', label: 'Silver', price: 3000, color: '#94a3b8', desc: 'Unlimited saves + customization' },
  { id: 'gold', label: 'Gold', price: 6000, color: '#eab308', desc: 'Schema compiler + all Silver features' },
  { id: 'platinum', label: 'Platinum', price: 7500, color: '#a855f7', desc: 'Visual folder mapping + all features' },
]

const paymentMethods = [
  { id: 'kbzpay', label: 'KBZ Pay', icon: '💳' },
  { id: 'ayapay', label: 'AYA Pay', icon: '💳' },
  { id: 'cbpay', label: 'CB Pay', icon: '💳' },
  { id: 'mmqr', label: 'MMQR', icon: '📱' },
]

const needsPayment = computed(() => form.tier !== 'free')

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

        <div class="register-tiers">
          <button
            v-for="tier in tiers"
            :key="tier.id"
            type="button"
            class="register-tier-card"
            :class="{ 'register-tier-active': form.tier === tier.id }"
            :style="form.tier === tier.id ? { borderColor: tier.color } : {}"
            @click="form.tier = tier.id"
          >
            <span class="register-tier-badge" :style="{ background: tier.color }">{{ tier.label }}</span>
            <span class="register-tier-price">
              {{ tier.price === 0 ? 'Free' : tier.price.toLocaleString() + ' MMK' }}
            </span>
            <span class="register-tier-desc">{{ tier.desc }}</span>
          </button>
        </div>

        <div class="register-divider"></div>

        <div class="login-field">
          <input id="reg-name" v-model="form.name" type="text" placeholder=" " class="login-input" />
          <label for="reg-name" class="login-label">Name</label>
          <span class="login-border"></span>
        </div>

        <div class="login-field">
          <input id="reg-email" v-model="form.email" type="email" placeholder=" " class="login-input" />
          <label for="reg-email" class="login-label">Email</label>
          <span class="login-border"></span>
        </div>

        <div class="login-field">
          <input id="reg-password" v-model="form.password" type="password" placeholder=" " class="login-input" />
          <label for="reg-password" class="login-label">Password</label>
          <span class="login-border"></span>
        </div>

        <div class="login-field">
          <input id="reg-password-confirm" v-model="form.password_confirmation" type="password" placeholder=" " class="login-input" />
          <label for="reg-password-confirm" class="login-label">Confirm Password</label>
          <span class="login-border"></span>
        </div>

        <div v-if="needsPayment" class="register-payment-section">
          <p class="register-payment-label">Select Payment Method</p>
          <div class="register-payment-grid">
            <button
              v-for="pm in paymentMethods"
              :key="pm.id"
              type="button"
              class="register-payment-btn"
              :class="{ 'register-payment-active': form.payment_method === pm.id }"
              @click="form.payment_method = pm.id"
            >
              <span>{{ pm.icon }}</span>
              <span>{{ pm.label }}</span>
            </button>
          </div>
        </div>

        <AppButton type="submit" :label="needsPayment ? 'Register & Pay' : 'Create Free Account'" :loading="loading" class="login-btn" />

        <p class="register-login-link">
          Already have an account?
          <a href="/login">Login</a>
        </p>
      </form>
    </div>
  </div>
</template>
