<script setup>
import { ref } from 'vue'
import { useUserStore } from '../stores/useUserStore.js'
import UpgradeModal from './UpgradeModal.vue'

const { state, logout, tierLabel, tierColor, isPlatinum } = useUserStore()

const showUpgrade = ref(false)

function openUpgrade() {
  if (!isPlatinum.value) {
    showUpgrade.value = true
  }
}
</script>

<template>
  <div v-if="state.user" class="user-profile">
    <span class="user-name">{{ state.user.name }}</span>
    <button
      class="tier-badge"
      :class="{ 'tier-badge-clickable': !isPlatinum }"
      :style="{ background: tierColor }"
      :title="isPlatinum ? 'Highest tier' : 'Click to upgrade'"
      @click="openUpgrade"
    >
      {{ tierLabel }}
    </button>
    <button class="btn-logout" @click="logout">Logout</button>
  </div>

  <UpgradeModal
    :visible="showUpgrade"
    :current-tier="tierLabel"
    @close="showUpgrade = false"
  />
</template>
