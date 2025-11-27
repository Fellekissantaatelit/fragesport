<template>
  <div class="container mt-4">
   
    <!-- Total XP -->
    <div class="mb-4">
      <h4>Total XP: {{ totalXP }}</h4>
      <div class="progress">
        <div class="progress-bar" :style="{ width: progressPercent + '%' }">
          Level {{ currentLevel.Level_Name }}
        </div>
      </div>
    </div>

    <!-- Senaste resultat -->
    <div class="mb-4">
      <h4>Senaste resultat</h4>
      <ul class="list-group">
        <li v-for="res in recentResults" :key="res.exercise_id" class="list-group-item d-flex justify-content-between">
          {{ res.title }}
          <span>Poäng: {{ res.xp }}</span>
        </li>
      </ul>
    </div>

    <!-- Tillgängliga övningar -->
    <div>
      <h4>Tillgängliga övningar</h4>
      <ul class="list-group">
        <li v-for="ex in exercises" :key="ex.Exercise_Id" class="list-group-item d-flex justify-content-between align-items-center">
          {{ ex.Title }}
          <router-link 
            :to="`/play-exercise?id=${ex.Exercise_Id}&class_id=${ex.class_id}`" 
            class="btn btn-sm btn-primary"
          >
            Spela
          </router-link>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const totalXP = ref(0)
const recentResults = ref([])
const exercises = ref([])
const levels = ref([])

// Beräkna nuvarande level baserat på XP
const currentLevel = computed(() => {
  let lvl = { Level_Name: 'Beginner', XP_Required: 0 }
  for (const l of levels.value) {
    if (totalXP.value >= l.XP_Required) lvl = l
  }
  return lvl
})

// Beräkna progress mot nästa level
const progressPercent = computed(() => {
  const current = currentLevel.value
  const next = levels.value.find(l => l.XP_Required > current.XP_Required) || { XP_Required: current.XP_Required + 100 }
  const percent = ((totalXP.value - current.XP_Required) / (next.XP_Required - current.XP_Required)) * 100
  return Math.min(100, percent)
})

const loadDashboard = async () => {
  try {
    const res = await axios.get('http://localhost/fragesport/api/user_progress.php', { withCredentials: true })
    if(res.data.success){
      totalXP.value = res.data.total_xp
      recentResults.value = res.data.recent_results
      exercises.value = res.data.available_exercises
      levels.value = res.data.levels
    }
  } catch (err) {
    console.error(err)
    alert('Fel vid hämtning av dashboard')
  }
}

onMounted(loadDashboard)
</script>
