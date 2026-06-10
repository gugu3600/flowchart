<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '../api/apiClient.js'
import { useUserStore } from '../stores/useUserStore.js'

const router = useRouter()
const store = useUserStore()

const queryTier = router.currentRoute.value.query.tier
const validTiers = ['silver', 'gold', 'platinum']
const selectedTier = ref(validTiers.includes(queryTier) ? queryTier : 'silver')
const selectedMethod = ref('')
const paymentMethods = ref([])
const loading = ref(false)
const paymentLoading = ref(false)
const error = ref('')
const successMsg = ref('')

const tiers = [
  { id: 'silver', label: 'Silver', price: 3000, color: '#94a3b8', desc: 'Unlimited saves + color customization' },
  { id: 'gold', label: 'Gold', price: 6000, color: '#eab308', desc: 'Schema compiler + all Silver features' },
  { id: 'platinum', label: 'Platinum', price: 7500, color: '#a855f7', desc: 'Visual folder mapping + all features' },
]

const currentTier = computed(() => {
  const roles = store.state.user?.roles ?? []
  const tierRole = roles.find(r => ['free', 'silver', 'gold', 'platinum'].includes(r.name))
  return tierRole?.name ?? 'free'
})

const needsPayment = computed(() => selectedTier.value !== 'free')

async function fetchPaymentMethods() {
  paymentLoading.value = true
  try {
    const res = await apiClient.get('/payment-methods')
    paymentMethods.value = res.data?.methods ?? []
  } catch {
    paymentMethods.value = []
  } finally {
    paymentLoading.value = false
  }
}

watch(selectedTier, () => {
  selectedMethod.value = ''
  if (needsPayment.value) {
    fetchPaymentMethods()
  }
}, { immediate: true })

async function handleSubscribe() {
  error.value = ''
  successMsg.value = ''
  if (!selectedMethod.value) {
    error.value = 'Please select a payment method'
    return
  }
  loading.value = true
  try {
    const res = await apiClient.post('/subscribe', {
      tier: selectedTier.value,
      payment_method: selectedMethod.value,
    })
    if (res.success) {
      successMsg.value = `Subscribed to ${selectedTier.value} tier successfully!`
      await store.fetchUser()
      setTimeout(() => router.push('/canvas'), 1500)
    }
  } catch (err) {
    error.value = err.message || 'Subscription failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <div class="register-card subscribe-card">
      <div class="login-header">
        <h1 class="login-title">Subscribe</h1>
        <p class="login-subtitle">
          Current plan: <strong :style="{ color: '#eab308' }">{{ currentTier }}</strong>
        </p>
      </div>

      <div v-if="error" class="error-msg">{{ error }}</div>
      <div v-if="successMsg" class="success-msg">{{ successMsg }}</div>

      <div class="tier-grid">
        <button
          v-for="tier in tiers"
          :key="tier.id"
          type="button"
          class="tier-card"
          :class="{
            'tier-active': selectedTier === tier.id,
            'tier-card-disabled': tier.id === currentTier,
          }"
          :style="selectedTier === tier.id ? { borderColor: tier.color } : {}"
          :disabled="tier.id === currentTier"
          @click="selectedTier = tier.id"
        >
          <span class="tier-badge" :style="{ background: tier.color }">{{ tier.label }}</span>
          <span class="tier-price">{{ tier.price.toLocaleString() }} MMK</span>
          <span class="tier-desc">{{ tier.desc }}</span>
          <span v-if="tier.id === currentTier" class="tier-current-label">Current</span>
        </button>
      </div>

      <div v-if="needsPayment" class="payment-section">
        <p class="payment-label">Select Payment Method</p>
        <div v-if="paymentLoading" class="payment-loading">Loading payment methods...</div>
        <div v-else class="payment-grid">
          <button
            v-for="pm in paymentMethods"
            :key="pm.id"
            type="button"
            class="payment-btn"
            :class="{ 'payment-active': selectedMethod === pm.id }"
            @click="selectedMethod = pm.id"
          >
            <span class="payment-icon">{{ pm.icon }}</span>
            <span>{{ pm.label }}</span>
          </button>
        </div>
      </div>

      <div class="subscribe-actions">
        <button
          class="btn-primary subscribe-btn"
          :disabled="loading || !selectedMethod || selectedTier === currentTier"
          @click="handleSubscribe"
        >
          {{ loading ? 'Processing...' : 'Subscribe' }}
        </button>
        <button class="btn-secondary" @click="router.push('/canvas')">Cancel</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.subscribe-card {
  max-width: 520px;
}

.tier-card-disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.tier-current-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: #eab308;
  color: #000;
  padding: 2px 8px;
  border-radius: 4px;
  margin-top: 6px;
}

.payment-section {
  margin-top: 16px;
}

.subscribe-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 24px;
}

.subscribe-btn {
  min-width: 160px;
}

.success-msg {
  color: #22c55e;
  background: rgba(34, 197, 94, 0.1);
  padding: 10px 14px;
  border-radius: 6px;
  margin-bottom: 12px;
  font-size: 0.9rem;
}
</style>
