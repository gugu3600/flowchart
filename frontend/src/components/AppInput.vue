<script setup>
import InputText from 'primevue/inputtext'

defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  type: { type: String, default: 'text' },
  label: { type: String, default: '' },
  id: { type: String, default: '' },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="flex flex-col gap-1">
    <label
      v-if="label"
      :for="id"
      class="text-sm font-medium text-surface-700 dark:text-surface-300"
    >
      {{ label }}
    </label>
    <InputText
      :id="id"
      :value="modelValue"
      :placeholder="placeholder"
      :type="type"
      class="form-input"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>
