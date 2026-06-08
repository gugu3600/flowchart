<script setup>
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
  </aside>
</template>
