<script setup>
import { ref, onMounted } from 'vue'
import AppHeader from '../components/AppHeader.vue'
import AdminResourceTable from '../components/AdminResourceTable.vue'
import { getAdminTables } from '../api/admin.js'

const tables = ref([])
const loading = ref(true)
const error = ref('')

const columns = [
  { key: 'name', label: 'Name', class: 'admin-table-name' },
  { key: 'owner', label: 'Owner' },
  { key: 'columns', label: 'Columns', format: 'count', suffix: 'columns' },
  { key: 'created_at', label: 'Created', class: 'admin-table-date', format: 'date' },
]

onMounted(async () => {
  try {
    const res = await getAdminTables()
    if (res.success) tables.value = res.data.tables || []
  } catch (err) {
    error.value = err.message || 'Failed to load tables'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="admin-page">
    <AppHeader title="Admin — Tables">
      <template #right>
        <a href="/admin" class="nav-link">Dashboard</a>
        <a href="/admin/logics" class="nav-link">Logics</a>
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
        <router-link to="/admin/logics" class="admin-resource-card">
          <span class="admin-resource-icon">&#9881;</span>
          <span class="admin-resource-label">Logics</span>
          <span class="admin-resource-arrow">&rarr;</span>
        </router-link>
        <router-link to="/admin/tables" class="admin-resource-card admin-resource-card--active">
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
        <h3 class="admin-section-title">All Table Definitions ({{ tables.length }})</h3>
        <AdminResourceTable
          :items="tables"
          :loading="loading"
          :columns="columns"
          empty-text="No tables created yet."
        />
      </section>
    </div>
  </div>
</template>
