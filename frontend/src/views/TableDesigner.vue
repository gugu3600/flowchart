<script setup>
import { ref, onMounted, computed } from 'vue'
import { getTables, createTable, updateTable, deleteTable } from '../api/tables.js'
import { getFlows } from '../api/flows.js'
import AppHeader from '../components/AppHeader.vue'
import { useUserStore } from '../stores/useUserStore.js'
import ColumnBuilder from '../components/ColumnBuilder.vue'

const { isAdmin, fetchUser, canSave, canGenerateSchema } = useUserStore()

const tables = ref([])
const flows = ref([])
const selectedFlowId = ref('')
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const editing = ref(null)
const form = ref(null)
const showForm = ref(false)

const emptyForm = () => ({
  name: '',
  columns: [{ name: '', type: 'VARCHAR(255)', pk: false, fk: false, unique: false }],
})

const currentFlowId = computed(() => selectedFlowId.value ? Number(selectedFlowId.value) : null)

onMounted(async () => {
  await fetchUser()
  if (canSave.value) {
    const res = await getFlows()
    if (res.success) flows.value = res.data.flows || []
  }
  await loadTables()
})

async function loadTables() {
  loading.value = true
  error.value = ''
  try {
    const res = await getTables(currentFlowId.value)
    if (res.success) tables.value = res.data.tables || []
  } catch (err) {
    error.value = err.message || 'Failed to load tables'
  } finally {
    loading.value = false
  }
}

function onFlowChange() {
  loadTables()
}

function openNew() {
  if (!canGenerateSchema) return
  editing.value = null
  form.value = emptyForm()
  showForm.value = true
}

function openEdit(table) {
  if (!canGenerateSchema) return
  editing.value = table.id
  form.value = {
    name: table.name,
    columns: table.columns.map((c) => ({ ...c })),
  }
  showForm.value = true
}

function cancelForm() {
  showForm.value = false
  form.value = null
  editing.value = null
}

async function handleSave() {
  saving.value = true
  error.value = ''
  try {
    const payload = { ...form.value }
    if (currentFlowId.value) payload.flow_id = currentFlowId.value
    if (editing.value) {
      const res = await updateTable(editing.value, payload)
      if (res.success) {
        const idx = tables.value.findIndex((t) => t.id === editing.value)
        if (idx >= 0) tables.value[idx] = res.data.table
      }
    } else {
      const res = await createTable(payload)
      if (res.success) tables.value.push(res.data.table)
    }
    showForm.value = false
    form.value = null
    editing.value = null
  } catch (err) {
    error.value = err.message || 'Failed to save table'
  } finally {
    saving.value = false
  }
}

async function handleDelete(id) {
  if (!confirm('Delete this table definition?')) return
  error.value = ''
  deleting.value = true
  try {
    const res = await deleteTable(id)
    if (res.success) {
      tables.value = tables.value.filter((t) => t.id !== id)
    }
  } catch (err) {
    error.value = err.message || 'Failed to delete table'
  } finally {
    deleting.value = false
  }
}

function updateColumns(val) {
  form.value.columns = val
}
</script>

<template>
  <div class="designer-page">
    <AppHeader title="Table Designer">
      <template #right>
        <a href="/canvas" class="nav-link">Canvas</a>
        <a href="/logics" class="nav-link">Logics</a>
        <a v-if="isAdmin" href="/admin" class="nav-link">Admin</a>
        <a href="/login" class="nav-link">Logout</a>
      </template>
    </AppHeader>

    <div v-if="!canGenerateSchema" class="free-banner">
      <span class="free-banner-icon">🔒</span>
      <span class="free-banner-text">
        You're on the <strong>Free</strong> plan. Table creation requires <strong>Gold</strong> or higher.
        <router-link to="/subscribe" class="free-banner-link">Upgrade to Gold</router-link> to start designing tables.
      </span>
    </div>

    <div class="designer-body">
      <div v-if="error" class="error-msg designer-error">{{ error }}</div>

      <div v-if="loading" class="designer-loading">Loading...</div>

      <template v-else>
        <div class="designer-toolbar">
          <select v-model="selectedFlowId" class="form-input flow-select" @change="onFlowChange">
            <option value="">All flows</option>
            <option v-for="f in flows" :key="f.id" :value="f.id">{{ f.name }}</option>
          </select>
          <button v-if="canGenerateSchema" class="btn-primary" @click="openNew">+ New Table</button>
        </div>

        <div v-if="tables.length === 0" class="designer-empty">
          No table definitions yet.
        </div>

        <div v-else class="designer-list">
          <div v-for="t in tables" :key="t.id" class="designer-card">
            <div class="designer-card-body">
              <h3 class="designer-card-title">{{ t.name }}</h3>
              <p class="designer-card-meta">{{ (t.columns || []).length }} columns</p>
              <ul v-if="t.columns" class="designer-card-preview">
                <li v-for="c in t.columns.slice(0, 5)" :key="c.name" class="preview-col">
                  <code>{{ c.name }}</code>
                  <span class="preview-type">{{ c.type }}</span>
                  <span v-if="c.pk" class="node-badge node-badge-pk">PK</span>
                  <span v-if="c.fk" class="node-badge node-badge-fk">FK</span>
                  <span v-if="c.unique" class="node-badge node-badge-uq">UQ</span>
                </li>
                <li v-if="t.columns.length > 5" class="preview-more">
                  ...and {{ t.columns.length - 5 }} more
                </li>
              </ul>
            </div>
            <div v-if="canGenerateSchema" class="designer-card-actions">
              <button class="btn-sm btn-secondary" @click="openEdit(t)">Edit</button>
              <button class="btn-sm btn-danger" :disabled="deleting" @click="handleDelete(t.id)">{{ deleting ? '...' : 'Delete' }}</button>
            </div>
          </div>
        </div>
      </template>

      <div v-if="showForm && canGenerateSchema" class="modal-overlay" @click.self="cancelForm">
        <div class="modal">
          <h2 class="modal-title">{{ editing ? 'Edit Table' : 'New Table' }}</h2>

          <div class="modal-body">
            <label class="field-label">Table Name</label>
            <input v-model="form.name" type="text" class="form-input" placeholder="e.g. users" />

            <ColumnBuilder :columns="form.columns" @update:columns="updateColumns" />
          </div>

          <div class="modal-actions">
            <button class="btn-secondary" @click="cancelForm">Cancel</button>
            <button
              class="btn-primary"
              :disabled="saving || !form.name || form.columns.some((c) => !c.name || !c.type)"
              @click="handleSave"
            >
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>
