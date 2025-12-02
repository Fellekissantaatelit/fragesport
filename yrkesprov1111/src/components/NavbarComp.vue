<template>
  <nav class="user-navbar">
    <div class="container d-flex justify-content-between align-items-center">

      <!-- LEVEL CARD -->
      <div v-if="isLoggedIn && level" class="level-card-nav">
        <div class="level-left">
          <!-- TEXT & PROGRESS -->
          <div>
            <div class="level-title">Level {{ currentLevelNumber }}</div>

            <div class="level-subtitle">
              {{ totalXP }} / {{ nextLevelXP }} XP
            </div>

            <div class="level-progress">
              <div class="level-progress-fill" :style="{ width: progressPercent + '%' }"></div>
            </div>
          </div>

        </div>
      </div>

      <!-- LOGO -->
      <router-link class="navbar-brand" to="/user-dashboard">
        Läsförståelse PRO MAX
      </router-link>


      <!-- LOGIN / LOGOUT -->
      <div>
        <router-link 
          v-if="!isLoggedIn" 
          class="btn btn-primary btn-sm" 
          to="/">
          Login
        </router-link>

        <button 
          v-if="isLoggedIn" 
          class="btn btn-danger btn-sm" 
          @click="logout">
          Logout
        </button>
      </div>

    </div>
  </nav>
</template>


<script setup>
import { ref, onMounted, computed } from "vue"
import { useRouter } from "vue-router"
import axios from "axios"

const router = useRouter()

const isLoggedIn = ref(false)
const totalXP = ref(0)
const level = ref(null)
const levels = ref([])
const nextLevelXP = ref(0)

// ================================
// CHECK LOGIN + LOAD USER DATA
// ================================
const checkSession = async () => {
  try {
    const res = await axios.get("http://localhost/fragesport/api/auth.php", { withCredentials: true })
    isLoggedIn.value = res.data.loggedIn

    if (isLoggedIn.value) loadLevel()
  } catch (err) {
    console.error(err)
  }
}

// ================================
// LOAD XP + LEVEL DATA
// ================================
const loadLevel = async () => {
  try {
    const res = await axios.get("http://localhost/fragesport/api/user_progress.php", { withCredentials: true })
    if (!res.data.success) return

    totalXP.value = res.data.total_xp
    levels.value = res.data.levels

    // Hitta current level
    let current = levels.value[0]
    for (const l of levels.value) {
      if (totalXP.value >= l.XP_Required) current = l
    }
    level.value = current

    // Hitta nästa
    const next = levels.value.find(l => l.XP_Required > current.XP_Required)
    nextLevelXP.value = next ? next.XP_Required : current.XP_Required + 500

  } catch (err) {
    console.error(err)
  }
}

const currentLevelNumber = computed(() => {
  if (!level.value) return 1
  return level.value.Level_Name.replace("Level ", "")
})

const progressPercent = computed(() => {
  if (!level.value) return 0
  return Math.min(
    100,
    ((totalXP.value - level.value.XP_Required) / (nextLevelXP.value - level.value.XP_Required)) * 100
  )
})

onMounted(checkSession)

// ================================
// LOGOUT
// ================================
const logout = async () => {
  try {
    const res = await axios.post("http://localhost/fragesport/api/logout.php", {}, { withCredentials: true })
    if (res.data.success) {
      isLoggedIn.value = false
      router.push("/")
    }
  } catch (err) {
    console.error(err)
    alert("Fel vid kontakt med servern")
  }
}
</script>