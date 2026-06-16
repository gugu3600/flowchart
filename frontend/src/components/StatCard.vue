<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], default: '' },
  icon: { type: String, default: '' },
  color: { type: String, default: '#3b82f6' },
})
</script>

<template>
  <div class="stat-card" :style="{ '--stat-color': color }">
    <div v-if="icon" class="stat-icon" v-html="icon" />
    <div class="stat-body">
      <span class="stat-value">{{ value }}</span>
      <span class="stat-label">{{ label }}</span>
    </div>
  </div>
</template>
