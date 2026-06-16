<script setup>
import { ref, onMounted } from 'vue'
import { getLogics, createLogic, updateLogic, deleteLogic } from '../api/logics.js'
import AppHeader from '../components/AppHeader.vue'
import { useUserStore } from '../stores/useUserStore.js'

const { isFree, fetchUser } = useUserStore()

const logics = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const editing = ref(null)
const form = ref(null)
const showForm = ref(false)
const logicCount = ref(0)
const maxLogicSlots = ref(0)
const showUpgradeModal = ref(false)

const emptyForm = () => ({
  name: '',
  description: '',
  inputs: [{ name: '', type: '' }],
  output: '',
})

onMounted(async () => {
  await fetchUser()
  await loadLogics()
})

async function loadLogics() {
  loading.value = true
  error.value = ''
  try {
    const res = await getLogics()
    if (res.success) {
      logics.value = res.data.logics || []
      logicCount.value = res.data.logic_count ?? 0
      maxLogicSlots.value = res.data.max_slots ?? 0
    }
  } catch (err) {
    error.value = err.message || 'Failed to load logics'
  } finally {
    loading.value = false
  }
}

function openNew() {
  if (isFree && logicCount.value >= maxLogicSlots.value && maxLogicSlots.value > 0) {
    showUpgradeModal.value = true
    return
  }
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
      if (res.success) {
        logics.value.push(res.data.logic)
        logicCount.value++
      }
    }
    showForm.value = false
    form.value = null
    editing.value = null
  } catch (err) {
    if (err.response?.status === 403) {
      showUpgradeModal.value = true
    } else {
      error.value = err.message || 'Failed to save logic'
    }
  } finally {
    saving.value = false
  }
}

async function handleDelete(id) {
  if (!confirm('Delete this logic definition?')) return
  error.value = ''
  deleting.value = true
  try {
    const res = await deleteLogic(id)
    if (res.success) {
      logics.value = logics.value.filter((l) => l.id !== id)
      logicCount.value = Math.max(0, logicCount.value - 1)
    }
  } catch (err) {
    error.value = err.message || 'Failed to delete logic'
  } finally {
    deleting.value = false
  }
}

function closeUpgradeModal() {
  showUpgradeModal.value = false
}
</script>

<template>
  <div class="designer-page">
    <AppHeader title="Logic Designer">
      <template #right>
        <a href="/tables" class="nav-link">Tables</a>
        <a href="/canvas" class="nav-link">Canvas</a>
        <a v-if="isAdmin" href="/admin" class="nav-link">Admin</a>
        <a href="/login" class="nav-link">Logout</a>
      </template>
    </AppHeader>

    <div v-if="isFree" class="free-banner">
      <span class="free-banner-icon">🔒</span>
      <span class="free-banner-text">
        You're on the <strong>Free</strong> plan. You can create up to <strong>{{ maxLogicSlots }} logics</strong>.
        <router-link to="/subscribe" class="free-banner-link">Upgrade to Silver</router-link> for unlimited logics.
      </span>
    </div>

    <div class="designer-body">
      <div v-if="error" class="error-msg designer-error">{{ error }}</div>

      <div v-if="loading" class="designer-loading">Loading...</div>

      <template v-else>
        <div class="designer-toolbar">
          <button class="btn-primary" @click="openNew">+ New Logic</button>
          <span v-if="maxLogicSlots > 0 && maxLogicSlots < 900" class="logic-slot-info">
            {{ logicCount }}/{{ maxLogicSlots }} used
          </span>
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
              <button class="btn-sm btn-danger" :disabled="deleting" @click="handleDelete(l.id)">{{ deleting ? '...' : 'Delete' }}</button>
            </div>
          </div>
        </div>
      </template>

      <div v-if="showForm" class="modal-overlay" @click.self="cancelForm">
        <div class="modal modal-sm">
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
                <input v-model="form.inputs[i].name" type="text" class="form-input col-flex-1" placeholder="name" />
                <input v-model="form.inputs[i].type" type="text" class="form-input col-flex-15" placeholder="string, int, User..." />
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

      <div v-if="showUpgradeModal" class="modal-overlay" @click.self="closeUpgradeModal">
        <div class="modal modal-sm">
          <h2 class="modal-title">Upgrade Required</h2>
          <p class="upgrade-intro">
            You've reached the <strong>Free</strong> plan limit of <strong>{{ maxLogicSlots }} logics</strong>.
            <router-link to="/subscribe" class="free-banner-link" @click="closeUpgradeModal">Upgrade to Silver</router-link>
            or higher to create unlimited logic definitions.
          </p>
          <div class="modal-actions">
            <button class="btn-secondary" @click="closeUpgradeModal">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>
