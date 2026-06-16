<script setup>
const props = defineProps({
  label: { type: String, required: true },
  colors: { type: Array, required: true },
  selectedColor: { type: String, default: '' },
  clearTitle: { type: String, default: 'Clear' },
  clearValue: { type: String, default: '' },
  isActive: { type: Function, default: (c, selected) => selected === c },
})

defineEmits(['select'])
</script>

<template>
  <span class="color-label">{{ label }}</span>
  <button
    v-for="c in colors"
    :key="c"
    class="color-swatch"
    :class="{ 'color-swatch-active': isActive(c, selectedColor) }"
    :style="{ background: c }"
    @click="$emit('select', c)"
  ></button>
  <button class="color-clear" :title="clearTitle" @click="$emit('select', clearValue)">✕</button>
</template>
