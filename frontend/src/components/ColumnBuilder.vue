<script setup>
const DATA_TYPE_GROUPS = [
  {
    label: 'Numeric',
    types: ['TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'BIGINT', 'DECIMAL(10,2)', 'FLOAT', 'DOUBLE', 'BIT', 'BOOLEAN'],
  },
  {
    label: 'String',
    types: ['CHAR(1)', 'CHAR(10)', 'VARCHAR(50)', 'VARCHAR(100)', 'VARCHAR(255)', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT', 'BINARY', 'VARBINARY(255)', 'TINYBLOB', 'BLOB', 'MEDIUMBLOB', 'LONGBLOB', 'ENUM', 'SET'],
  },
  {
    label: 'Date/Time',
    types: ['DATE', 'TIME', 'DATETIME', 'TIMESTAMP', 'YEAR'],
  },
  {
    label: 'JSON',
    types: ['JSON'],
  },
  {
    label: 'Spatial',
    types: ['GEOMETRY', 'POINT', 'LINESTRING', 'POLYGON'],
  },
]

const ALL_TYPES = DATA_TYPE_GROUPS.flatMap((g) => g.types)

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
      <div class="col-type-wrap">
        <select
          :value="ALL_TYPES.includes(col.type) ? col.type : '__custom__'"
          class="col-input col-select"
          @change="setField(i, 'type', $event.target.value)"
        >
          <option v-if="!ALL_TYPES.includes(col.type)" value="__custom__" disabled>{{ col.type }}</option>
          <optgroup v-for="group in DATA_TYPE_GROUPS" :key="group.label" :label="group.label">
            <option v-for="type in group.types" :key="type" :value="type">{{ type }}</option>
          </optgroup>
        </select>
        <input
          v-if="!ALL_TYPES.includes(col.type)"
          :value="col.type"
          type="text"
          class="col-input col-type-custom"
          placeholder="VARCHAR(255)"
          @input="setField(i, 'type', $event.target.value)"
        />
      </div>
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
.col-type-wrap {
  display: flex;
  flex: 1.5;
  gap: 0.25rem;
  min-width: 80px;
}

.col-select {
  flex: 1;
  min-width: 0;
  cursor: pointer;
  appearance: auto;
  -webkit-appearance: auto;
  -moz-appearance: auto;
}

.col-type-custom {
  flex: 1;
  min-width: 60px;
}
</style>
