<template>
  <div class="container mt-4">
    <h2>{{ isEdit ? "Redigera Övning" : "Skapa Ny Övning" }}</h2>

    <form @submit.prevent="saveExercise">

      <!-- Titel -->
      <div class="mb-3">
        <label class="form-label">Titel</label>
        <input v-model="exercise.title" type="text" class="form-control" required />
      </div>

      <!-- Beskrivning -->
      <div class="mb-3">
        <label class="form-label">Beskrivning</label>
        <textarea v-model="exercise.description" class="form-control"></textarea>
      </div>

      <!-- Typ -->
      <div class="mb-3">
        <label class="form-label">Typ av övning</label>
        <select v-model="exercise.type" class="form-select" required>
          <option value="true_false">Sant/Falskt</option>
          <option value="mcq">Flervalsfrågor</option>
          <option value="ordering">Ordna meningar</option>
        </select>
      </div>

      <!-- Max XP -->
      <div class="mb-3">
        <label class="form-label">Max XP</label>
        <input v-model.number="exercise.max_xp" type="number" class="form-control" min="1" required />
      </div>

      <!-- Frågor -->
      <div class="mb-3">
        <label class="form-label">Frågor / Meningar</label>

        <div v-for="(q, idx) in exercise.questions" :key="idx" class="mb-2 p-2 border rounded">
          <input v-model="q.statement" type="text" class="form-control mb-2" placeholder="Fråga / Mening" required />

          <!-- TRUE/FALSE -->
          <div v-if="exercise.type === 'true_false'" class="mb-2">
            <select v-model="q.correct" class="form-select" required>
              <option disabled value="">Välj korrekt svar</option>
              <option value="1">Sant</option>
              <option value="0">Falskt</option>
            </select>
          </div>

          <!-- MCQ -->
          <div v-if="exercise.type === 'mcq'">
            <label class="form-label">Alternativ</label>
            <div v-for="(opt, i) in q.options" :key="i" class="d-flex mb-1">
              <input v-model="opt.text" class="form-control me-2" placeholder="Alternativ" required />
              <input type="radio" :name="'correct-' + idx" v-model="q.correct" :value="i" />
              <button class="btn btn-danger btn-sm ms-2" @click="removeOption(q, i)">X</button>
            </div>
            <button type="button" class="btn btn-secondary btn-sm mt-1" @click="addOption(q)">Lägg till alternativ</button>
          </div>

          <!-- ORDERING -->
          <div v-if="exercise.type === 'ordering'">
            <label class="form-label">Rätt position</label>
            <input v-model.number="q.correct" type="number" min="1" class="form-control" required />
          </div>

          <button type="button" class="btn btn-warning btn-sm mt-2" @click="removeQuestion(idx)">Ta bort fråga</button>
        </div>

        <button type="button" class="btn btn-primary mt-2" @click="addQuestion">Lägg till fråga</button>
      </div>

      <!-- Tilldela klasser -->
      <div class="mb-3">
        <label class="form-label">Tilldela klasser</label>
        <div class="d-flex flex-wrap">
          <div v-for="cls in classes" :key="cls.class_id" class="form-check me-3">
            <input type="checkbox" class="form-check-input" :value="cls.class_id" v-model="selectedClasses" />
            <label class="form-check-label">{{ cls.class_name }}</label>
          </div>
        </div>
      </div>

      <button class="btn btn-success">{{ isEdit ? "Spara Ändringar" : "Skapa Övning" }}</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import axios from "axios"

const router = useRouter()
const route = useRoute()

const exercise = ref({
  exercise_id: null,
  title: "",
  description: "",
  type: "true_false",
  max_xp: 25,
  questions: []
})

const classes = ref([])
const selectedClasses = ref([])
const isEdit = ref(false)

// Load classes
const loadClasses = async () => {
  const res = await axios.get("http://localhost/fragesport/api/get_classes.php", { withCredentials: true })
  classes.value = res.data.classes
}

// Add/remove questions/options
const addQuestion = () => {
  if (exercise.value.type === "mcq") {
    exercise.value.questions.push({
      statement: "",
      correct: null,
      options: [{ text: "" }]
    })
  } else if (exercise.value.type === "ordering") {
    exercise.value.questions.push({
      statement: "",
      correct: exercise.value.questions.length + 1
    })
  } else {
    exercise.value.questions.push({ statement: "", correct: "" })
  }
}

const removeQuestion = (i) => {
  exercise.value.questions.splice(i, 1)
}

const addOption = (q) => q.options.push({ text: "" })
const removeOption = (q, i) => q.options.splice(i, 1)

// Load existing exercise
const loadExercise = async (id) => {
  const res = await axios.get(`http://localhost/fragesport/api/get_exercise.php?id=${id}`, { withCredentials: true })
  const data = res.data.exercise

  exercise.value = {
    exercise_id: data.Exercise_Id,
    title: data.Title,
    description: data.Description,
    type: data.Type,
    max_xp: data.Max_XP,
    questions: data.questions.map(q => {
      if (data.Type === "mcq") {
        return {
          statement: q.Statement,
          correct: q.options.findIndex(o => o.correct == 1),
          options: q.options.map(o => ({ text: o.text }))
        }
      }
      if (data.Type === "ordering") {
        return { statement: q.Statement, correct: Number(q.Correct) }
      }
      return { statement: q.Statement, correct: q.Correct }
    })
  }

  selectedClasses.value = data.classes.map(c => c.class_id)
  isEdit.value = true
}

// Save exercise
const saveExercise = async () => {
  if (!exercise.value.title || exercise.value.questions.length === 0)
    return alert("Titel och minst en fråga krävs")

  const payload = { exercise: exercise.value, classes: selectedClasses.value }
  const res = await axios.post("http://localhost/fragesport/api/create_edit_exercise.php", payload, {
    withCredentials: true,
    headers: { "Content-Type": "application/json" }
  })

  if (res.data.success) {
    alert("Övning sparad!")
    router.push("/teacher-dashboard")
  } else {
    alert("Fel vid sparande: " + res.data.message)
  }
}

onMounted(() => {
  loadClasses()
  if (route.query.id) loadExercise(route.query.id)
})
</script>
