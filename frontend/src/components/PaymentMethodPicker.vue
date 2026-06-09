<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  methods: { type: Array, default: () => [] },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="payment-section">
    <p class="payment-label">Select Payment Method</p>
    <div class="payment-grid">
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

<style scoped>
.payment-section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.payment-label {
  margin: 0;
  font-size: 0.85rem;
  font-weight: 500;
  color: #94a3b8;
}

.payment-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
}

.payment-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  padding: 0.6rem;
  background: #0f172a;
  border: 2px solid #334155;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.85rem;
  color: #e2e8f0;
  transition: border-color 0.2s, background 0.15s;
}

.payment-btn:hover {
  border-color: #475569;
}

.payment-active {
  border-color: #3b82f6;
  background: #1a2332;
}

.payment-icon {
  font-size: 1rem;
  line-height: 1;
}
</style>
