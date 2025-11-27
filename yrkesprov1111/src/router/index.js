import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'

// --- User Views ---
import LoginView from '@/views/LoginView.vue'
import Dashboard from '@/views/User/DashboardView.vue'
import PlayExercise from '@/views/User/PlayExerciseView.vue'

// --- Teacher Views ---
import TeacherPanel from '@/views/Teacher/TeacherPanelView.vue'
import TeacherClassExercises from '@/views/Teacher/TeacherClassExercisesView.vue'
import TeacherExerciseForm from '@/views/Teacher/ExerciseView.vue'

// --- Admin Views ---
import AdminPanel from '@/views/Admin/AdminPanelView.vue'

const routes = [
  { path: '/', name: 'Login', component: LoginView },

  // User
  { path: '/user-dashboard', name: 'UserDashboard', component: Dashboard, meta: { requiresAuth: true, role: 1 } },
  { path: '/play-exercise', name: 'PlayExercise', component: PlayExercise, meta: { requiresAuth: true, role: 1 } },


  // Teacher
  { path: '/teacher-dashboard', name: 'TeacherDashboard', component: TeacherPanel, meta: { requiresAuth: true, role: 2 } },
  { path: '/teacher-class-exercises', name: 'TeacherClassExercises', component: TeacherClassExercises, meta: { requiresAuth: true, role: 2 } },
  { path: '/teacher-exercise-form', name: 'TeacherExerciseForm', component: TeacherExerciseForm, meta: { requiresAuth: true, role: 2 } },

  // Admin
  { path: '/admin-dashboard', name: 'AdminDashboard', component: AdminPanel, meta: { requiresAuth: true, role: 3 } },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// --- Route Guards ---
router.beforeEach(async (to, from, next) => {
  if (!to.meta.requiresAuth) return next()

  try {
    const res = await axios.get('http://localhost/fragesport/api/auth.php', { withCredentials: true })

    if (!res.data.loggedIn) {
      return next('/')
    }

    const role = Number(res.data.user.role)
    const routeRole = to.meta.role

    if (routeRole && routeRole !== role) {
      switch (role) {
        case 1: return next({ name: 'UserDashboard' })
        case 2: return next({ name: 'TeacherDashboard' })
        case 3: return next({ name: 'AdminDashboard' })
        default: return next({ name: 'Login' })
      }
    }

    next()
  } catch (err) {
    console.error(err)
    alert('Fel vid kontakt med servern')
    next({ name: 'Login' })
  }
})

export default router
