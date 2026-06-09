<script setup>
defineProps({
  visible: { type: Boolean, default: false },
  currentTier: { type: String, default: 'Free' },
})

defineEmits(['close'])

const tiers = [
  {
    name: 'Free',
    features: ['Browse & edit canvas', 'Drag & drop nodes', 'Connect nodes'],
  },
  {
    name: 'Silver',
    features: ['Everything in Free', 'Unlimited save slots', 'Persist flowcharts'],
  },
  {
    name: 'Gold',
    features: ['Everything in Silver', 'SQL DDL generation', 'Export schemas'],
  },
  {
    name: 'Platinum',
    features: ['Everything in Gold', 'Visual folder mapping', 'Workspace architecture'],
  },
]

function tierClass(name) {
  return {
    'tier-card-current': name === 'Platinum',
  }
}
</script>

<template>
  <div v-if="visible" class="modal-overlay" @click.self="$emit('close')">
    <div class="modal">
      <h2 class="modal-title">Upgrade Your Plan</h2>

      <div class="modal-body">
        <p class="upgrade-intro">
          You're currently on the <strong>{{ currentTier }}</strong> plan.
          Upgrade to unlock more features.
        </p>

        <div class="tier-grid">
          <div
            v-for="tier in tiers"
            :key="tier.name"
            class="tier-card"
            :class="{
              'tier-card-current': tier.name === currentTier,
              'tier-card-disabled': tier.name === currentTier,
            }"
          >
            <h3 class="tier-card-name">{{ tier.name }}</h3>
            <ul class="tier-card-features">
              <li v-for="feat in tier.features" :key="feat" class="tier-card-feat">{{ feat }}</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn-secondary" @click="$emit('close')">Close</button>
      </div>
    </div>
  </div>
</template>
