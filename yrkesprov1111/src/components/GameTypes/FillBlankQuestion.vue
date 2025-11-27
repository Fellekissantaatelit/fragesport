<template>
  <div>
    <p v-html="formattedStatement"></p>
  </div>
</template>

<script setup>
import { reactive, computed, watch } from 'vue'

const props = defineProps({
  question: Object,
  modelValue: Object
})
const emit = defineEmits(['update:modelValue'])

const answer = reactive(props.modelValue || {})

watch(answer, () => emit('update:modelValue', { ...answer }), { deep: true })

const formattedStatement = computed(() => {
  let stmt = props.question.Statement
  props.question.options?.forEach((_, i) => {
    stmt = stmt.replace('____', `<input type="text" v-model="answer[${i}]" class="form-control d-inline w-auto mx-1">`)
  })
  return stmt
})
</script>
