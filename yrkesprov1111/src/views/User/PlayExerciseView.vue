<template>
  <div class="container mt-4" v-if="exercise">
    <h3>{{ exercise.Title }}</h3>
    <p>{{ exercise.Description }}</p>

    <!-- Frågeindikator -->
    <p class="text-muted">Fråga {{ currentIndex + 1 }} av {{ exercise.questions.length }}</p>

    <!-- Nuvarande fråga -->
    <div v-if="currentQuestion" class="mb-3 p-3 border rounded bg-light">
      <component
        :is="questionComponent(currentQuestion.Type)"
        :question="currentQuestion"
        :model-value="answers[currentQuestion.Question_Id]"
        @update:modelValue="val => saveAnswer(currentQuestion.Question_Id, val)"
      />
    </div>

    <!-- Navigering -->
    <div class="d-flex gap-2 mb-3">
      <button class="btn btn-secondary" @click="prevQuestion" :disabled="currentIndex === 0">Föregående</button>
      <button class="btn btn-primary" @click="nextQuestion" :disabled="currentIndex === exercise.questions.length - 1">Nästa</button>
      <button class="btn btn-success ms-auto" v-if="currentIndex === exercise.questions.length - 1" @click="submitExercise">Slutför</button>
    </div>

    <!-- DEBUG-PANEL -->
    <div class="p-3 border rounded bg-light">
      <h5>Debug-panel</h5>
      <p><strong>Nuvarande svar:</strong></p>
      <pre>{{ answers }}</pre>
      <p v-if="backendResponse"><strong>Backend-respons:</strong></p>
      <pre v-if="backendResponse">{{ backendResponse }}</pre>
    </div>
  </div>

  <div v-else class="text-center mt-4">
    <p>Loading...</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

// Frågetyper
import TrueFalseQuestion from '@/components/GameTypes/TrueFalseQuestion.vue'
import MCQQuestion from '@/components/GameTypes/MCQQuestion.vue'
import MatchQuestion from '@/components/GameTypes/MatchQuestion.vue'
import OrderingQuestion from '@/components/GameTypes/OrderingQuestion.vue'
import FillBlankQuestion from '@/components/GameTypes/FillBlankQuestion.vue'

const route = useRoute()
const exercise = ref(null)
const currentIndex = ref(0)
const answers = ref({})
const backendResponse = ref(null)  // <-- För debug

// Dynamisk komponentkarta
const questionComponent = (type) => {
  switch(type){
    case 'true_false': return TrueFalseQuestion
    case 'mcq': return MCQQuestion
    case 'match': return MatchQuestion
    case 'ordering': return OrderingQuestion
    case 'fill_blank': return FillBlankQuestion
    default: return null
  }
}

// Nuvarande fråga
const currentQuestion = computed(() => exercise.value?.questions[currentIndex.value] ?? null)

// Ladda övning
const loadExercise = async () => {
  try {
    const exerciseId = route.query.id
    if(!exerciseId) return alert('Exercise ID saknas!')

    const res = await axios.get(`http://localhost/fragesport/api/get_exercise.php?id=${exerciseId}`, { withCredentials: true })
    console.log('get_exercise response:', res.data)
    if(res.data.success){
      exercise.value = res.data.exercise
      exercise.value.questions = exercise.value.questions.map(q => ({
        ...q,
        Type: q.Type || exercise.value.Type,
        options: q.options || [],
        Correct: q.Correct ?? null
      }))
    } else {
      alert(res.data.message)
    }
  } catch(err){
    console.error(err)
    alert('Fel vid hämtning av övning')
  }
}

// Spara svar
const saveAnswer = (questionId, answer) => {
  answers.value[questionId] = answer
  console.log('Sparade svar:', answers.value)
}

// Navigering
const nextQuestion = () => {
  if(currentIndex.value < exercise.value.questions.length - 1) currentIndex.value++
}
const prevQuestion = () => {
  if(currentIndex.value > 0) currentIndex.value--
}

// Skicka svar
const submitExercise = async () => {
  if(!exercise.value) return
  try {
    const classId = route.query.class_id
    const res = await axios.post('http://localhost/fragesport/api/submit_result.php', {
      exercise_id: exercise.value.Exercise_Id,
      class_id: classId,
      answers: answers.value
    }, { withCredentials: true })
    
    console.log('submit_result response:', res.data)
    backendResponse.value = res.data  

    if(res.data.success){
      alert(`Du fick ${res.data.xp_earned} XP! (${res.data.percent_correct}% korrekt)`)
    } else {
      alert(res.data.message)
    }
  } catch(err){
    console.error(err)
    alert('Fel vid skickande av svar')
  }
}

onMounted(loadExercise)
</script>

<style scoped>
.container {
  max-width: 800px;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
