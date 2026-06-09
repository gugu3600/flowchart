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

const badgeClass = (col) => {
  if (col.pk) return 'node-badge-pk'
  if (col.fk) return 'node-badge-fk'
  if (col.unique) return 'node-badge-uq'
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
    <div class="node-table-list">
      <div
        v-for="(col, i) in data.columns"
        :key="i"
        class="node-table-row"
      >
        <span class="node-table-col-name">{{ col.name }}</span>
        <div class="node-table-col-meta">
          <span class="node-table-col-type">{{ col.type }}</span>
          <span
            v-if="badgeLabel(col)"
            :class="['node-badge', badgeClass(col)]"
          >
            {{ badgeLabel(col) }}
          </span>
        </div>
      </div>
    </div>
  </BaseNode>
</template>
