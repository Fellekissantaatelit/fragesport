<template>
  <div>
    <p>{{ question.Statement }}</p>
    <button class="btn btn-primary me-2" @click="select(true)">Sant</button>
    <button class="btn btn-secondary" @click="select(false)">Falskt</button>
  </div>
</template>

<script setup>
const props = defineProps({
  question: Object,
  modelValue: Boolean
})
const emit = defineEmits(['update:modelValue'])

const select = val => emit('update:modelValue', val)
</script>
