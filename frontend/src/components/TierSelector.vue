<script setup>
defineProps({
  modelValue: { type: String, default: 'free' },
  tiers: { type: Array, default: () => [] },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="tier-grid">
    <button
      v-for="tier in tiers"
      :key="tier.id"
      type="button"
      class="tier-card"
      :class="{ 'tier-active': modelValue === tier.id }"
      :style="modelValue === tier.id ? { borderColor: tier.color } : {}"
      @click="$emit('update:modelValue', tier.id)"
    >
      <span class="tier-badge" :style="{ background: tier.color }">{{ tier.label }}</span>
      <span class="tier-price">
        {{ tier.price === 0 ? 'Free' : tier.price.toLocaleString() + ' MMK' }}
      </span>
      <span class="tier-desc">{{ tier.desc }}</span>
    </button>
  </div>
</template>

<style scoped>
.tier-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
}

.tier-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.35rem;
  padding: 0.75rem 0.5rem;
  background: #0f172a;
  border: 2px solid #334155;
  border-radius: 10px;
  cursor: pointer;
  transition: border-color 0.2s, transform 0.15s;
  text-align: center;
}

.tier-card:hover {
  border-color: #475569;
  transform: translateY(-1px);
}

.tier-active {
  border-color: #3b82f6;
  background: #1a2332;
}

.tier-badge {
  font-size: 0.65rem;
  font-weight: 600;
  color: #fff;
  padding: 0.15rem 0.45rem;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.tier-price {
  font-size: 0.95rem;
  font-weight: 700;
  color: #f1f5f9;
}

.tier-desc {
  font-size: 0.7rem;
  color: #64748b;
  line-height: 1.3;
}
</style>
