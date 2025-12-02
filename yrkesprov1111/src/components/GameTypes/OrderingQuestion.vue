<template>
  <div class="ordering-container">

    <p class="instructions">Dra orden till rätt ordning:</p>

    <ul class="drag-list">
      <li v-for="(opt, idx) in localOrder"
          :key="opt.Option_Id"
          class="drag-item"
          draggable="true"
          @dragstart="dragStart(idx)"
          @dragover.prevent
          @drop="dropItem(idx)">
        {{ opt.text }}
      </li>
    </ul>

  </div>
</template>

<script setup>
import { ref, watch } from "vue"

const props = defineProps({
  question: Object,
  modelValue: Array
})

const emit = defineEmits(["update:modelValue"])

// Start order from props OR default
const localOrder = ref(
  props.modelValue?.length
    ? [...props.modelValue]
    : [...props.question.options]
)

let draggedIndex = null

const dragStart = (index) => {
  draggedIndex = index
}

const dropItem = (dropIndex) => {
  if (draggedIndex === null) return

  const moved = localOrder.value.splice(draggedIndex, 1)[0]
  localOrder.value.splice(dropIndex, 0, moved)

  draggedIndex = null

  // Send only Option_Id in final order
  emit(
    "update:modelValue",
    localOrder.value.map(o => o.Option_Id)
  )
}
</script>

<style scoped>
.ordering-container {
  text-align: center;
}

.instructions {
  font-size: 1.1rem;
  margin-bottom: 12px;
  font-weight: 600;
}

.drag-list {
  list-style: none;
  padding: 0;
  margin: 0 auto;
  width: 80%;
}

.drag-item {
  background: #f7f7f9;
  border: 2px solid #ddd;
  padding: 12px;
  margin-bottom: 8px;
  border-radius: 10px;
  cursor: grab;
  font-size: 1.1rem;
  transition: transform 0.15s
}

.drag-item:active {
  cursor: grabbing;
}

.drag-item:hover {
  background: #e9f2ff;
  transform: scale(1.02);
}
</style>
