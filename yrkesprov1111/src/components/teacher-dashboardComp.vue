<template>
  <div class="container mt-4">
    <h2>Dina klasser</h2>
    <div class="row">
      <div v-for="cls in classes" :key="cls.class_id" class="col-md-4 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">{{ cls.class_name }}</h5>
            <button class="btn btn-sm btn-primary me-2" @click="viewExercises(cls)">Visa Övningar</button>
            <button class="btn btn-sm btn-warning" @click="editClass(cls)">Redigera Klass</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Redigera Klass Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <h5>Redigera Klass</h5>
        <div class="mb-3">
          <label class="form-label">Klassnamn</label>
          <input type="text" class="form-control" v-model="modalClass.class_name" />
        </div>
        <div class="d-flex justify-content-end mt-3">
          <button class="btn btn-danger me-2" @click="showDeleteConfirm = true">Radera</button>
          <button class="btn btn-primary" @click="saveClassChanges">Spara ändringar</button>
        </div>
        <button class="modal-close" @click="showModal=false">&times;</button>
      </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div v-if="showDeleteConfirm" class="modal-overlay">
      <div class="modal-content">
        <h5>Bekräfta radering</h5>
        <p>Är du säker på att du vill radera klassen "<strong>{{ modalClass.class_name }}</strong>"?</p>
        <div class="d-flex justify-content-end mt-3">
          <button class="btn btn-secondary me-2" @click="showDeleteConfirm = false">Avbryt</button>
          <button class="btn btn-danger" @click="deleteClass">Radera</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const classes = ref([])
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const modalClass = ref({ class_id: null, class_name: '' })

// --- Load all classes ---
const loadClasses = async () => {
  try {
    const res = await axios.get('http://localhost/fragesport/api/get_classes.php', { withCredentials: true })
    classes.value = res.data.classes || []
  } catch (err) {
    console.error(err)
    alert("Fel vid hämtning av klasser")
  }
}

// --- Navigate to exercises ---
const viewExercises = (cls) => {
  router.push({ 
    name: 'TeacherClassExercises', 
    query: { class_id: cls.class_id, class_name: cls.class_name } 
  })
}

// --- Open modal to edit ---
const editClass = (cls) => {
  modalClass.value = { ...cls }
  showModal.value = true
}

// --- Save class changes ---
const saveClassChanges = async () => {
  if (!modalClass.value.class_name) {
    alert("Klassnamn får inte vara tomt")
    return
  }

  try {
    const res = await axios.post(
      'http://localhost/fragesport/api/update_class.php',
      { class_id: modalClass.value.class_id, class_name: modalClass.value.class_name },
      { withCredentials: true }
    )

    if (res.data.success) {
      showModal.value = false
      loadClasses()
    } else {
      alert("Fel vid sparande: " + res.data.message)
    }
  } catch (err) {
    console.error(err)
    alert("Fel vid kontakt med servern")
  }
}

// --- Delete class ---
const deleteClass = async () => {
  try {
    const res = await axios.post(
      'http://localhost/fragesport/api/delete_class.php',
      { class_id: modalClass.value.class_id },
      { withCredentials: true }
    )

    if (res.data.success) {
      showDeleteConfirm.value = false
      showModal.value = false
      loadClasses()
    } else {
      alert("Fel vid radering: " + res.data.message)
    }
  } catch (err) {
    console.error(err)
    alert("Fel vid kontakt med servern")
  }
}

onMounted(() => {
  loadClasses()
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 400px;
  position: relative;
}

.modal-close {
  position: absolute;
  top: 10px; right: 15px;
  background: transparent;
  border: none;
  font-size: 20px;
  cursor: pointer;
}
</style>
