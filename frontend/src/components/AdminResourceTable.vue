<script setup>
import { ref } from 'vue'

const props = defineProps({
  items: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  columns: { type: Array, required: true },
  emptyText: { type: String, default: 'No items found.' },
})

function ownerBadge(ownerName, ownerEmail) {
  return { label: ownerName || ownerEmail || 'Unknown', color: '#3b82f6' }
}

function cellValue(item, key) {
  if (key === 'owner') return null
  const parts = key.split('.')
  let val = item
  for (const p of parts) {
    if (val == null) return ''
    val = val[p]
  }
  return val
}
</script>

<template>
  <div v-if="loading" class="page-loading">Loading...</div>
  <div v-else-if="items.length === 0" class="page-empty">{{ emptyText }}</div>
  <table v-else class="admin-table">
    <thead>
      <tr>
        <th v-for="col in columns" :key="col.key">{{ col.label }}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in items" :key="item.id">
        <td v-for="col in columns" :key="col.key" :class="col.class || ''">
          <template v-if="col.key === 'owner'">
            <span class="tier-badge" :style="{ background: ownerBadge(item.owner_name, item.owner_email).color }">
              {{ ownerBadge(item.owner_name, item.owner_email).label }}
            </span>
          </template>
          <template v-else-if="col.format === 'date'">
            {{ new Date(cellValue(item, col.key)).toLocaleDateString() }}
          </template>
          <template v-else-if="col.format === 'count'">
            {{ cellValue(item, col.key)?.length || 0 }} {{ col.suffix || '' }}
          </template>
          <template v-else>
            {{ cellValue(item, col.key) || col.fallback || '-' }}
          </template>
        </td>
      </tr>
    </tbody>
  </table>
</template>
