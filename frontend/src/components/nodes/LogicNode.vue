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
    <p v-if="data.description" class="node-logic-desc">
      {{ data.description }}
    </p>
    <div class="node-logic-body">
      <div class="node-logic-row">
        <i class="pi pi-arrow-right" />
        <span class="node-logic-label">Inputs:</span>
        <div class="node-logic-taglist">
          <span
            v-for="(inp, i) in data.inputs"
            :key="i"
            class="node-tag node-tag-purple"
          >
            {{ inp }}
          </span>
        </div>
      </div>
      <div class="node-logic-row">
        <i class="pi pi-arrow-left" />
        <span class="node-logic-label">Output:</span>
        <span class="node-tag node-tag-emerald">
          {{ data.output }}
        </span>
      </div>
    </div>
  </BaseNode>
</template>
