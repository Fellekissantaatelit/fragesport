<template>
  <div>
    <p>{{ question.Statement }}</p>
    <div class="list-group">
      <button
        v-for="opt in question.options"
        :key="opt.Option_Id"
        @click="select(opt.Option_Id)"
        :class="['list-group-item', modelValue === opt.Option_Id ? 'active' : '']"
      >
        {{ opt.text }}
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  question: Object,
  modelValue: [Number, String, null]
})
const emit = defineEmits(['update:modelValue'])

const select = id => emit('update:modelValue', id)
</script>
