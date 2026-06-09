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

<style scoped>
.floating-field {
  position: relative;
}

.floating-input {
  width: 100%;
  padding: 0.75rem 0;
  font-size: 1rem;
  color: #f1f5f9;
  background: transparent;
  border: none;
  outline: none;
  box-sizing: border-box;
}

.floating-input:-webkit-autofill,
.floating-input:-webkit-autofill:hover,
.floating-input:-webkit-autofill:focus {
  -webkit-text-fill-color: #f1f5f9;
  -webkit-box-shadow: 0 0 0px 1000px #1e293b inset;
  transition: background-color 5000s ease-in-out 0s;
}

.floating-label {
  position: absolute;
  left: 0;
  top: 0.75rem;
  font-size: 1rem;
  color: #64748b;
  pointer-events: none;
  transition: 0.2s ease all;
}

.floating-input:focus ~ .floating-label,
.floating-input:not(:placeholder-shown) ~ .floating-label {
  top: -0.5rem;
  font-size: 0.75rem;
  color: #3b82f6;
}

.floating-border {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: #334155;
  transition: 0.2s ease all;
}

.floating-input:focus ~ .floating-border {
  background: #3b82f6;
}
</style>
