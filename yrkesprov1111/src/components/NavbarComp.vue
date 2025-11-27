<template>
  <nav class="navbar navbar-light bg-light p-3 shadow">
    <div class="container">
      <router-link class="navbar-brand" to="/user-dashboard">Frågesport</router-link>
      <div>
        <router-link v-if="!isLoggedIn" class="btn btn-primary btn-sm" to="/">Login</router-link>
        <button v-if="isLoggedIn" class="btn btn-danger btn-sm" @click="logout">Logout</button>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const isLoggedIn = ref(false)

const checkSession = async () => {
  try {
    const res = await axios.get('http://localhost/fragesport/api/auth.php', { withCredentials: true })
    isLoggedIn.value = res.data.loggedIn
  } catch (err) {
    console.error(err)
    isLoggedIn.value = false
  }
}

onMounted(checkSession)

const logout = async () => {
  try {
    const res = await axios.post('http://localhost/fragesport/api/logout.php', {}, { withCredentials: true })
    if (res.data.success) {
      isLoggedIn.value = false
      router.push('/')
    }
  } catch (err) {
    console.error(err)
    alert('Fel vid kontakt med servern')
  }
}
</script>
