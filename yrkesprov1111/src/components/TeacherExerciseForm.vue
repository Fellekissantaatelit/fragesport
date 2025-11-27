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
          <option value="match">Para ihop</option>
          <option value="ordering">Ordna meningar</option>
          <option value="fill_blank">Fyll i luckor</option>
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
          <input v-model="q.statement" type="text" class="form-control mb-1" placeholder="Fråga / Mening" required />

          <!-- True/False -->
          <div v-if="exercise.type === 'true_false'" class="mb-1">
            <select v-model="q.correct" class="form-select" required>
              <option value="" disabled>Välj korrekt svar</option>
              <option value="1">Sant</option>
              <option value="0">Falskt</option>
            </select>
          </div>

          <!-- Fill-in-the-blank / MCQ / Match -->
          <div v-if="exercise.type === 'fill_blank' || exercise.type === 'mcq' || exercise.type === 'match'">
            <label>Alternativ</label>
            <div v-for="(opt, i) in q.options" :key="i" class="d-flex mb-1">
              <input v-model="opt.text" class="form-control me-2" placeholder="Alternativ" required />
              <input type="checkbox" v-model="opt.correct" class="form-check-input mt-2" /> Rätt
              <button type="button" class="btn btn-danger btn-sm ms-2" @click="removeOption(q, i)">X</button>
            </div>
            <button type="button" class="btn btn-secondary btn-sm mt-1" @click="addOption(q)">Lägg till alternativ</button>
          </div>

          <!-- Ordering -->
          <div v-if="exercise.type === 'ordering'">
            <input v-model.number="q.correct" type="number" class="form-control mb-1" placeholder="Korrekt plats" required />
          </div>

          <button type="button" class="btn btn-warning btn-sm mt-2" @click="removeQuestion(idx)">Ta bort fråga</button>
        </div>
        <button type="button" class="btn btn-primary mt-2" @click="addQuestion">Lägg till fråga</button>
      </div>

      <!-- Tilldelade klasser -->
      <div class="mb-3">
        <label class="form-label">Tilldela klasser</label>
        <div class="d-flex flex-wrap">
          <div v-for="cls in classes" :key="cls.class_id" class="form-check me-3">
            <input type="checkbox" class="form-check-input" :id="'cls-'+cls.class_id" :value="cls.class_id" v-model="selectedClasses" />
            <label class="form-check-label" :for="'cls-'+cls.class_id">{{ cls.class_name }}</label>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-success">{{ isEdit ? "Spara Ändringar" : "Skapa Övning" }}</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue"
import { useRoute, useRouter } from "vue-router"
import axios from "axios"

const router = useRouter()
const route = useRoute()

const exercise = ref({ 
  exercise_id: null,
  title: "",
  description: "",
  type: "true_false",
  max_xp: 25,   // <-- NYTT
  questions: [] 
})
const classes = ref([])
const selectedClasses = ref([])
const isEdit = ref(false)

// Load classes
const loadClasses = async () => {
  try {
    const res = await axios.get("http://localhost/fragesport/api/get_classes.php", { withCredentials: true })
    classes.value = res.data.classes
  } catch (err) {
    console.error(err)
    alert("Fel vid hämtning av klasser")
  }
}

// Load exercise for edit
const loadExercise = async (id) => {
  try {
    const res = await axios.get(`http://localhost/fragesport/api/get_exercise.php?id=${id}`, { withCredentials: true })
    const data = res.data.exercise

    exercise.value = {
       exercise_id: data.Exercise_Id,
      title: data.Title,
      description: data.Description,
      type: data.Type,
      max_xp: data.Max_XP,
      questions: data.questions.map(q => {
        if (["mcq","match","fill_blank"].includes(data.Type)) {
    return { 
        statement: q.Statement, 
        options: q.options.map(o => ({
            text: o.text,
            correct: o.correct == 1 ? true : false
        }))
    }
}
console.log("Loaded options:", data.questions);
        if (data.Type === "ordering") {
          return { statement: q.Statement, correct: Number(q.Correct) || 1 }
        }
        return { statement: q.Statement, correct: q.Correct }
      })
    }

    selectedClasses.value = data.classes.map(c => c.class_id)
    isEdit.value = true
  } catch (err) {
    console.error(err)
    alert("Fel vid hämtning av övningen")
  }
}


// Questions / options
const addQuestion = () => {
  if (["mcq","match","fill_blank"].includes(exercise.value.type)) {
    exercise.value.questions.push({ statement: "", options: [{ text: "", correct: false }] })
  } else if (exercise.value.type === "ordering") {
    exercise.value.questions.push({ statement: "", correct: exercise.value.questions.length + 1 })
  } else {
    exercise.value.questions.push({ statement: "", correct: "" })
  }
}
const removeQuestion = idx => exercise.value.questions.splice(idx, 1)
const addOption = q => q.options.push({ text: "", correct: false })
const removeOption = (q, i) => q.options.splice(i, 1)

// Watch type change
watch(() => exercise.value.type, newType => {
  exercise.value.questions.forEach((q, idx) => {
    if (["mcq","match","fill_blank"].includes(newType)) { if (!q.options) q.options = [{ text:"", correct:false }]; delete q.correct }
    else if (newType === "ordering") { q.correct = idx + 1; delete q.options }
    else { q.correct = ""; delete q.options }
  })
})

// Save
const saveExercise = async () => {
  if (!exercise.value.title || !exercise.value.questions.length) { alert("Titel och minst en fråga krävs"); return }

  try {
    const payload = { exercise: exercise.value, classes: selectedClasses.value }
    const res = await axios.post("http://localhost/fragesport/api/create_edit_exercise.php", payload, {
      withCredentials: true,
      headers: { "Content-Type": "application/json" }
    })

    if (res.data.success) {
      alert("Övning sparad!")
      router.push("/teacher-dashboard")
    } else {
      console.error(res.data)
      alert("Fel vid sparande: " + res.data.message)
    }
  } catch (err) {
    console.error(err)
    alert("Fel vid kontakt med servern")
  }
}

onMounted(() => {
  loadClasses()
  if (route.query.id) loadExercise(route.query.id)
})
</script>
