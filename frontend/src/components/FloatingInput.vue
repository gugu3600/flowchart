<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  type: { type: String, default: 'text' },
  id: { type: String, default: '' },
  label: { type: String, default: '' },
  autocomplete: { type: String, default: 'off' },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="floating-field">
    <input
      :id="id"
      :value="modelValue"
      :type="type"
      :autocomplete="autocomplete"
      placeholder=" "
      class="floating-input"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <label :for="id" class="floating-label">{{ label }}</label>
    <span class="floating-border"></span>
  </div>
</template>

