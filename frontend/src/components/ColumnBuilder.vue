<script setup>
const props = defineProps({
  columns: { type: Array, required: true },
})

const emit = defineEmits(['update:columns', 'add', 'remove'])

function addColumn() {
  const updated = [...props.columns, { name: '', type: 'VARCHAR(255)', pk: false, fk: false, unique: false }]
  emit('update:columns', updated)
  emit('add')
}

function removeColumn(idx) {
  const updated = props.columns.filter((_, i) => i !== idx)
  emit('update:columns', updated)
  emit('remove', idx)
}

function setField(i, field, value) {
  const updated = props.columns.map((col, idx) =>
    idx === i ? { ...col, [field]: value } : col
  )
  emit('update:columns', updated)
}
</script>

<template>
  <div>
    <div class="columns-header">
      <span class="field-label">Columns</span>
      <button class="btn-sm btn-secondary" @click="addColumn">+ Add Column</button>
    </div>

    <div v-for="(col, i) in props.columns" :key="i" class="column-row">
      <input
        :value="col.name"
        type="text"
        class="col-input"
        placeholder="name"
        @input="setField(i, 'name', $event.target.value)"
      />
      <input
        :value="col.type"
        type="text"
        class="col-input col-type"
        placeholder="VARCHAR(255)"
        @input="setField(i, 'type', $event.target.value)"
      />
      <label class="col-check">
        <input
          :checked="col.pk"
          type="checkbox"
          @change="setField(i, 'pk', $event.target.checked)"
        /> PK
      </label>
      <label class="col-check">
        <input
          :checked="col.fk"
          type="checkbox"
          @change="setField(i, 'fk', $event.target.checked)"
        /> FK
      </label>
      <label class="col-check">
        <input
          :checked="col.unique"
          type="checkbox"
          @change="setField(i, 'unique', $event.target.checked)"
        /> UQ
      </label>
      <button class="btn-sm btn-danger" @click="removeColumn(i)">×</button>
    </div>
  </div>
</template>

<style scoped>
</style>
