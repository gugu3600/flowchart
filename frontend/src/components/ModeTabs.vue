<script setup>
defineProps({
  mode: { type: String, required: true },
})

defineEmits(['update:mode'])
</script>

<template>
  <div class="mode-tabs">
    <button
      v-for="tab in [{ key: 'flow', label: 'Flow' }, { key: 'schema', label: 'Schema' }]"
      :key="tab.key"
      :class="['mode-tab', { active: mode === tab.key }]"
      @click="$emit('update:mode', tab.key)"
    >
      {{ tab.label }}
    </button>
  </div>
</template>
