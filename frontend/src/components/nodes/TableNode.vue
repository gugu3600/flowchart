<script setup>
import BaseNode from './BaseNode.vue'

const props = defineProps({
  id: { type: String, required: true },
  data: {
    type: Object,
    default: () => ({
      label: 'users',
      columns: [
        { name: 'id', type: 'BIGINT PK', pk: true },
        { name: 'email', type: 'VARCHAR(255)', unique: true },
        { name: 'password', type: 'VARCHAR(255)' },
      ],
    }),
  },
  selected: { type: Boolean, default: false },
})

const columnBadge = (col) => {
  if (col.pk) return 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'
  if (col.fk) return 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300'
  if (col.unique) return 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'
  return ''
}

const badgeLabel = (col) => {
  if (col.pk) return 'PK'
  if (col.fk) return 'FK'
  if (col.unique) return 'UQ'
  return ''
}
</script>

<template>
  <BaseNode
    :id="id"
    :selected="selected"
    :label="data.label"
    icon="pi-database"
    color="blue"
  >
    <div class="flex flex-col gap-0.5">
      <div
        v-for="(col, i) in data.columns"
        :key="i"
        class="flex items-center justify-between gap-2 rounded px-1 py-0.5 hover:bg-black/5 dark:hover:bg-white/5"
      >
        <span class="font-mono font-medium">{{ col.name }}</span>
        <div class="flex items-center gap-1.5">
          <span class="font-mono text-[10px] text-slate-500 dark:text-slate-400">{{ col.type }}</span>
          <span
            v-if="badgeLabel(col)"
            :class="['rounded px-1 py-0.5 text-[10px] font-semibold leading-none', columnBadge(col)]"
          >
            {{ badgeLabel(col) }}
          </span>
        </div>
      </div>
    </div>
  </BaseNode>
</template>
