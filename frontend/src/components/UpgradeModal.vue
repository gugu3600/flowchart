<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  visible: { type: Boolean, default: false },
  currentTier: { type: String, default: 'Free' },
})

defineEmits(['close'])

const router = useRouter()

const tierRank = { Free: 0, Silver: 1, Gold: 2, Platinum: 3 }

const tiers = [
  {
    name: 'Free',
    features: ['Browse & edit canvas', 'Drag & drop nodes', 'Connect nodes'],
    rank: 0,
  },
  {
    name: 'Silver',
    features: ['Everything in Free', 'Unlimited save slots', 'Persist flowcharts'],
    rank: 1,
  },
  {
    name: 'Gold',
    features: ['Everything in Silver', 'SQL DDL generation', 'Export schemas'],
    rank: 2,
  },
  {
    name: 'Platinum',
    features: ['Everything in Gold', 'Visual folder mapping', 'Workspace architecture'],
    rank: 3,
  },
]

const currentRank = computed(() => tierRank[props.currentTier] ?? 0)

function isUpgrade(tierName) {
  return (tierRank[tierName] ?? 0) > currentRank.value
}

function goToSubscribe(tierName) {
  router.push(`/subscribe?tier=${tierName.toLowerCase()}`)
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
            <button
              v-if="isUpgrade(tier.name)"
              class="btn-sm btn-primary subscribe-btn"
              @click="goToSubscribe(tier.name)"
            >
              Subscribe
            </button>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn-secondary" @click="$emit('close')">Close</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.tier-card-disabled {
  opacity: 0.5;
}

.subscribe-btn {
  margin-top: 12px;
  width: 100%;
}
</style>
