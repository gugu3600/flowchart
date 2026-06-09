<script setup>
import Menubar from 'primevue/menubar'

defineProps({
  items: {
    type: Array,
    default: () => [],
  },
  logo: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <nav class="border-b border-surface-200 bg-white dark:border-surface-700 dark:bg-surface-900">
    <div class="mx-auto flex max-w-7xl items-center px-4 py-2">
      <div v-if="logo" class="mr-4 flex items-center gap-2">
        <span class="text-lg font-bold text-surface-900 dark:text-surface-100">Flowchart</span>
      </div>
      <div class="flex-1">
        <Menubar :model="items" />
      </div>
    </div>
  </nav>
</template>
