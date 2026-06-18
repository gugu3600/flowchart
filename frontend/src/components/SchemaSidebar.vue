<script setup>
const props = defineProps({
  definitions: { type: Array, default: () => [] },
})

const nodeTypes = [
  {
    type: 'table',
    label: 'Table',
    icon: 'pi pi-table',
    color: 'blue',
    defaultData: {
      columns: [
        { name: 'id', type: 'INT PK', pk: true },
        { name: 'name', type: 'VARCHAR(255)' },
      ],
    },
  },
]

function onDragStart(event, nodeType) {
  event.dataTransfer.setData('application/json', JSON.stringify(nodeType))
  event.dataTransfer.effectAllowed = 'move'
}

function onDefDragStart(event, def) {
  const payload = {
    type: 'table',
    label: def.name,
    defaultData: {
      label: def.name,
      definitionId: def.id,
      definitionType: 'table',
      columns: def.columns,
    },
  }
  event.dataTransfer.setData('application/json', JSON.stringify(payload))
  event.dataTransfer.effectAllowed = 'move'
}
</script>

<template>
  <aside class="sidebar">
    <h2 class="sidebar-title">Schema</h2>
    <p class="sidebar-subtitle">Drag a table onto the canvas</p>

    <div
      v-for="nt in nodeTypes"
      :key="nt.type"
      class="sidebar-item"
      draggable="true"
      @dragstart="onDragStart($event, nt)"
    >
      <i :class="[nt.icon, 'sidebar-item-icon']" />
      <span class="sidebar-item-label">{{ nt.label }}</span>
    </div>

    <template v-if="definitions.length > 0">
      <h3 class="sidebar-section-title">Your Tables</h3>
      <div
        v-for="def in definitions"
        :key="def.id"
        class="sidebar-item sidebar-item-def"
        draggable="true"
        @dragstart="onDefDragStart($event, def)"
      >
        <i class="pi pi-table sidebar-item-icon" />
        <span class="sidebar-item-label">{{ def.name }}</span>
      </div>
    </template>
  </aside>
</template>

<style scoped>
.sidebar-section-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  margin-top: 1rem;
  margin-bottom: 0.25rem;
  padding-left: 0.25rem;
}
.sidebar-item-def {
  opacity: 0.85;
}
</style>