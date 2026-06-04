<script setup>
import BaseNode from './BaseNode.vue'

const props = defineProps({
  id: { type: String, required: true },
  data: {
    type: Object,
    default: () => ({
      label: 'validateInput',
      description: 'Validates user input and returns boolean',
      inputs: ['payload', 'schema'],
      output: 'boolean',
    }),
  },
  selected: { type: Boolean, default: false },
})
</script>

<template>
  <BaseNode
    :id="id"
    :selected="selected"
    :label="data.label"
    icon="pi-code"
    color="purple"
  >
    <p v-if="data.description" class="mb-2 text-[11px] italic text-slate-500 dark:text-slate-400">
      {{ data.description }}
    </p>
    <div class="flex flex-col gap-1">
      <div class="flex items-center gap-1.5">
        <i class="pi pi-arrow-right text-[10px] text-purple-500" />
        <span class="text-[11px] font-medium">Inputs:</span>
        <div class="flex flex-wrap gap-1">
          <span
            v-for="(inp, i) in data.inputs"
            :key="i"
            class="rounded bg-purple-100 px-1.5 py-0.5 text-[10px] font-mono text-purple-700 dark:bg-purple-900/30 dark:text-purple-300"
          >
            {{ inp }}
          </span>
        </div>
      </div>
      <div class="flex items-center gap-1.5">
        <i class="pi pi-arrow-left text-[10px] text-emerald-500" />
        <span class="text-[11px] font-medium">Output:</span>
        <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-mono text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
          {{ data.output }}
        </span>
      </div>
    </div>
  </BaseNode>
</template>
