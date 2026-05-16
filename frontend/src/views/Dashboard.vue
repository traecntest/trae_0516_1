<template>
  <div class="dashboard">
    <header class="header">
      <div class="container">
        <div class="header-content">
          <div class="logo">
            <span class="logo-icon">⚙️</span>
            <span class="logo-text">CAD Collaborate</span>
          </div>
          <nav class="nav">
            <span class="user-info">Welcome, {{ user?.username }}</span>
            <button class="btn btn-secondary" @click="logout">Logout</button>
          </nav>
        </div>
      </div>
    </header>
    
    <main class="main-content">
      <div class="container">
        <div class="page-header">
          <h1>My Drawings</h1>
          <button class="btn btn-primary" @click="showCreateModal = true">New Drawing</button>
        </div>
        
        <div class="grid grid-3">
          <div 
            v-for="drawing in drawings" 
            :key="drawing.id" 
            class="card drawing-card"
            @click="openDrawing(drawing.id)"
          >
            <div class="thumbnail">📐</div>
            <h3>{{ drawing.title }}</h3>
            <p class="meta">{{ formatDate(drawing.updated_at) }}</p>
          </div>
          
          <div 
            v-if="drawings.length === 0" 
            class="card empty-state"
            @click="showCreateModal = true"
          >
            <div class="empty-icon">➕</div>
            <p>Create your first drawing</p>
          </div>
        </div>
      </div>
    </main>
    
    <div v-if="showCreateModal" class="modal">
      <div class="modal-content">
        <button class="close" @click="showCreateModal = false">&times;</button>
        <h2>Create New Drawing</h2>
        <form @submit.prevent="createDrawing">
          <div class="form-group">
            <label>Title</label>
            <input v-model="newDrawing.title" type="text" placeholder="Drawing title" required>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="newDrawing.description" placeholder="Description"></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="width: 100%;">
            Create Drawing
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const user = ref(null)
const drawings = ref([])
const showCreateModal = ref(false)
const newDrawing = ref({ title: '', description: '' })

const loadUser = () => {
  const userData = localStorage.getItem('user')
  if (userData) {
    user.value = JSON.parse(userData)
  }
}

const loadDrawings = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/drawings', {
      headers: { Authorization: `Bearer ${token}` }
    })
    drawings.value = response.data
  } catch (err) {
    console.error('Failed to load drawings:', err)
  }
}

const createDrawing = async () => {
  try {
    const token = localStorage.getItem('token')
    await axios.post('/api/drawings', {
      title: newDrawing.value.title,
      description: newDrawing.value.description
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    showCreateModal.value = false
    newDrawing.value = { title: '', description: '' }
    loadDrawings()
  } catch (err) {
    console.error('Failed to create drawing:', err)
  }
}

const openDrawing = (id) => {
  router.push(`/editor/${id}`)
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/')
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

onMounted(() => {
  loadUser()
  loadDrawings()
})
</script>

<style scoped>
.header {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding: 16px 0;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-icon {
  font-size: 28px;
}

.logo-text {
  font-size: 20px;
  font-weight: 700;
}

.nav {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-info {
  color: rgba(255, 255, 255, 0.8);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  cursor: pointer;
  border: 2px dashed rgba(255, 255, 255, 0.2);
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 12px;
  color: rgba(255, 255, 255, 0.4);
}

.empty-state p {
  color: rgba(255, 255, 255, 0.6);
}
</style>
