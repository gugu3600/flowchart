<script setup>
import { computed } from 'vue'

const props = defineProps({
  definitions: { type: Array, default: () => [] },
})

const nodeTypes = [
  {
    type: 'logic',
    label: 'Logic',
    icon: 'pi pi-cog',
    color: 'purple',
    defaultData: {
      description: 'Process logic step',
      inputs: [{ name: 'input', type: 'any' }],
      output: 'result',
    },
  },
  {
    type: 'folderFile',
    label: 'Folder / File',
    icon: 'pi pi-folder',
    color: 'amber',
    defaultData: {
      path: '/path/to/item',
      isFolder: true,
      children: ['file1.php', 'file2.php'],
    },
  },
]

function onDragStart(event, nodeType) {
  event.dataTransfer.setData('application/json', JSON.stringify(nodeType))
  event.dataTransfer.effectAllowed = 'move'
}

function onDefDragStart(event, def) {
  const payload = {
    type: 'logic',
    label: def.name,
    defaultData: {
      label: def.name,
      definitionId: def.id,
      definitionType: 'logic',
      description: def.description,
      inputs: def.inputs,
      output: def.output,
    },
  }
  event.dataTransfer.setData('application/json', JSON.stringify(payload))
  event.dataTransfer.effectAllowed = 'move'
}
</script>

<template>
  <aside class="sidebar">
    <h2 class="sidebar-title">Nodes</h2>
    <p class="sidebar-subtitle">Drag onto canvas</p>

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
      <h3 class="sidebar-section-title">Your Logics</h3>
      <div
        v-for="def in definitions"
        :key="def.id"
        class="sidebar-item sidebar-item-def"
        draggable="true"
        @dragstart="onDefDragStart($event, def)"
      >
        <i class="pi pi-cog sidebar-item-icon" />
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