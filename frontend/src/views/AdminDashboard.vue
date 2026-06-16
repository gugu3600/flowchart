<script setup>
import { ref, onMounted } from 'vue'
import { useUserStore } from '../stores/useUserStore.js'
import AppHeader from '../components/AppHeader.vue'
import StatCard from '../components/StatCard.vue'
import { getUsers, updateUserRoles, upgradeUser, deleteUser, getTiers } from '../api/admin.js'
import apiClient from '../api/apiClient.js'

const { state: auth, tierLabel, tierColor } = useUserStore()

const stats = ref([])
const users = ref([])
const meta = ref(null)
const loading = ref(false)
const error = ref('')

const showRoleModal = ref(false)
const editingUser = ref(null)
const selectedRoles = ref([])

const showDeleteConfirm = ref(false)
const deletingUser = ref(null)

const tiers = ref([])

const availableTiers = [
  { role: 'free', label: 'Free', color: '#64748b' },
  { role: 'silver', label: 'Silver', color: '#94a3b8' },
  { role: 'gold', label: 'Gold', color: '#eab308' },
  { role: 'platinum', label: 'Platinum', color: '#a855f7' },
]

onMounted(async () => {
  await Promise.all([fetchStats(), fetchUsers(), fetchTiers()])
})

async function fetchStats() {
  try {
    const res = await apiClient.get('/stats')
    if (res.success) {
      stats.value = res.data.stats || []
    }
  } catch {
    stats.value = []
  }
}

async function fetchUsers() {
  loading.value = true
  error.value = ''
  try {
    const res = await getUsers()
    if (res.success) {
      users.value = res.data.users || []
      meta.value = res.data.meta || null
    }
  } catch (err) {
    error.value = err.message || 'Failed to load users'
  } finally {
    loading.value = false
  }
}

function openRoleModal(user) {
  editingUser.value = user
  selectedRoles.value = [...(user.roles || [])]
  showRoleModal.value = true
}

async function saveRoles() {
  if (!editingUser.value) return
  try {
    const res = await updateUserRoles(editingUser.value.id, selectedRoles.value)
    if (res.success) {
      const idx = users.value.findIndex(u => u.id === editingUser.value.id)
      if (idx !== -1) {
        users.value[idx] = res.data.user
      }
    }
    showRoleModal.value = false
    editingUser.value = null
  } catch (err) {
    error.value = err.message || 'Failed to update roles'
  }
}

function confirmDelete(user) {
  deletingUser.value = user
  showDeleteConfirm.value = true
}

async function doDelete() {
  if (!deletingUser.value) return
  try {
    const res = await deleteUser(deletingUser.value.id)
    if (res.success) {
      users.value = users.value.filter(u => u.id !== deletingUser.value.id)
    }
    showDeleteConfirm.value = false
    deletingUser.value = null
  } catch (err) {
    error.value = err.message || 'Failed to delete user'
  }
}

const showUpgradeModal = ref(false)
const upgradingUser = ref(null)
const upgradingTo = ref('')
const upgradeConfirmed = ref(false)

function tierColorFor(roles) {
  const tier = availableTiers.find(t => roles.includes(t.role))
  return tier ? tier.color : '#64748b'
}

function tierLabelFor(roles) {
  const tier = availableTiers.find(t => roles.includes(t.role))
  return tier ? tier.label : 'Free'
}

async function fetchTiers() {
  try {
    const res = await getTiers()
    if (res.success) {
      tiers.value = res.data.tiers || []
    }
  } catch {
    // ignore
  }
}

function openUpgradeModal(user) {
  upgradingUser.value = user
  upgradingTo.value = ''
  upgradeConfirmed.value = false
  showUpgradeModal.value = true
}

async function confirmUpgrade() {
  if (!upgradingUser.value || !upgradingTo.value) return
  try {
    const res = await upgradeUser(upgradingUser.value.id, upgradingTo.value)
    if (res.success) {
      const idx = users.value.findIndex(u => u.id === upgradingUser.value.id)
      if (idx !== -1) {
        users.value[idx] = res.data.user
      }
      upgradeConfirmed.value = true
    }
  } catch (err) {
    error.value = err.message || 'Failed to upgrade tier'
  }
}
</script>

