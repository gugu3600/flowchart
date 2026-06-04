<script setup>
import { Position, Handle } from '@vue-flow/core'

const props = defineProps({
  id: { type: String, required: true },
  data: { type: Object, default: () => ({}) },
  selected: { type: Boolean, default: false },
  label: { type: String, default: '' },
  icon: { type: String, default: '' },
  color: { type: String, default: 'blue' },
  showSource: { type: Boolean, default: true },
  showTarget: { type: Boolean, default: true },
})

const colorMap = {
  blue: 'border-blue-500 bg-blue-50 dark:bg-blue-950/30',
  green: 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/30',
  purple: 'border-purple-500 bg-purple-50 dark:bg-purple-950/30',
  amber: 'border-amber-500 bg-amber-50 dark:bg-amber-950/30',
  slate: 'border-slate-500 bg-slate-50 dark:bg-slate-950/30',
}

const headMap = {
  blue: 'bg-blue-600 text-white',
  green: 'bg-emerald-600 text-white',
  purple: 'bg-purple-600 text-white',
  amber: 'bg-amber-600 text-white',
  slate: 'bg-slate-600 text-white',
}
</script>

<template>
  <div
    :class="[
      'min-w-[180px] rounded-lg border-2 shadow-md transition-shadow',
      colorMap[color] || colorMap.blue,
      selected ? 'ring-2 ring-blue-400 shadow-lg' : '',
    ]"
  >
    <Handle
      v-if="showTarget"
      type="target"
      :position="Position.Top"
      class="!h-3 !w-3 !border-2 !border-white !bg-blue-500"
    />

    <div
      v-if="label"
      :class="[
        'flex items-center gap-2 rounded-t-lg px-3 py-2 text-sm font-semibold',
        headMap[color] || headMap.blue,
      ]"
    >
      <i v-if="icon" :class="['pi', icon]" />
      <span>{{ label }}</span>
    </div>

    <div class="px-3 py-2 text-xs text-slate-700 dark:text-slate-300">
      <slot />
    </div>

    <Handle
      v-if="showSource"
      type="source"
      :position="Position.Bottom"
      class="!h-3 !w-3 !border-2 !border-white !bg-blue-500"
    />
  </div>
</template>
