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

const { fetchUser, isAdmin, isSilver, isFree, canSave, tierLabel, tierColor } = useUserStore()

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
const flowCount = ref(0)
const maxSlots = ref(0)

const selectedNode = ref(null)
const selectedEdge = ref(null)

const nodeTypes = computed(() =>
  mode.value === 'schema' ? schemaNodeTypes : flowNodeTypes
)

const slotsUsed = computed(() => flowCount.value)
const slotsTotal = computed(() => maxSlots.value)
const slotsRemaining = computed(() => Math.max(0, maxSlots.value - flowCount.value))

const nodeColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16']
const edgeColors = ['#64748b', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']

onMounted(async () => {
  await fetchUser()
  if (canSave.value) {
    await loadFlows()
  }
})

async function loadFlows() {
  loading.value = true
  error.value = ''
  try {
    const res = await getFlows()
    if (res.success) {
      flows.value = res.data.flows || []
      flowCount.value = res.data.flow_count ?? 0
      maxSlots.value = res.data.max_slots ?? 0
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
  selectedNode.value = null
  selectedEdge.value = null
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
        const nodeConfig = n.config || {}
        savedNodes.push({
          id: String(n.id),
          type: n.type,
          position: { x: n.position_x, y: n.position_y },
          data: { label: n.label, ...(n.data || {}) },
          style: nodeConfig.backgroundColor ? { background: nodeConfig.backgroundColor } : {},
          config: nodeConfig,
        })
      }
      savedEdges.push(
        ...(flow.edges || []).map((e) => {
          const edgeConfig = e.config || {}
          return {
            id: `e-${e.id}`,
            source: String(e.source_node_id),
            target: String(e.target_node_id),
            label: e.label || '',
            style: edgeConfig.strokeColor ? { stroke: edgeConfig.strokeColor } : {},
            config: edgeConfig,
          }
        })
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
  if (!canSave.value) {
    error.value = 'Upgrade to Silver to create saved flows.'
    return
  }
  creating.value = true
  error.value = ''
  try {
    const res = await createFlow({ name: newFlowName.value.trim() })
    if (res.success) {
      flows.value.push(res.data.flow)
      flowCount.value++
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
  if (saving.value) return
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
        config: n.config || {},
      })),
      edges: edges.value.map((e) => ({
        source: e.source,
        target: e.target,
        label: e.label || '',
        config: e.config || {},
      })),
    }
    const res = await saveFlow(currentFlowId.value, payload)
    if (res.success) {
      nodes.value = (res.data.flow?.nodes || []).map((n) => {
        const nc = n.config || {}
        return {
          id: String(n.id),
          type: n.type,
          position: { x: n.position_x, y: n.position_y },
          data: { label: n.label, ...(n.data || {}) },
          style: nc.backgroundColor ? { background: nc.backgroundColor } : {},
          config: nc,
        }
      })
      edges.value = (res.data.flow?.edges || []).map((e) => {
        const ec = e.config || {}
        return {
          id: `e-${e.id}`,
          source: String(e.source_node_id),
          target: String(e.target_node_id),
          label: e.label || '',
          style: ec.strokeColor ? { stroke: ec.strokeColor } : {},
          config: ec,
        }
      })

      // Restore selection so color swatch active state stays in sync
      if (selectedNode.value) {
        selectedNode.value = nodes.value.find(n => n.id === selectedNode.value.id) || null
      }
      if (selectedEdge.value) {
        selectedEdge.value = edges.value.find(e => e.id === selectedEdge.value.id) || null
      }
    }
  } catch (err) {
    error.value = err.message || 'Failed to save flow'
  } finally {
    saving.value = false
  }
}

function setNodeColor(color) {
  const sel = selectedNode.value
  if (!sel) return
  const idx = nodes.value.findIndex(n => n.id === sel.id)
  if (idx === -1) return
  const node = { ...nodes.value[idx] }
  node.config = { ...(node.config || {}), backgroundColor: color }
  nodes.value[idx] = node
  if (currentFlowId.value) {
    handleSave()
  }
}

function setEdgeColor(color) {
  const sel = selectedEdge.value
  if (!sel) return
  const idx = edges.value.findIndex(e => e.id === sel.id)
  if (idx === -1) return
  const edge = { ...edges.value[idx] }
  edge.config = { ...(edge.config || {}), strokeColor: color }
  edges.value[idx] = edge
  if (currentFlowId.value) {
    handleSave()
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
      config: {},
    },
  ]
}

function onNodeClick(event, node) {
  selectedNode.value = node
  selectedEdge.value = null
}

function onEdgeClick(event, edge) {
  edge.selected = true
  selectedEdge.value = edge
  selectedNode.value = null
}

function onPaneClick() {
  selectedNode.value = null
  selectedEdge.value = null
}

function onEdgesDelete(removedEdges) {
  const removedIds = new Set(removedEdges.map(e => e.id))
  edges.value = edges.value.filter(e => !removedIds.has(e.id))
  if (selectedEdge.value && removedIds.has(selectedEdge.value.id)) {
    selectedEdge.value = null
  }
}

