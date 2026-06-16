<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  type: { type: String, default: 'text' },
  id: { type: String, default: '' },
  label: { type: String, default: '' },
  autocomplete: { type: String, default: 'off' },
  showPasswordToggle: { type: Boolean, default: false },
  hasError: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const visible = ref(false)
const inputType = computed(() => {
  if (props.type === 'password' && props.showPasswordToggle) {
    return visible.value ? 'text' : 'password'
  }
  return props.type
})
</script>

<template>
  <div class="floating-field" :class="{ 'floating-error': hasError }">
    <input
      :id="id"
      :value="modelValue"
      :type="inputType"
      :autocomplete="autocomplete"
      placeholder=" "
      class="floating-input"
      @input="emit('update:modelValue', $event.target.value)"
    />
    <label :for="id" class="floating-label">{{ label }}</label>
    <span class="floating-border"></span>
    <button
      v-if="type === 'password' && showPasswordToggle"
      type="button"
      class="password-toggle"
      :title="visible ? 'Hide password' : 'Show password'"
      @click="visible = !visible"
    >
      <svg v-if="!visible" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
        <circle cx="12" cy="12" r="3" />
      </svg>
      <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
        <line x1="1" y1="1" x2="23" y2="23" />
      </svg>
    </button>
  </div>
</template>
