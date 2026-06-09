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
      <div v-if="data.typeField" class="node-logic-row">
        <span class="node-logic-label">Type:</span>
        <span class="node-tag node-tag-slate">{{ data.typeField }}</span>
      </div>
      <div class="node-logic-row">
        <i class="pi pi-arrow-right" />
        <span class="node-logic-label">Inputs:</span>
        <div class="node-logic-taglist">
          <span
            v-for="(inp, i) in data.inputs"
            :key="i"
            class="node-tag node-tag-purple"
          >
            <span v-if="inp.name">{{ inp.name }}<span class="preview-type">:{{ inp.type || 'any' }}</span></span>
            <span v-else>{{ inp }}</span>
          </span>
        </div>
      </div>
      <div class="node-logic-row">
        <i class="pi pi-arrow-left" />
        <span class="node-logic-label">Output:</span>
        <span class="node-tag node-tag-emerald">
          {{ data.output || 'void' }}
        </span>
      </div>
    </div>
  </BaseNode>
</template>
