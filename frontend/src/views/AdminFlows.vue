<script setup>
import { ref, onMounted } from 'vue'
import AppHeader from '../components/AppHeader.vue'
import AdminResourceTable from '../components/AdminResourceTable.vue'
import { getAdminFlows } from '../api/admin.js'

const flows = ref([])
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
    const res = await getAdminFlows()
    if (res.success) flows.value = res.data.flows || []
  } catch (err) {
    error.value = err.message || 'Failed to load flows'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="admin-page">
    <AppHeader title="Admin — Flows">
      <template #right>
        <a href="/admin" class="nav-link">Dashboard</a>
        <a href="/canvas" class="nav-link">Canvas</a>
      </template>
    </AppHeader>
    <div class="page-body">
      <div v-if="error" class="error-msg page-error">{{ error }}</div>
      <section class="admin-section">
        <h3 class="admin-section-title">All Flows ({{ flows.length }})</h3>
        <AdminResourceTable
          :items="flows"
          :loading="loading"
          :columns="columns"
          empty-text="No flows created yet."
        />
      </section>
    </div>
  </div>
</template>
