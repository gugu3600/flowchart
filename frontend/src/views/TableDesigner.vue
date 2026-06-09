<script setup>
import { ref, onMounted } from 'vue'
import { getTables, createTable, updateTable, deleteTable } from '../api/tables.js'
import AppHeader from '../components/AppHeader.vue'
import { useUserStore } from '../stores/useUserStore.js'

const { isAdmin } = useUserStore()

const tables = ref([])
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

onMounted(loadTables)

async function loadTables() {
  loading.value = true
  error.value = ''
  try {
    const res = await getTables()
    if (res.success) tables.value = res.data.tables || []
  } catch (err) {
    error.value = err.message || 'Failed to load tables'
  } finally {
    loading.value = false
  }
}

function openNew() {
  editing.value = null
  form.value = emptyForm()
  showForm.value = true
}

function openEdit(table) {
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

function addColumn() {
  form.value.columns.push({ name: '', type: 'VARCHAR(255)', pk: false, fk: false, unique: false })
}

function removeColumn(idx) {
  form.value.columns.splice(idx, 1)
}

async function handleSave() {
  saving.value = true
  error.value = ''
  try {
    if (editing.value) {
      const res = await updateTable(editing.value, form.value)
      if (res.success) {
        const idx = tables.value.findIndex((t) => t.id === editing.value)
        if (idx >= 0) tables.value[idx] = res.data.table
      }
    } else {
      const res = await createTable(form.value)
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

    <div class="designer-body">
      <div v-if="error" class="error-msg designer-error">{{ error }}</div>

      <div v-if="loading" class="designer-loading">Loading...</div>

      <template v-else>
        <div class="designer-toolbar">
          <button class="btn-primary" @click="openNew">+ New Table</button>
        </div>

        <div v-if="tables.length === 0" class="designer-empty">
          No table definitions yet. Create one to get started.
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
            <div class="designer-card-actions">
              <button class="btn-sm btn-secondary" @click="openEdit(t)">Edit</button>
              <button class="btn-sm btn-danger" :disabled="deleting" @click="handleDelete(t.id)">{{ deleting ? '...' : 'Delete' }}</button>
            </div>
          </div>
        </div>
      </template>

      <div v-if="showForm" class="modal-overlay" @click.self="cancelForm">
        <div class="modal">
          <h2 class="modal-title">{{ editing ? 'Edit Table' : 'New Table' }}</h2>

          <div class="modal-body">
            <label class="field-label">Table Name</label>
            <input v-model="form.name" type="text" class="form-input" placeholder="e.g. users" />

            <div class="columns-header">
              <span class="field-label">Columns</span>
              <button class="btn-sm btn-secondary" @click="addColumn">+ Add Column</button>
            </div>

            <div v-for="(col, i) in form.columns" :key="i" class="column-row">
              <input v-model="col.name" type="text" class="col-input" placeholder="name" />
              <input v-model="col.type" type="text" class="col-input col-type" placeholder="VARCHAR(255)" />
              <label class="col-check"><input v-model="col.pk" type="checkbox" /> PK</label>
              <label class="col-check"><input v-model="col.fk" type="checkbox" /> FK</label>
              <label class="col-check"><input v-model="col.unique" type="checkbox" /> UQ</label>
              <button class="btn-sm btn-danger" @click="removeColumn(i)">×</button>
            </div>
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
