<script setup>
import BaseNode from './BaseNode.vue'

const props = defineProps({
  id: { type: String, required: true },
  data: {
    type: Object,
    default: () => ({
      label: 'src/controllers',
      path: '/app/Http/Controllers',
      isFolder: true,
      children: ['AuthController.php', 'FlowController.php'],
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
    :icon="data.isFolder ? 'pi-folder' : 'pi-file'"
    :color="data.isFolder ? 'amber' : 'slate'"
  >
    <p class="node-folder-path">{{ data.path }}</p>
    <div v-if="data.children?.length" class="node-folder-children">
      <div
        v-for="(child, i) in data.children"
        :key="i"
        class="node-folder-row"
      >
        <i
          :class="[
            'pi',
            child.endsWith('/') ? 'pi-folder' : 'pi-file',
            child.endsWith('/') ? 'text-amber-500' : 'text-slate-400',
          ]"
        />
        <span class="node-folder-child-name">{{ child }}</span>
      </div>
    </div>
  </BaseNode>
</template>
