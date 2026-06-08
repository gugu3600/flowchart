<script setup>
import { ref, onMounted } from 'vue'
import { getTables, createTable, updateTable, deleteTable } from '../api/tables.js'

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
    <header class="designer-header">
      <h1 class="designer-title">Table Designer</h1>
      <div class="designer-nav">
        <a href="/canvas" class="nav-link">Canvas</a>
        <a href="/logics" class="nav-link">Logics</a>
        <a href="/login" class="nav-link">Logout</a>
      </div>
    </header>

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
.designer-page {
  min-height: 100vh;
  background: #0f172a;
  color: #f1f5f9;
}

.designer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1.5rem;
  background: #1e293b;
  border-bottom: 1px solid #334155;
}

.designer-title {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.designer-nav {
  display: flex;
  gap: 1rem;
}

.nav-link {
  font-size: 0.875rem;
  color: #94a3b8;
  text-decoration: none;
}

.nav-link:hover {
  color: #3b82f6;
}

.designer-body {
  max-width: 900px;
  margin: 0 auto;
  padding: 1.5rem;
}

.designer-error {
  margin-bottom: 1rem;
}

.designer-loading,
.designer-empty {
  text-align: center;
  padding: 3rem;
  color: #64748b;
}

.designer-toolbar {
  margin-bottom: 1rem;
}

.designer-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.designer-card {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 10px;
  padding: 1rem;
}

.designer-card-body {
  flex: 1;
}

.designer-card-title {
  margin: 0 0 0.25rem;
  font-size: 1rem;
  font-weight: 600;
}

.designer-card-meta {
  margin: 0 0 0.5rem;
  font-size: 0.8rem;
  color: #64748b;
}

.designer-card-preview {
  list-style: none;
  margin: 0;
  padding: 0;
}

.preview-col {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  padding: 0.15rem 0;
}

.preview-type {
  color: #94a3b8;
  font-size: 0.75rem;
}

.preview-more {
  font-size: 0.75rem;
  color: #64748b;
  padding-top: 0.25rem;
}

.designer-card-actions {
  display: flex;
  gap: 0.4rem;
  flex-shrink: 0;
}

.btn-sm {
  padding: 0.25rem 0.6rem;
  font-size: 0.8rem;
  border-radius: 6px;
  cursor: pointer;
  border: none;
  font-weight: 500;
  transition: background 0.15s, opacity 0.15s;
}

.btn-sm:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.modal {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 1.5rem;
  width: 640px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-title {
  margin: 0 0 1rem;
  font-size: 1.1rem;
  font-weight: 600;
}

.modal-body {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.field-label {
  font-size: 0.85rem;
  font-weight: 500;
  color: #94a3b8;
}

.columns-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 0.5rem;
}

.column-row {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.col-input {
  background: #0f172a;
  color: #f1f5f9;
  border: 1px solid #334155;
  border-radius: 4px;
  padding: 0.3rem 0.5rem;
  font-size: 0.8rem;
  flex: 1;
  min-width: 80px;
}

.col-type {
  flex: 1.5;
}

.col-check {
  display: flex;
  align-items: center;
  gap: 0.2rem;
  font-size: 0.75rem;
  color: #94a3b8;
  white-space: nowrap;
}

.col-check input {
  width: 14px;
  height: 14px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1rem;
}
</style>
