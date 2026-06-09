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

