<template>
  <div class="container mt-4">
    <h2>Övningar i {{ className }}</h2>
    <button class="btn btn-success mb-3" @click="createExercise">Skapa Ny Övning</button>
    <div class="list-group">
      <div v-for="ex in exercises" :key="ex.Exercise_Id" class="list-group-item d-flex justify-content-between align-items-center">
        {{ ex.Title }}
        <div>
          <button class="btn btn-sm btn-primary me-2" @click="editExercise(ex)">Redigera</button>
          <button class="btn btn-sm btn-danger me-2" @click="deleteExercise(ex.Exercise_Id)">Ta bort</button>
          <button class="btn btn-sm btn-secondary" @click="playExercise(ex)">Spela</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()
const exercises = ref([])
const classId = route.query.class_id
const className = route.query.class_name

const loadExercises = async () => {
  const res = await axios.get(`http://localhost/fragesport/api/get_class_exercises.php?class_id=${classId}`, { withCredentials: true })
  exercises.value = res.data.exercises
}

const createExercise = () => {
  router.push({ 
    name: 'TeacherExerciseForm', 
    query: { class_id: classId } 
  })
}

const editExercise = (ex) => {
  router.push({ 
    name: 'TeacherExerciseForm', 
    query: { id: ex.Exercise_Id, class_id: classId } 
  })
}

const deleteExercise = async (exerciseId) => {
  if (!confirm("Är du säker på att du vill ta bort övningen?")) return;
  try {
    const res = await axios.post('http://localhost/fragesport/api/delete_exercise.php', { exercise_id: exerciseId }, { withCredentials: true });
    console.log("delete_exercise response:", res.data);
    if (res.data.success) {
      alert("Övningen har raderats.");
      loadExercises();
    } else {
      alert("Fel: " + (res.data.message || "okänt fel"));
    }
  } catch (err) {
    console.error("Network/error:", err);
    alert("Nätverksfel eller serverfel. Kolla console och PHP error_log.");
  }
}


const playExercise = (ex) => {
  router.push({ 
    name: 'PlayExercise', 
    query: { exercise_id: ex.Exercise_Id } 
  })
}

onMounted(() => {
  loadExercises()
})
</script>
