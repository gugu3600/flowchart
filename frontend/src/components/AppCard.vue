<script setup>
defineProps({
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
})
</script>

<template>
  <div class="card">
    <div v-if="title || subtitle" class="mb-4">
      <h2 v-if="title" class="m-0 text-lg font-semibold text-surface-900 dark:text-surface-100">
        {{ title }}
      </h2>
      <p v-if="subtitle" class="mt-1 text-sm text-surface-500 dark:text-surface-400">
        {{ subtitle }}
      </p>
    </div>
    <div class="text-surface-700 dark:text-surface-300">
      <slot />
    </div>
  </div>
</template>
