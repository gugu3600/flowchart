<script setup>
import { ref, markRaw, onMounted, computed } from 'vue'
import { VueFlow, useVueFlow } from '@vue-flow/core'
import '@vue-flow/core/dist/style.css'
import '@vue-flow/core/dist/theme-default.css'
import { TableNode, LogicNode, FolderFileNode } from '../components/nodes'
import Sidebar from '../components/Sidebar.vue'
import SchemaSidebar from '../components/SchemaSidebar.vue'
import { getFlows, getFlow, createFlow, saveFlow } from '../api/flows.js'
import { getTables } from '../api/tables.js'
import { getLogics } from '../api/logics.js'
import { useUserStore } from '../stores/useUserStore.js'
import UserProfile from '../components/UserProfile.vue'
import AppHeader from '../components/AppHeader.vue'

const { fetchUser, isAdmin, canSave } = useUserStore()

const mode = ref('flow')

const flowNodeTypes = markRaw({
  logic: LogicNode,
  folderFile: FolderFileNode,
})

const schemaNodeTypes = markRaw({
  table: TableNode,
})

const { screenToFlowCoordinate } = useVueFlow()

const flows = ref([])
const currentFlowId = ref(null)
const nodes = ref([])
const edges = ref([])
const loading = ref(false)
const saving = ref(false)
const creating = ref(false)
const error = ref('')
const newFlowName = ref('')
const showNewFlowInput = ref(false)

const nodeTypes = computed(() =>
  mode.value === 'schema' ? schemaNodeTypes : flowNodeTypes
)

onMounted(async () => {
  await fetchUser()
  await loadFlows()
})

async function loadFlows() {
  loading.value = true
  error.value = ''
  try {
    const res = await getFlows()
    if (res.success) {
      flows.value = res.data.flows || []
      if (flows.value.length > 0 && !currentFlowId.value) {
        await selectFlow(flows.value[0].id)
      }
    }
  } catch (err) {
    error.value = err.message || 'Failed to load flows'
  } finally {
    loading.value = false
  }
}

async function selectFlow(flowId) {
  currentFlowId.value = flowId
  await loadFlowData(flowId)
}

async function loadFlowData(flowId) {
  loading.value = true
  error.value = ''
  try {
    const flowRes = await getFlow(flowId)
    const savedNodes = []
    const savedEdges = []

    if (flowRes.success) {
      const flow = flowRes.data.flow
      for (const n of flow.nodes || []) {
        const isFlowType = n.type === 'logic' || n.type === 'folderFile'
        const isSchemaType = n.type === 'table'
        if (mode.value === 'flow' && !isFlowType) continue
        if (mode.value === 'schema' && !isSchemaType) continue
        savedNodes.push({
          id: String(n.id),
          type: n.type,
          position: { x: n.position_x, y: n.position_y },
          data: { label: n.label, ...(n.data || {}) },
        })
      }
      savedEdges.push(
        ...(flow.edges || []).map((e) => ({
          id: `e-${e.id}`,
          source: String(e.source_node_id),
          target: String(e.target_node_id),
          label: e.label || '',
        }))
      )
    }

    const existingDefIds = new Set()
    for (const n of savedNodes) {
      if (n.data?.definitionId) {
        existingDefIds.add(`${n.data.definitionType}:${n.data.definitionId}`)
      }
    }

    const defNodes = []
    let defIndex = 0

    if (mode.value === 'flow') {
      const logicsRes = await getLogics()
      if (logicsRes.success) {
        for (const l of logicsRes.data.logics || []) {
          const key = `logic:${l.id}`
          if (existingDefIds.has(key)) continue
          defNodes.push({
            id: `def_logic_${l.id}`,
            type: 'logic',
            position: { x: 50 + defIndex++ * 350, y: 50 },
              data: {
                label: l.name,
                definitionId: l.id,
                definitionType: 'logic',
                description: l.description,
                inputs: l.inputs,
                output: l.output,
                typeField: l.type,
              },
          })
        }
      }
    } else {
      const tablesRes = await getTables()
      if (tablesRes.success) {
        for (const t of tablesRes.data.tables || []) {
          const key = `table:${t.id}`
          if (existingDefIds.has(key)) continue
          defNodes.push({
            id: `def_table_${t.id}`,
            type: 'table',
            position: { x: 50 + defIndex++ * 350, y: 50 },
            data: {
              label: t.name,
              definitionId: t.id,
              definitionType: 'table',
              columns: t.columns,
            },
          })
        }
      }
    }

    nodes.value = [...defNodes, ...savedNodes]
    edges.value = savedEdges
  } catch (err) {
    error.value = err.message || 'Failed to load data'
  } finally {
    loading.value = false
  }
}

