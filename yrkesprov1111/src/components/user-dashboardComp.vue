<template>
  <div class="dashboard-container">

    <!-- senaste resultat -->
    <div class="section-card">
      <h3 class="section-title">Senaste resultat</h3>

      <ul class="results-list">
        <li
          v-for="res in recentResults"
          :key="res.exercise_id"
          class="result-item"
          :class="{ failed: res.xp === 0 }"
        >
          <span>{{ res.title }}</span>
          <span class="xp-tag">{{ res.xp }} XP</span>
        </li>
      </ul>
    </div>

    <!-- tillgängliga övningar -->
    <div class="section-card">
      <h3 class="section-title">Tillgängliga övningar</h3>

      <div
        v-for="ex in exercises"
        :key="ex.Exercise_Id"
        class="exercise-card"
      >
        <div>
          <h4>{{ ex.Title }}</h4>
          <p class="exercise-type">{{ ex.Type.toUpperCase() }}</p>
        </div>

        <router-link
          :to="`/play-exercise?exercise_id=${ex.Exercise_Id}&class_id=${ex.class_id}`"
          class="play-btn"
        >
          Spela →
        </router-link>
      </div>

      <p v-if="exercises.length === 0" class="no-ex">Inga övningar kvar 🙌</p>
    </div>

  </div>
</template>



<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// DATA
const recentResults = ref([])
const exercises = ref([])

const loadDashboard = async () => {
  try {
    const res = await axios.get(
      'http://localhost/fragesport/api/user_progress.php',
      { withCredentials: true }
    )

    if (res.data.success) {
      recentResults.value = res.data.recent_results
      exercises.value = res.data.available_exercises
    }
  } catch (err) {
    console.error(err)
    alert('Fel vid hämtning av dashboard')
  }
}

onMounted(loadDashboard)
</script>