<template>
  <div class="admin-page">
    <AppHeader title="Admin Dashboard">
      <template #right>
        <a href="/canvas" class="nav-link">Canvas</a>
        <a href="/help" class="nav-link">Help</a>
      </template>
    </AppHeader>

    <div class="page-body">
      <div v-if="error" class="error-msg page-error">{{ error }}</div>

      <div class="admin-welcome">
        <h2 class="admin-welcome-title">Welcome, {{ auth.user?.name }}</h2>
        <span class="tier-badge" :style="{ background: tierColor }">{{ tierLabel }}</span>
      </div>

      <div class="admin-stats">
        <StatCard
          v-for="s in stats"
          :key="s.label"
          :label="s.label"
          :value="s.value"
          :icon="s.icon"
          :color="s.color || '#3b82f6'"
        />
      </div>

      <section class="admin-section">
        <h3 class="admin-section-title">Users</h3>

        <div v-if="loading" class="page-loading">Loading users...</div>

        <div v-else-if="users.length === 0" class="page-empty">No users found.</div>

        <table v-else class="admin-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Tier</th>
              <th>Roles</th>
              <th>Joined</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td class="admin-table-name">{{ user.name }}</td>
              <td class="admin-table-email">{{ user.email }}</td>
              <td>
                <span
                  class="tier-badge"
                  :style="{ background: tierColorFor(user.roles) }"
                >{{ tierLabelFor(user.roles) }}</span>
              </td>
              <td class="admin-table-roles">{{ user.roles?.join(', ') }}</td>
              <td class="admin-table-date">{{ new Date(user.created_at).toLocaleDateString() }}</td>
              <td class="admin-table-actions">
                <button class="btn-sm" style="background:#3b82f6;color:#fff" @click="openRoleModal(user)">
                  Roles
                </button>
                <button
                  class="btn-sm"
                  style="background:#10b981;color:#fff"
                  @click="openUpgradeModal(user)"
                  :disabled="user.roles?.includes('super-admin')"
                >Upgrade</button>
                <button
                  class="btn-sm btn-danger"
                  @click="confirmDelete(user)"
                  :disabled="auth.user?.id === user.id"
                >Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <section class="admin-section">
        <h3 class="admin-section-title">Resources</h3>
        <div class="admin-resource-nav">
          <a href="/admin/logics" class="admin-resource-card">
            <span class="admin-resource-icon">&#9881;</span>
            <span class="admin-resource-label">Logics</span>
            <span class="admin-resource-arrow">&rarr;</span>
          </a>
          <a href="/admin/tables" class="admin-resource-card">
            <span class="admin-resource-icon">&#128202;</span>
            <span class="admin-resource-label">Tables</span>
            <span class="admin-resource-arrow">&rarr;</span>
          </a>
          <a href="/admin/flows" class="admin-resource-card">
            <span class="admin-resource-icon">&#128196;</span>
            <span class="admin-resource-label">Flows</span>
            <span class="admin-resource-arrow">&rarr;</span>
          </a>
        </div>
      </section>
    </div>

      <section class="admin-section">
        <h3 class="admin-section-title">Tier Definitions</h3>
        <div class="tier-grid">
          <div
            v-for="tier in tiers"
            :key="tier.name"
            class="tier-card"
          >
            <h4 class="tier-card-name">{{ tier.name }}</h4>
            <ul class="tier-card-features">
              <li
                v-for="perm in tier.permissions"
                :key="perm"
                class="tier-card-feat"
              >{{ perm }}</li>
              <li v-if="!tier.permissions?.length" class="tier-card-feat" style="opacity:0.5">No permissions</li>
            </ul>
          </div>
        </div>
      </section>

    <!-- Upgrade Modal -->
    <div v-if="showUpgradeModal" class="modal-overlay" @click.self="showUpgradeModal = false">
      <div class="modal modal-sm">
        <h3 class="modal-title">Upgrade Tier — {{ upgradingUser?.name }}</h3>

        <div v-if="!upgradeConfirmed" class="modal-body">
          <p class="admin-delete-warning">Select a new tier for this user:</p>
          <div
            v-for="tier in availableTiers"
            :key="tier.role"
            class="admin-role-option"
          >
            <input
              type="radio"
              :value="tier.role"
              v-model="upgradingTo"
              :id="'upgrade-' + tier.role"
            />
            <label :for="'upgrade-' + tier.role" style="display:flex;align-items:center;gap:0.5rem;cursor:pointer">
              <span class="tier-badge" :style="{ background: tier.color }">{{ tier.label }}</span>
              <span class="admin-role-name">{{ tier.role }}</span>
            </label>
          </div>
        </div>

        <div v-else class="modal-body">
          <div class="admin-upgrade-success">
            <span style="font-size:2rem">&#10003;</span>
            <p>Tier upgraded to <strong>{{ upgradingTo }}</strong> successfully!</p>
          </div>
        </div>

        <div class="modal-actions">
          <button v-if="!upgradeConfirmed" class="btn-secondary" @click="showUpgradeModal = false">Cancel</button>
          <button v-if="!upgradeConfirmed" class="btn-primary" :disabled="!upgradingTo" @click="confirmUpgrade">
            Confirm Upgrade
          </button>
          <button v-else class="btn-primary" @click="showUpgradeModal = false">Close</button>
        </div>
      </div>
    </div>

    <!-- Role Editing Modal -->
    <div v-if="showRoleModal" class="modal-overlay" @click.self="showRoleModal = false">
      <div class="modal modal-sm">
        <h3 class="modal-title">Edit Roles — {{ editingUser?.name }}</h3>

        <div class="modal-body">
          <label
            v-for="tier in availableTiers"
            :key="tier.role"
            class="admin-role-option"
          >
            <input
              type="checkbox"
              :value="tier.role"
              v-model="selectedRoles"
            />
            <span class="tier-badge" :style="{ background: tier.color }">{{ tier.label }}</span>
            <span class="admin-role-name">{{ tier.role }}</span>
          </label>
        </div>

        <div class="modal-actions">
          <button class="btn-secondary" @click="showRoleModal = false">Cancel</button>
          <button class="btn-primary" @click="saveRoles">Save</button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
      <div class="modal modal-sm">
        <h3 class="modal-title">Delete User</h3>
        <p class="admin-delete-warning">
          Are you sure you want to delete <strong>{{ deletingUser?.name }}</strong> ({{ deletingUser?.email }})?
          This action cannot be undone.
        </p>
        <div class="modal-actions">
          <button class="btn-secondary" @click="showDeleteConfirm = false">Cancel</button>
          <button class="btn-danger" style="padding:0.5rem 1rem;border-radius:8px;font-size:0.875rem" @click="doDelete">
            Delete User
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
