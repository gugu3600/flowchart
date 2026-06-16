<script setup>
import { Position, Handle } from '@vue-flow/core'
import { NodeResizer } from '@vue-flow/node-resizer'
import '@vue-flow/node-resizer/dist/style.css'

const props = defineProps({
  id: { type: String, required: true },
  data: { type: Object, default: () => ({}) },
  selected: { type: Boolean, default: false },
  label: { type: String, default: '' },
  icon: { type: String, default: '' },
  color: { type: String, default: 'blue' },
  showSource: { type: Boolean, default: true },
  showTarget: { type: Boolean, default: true },
  style: { type: Object, default: () => ({}) },
})
</script>

<template>
  <div
    :class="[
      'node-container',
      `node-${color}`,
      selected ? 'selected' : '',
    ]"
    :style="style"
  >
    <NodeResizer
      :is-visible="selected"
      min-width="160"
      min-height="60"
      handle-class-name="node-resize-handle"
    />
    <Handle
      v-if="showTarget"
      type="target"
      :position="Position.Top"
      class="node-handle"
    />

    <div
      v-if="label"
      class="node-head"
    >
      <i v-if="icon" :class="['pi', icon]" />
      <span>{{ label }}</span>
    </div>

    <div class="node-body">
      <slot />
    </div>

    <Handle
      v-if="showSource"
      type="source"
      :position="Position.Bottom"
      class="node-handle"
    />
  </div>
</template>
