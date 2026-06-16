<script setup>
import { ref, onMounted } from 'vue'
import AppHeader from '../components/AppHeader.vue'
import AdminResourceTable from '../components/AdminResourceTable.vue'
import { getAdminLogics } from '../api/admin.js'

const logics = ref([])
const loading = ref(true)
const error = ref('')

const columns = [
  { key: 'name', label: 'Name', class: 'admin-table-name' },
  { key: 'owner', label: 'Owner' },
  { key: 'description', label: 'Description', class: 'admin-table-desc' },
  { key: 'created_at', label: 'Created', class: 'admin-table-date', format: 'date' },
]

onMounted(async () => {
  try {
    const res = await getAdminLogics()
    if (res.success) logics.value = res.data.logics || []
  } catch (err) {
    error.value = err.message || 'Failed to load logics'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="admin-page">
    <AppHeader title="Admin — Logics">
      <template #right>
        <a href="/admin" class="nav-link">Dashboard</a>
        <a href="/admin/tables" class="nav-link">Tables</a>
        <a href="/admin/flows" class="nav-link">Flows</a>
        <a href="/canvas" class="nav-link">Canvas</a>
      </template>
    </AppHeader>
    <div class="page-body">
      <nav class="admin-resource-nav">
        <router-link to="/admin" class="admin-resource-card">
          <span class="admin-resource-icon">&#128202;</span>
          <span class="admin-resource-label">Dashboard</span>
          <span class="admin-resource-arrow">&rarr;</span>
        </router-link>
        <router-link to="/admin/logics" class="admin-resource-card admin-resource-card--active">
          <span class="admin-resource-icon">&#9881;</span>
          <span class="admin-resource-label">Logics</span>
          <span class="admin-resource-arrow">&rarr;</span>
        </router-link>
        <router-link to="/admin/tables" class="admin-resource-card">
          <span class="admin-resource-icon">&#128202;</span>
          <span class="admin-resource-label">Tables</span>
          <span class="admin-resource-arrow">&rarr;</span>
        </router-link>
        <router-link to="/admin/flows" class="admin-resource-card">
          <span class="admin-resource-icon">&#128196;</span>
          <span class="admin-resource-label">Flows</span>
          <span class="admin-resource-arrow">&rarr;</span>
        </router-link>
      </nav>
      <div v-if="error" class="error-msg page-error">{{ error }}</div>
      <section class="admin-section">
        <h3 class="admin-section-title">All Logic Definitions ({{ logics.length }})</h3>
        <AdminResourceTable
          :items="logics"
          :loading="loading"
          :columns="columns"
          empty-text="No logics created yet."
        />
      </section>
    </div>
  </div>
</template>
