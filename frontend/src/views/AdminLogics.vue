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
        <a href="/canvas" class="nav-link">Canvas</a>
      </template>
    </AppHeader>
    <div class="page-body">
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