async function handleCreateFlow() {
  if (!newFlowName.value.trim()) return
  creating.value = true
  error.value = ''
  try {
    const res = await createFlow({ name: newFlowName.value.trim() })
    if (res.success) {
      flows.value.push(res.data.flow)
      currentFlowId.value = res.data.flow.id
      nodes.value = []
      edges.value = []
      showNewFlowInput.value = false
      newFlowName.value = ''
    }
  } catch (err) {
    error.value = err.message || 'Failed to create flow'
  } finally {
    creating.value = false
  }
}

async function handleSave() {
  if (!currentFlowId.value) return
  if (!canSave.value) {
    error.value = 'Saving requires a Silver or higher subscription. Upgrade to unlock.'
    return
  }
  saving.value = true
  error.value = ''
  try {
    const payload = {
      nodes: nodes.value.map((n) => ({
        id: n.id,
        type: n.type,
        label: n.data?.label || '',
        position_x: n.position.x,
        position_y: n.position.y,
        data: n.data || {},
      })),
      edges: edges.value.map((e) => ({
        source: e.source,
        target: e.target,
        label: e.label || '',
      })),
    }
    const res = await saveFlow(currentFlowId.value, payload)
    if (res.success) {
      nodes.value = (res.data.flow?.nodes || []).map((n) => ({
        id: String(n.id),
        type: n.type,
        position: { x: n.position_x, y: n.position_y },
        data: { label: n.label, ...(n.data || {}) },
      }))
      edges.value = (res.data.flow?.edges || []).map((e) => ({
        id: `e-${e.id}`,
        source: String(e.source_node_id),
        target: String(e.target_node_id),
        label: e.label || '',
      }))
    }
  } catch (err) {
    error.value = err.message || 'Failed to save flow'
  } finally {
    saving.value = false
  }
}

function findNode(nodeId) {
  return nodes.value.find((n) => n.id === nodeId)
}

function isValidConnection(connection) {
  const src = findNode(connection.source)
  const tgt = findNode(connection.target)
  if (!src || !tgt) return false
  if (mode.value === 'schema') {
    return src.type === 'table' && tgt.type === 'table'
  }
  return src.type !== 'table' && tgt.type !== 'table'
}

