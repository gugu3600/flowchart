<script setup>
import { ref, onMounted } from 'vue'
import { getLogics, createLogic, updateLogic, deleteLogic } from '../api/logics.js'

const logics = ref([])
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const editing = ref(null)
const form = ref(null)
const showForm = ref(false)

const emptyForm = () => ({
  name: '',
  description: '',
  inputs: [{ name: '', type: '' }],
  output: '',
})

onMounted(loadLogics)

async function loadLogics() {
  loading.value = true
  error.value = ''
  try {
    const res = await getLogics()
    if (res.success) logics.value = res.data.logics || []
  } catch (err) {
    error.value = err.message || 'Failed to load logics'
  } finally {
    loading.value = false
  }
}

function openNew() {
  editing.value = null
  form.value = emptyForm()
  showForm.value = true
}

function openEdit(logic) {
  editing.value = logic.id
  form.value = {
    name: logic.name,
    description: logic.description || '',
    inputs: logic.inputs && logic.inputs.length
      ? logic.inputs.map((i) => (typeof i === 'string' ? { name: i, type: '' } : { ...i }))
      : [{ name: '', type: '' }],
    output: logic.output || '',
  }
  showForm.value = true
}

function cancelForm() {
  showForm.value = false
  form.value = null
  editing.value = null
}

function addInput() {
  form.value.inputs.push({ name: '', type: '' })
}

function removeInput(idx) {
  form.value.inputs.splice(idx, 1)
}

async function handleSave() {
  saving.value = true
  error.value = ''
  try {
    const payload = {
      ...form.value,
      inputs: form.value.inputs.filter((i) => i.name.trim() && i.type.trim()),
    }
    if (editing.value) {
      const res = await updateLogic(editing.value, payload)
      if (res.success) {
        const idx = logics.value.findIndex((l) => l.id === editing.value)
        if (idx >= 0) logics.value[idx] = res.data.logic
      }
    } else {
      const res = await createLogic(payload)
      if (res.success) logics.value.push(res.data.logic)
    }
    showForm.value = false
    form.value = null
    editing.value = null
  } catch (err) {
    error.value = err.message || 'Failed to save logic'
  } finally {
    saving.value = false
  }
}

async function handleDelete(id) {
  if (!confirm('Delete this logic definition?')) return
  error.value = ''
  try {
    const res = await deleteLogic(id)
    if (res.success) {
      logics.value = logics.value.filter((l) => l.id !== id)
    }
  } catch (err) {
    error.value = err.message || 'Failed to delete logic'
  }
}
</script>

<template>
  <div class="designer-page">
    <header class="designer-header">
      <h1 class="designer-title">Logic Designer</h1>
      <div class="designer-nav">
        <a href="/tables" class="nav-link">Tables</a>
        <a href="/canvas" class="nav-link">Canvas</a>
        <a href="/login" class="nav-link">Logout</a>
      </div>
    </header>

    <div class="designer-body">
      <div v-if="error" class="error-msg designer-error">{{ error }}</div>

      <div v-if="loading" class="designer-loading">Loading...</div>

      <template v-else>
        <div class="designer-toolbar">
          <button class="btn-primary" @click="openNew">+ New Logic</button>
        </div>

        <div v-if="logics.length === 0" class="designer-empty">
          No logic definitions yet. Create one to get started.
        </div>

        <div v-else class="designer-list">
          <div v-for="l in logics" :key="l.id" class="designer-card">
            <div class="designer-card-body">
              <h3 class="designer-card-title">{{ l.name }}</h3>
              <p v-if="l.description" class="designer-card-desc">{{ l.description }}</p>
              <div class="designer-card-tags">
                <span v-for="(inp, i) in l.inputs" :key="i" class="node-tag node-tag-purple">{{ inp.name }}<span class="preview-type">:{{ inp.type }}</span></span>
                <span v-if="l.output" class="node-tag node-tag-emerald">{{ l.output }}</span>
              </div>
            </div>
            <div class="designer-card-actions">
              <button class="btn-sm btn-secondary" @click="openEdit(l)">Edit</button>
              <button class="btn-sm btn-danger" @click="handleDelete(l.id)">Delete</button>
            </div>
          </div>
        </div>
      </template>

      <div v-if="showForm" class="modal-overlay" @click.self="cancelForm">
        <div class="modal">
          <h2 class="modal-title">{{ editing ? 'Edit Logic' : 'New Logic' }}</h2>

          <div class="modal-body">
            <label class="field-label">Name</label>
            <input v-model="form.name" type="text" class="form-input" placeholder="e.g. validateLogin" />

            <label class="field-label">Description</label>
            <textarea v-model="form.description" class="form-input" rows="2" placeholder="What this logic does..."></textarea>

            <div class="inputs-header">
              <span class="field-label">Inputs</span>
              <button class="btn-sm btn-secondary" @click="addInput">+ Add Input</button>
            </div>

            <div v-for="(inp, i) in form.inputs" :key="i" class="input-row">
              <input v-model="form.inputs[i].name" type="text" class="form-input" placeholder="name" style="flex:1" />
              <input v-model="form.inputs[i].type" type="text" class="form-input" placeholder="string, int, User..." style="flex:1.5" />
              <button class="btn-sm btn-danger" @click="removeInput(i)">×</button>
            </div>

            <label class="field-label">Output</label>
            <input v-model="form.output" type="text" class="form-input" placeholder="e.g. User | null, boolean" />
          </div>

          <div class="modal-actions">
            <button class="btn-secondary" @click="cancelForm">Cancel</button>
            <button
              class="btn-primary"
              :disabled="saving || !form.name"
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

.designer-card-desc {
  margin: 0 0 0.5rem;
  font-size: 0.85rem;
  color: #94a3b8;
  font-style: italic;
}

.designer-card-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.3rem;
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
  width: 520px;
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

.inputs-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 0.5rem;
}

.input-row {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1rem;
}
</style>
