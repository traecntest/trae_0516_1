<template>
  <div class="editor">
    <header class="editor-header">
      <div class="header-left">
        <button class="btn btn-secondary" @click="goBack">← Back</button>
        <h1>{{ drawing?.title || 'Untitled' }}</h1>
      </div>
      <div class="header-center">
        <button class="btn btn-secondary" @click="saveDrawing">💾 Save</button>
        <button class="btn btn-secondary" @click="showVersionModal = true">📋 Versions</button>
        <button class="btn btn-secondary" @click="showShareModal = true">👥 Share</button>
      </div>
      <div class="header-right">
        <div class="collaborators">
          <div 
            v-for="collab in collaborators" 
            :key="collab.id" 
            class="collaborator"
            :title="collab.username"
          >
            {{ collab.username.charAt(0).toUpperCase() }}
          </div>
        </div>
      </div>
    </header>
    
    <div class="editor-content">
      <aside class="sidebar">
        <h3>Tools</h3>
        <div class="toolbar">
          <button @click="addBox" title="Box">📦</button>
          <button @click="addSphere" title="Sphere">⚪</button>
          <button @click="addCylinder" title="Cylinder">🔷</button>
          <button @click="addTorus" title="Torus">⭕</button>
          <button @click="addText" title="Text">📝</button>
        </div>
        
        <h3>Operations</h3>
        <div class="toolbar">
          <button @click="translateSelected" title="Move">↕️</button>
          <button @click="rotateSelected" title="Rotate">🔄</button>
          <button @click="scaleSelected" title="Scale">⬜</button>
          <button @click="mirrorSelected" title="Mirror">🔀</button>
          <button @click="booleanUnion" title="Union">⊕</button>
          <button @click="booleanSubtract" title="Subtract">⊖</button>
          <button @click="booleanIntersect" title="Intersect">⊗</button>
        </div>
      </aside>
      
      <main class="canvas-container" ref="canvasContainer">
        <canvas ref="canvas"></canvas>
        <div v-if="!cadReady" class="loading-overlay">
          <div class="loading-spinner"></div>
          <p>Loading CAD Engine...</p>
        </div>
      </main>
      
      <aside class="properties-panel">
        <h3>Properties</h3>
        <div v-if="selectedObject" class="object-properties">
          <div class="property">
            <label>Type</label>
            <input :value="selectedObject.type" disabled>
          </div>
          <div class="property">
            <label>Position</label>
            <input v-model="selectedObject.position" @change="updatePosition">
          </div>
          <div class="property">
            <label>Scale</label>
            <input v-model="selectedObject.scale" @change="updateScale">
          </div>
        </div>
        <div v-else class="no-selection">
          <p>Select an object to edit properties</p>
        </div>
        
        <h3>Export</h3>
        <button class="btn btn-secondary" style="width: 100%;" @click="exportStl">Export STL</button>
        <button class="btn btn-secondary" style="width: 100%;" @click="exportStep">Export STEP</button>
      </aside>
    </div>
    
    <div v-if="showVersionModal" class="modal">
      <div class="modal-content">
        <button class="close" @click="showVersionModal = false">&times;</button>
        <h2>Versions</h2>
        <div class="version-list">
          <div 
            v-for="version in versions" 
            :key="version.id" 
            class="version-item"
          >
            <div class="version-info">
              <span class="version-number">v{{ version.version_number }}</span>
              <span class="version-date">{{ formatDate(version.created_at) }}</span>
            </div>
            <p>{{ version.change_description || 'No description' }}</p>
            <button class="btn btn-secondary" @click="restoreVersion(version)">Restore</button>
          </div>
        </div>
      </div>
    </div>
    
    <div v-if="showShareModal" class="modal">
      <div class="modal-content">
        <button class="close" @click="showShareModal = false">&times;</button>
        <h2>Share Drawing</h2>
        <form @submit.prevent="shareDrawing">
          <div class="form-group">
            <label>Email</label>
            <input v-model="shareEmail" type="email" placeholder="user@example.com" required>
          </div>
          <div class="form-group">
            <label>Permission</label>
            <select v-model="sharePermission">
              <option value="view">View</option>
              <option value="edit">Edit</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" style="width: 100%;">Share</button>
        </form>
        
        <h3>Shared With</h3>
        <div v-if="sharedUsers.length === 0" class="no-shared">
          <p>No one shared yet</p>
        </div>
        <div v-else class="shared-list">
          <div v-for="user in sharedUsers" :key="user.id" class="shared-item">
            <span>{{ user.email }}</span>
            <span class="permission-badge">{{ user.permission }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '../utils/api'
import { useRoute, useRouter } from 'vue-router'
import { CadEngine } from '../../../cad-core/cad-engine.js'

const route = useRoute()
const router = useRouter()

const canvas = ref(null)
const canvasContainer = ref(null)
const drawing = ref(null)
const cadReady = ref(false)
const selectedObject = ref(null)
const showVersionModal = ref(false)
const showShareModal = ref(false)
const versions = ref([])
const sharedUsers = ref([])
const collaborators = ref([])
const shareEmail = ref('')
const sharePermission = ref('view')

let cadEngine = null
let ctx = null

const loadDrawing = async () => {
  try {
    const response = await api.get(`/drawings/${route.params.id}`)
    drawing.value = response.data
  } catch (err) {
    console.error('Failed to load drawing:', err)
  }
}

const loadVersions = async () => {
  try {
    const response = await api.get(`/drawings/${route.params.id}/versions`)
    versions.value = response.data
  } catch (err) {
    console.error('Failed to load versions:', err)
  }
}

const loadSharedUsers = async () => {
  try {
    const response = await api.get(`/drawings/${route.params.id}/shared`)
    sharedUsers.value = response.data
  } catch (err) {
    console.error('Failed to load shared users:', err)
  }
}

const loadCollaborators = async () => {
  try {
    const response = await api.get(`/collaboration/${route.params.id}/users`)
    collaborators.value = response.data
  } catch (err) {
    console.error('Failed to load collaborators:', err)
  }
}

const joinCollaboration = async () => {
  try {
    await api.post(`/collaboration/join/${route.params.id}`, {
      session_id: 'session-' + Math.random().toString(36).substr(2, 9)
    })
    loadCollaborators()
  } catch (err) {
    console.error('Failed to join collaboration:', err)
  }
}

const saveDrawing = async () => {
  try {
    await api.put(`/drawings/${route.params.id}`, {
      title: drawing.value.title,
      json_data: JSON.stringify({ shapes: [] })
    })
    
    await api.post(`/drawings/${route.params.id}/versions`, {
      json_data: JSON.stringify({ shapes: [] }),
      change_description: 'Auto-save'
    })
    
    alert('Drawing saved!')
  } catch (err) {
    console.error('Failed to save drawing:', err)
  }
}

const shareDrawing = async () => {
  try {
    await api.post(`/drawings/${route.params.id}/share`, {
      email: shareEmail.value,
      permission: sharePermission.value
    })
    
    shareEmail.value = ''
    sharePermission.value = 'view'
    loadSharedUsers()
  } catch (err) {
    console.error('Failed to share:', err)
  }
}

const restoreVersion = async (version) => {
  try {
    await api.put(`/drawings/${route.params.id}`, {
      json_data: version.json_data
    })
    showVersionModal.value = false
    loadDrawing()
  } catch (err) {
    console.error('Failed to restore version:', err)
  }
}

const addBox = () => {
  cadEngine.createBox({ size: [50, 50, 50] })
}

const addSphere = () => {
  cadEngine.createSphere({ radius: 30 })
}

const addCylinder = () => {
  cadEngine.createCylinder({ radius: 25, height: 60 })
}

const addTorus = () => {
  cadEngine.createTorus({ innerRadius: 40, outerRadius: 15 })
}

const addText = () => {
  cadEngine.createText({ text: 'CAD', size: 30, height: 10 })
}

const translateSelected = () => {
  if (selectedObject.value) {
    cadEngine.translate(selectedObject.value, [10, 0, 0])
  }
}

const rotateSelected = () => {
  if (selectedObject.value) {
    cadEngine.rotate(selectedObject.value, [0, Math.PI / 4, 0])
  }
}

const scaleSelected = () => {
  if (selectedObject.value) {
    cadEngine.scale(selectedObject.value, [1.2, 1.2, 1.2])
  }
}

const mirrorSelected = () => {
  if (selectedObject.value) {
    cadEngine.mirror(selectedObject.value, [1, 0, 0])
  }
}

const booleanUnion = () => {
  cadEngine.booleanUnion([])
}

const booleanSubtract = () => {
  cadEngine.booleanSubtract({}, {})
}

const booleanIntersect = () => {
  cadEngine.booleanIntersect({}, {})
}

const exportStl = () => {
  cadEngine.generateStl({})
}

const exportStep = () => {
  cadEngine.generateStep({})
}

const updatePosition = () => {
  console.log('Position updated')
}

const updateScale = () => {
  console.log('Scale updated')
}

const initCadEngine = () => {
  cadEngine = new CadEngine()
  cadEngine.onResult((result) => {
    console.log('CAD Result:', result)
    drawPreview()
  })
  cadEngine.onError((error) => {
    console.error('CAD Error:', error)
  })
  cadReady.value = true
}

const drawPreview = () => {
  if (!ctx) return
  
  ctx.fillStyle = '#1a1a2e'
  ctx.fillRect(0, 0, canvas.value.width, canvas.value.height)
  
  ctx.strokeStyle = '#667eea'
  ctx.lineWidth = 2
  
  ctx.beginPath()
  ctx.arc(canvas.value.width / 2, canvas.value.height / 2, 80, 0, Math.PI * 2)
  ctx.stroke()
  
  ctx.fillStyle = '#667eea'
  ctx.font = '24px Arial'
  ctx.textAlign = 'center'
  ctx.fillText('3D Preview Area', canvas.value.width / 2, canvas.value.height / 2 + 8)
}

const initCanvas = () => {
  if (!canvas.value || !canvasContainer.value) return
  
  const rect = canvasContainer.value.getBoundingClientRect()
  canvas.value.width = rect.width
  canvas.value.height = rect.height
  
  ctx = canvas.value.getContext('2d')
  drawPreview()
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const goBack = () => {
  router.push('/dashboard')
}

onMounted(() => {
  loadDrawing()
  loadVersions()
  loadSharedUsers()
  initCadEngine()
  initCanvas()
  joinCollaboration()
  
  window.addEventListener('resize', initCanvas)
})

onUnmounted(() => {
  if (cadEngine) {
    cadEngine.destroy()
  }
  window.removeEventListener('resize', initCanvas)
})
</script>

<style scoped>
.editor {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.header-left,
.header-center,
.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.editor-content {
  display: flex;
  flex: 1;
  overflow: hidden;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid rgba(255, 255, 255, 0.2);
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.object-properties {
  margin-top: 16px;
}

.no-selection {
  margin-top: 16px;
  color: rgba(255, 255, 255, 0.5);
}

.version-list {
  max-height: 300px;
  overflow-y: auto;
}

.version-item {
  padding: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.version-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.version-number {
  font-weight: 600;
  color: #667eea;
}

.version-date {
  color: rgba(255, 255, 255, 0.6);
  font-size: 14px;
}

.shared-list {
  margin-top: 16px;
}

.shared-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.permission-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  background: rgba(255, 255, 255, 0.1);
}

.no-shared {
  color: rgba(255, 255, 255, 0.5);
  padding: 16px;
}
</style>
