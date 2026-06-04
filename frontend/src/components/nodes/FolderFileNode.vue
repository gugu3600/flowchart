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
    <p class="mb-1.5 font-mono text-[10px] text-slate-500 dark:text-slate-400">
      {{ data.path }}
    </p>
    <div v-if="data.children?.length" class="flex flex-col gap-0.5">
      <div
        v-for="(child, i) in data.children"
        :key="i"
        class="flex items-center gap-1.5 rounded px-1.5 py-0.5 hover:bg-black/5 dark:hover:bg-white/5"
      >
        <i :class="['pi text-[10px]', child.endsWith('/') ? 'pi-folder text-amber-500' : 'pi-file text-slate-400']" />
        <span class="text-[11px] font-mono">{{ child }}</span>
      </div>
    </div>
  </BaseNode>
</template>
