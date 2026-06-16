<script setup>
defineProps({
  title: { type: String, default: '' },
  headerClass: { type: String, default: '' },
})
</script>

<template>
  <header class="page-header" :class="headerClass">
    <div class="page-header-left">
      <h1 v-if="title" class="page-title">{{ title }}</h1>
      <slot name="left" />
    </div>
    <div class="page-header-right">
      <slot name="right" />
    </div>
  </header>
</template>
