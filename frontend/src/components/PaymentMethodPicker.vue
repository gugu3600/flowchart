<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  methods: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="payment-section">
    <p class="payment-label">Select Payment Method</p>
    <div v-if="loading" class="payment-loading">Loading payment methods...</div>
    <div v-else class="payment-grid">
      <button
        v-for="pm in methods"
        :key="pm.id"
        type="button"
        class="payment-btn"
        :class="{ 'payment-active': modelValue === pm.id }"
        @click="$emit('update:modelValue', pm.id)"
      >
        <span class="payment-icon">{{ pm.icon }}</span>
        <span>{{ pm.label }}</span>
      </button>
    </div>
  </div>
</template>

