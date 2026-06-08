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

function onDragOver(event) {
  event.preventDefault()
}

function onDrop(event) {
  event.preventDefault()
  const raw = event.dataTransfer.getData('application/json')
  if (!raw) return
  const nodeDef = JSON.parse(raw)
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
    <header class="canvas-header">
      <div class="canvas-header-left">
        <h1 class="canvas-title">Flowchart</h1>

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
      </div>

      <div class="canvas-header-right">
        <a v-if="mode === 'flow'" href="/logics" class="nav-link">Logics</a>
        <a v-if="mode === 'schema'" href="/tables" class="nav-link">Tables</a>
        <button class="btn-sm btn-secondary" @click="refresh">⟳</button>
        <button class="btn-sm btn-primary" :disabled="!currentFlowId || saving" @click="handleSave">
          {{ saving ? 'Saving...' : 'Save' }}
        </button>
        <a href="/login" class="canvas-logout">Logout</a>
      </div>
    </header>

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
          fit-view-on-init
          @connect="onConnect"
          @dragover="onDragOver"
          @drop="onDrop"
        />
        <div v-else class="canvas-empty"><p>Select or create a flow to get started</p></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.canvas-page {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #0f172a;
}

.canvas-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem 1rem;
  background: #1e293b;
  border-bottom: 1px solid #334155;
  gap: 0.75rem;
  flex-shrink: 0;
}

.canvas-header-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
}

.canvas-header-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.canvas-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #f1f5f9;
  white-space: nowrap;
}

.mode-tabs {
  display: flex;
  gap: 0;
  border: 1px solid #475569;
  border-radius: 6px;
  overflow: hidden;
}

.mode-tab {
  background: #334155;
  color: #94a3b8;
  border: none;
  padding: 0.25rem 0.75rem;
  font-size: 0.8rem;
  cursor: pointer;
  font-weight: 500;
  transition: background 0.15s, color 0.15s;
}

.mode-tab.active {
  background: #3b82f6;
  color: #fff;
}

.mode-tab:not(.active):hover {
  background: #475569;
  color: #f1f5f9;
}

.nav-link {
  font-size: 0.8rem;
  color: #94a3b8;
  text-decoration: none;
}

.nav-link:hover { color: #3b82f6; }

.flow-select {
  background: #334155;
  color: #f1f5f9;
  border: 1px solid #475569;
  border-radius: 6px;
  padding: 0.3rem 0.6rem;
  font-size: 0.875rem;
  cursor: pointer;
  min-width: 140px;
}

.flow-select:focus { outline: none; border-color: #3b82f6; }

.new-flow-form { display: flex; align-items: center; gap: 0.4rem; }

.flow-name-input {
  background: #334155;
  color: #f1f5f9;
  border: 1px solid #475569;
  border-radius: 6px;
  padding: 0.3rem 0.6rem;
  font-size: 0.875rem;
  width: 140px;
}

.flow-name-input:focus { outline: none; border-color: #3b82f6; }

.btn-sm {
  padding: 0.25rem 0.6rem;
  font-size: 0.8rem;
  border-radius: 6px;
  cursor: pointer;
  border: none;
  font-weight: 500;
  transition: background 0.15s, opacity 0.15s;
}

.btn-sm:disabled { opacity: 0.5; cursor: not-allowed; }

.canvas-logout {
  font-size: 0.8rem;
  color: #94a3b8;
  text-decoration: none;
  white-space: nowrap;
}

.canvas-logout:hover { color: #f87171; }

.canvas-error { margin: 0.5rem 1rem 0; flex-shrink: 0; }

.canvas-body { display: flex; flex: 1; overflow: hidden; }

.canvas-flow-wrapper { flex: 1; position: relative; }

.canvas-loading,
.canvas-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #64748b;
  font-size: 0.9rem;
}
</style>
