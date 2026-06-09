<script setup>
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
  </aside>
</template>
