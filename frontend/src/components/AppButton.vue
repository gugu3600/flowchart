<script setup>
import Button from 'primevue/button'

defineProps({
  label: { type: String, default: '' },
  variant: { type: String, default: 'primary' },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  icon: { type: String, default: '' },
  type: { type: String, default: 'button' },
})
</script>

<template>
  <Button
    :label="label"
    :loading="loading"
    :disabled="disabled || loading"
    :icon="icon"
    :type="type"
    :class="[
      variant === 'secondary' ? 'btn-secondary' : 'btn-primary',
      'w-full sm:w-auto',
    ]"
  />
</template>