function onNodesDelete(removedNodes) {
  const removedIds = new Set(removedNodes.map(n => n.id))
  nodes.value = nodes.value.filter(n => !removedIds.has(n.id))
  if (selectedNode.value && removedIds.has(selectedNode.value.id)) {
    selectedNode.value = null
  }
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
      config: {},
    },
  ]
}

function switchMode(newMode) {
  if (newMode === mode.value) return
  mode.value = newMode
  selectedNode.value = null
  selectedEdge.value = null
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
          v-if="canSave && flows.length > 0"
          class="flow-select"
          :value="currentFlowId"
          @change="selectFlow(Number($event.target.value))"
        >
          <option v-for="f in flows" :key="f.id" :value="f.id">{{ f.name }}</option>
        </select>

        <div v-if="canSave && showNewFlowInput" class="new-flow-form">
          <input v-model="newFlowName" type="text" placeholder="Flow name..." class="flow-name-input" @keyup.enter="handleCreateFlow" />
          <button class="btn-sm btn-primary" :disabled="creating || !newFlowName.trim()" @click="handleCreateFlow">
            {{ creating ? 'Creating...' : 'Create' }}
          </button>
          <button class="btn-sm btn-secondary" @click="showNewFlowInput = false; newFlowName = ''">Cancel</button>
        </div>
        <button v-else-if="canSave" class="btn-sm btn-secondary" @click="showNewFlowInput = true">+ New Flow</button>
      </template>

      <template #right>
        <a v-if="mode === 'flow'" href="/logics" class="nav-link">Logics</a>
        <a v-if="mode === 'schema'" href="/tables" class="nav-link">Tables</a>
        <a v-if="isAdmin" href="/admin" class="nav-link">Admin</a>
        <a href="/help" class="nav-link">Help</a>
        <button class="btn-sm btn-secondary" @click="refresh" :disabled="!currentFlowId">⟳</button>
        <button v-if="canSave" class="btn-sm btn-primary" :disabled="!currentFlowId || saving" @click="handleSave">
          {{ saving ? 'Saving...' : 'Save' }}
        </button>
        <UserProfile />
      </template>
    </AppHeader>

    <!-- Free tier upgrade banner -->
    <div v-if="isFree" class="free-banner">
      <span class="free-banner-icon">🔒</span>
      <span class="free-banner-text">
        You're on the <strong>Free</strong> plan. Create flowcharts visually, but they won't be saved.
        <router-link to="/register" class="free-banner-link">Upgrade to Silver</router-link> to save your work.
      </span>
    </div>

    <!-- Slot usage (silver+) -->
    <div v-else-if="canSave && maxSlots > 0 && maxSlots < 900" class="slot-bar">
      <span class="slot-label">Slots: {{ slotsUsed }}/{{ slotsTotal }} used</span>
      <div class="slot-track">
        <div class="slot-fill" :style="{ width: (slotsUsed / slotsTotal * 100) + '%' }"></div>
      </div>
      <span v-if="slotsRemaining <= 1" class="slot-warning">{{ slotsRemaining }} slot remaining</span>
    </div>

    <!-- Color toolbar (silver+ when node/edge selected) -->
    <div v-if="!isFree && (selectedNode || selectedEdge)" class="color-bar">
      <template v-if="selectedNode">
        <span class="color-label">Node Color:</span>
        <button
          v-for="c in nodeColors"
          :key="c"
          class="color-swatch"
          :class="{ 'color-swatch-active': selectedNode.style?.background === c }"
          :style="{ background: c }"
          @click="setNodeColor(c)"
        ></button>
        <button class="color-clear" title="Remove custom color" @click="setNodeColor('')">✕</button>
      </template>
      <template v-if="selectedEdge">
        <span class="color-label">Edge Color:</span>
        <button
          v-for="c in edgeColors"
          :key="c"
          class="color-swatch"
          :class="{ 'color-swatch-active': selectedEdge.style?.stroke === c || (!selectedEdge.style?.stroke && c === '#64748b') }"
          :style="{ background: c }"
          @click="setEdgeColor(c)"
        ></button>
        <button class="color-clear" title="Reset to default" @click="setEdgeColor('#64748b')">✕</button>
      </template>
    </div>

    <div v-if="error" class="canvas-error error-msg">{{ error }}</div>

    <div class="canvas-body">
      <SchemaSidebar v-if="mode === 'schema'" />
      <Sidebar v-else />

      <div class="canvas-flow-wrapper">
        <div v-if="loading" class="canvas-loading">Loading...</div>
        <VueFlow
          v-else
          v-model:nodes="nodes"
          v-model:edges="edges"
          :node-types="nodeTypes"
          :default-edge-options="{ type: 'smoothstep', animated: true }"
          :is-valid-connection="isValidConnection"
          :delete-key-code="['Delete', 'Backspace']"
          fit-view-on-init
          @connect="onConnect"
          @node-click="onNodeClick"
          @edge-click="onEdgeClick"
          @pane-click="onPaneClick"
          @edges-delete="onEdgesDelete"
          @nodes-delete="onNodesDelete"
          @dragover="onDragOver"
          @drop="onDrop"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>
