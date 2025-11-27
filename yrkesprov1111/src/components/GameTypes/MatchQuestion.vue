<template>
  <div>
    <p>{{ question.Statement }}</p>
    <div v-for="(left, index) in question.left_items" :key="index" class="mb-2 d-flex align-items-center gap-2">
      <span class="me-2">{{ left }}</span>
      <select class="form-select w-auto" v-model="answer[index]">
        <option disabled value="">Välj</option>
        <option v-for="right in question.right_items" :key="right" :value="right">{{ right }}</option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  question: Object,
  modelValue: Object
})
const emit = defineEmits(['update:modelValue'])

const answer = reactive(props.modelValue || {})

watch(answer, () => emit('update:modelValue', { ...answer }), { deep: true })
</script>