function onConnect(params) {
  if (!isValidConnection(params)) return
  edges.value = [
    ...edges.value,
    {
      id: `e-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
      source: params.source,
      target: params.target,
      animated: true,
    },
  ]
}

function onEdgeClick(event, edge) {
  edge.selected = true
}

function onEdgesDelete(removedEdges) {
  const removedIds = new Set(removedEdges.map(e => e.id))
  edges.value = edges.value.filter(e => !removedIds.has(e.id))
}

function onNodesDelete(removedNodes) {
  const removedIds = new Set(removedNodes.map(n => n.id))
  nodes.value = nodes.value.filter(n => !removedIds.has(n.id))
}

function onDragOver(event) {
  event.preventDefault()
}

function isAllowedType(type) {
  if (mode.value === 'flow') return type === 'logic' || type === 'folderFile'
  return type === 'table'
}

function onDrop(event) {
  event.preventDefault()
  const raw = event.dataTransfer.getData('application/json')
  if (!raw) return
  let nodeDef
  try { nodeDef = JSON.parse(raw) } catch { return }
  if (!isAllowedType(nodeDef.type)) return
  const position = screenToFlowCoordinate({
    x: event.clientX,
    y: event.clientY,
  })
  const id = `node_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`
  nodes.value = [
    ...nodes.value,
    {
      id,
      type: nodeDef.type,
      position,
      data: {
        label: nodeDef.defaultData?.label || nodeDef.label,
        columns: nodeDef.defaultData?.columns,
        description: nodeDef.defaultData?.description,
        inputs: nodeDef.defaultData?.inputs,
        output: nodeDef.defaultData?.output,
        path: nodeDef.defaultData?.path,
        isFolder: nodeDef.defaultData?.isFolder,
        children: nodeDef.defaultData?.children,
      },
    },
  ]
}

function switchMode(newMode) {
  if (newMode === mode.value) return
  mode.value = newMode
  if (currentFlowId.value) {
    loadFlowData(currentFlowId.value)
  }
}

function refresh() {
  if (currentFlowId.value) {
    loadFlowData(currentFlowId.value)
  }
}
</script>

<template>
  <div class="canvas-page">
    <AppHeader title="Flowchart">
      <template #left>
        <div class="mode-tabs">
          <button
            :class="['mode-tab', { active: mode === 'flow' }]"
            @click="switchMode('flow')"
          >
            Flow
          </button>
          <button
            :class="['mode-tab', { active: mode === 'schema' }]"
            @click="switchMode('schema')"
          >
            Schema
          </button>
        </div>

        <select
          v-if="flows.length > 0"
          class="flow-select"
          :value="currentFlowId"
          @change="selectFlow(Number($event.target.value))"
        >
          <option v-for="f in flows" :key="f.id" :value="f.id">{{ f.name }}</option>
        </select>

        <div v-if="showNewFlowInput" class="new-flow-form">
          <input v-model="newFlowName" type="text" placeholder="Flow name..." class="flow-name-input" @keyup.enter="handleCreateFlow" />
          <button class="btn-sm btn-primary" :disabled="creating || !newFlowName.trim()" @click="handleCreateFlow">
            {{ creating ? 'Creating...' : 'Create' }}
          </button>
          <button class="btn-sm btn-secondary" @click="showNewFlowInput = false; newFlowName = ''">Cancel</button>
        </div>
        <button v-else class="btn-sm btn-secondary" @click="showNewFlowInput = true">+ New Flow</button>
      </template>

      <template #right>
        <a v-if="mode === 'flow'" href="/logics" class="nav-link">Logics</a>
        <a v-if="mode === 'schema'" href="/tables" class="nav-link">Tables</a>
        <a v-if="isAdmin" href="/admin" class="nav-link">Admin</a>
        <a href="/help" class="nav-link">Help</a>
        <button class="btn-sm btn-secondary" @click="refresh">⟳</button>
        <button
          class="btn-sm btn-primary"
          :disabled="!currentFlowId || saving || !canSave"
          :title="!canSave ? 'Upgrade to Silver to save' : ''"
          @click="handleSave"
        >
          {{ saving ? 'Saving...' : 'Save' }}
        </button>
        <UserProfile />
      </template>
    </AppHeader>

    <div v-if="error" class="canvas-error error-msg">{{ error }}</div>

    <div class="canvas-body">
      <SchemaSidebar v-if="mode === 'schema'" />
      <Sidebar v-else />

      <div class="canvas-flow-wrapper">
        <div v-if="loading" class="canvas-loading">Loading...</div>
        <VueFlow
          v-else-if="currentFlowId"
          v-model:nodes="nodes"
          v-model:edges="edges"
          :node-types="nodeTypes"
          :default-edge-options="{ type: 'smoothstep', animated: true }"
          :is-valid-connection="isValidConnection"
          :delete-key-code="['Delete', 'Backspace']"
          fit-view-on-init
          @connect="onConnect"
          @edge-click="onEdgeClick"
          @edges-delete="onEdgesDelete"
          @nodes-delete="onNodesDelete"
          @dragover="onDragOver"
          @drop="onDrop"
        />
        <div v-else class="canvas-empty"><p>Select or create a flow to get started</p></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>
