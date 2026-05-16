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
          
          <div class="property-section">
            <h4>Position (x, y, z)</h4>
            <div class="coord-inputs">
              <div class="coord-input">
                <span class="coord-label x">X</span>
                <input type="number" v-model.number="selectedObject.position[0]" @change="drawScene" step="1">
              </div>
              <div class="coord-input">
                <span class="coord-label y">Y</span>
                <input type="number" v-model.number="selectedObject.position[1]" @change="drawScene" step="1">
              </div>
              <div class="coord-input">
                <span class="coord-label z">Z</span>
                <input type="number" v-model.number="selectedObject.position[2]" @change="drawScene" step="1">
              </div>
            </div>
            <p class="hint">Units in millimeters. Y-axis is up.</p>
          </div>
          
          <div class="property-section">
            <h4>Scale (x, y, z)</h4>
            <div class="coord-inputs">
              <div class="coord-input">
                <span class="coord-label x">X</span>
                <input type="number" v-model.number="selectedObject.scale[0]" @change="drawScene" step="0.1" min="0.1">
              </div>
              <div class="coord-input">
                <span class="coord-label y">Y</span>
                <input type="number" v-model.number="selectedObject.scale[1]" @change="drawScene" step="0.1" min="0.1">
              </div>
              <div class="coord-input">
                <span class="coord-label z">Z</span>
                <input type="number" v-model.number="selectedObject.scale[2]" @change="drawScene" step="0.1" min="0.1">
              </div>
            </div>
            <p class="hint">1.0 = original size. Use negative values to mirror.</p>
          </div>
          
          <div class="property-section">
            <h4>Rotation (radians)</h4>
            <div class="coord-inputs">
              <div class="coord-input">
                <span class="coord-label x">X</span>
                <input type="number" v-model.number="selectedObject.rotation[0]" @change="drawScene" step="0.1">
              </div>
              <div class="coord-input">
                <span class="coord-label y">Y</span>
                <input type="number" v-model.number="selectedObject.rotation[1]" @change="drawScene" step="0.1">
              </div>
              <div class="coord-input">
                <span class="coord-label z">Z</span>
                <input type="number" v-model.number="selectedObject.rotation[2]" @change="drawScene" step="0.1">
              </div>
            </div>
            <p class="hint">π = 3.14159 radians = 180°. π/2 = 1.5708 = 90°</p>
          </div>
          
          <div class="property-section">
            <h4>Color</h4>
            <input type="color" v-model="selectedObject.color" @change="drawScene" class="color-input">
          </div>
          
          <div class="property-section" v-if="selectedObject.type === 'box'">
            <h4>Box Dimensions</h4>
            <div class="coord-inputs">
              <div class="coord-input">
                <span class="coord-label">W</span>
                <input type="number" v-model.number="selectedObject.params.size[0]" @change="drawScene" step="1" min="1">
              </div>
              <div class="coord-input">
                <span class="coord-label">H</span>
                <input type="number" v-model.number="selectedObject.params.size[1]" @change="drawScene" step="1" min="1">
              </div>
              <div class="coord-input">
                <span class="coord-label">D</span>
                <input type="number" v-model.number="selectedObject.params.size[2]" @change="drawScene" step="1" min="1">
              </div>
            </div>
            <p class="hint">Width × Height × Depth in millimeters</p>
          </div>
          
          <div class="property-section" v-if="selectedObject.type === 'sphere'">
            <h4>Sphere Radius</h4>
            <input type="number" v-model.number="selectedObject.params.radius" @change="drawScene" step="1" min="1" class="full-width">
            <p class="hint">Radius in millimeters</p>
          </div>
          
          <div class="property-section" v-if="selectedObject.type === 'cylinder'">
            <h4>Cylinder Properties</h4>
            <div class="coord-inputs">
              <div class="coord-input">
                <span class="coord-label">R</span>
                <input type="number" v-model.number="selectedObject.params.radius" @change="drawScene" step="1" min="1">
              </div>
              <div class="coord-input">
                <span class="coord-label">H</span>
                <input type="number" v-model.number="selectedObject.params.height" @change="drawScene" step="1" min="1">
              </div>
            </div>
            <p class="hint">Radius × Height in millimeters</p>
          </div>
          
          <div class="property-section" v-if="selectedObject.type === 'torus'">
            <h4>Torus Properties</h4>
            <div class="coord-inputs">
              <div class="coord-input">
                <span class="coord-label">R1</span>
                <input type="number" v-model.number="selectedObject.params.innerRadius" @change="drawScene" step="1" min="1">
              </div>
              <div class="coord-input">
                <span class="coord-label">R2</span>
                <input type="number" v-model.number="selectedObject.params.outerRadius" @change="drawScene" step="1" min="1">
              </div>
            </div>
            <p class="hint">R1 = ring radius, R2 = tube radius</p>
          </div>
          
          <div class="property-section" v-if="selectedObject.type === 'text'">
            <h4>Text Properties</h4>
            <input type="text" v-model="selectedObject.params.text" @change="drawScene" class="full-width" placeholder="Enter text">
            <div class="coord-inputs" style="margin-top: 8px;">
              <div class="coord-input">
                <span class="coord-label">S</span>
                <input type="number" v-model.number="selectedObject.params.size" @change="drawScene" step="1" min="1">
              </div>
              <div class="coord-input">
                <span class="coord-label">H</span>
                <input type="number" v-model.number="selectedObject.params.height" @change="drawScene" step="1" min="1">
              </div>
            </div>
            <p class="hint">S = font size, H = extrusion height</p>
          </div>
          
          <button class="btn btn-danger" style="width: 100%; margin-top: 16px;" @click="deleteSelected">
            🗑️ Delete Object
          </button>
        </div>
        <div v-else class="no-selection">
          <p>Select an object to edit properties</p>
          <div class="tips">
            <h4>Tips:</h4>
            <ul>
              <li>Click shapes to select</li>
              <li>Drag to move selected shape</li>
              <li>Right-drag to rotate view</li>
              <li>Scroll to zoom</li>
            </ul>
          </div>
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
import { ref, reactive, onMounted, onUnmounted } from 'vue'
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
const shapes = ref([])
const isDragging = ref(false)
const dragStartPos = reactive({ x: 0, y: 0 })
const camera = reactive({ rotX: -0.5, rotY: 0.6, zoom: 3 })
const isRotating = ref(false)
const lastMousePos = reactive({ x: 0, y: 0 })

let cadEngine = null
let ctx = null
let shapeIdCounter = 0

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
      json_data: JSON.stringify({ shapes: shapes.value })
    })
    
    await api.post(`/drawings/${route.params.id}/versions`, {
      json_data: JSON.stringify({ shapes: shapes.value }),
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
    try {
      const parsed = JSON.parse(version.json_data)
      if (parsed.shapes) {
        shapes.value = parsed.shapes
      }
    } catch(e) {
      console.error('Failed to parse version data:', e)
    }
    showVersionModal.value = false
    loadDrawing()
    drawScene()
  } catch (err) {
    console.error('Failed to restore version:', err)
  }
}

const addBox = () => {
  const id = 'shape-' + (++shapeIdCounter)
  const shape = {
    id,
    type: 'box',
    params: { size: [50, 50, 50] },
    position: [0, 0, 0],
    rotation: [0, 0, 0],
    scale: [1, 1, 1],
    color: '#667eea',
    geometry: null
  }
  shapes.value.push(shape)
  cadEngine.createBox({ size: [50, 50, 50] })
  selectedObject.value = shape
  drawScene()
}

const addSphere = () => {
  const id = 'shape-' + (++shapeIdCounter)
  const shape = {
    id,
    type: 'sphere',
    params: { radius: 30 },
    position: [0, 0, 0],
    rotation: [0, 0, 0],
    scale: [1, 1, 1],
    color: '#764ba2',
    geometry: null
  }
  shapes.value.push(shape)
  cadEngine.createSphere({ radius: 30 })
  selectedObject.value = shape
  drawScene()
}

const addCylinder = () => {
  const id = 'shape-' + (++shapeIdCounter)
  const shape = {
    id,
    type: 'cylinder',
    params: { radius: 25, height: 60 },
    position: [0, 0, 0],
    rotation: [0, 0, 0],
    scale: [1, 1, 1],
    color: '#f093fb',
    geometry: null
  }
  shapes.value.push(shape)
  cadEngine.createCylinder({ radius: 25, height: 60 })
  selectedObject.value = shape
  drawScene()
}

const addTorus = () => {
  const id = 'shape-' + (++shapeIdCounter)
  const shape = {
    id,
    type: 'torus',
    params: { innerRadius: 40, outerRadius: 15 },
    position: [0, 0, 0],
    rotation: [0, 0, 0],
    scale: [1, 1, 1],
    color: '#4facfe',
    geometry: null
  }
  shapes.value.push(shape)
  cadEngine.createTorus({ innerRadius: 40, outerRadius: 15 })
  selectedObject.value = shape
  drawScene()
}

const addText = () => {
  const id = 'shape-' + (++shapeIdCounter)
  const shape = {
    id,
    type: 'text',
    params: { text: 'CAD', size: 30, height: 10 },
    position: [0, 0, 0],
    rotation: [0, 0, 0],
    scale: [1, 1, 1],
    color: '#00f2fe',
    geometry: null
  }
  shapes.value.push(shape)
  cadEngine.createText({ text: 'CAD', size: 30, height: 10 })
  selectedObject.value = shape
  drawScene()
}

const translateSelected = () => {
  if (selectedObject.value) {
    selectedObject.value.position[0] += 10
    drawScene()
  }
}

const rotateSelected = () => {
  if (selectedObject.value) {
    selectedObject.value.rotation[1] += Math.PI / 4
    drawScene()
  }
}

const scaleSelected = () => {
  if (selectedObject.value) {
    selectedObject.value.scale = selectedObject.value.scale.map(s => s * 1.2)
    drawScene()
  }
}

const mirrorSelected = () => {
  if (selectedObject.value) {
    selectedObject.value.scale[0] *= -1
    drawScene()
  }
}

const booleanUnion = () => {
  console.log('Boolean union - select multiple shapes first')
}

const booleanSubtract = () => {
  console.log('Boolean subtract - select two shapes first')
}

const booleanIntersect = () => {
  console.log('Boolean intersect - select two shapes first')
}

const exportStl = () => {
  alert('STL export would be implemented with full JSCAD integration')
}

const exportStep = () => {
  alert('STEP export would be implemented with full JSCAD integration')
}

const updatePosition = () => {
  drawScene()
}

const updateScale = () => {
  drawScene()
}

const deleteSelected = () => {
  if (selectedObject.value) {
    const index = shapes.value.findIndex(s => s.id === selectedObject.value.id)
    if (index !== -1) {
      shapes.value.splice(index, 1)
    }
    selectedObject.value = null
    drawScene()
  }
}

const initCadEngine = () => {
  cadEngine = new CadEngine()
  cadEngine.onResult((result) => {
    console.log('CAD Result received:', result)
    if (shapes.value.length > 0) {
      shapes.value[shapes.value.length - 1].geometry = result
    }
    drawScene()
  })
  cadEngine.onError((error) => {
    console.error('CAD Error:', error)
  })
  cadReady.value = true
  drawScene()
}

function rotatePoint(x, y, z, rotX, rotY) {
  let y1 = y * Math.cos(rotX) - z * Math.sin(rotX)
  let z1 = y * Math.sin(rotX) + z * Math.cos(rotX)
  let x1 = x * Math.cos(rotY) + z1 * Math.sin(rotY)
  let z2 = -x * Math.sin(rotY) + z1 * Math.cos(rotY)
  return { x: x1, y: y1, z: z2 }
}

function project(x, y, z, width, height, zoom) {
  const scale = Math.min(width, height) / (200 * zoom)
  const projX = width / 2 + x * scale
  const projY = height / 2 - y * scale
  return { x: projX, y: projY, depth: z }
}

function getShapeVertices(shape) {
  const { type, params, position, scale } = shape
  const vertices = []
  
  if (type === 'box') {
    const [sx, sy, sz] = params.size
    const half = [sx / 2, sy / 2, sz / 2]
    for (let i = 0; i < 8; i++) {
      vertices.push([
        (i & 1 ? half[0] : -half[0]) * scale[0] + position[0],
        (i & 2 ? half[1] : -half[1]) * scale[1] + position[1],
        (i & 4 ? half[2] : -half[2]) * scale[2] + position[2]
      ])
    }
  } else if (type === 'sphere') {
    const r = params.radius * scale[0]
    const segments = 16
    for (let i = 0; i <= segments; i++) {
      const phi = (i / segments) * Math.PI
      for (let j = 0; j < segments; j++) {
        const theta = (j / segments) * Math.PI * 2
        vertices.push([
          r * Math.sin(phi) * Math.cos(theta) + position[0],
          r * Math.cos(phi) + position[1],
          r * Math.sin(phi) * Math.sin(theta) + position[2]
        ])
      }
    }
  } else if (type === 'cylinder') {
    const r = params.radius * scale[0]
    const h = params.height * scale[1]
    const segments = 16
    for (let i = 0; i < 2; i++) {
      const y = (i === 0 ? -h / 2 : h / 2) + position[1]
      for (let j = 0; j < segments; j++) {
        const theta = (j / segments) * Math.PI * 2
        vertices.push([
          r * Math.cos(theta) + position[0],
          y,
          r * Math.sin(theta) + position[2]
        ])
      }
    }
  } else if (type === 'torus') {
    const R = params.innerRadius * scale[0]
    const r = params.outerRadius * scale[1]
    const segments = 24
    for (let i = 0; i <= segments; i++) {
      const u = (i / segments) * Math.PI * 2
      for (let j = 0; j < segments; j++) {
        const v = (j / segments) * Math.PI * 2
        vertices.push([
          (R + r * Math.cos(v)) * Math.cos(u) + position[0],
          r * Math.sin(v) + position[1],
          (R + r * Math.cos(v)) * Math.sin(u) + position[2]
        ])
      }
    }
  } else if (type === 'text') {
    const size = params.size * scale[0]
    const height = params.height * scale[1]
    const positions = [
      [-size / 2, -height / 2, -size / 4],
      [size / 2, -height / 2, -size / 4],
      [size / 2, -height / 2, size / 4],
      [-size / 2, -height / 2, size / 4],
      [-size / 2, height / 2, -size / 4],
      [size / 2, height / 2, -size / 4],
      [size / 2, height / 2, size / 4],
      [-size / 2, height / 2, size / 4]
    ]
    positions.forEach(p => {
      vertices.push([p[0] + position[0], p[1] + position[1], p[2] + position[2]])
    })
  }
  
  return vertices
}

function drawShape(ctx, shape, width, height) {
  const vertices = getShapeVertices(shape)
  if (vertices.length === 0) return
  
  const projected = vertices.map(v => {
    const rotated = rotatePoint(v[0], v[1], v[2], camera.rotX, camera.rotY)
    return project(rotated.x, rotated.y, rotated.z, width, height, camera.zoom)
  })
  
  ctx.strokeStyle = shape.color
  ctx.fillStyle = shape.color + '40'
  ctx.lineWidth = selectedObject.value?.id === shape.id ? 3 : 1
  
  if (shape.type === 'box' && projected.length === 8) {
    const edges = [
      [0,1],[1,3],[3,2],[2,0],
      [4,5],[5,7],[7,6],[6,4],
      [0,4],[1,5],[2,6],[3,7]
    ]
    edges.forEach(([a, b]) => {
      ctx.beginPath()
      ctx.moveTo(projected[a].x, projected[a].y)
      ctx.lineTo(projected[b].x, projected[b].y)
      ctx.stroke()
    })
    
    const faces = [
      [0,1,3,2], [4,5,7,6], [0,4,6,2],
      [1,5,7,3], [0,1,5,4], [2,3,7,6]
    ]
    ctx.fillStyle = shape.color + '20'
    faces.forEach(face => {
      ctx.beginPath()
      ctx.moveTo(projected[face[0]].x, projected[face[0]].y)
      face.forEach(i => ctx.lineTo(projected[i].x, projected[i].y))
      ctx.closePath()
      ctx.fill()
    })
  } else {
    ctx.fillStyle = shape.color + '30'
    ctx.beginPath()
    projected.forEach((p, i) => {
      if (i === 0) ctx.moveTo(p.x, p.y)
      else ctx.lineTo(p.x, p.y)
    })
    ctx.closePath()
    ctx.fill()
    ctx.stroke()
  }
  
  if (selectedObject.value?.id === shape.id) {
    const xs = projected.map(p => p.x)
    const ys = projected.map(p => p.y)
    const minX = Math.min(...xs), maxX = Math.max(...xs)
    const minY = Math.min(...ys), maxY = Math.max(...ys)
    ctx.strokeStyle = '#fff'
    ctx.setLineDash([5, 5])
    ctx.lineWidth = 1
    ctx.strokeRect(minX - 5, minY - 5, maxX - minX + 10, maxY - minY + 10)
    ctx.setLineDash([])
  }
}

function drawGrid(ctx, width, height) {
  ctx.strokeStyle = 'rgba(102, 126, 234, 0.15)'
  ctx.lineWidth = 1
  const gridSize = 50
  const numLines = 20
  
  for (let i = -numLines; i <= numLines; i++) {
    const start = rotatePoint(i * gridSize, 0, -numLines * gridSize, camera.rotX, camera.rotY)
    const end = rotatePoint(i * gridSize, 0, numLines * gridSize, camera.rotX, camera.rotY)
    const p1 = project(start.x, start.y, start.z, width, height, camera.zoom)
    const p2 = project(end.x, end.y, end.z, width, height, camera.zoom)
    
    ctx.beginPath()
    ctx.moveTo(p1.x, p1.y)
    ctx.lineTo(p2.x, p2.y)
    ctx.stroke()
    
    const start2 = rotatePoint(-numLines * gridSize, 0, i * gridSize, camera.rotX, camera.rotY)
    const end2 = rotatePoint(numLines * gridSize, 0, i * gridSize, camera.rotX, camera.rotY)
    const p3 = project(start2.x, start2.y, start2.z, width, height, camera.zoom)
    const p4 = project(end2.x, end2.y, end2.z, width, height, camera.zoom)
    
    ctx.beginPath()
    ctx.moveTo(p3.x, p3.y)
    ctx.lineTo(p4.x, p4.y)
    ctx.stroke()
  }
}

function drawAxes(ctx, width, height) {
  const origin = project(0, 0, 0, width, height, camera.zoom)
  const axisLen = 100
  
  const xEnd = rotatePoint(axisLen, 0, 0, camera.rotX, camera.rotY)
  const xProj = project(xEnd.x, xEnd.y, xEnd.z, width, height, camera.zoom)
  ctx.strokeStyle = '#ff6b6b'
  ctx.lineWidth = 2
  ctx.beginPath()
  ctx.moveTo(origin.x, origin.y)
  ctx.lineTo(xProj.x, xProj.y)
  ctx.stroke()
  ctx.fillStyle = '#ff6b6b'
  ctx.fillText('X', xProj.x + 5, xProj.y)
  
  const yEnd = rotatePoint(0, axisLen, 0, camera.rotX, camera.rotY)
  const yProj = project(yEnd.x, yEnd.y, yEnd.z, width, height, camera.zoom)
  ctx.strokeStyle = '#51cf66'
  ctx.beginPath()
  ctx.moveTo(origin.x, origin.y)
  ctx.lineTo(yProj.x, yProj.y)
  ctx.stroke()
  ctx.fillStyle = '#51cf66'
  ctx.fillText('Y', yProj.x + 5, yProj.y)
  
  const zEnd = rotatePoint(0, 0, axisLen, camera.rotX, camera.rotY)
  const zProj = project(zEnd.x, zEnd.y, zEnd.z, width, height, camera.zoom)
  ctx.strokeStyle = '#339af0'
  ctx.beginPath()
  ctx.moveTo(origin.x, origin.y)
  ctx.lineTo(zProj.x, zProj.y)
  ctx.stroke()
  ctx.fillStyle = '#339af0'
  ctx.fillText('Z', zProj.x + 5, zProj.y)
}

const drawScene = () => {
  if (!ctx || !canvas.value) return
  
  const width = canvas.value.width
  const height = canvas.value.height
  
  ctx.fillStyle = '#1a1a2e'
  ctx.fillRect(0, 0, width, height)
  
  drawGrid(ctx, width, height)
  drawAxes(ctx, width, height)
  
  const sortedShapes = [...shapes.value].map(shape => {
    const center = [shape.position[0], shape.position[1], shape.position[2]]
    const rotated = rotatePoint(center[0], center[1], center[2], camera.rotX, camera.rotY)
    return { shape, depth: rotated.z }
  }).sort((a, b) => a.depth - b.depth)
  
  sortedShapes.forEach(({ shape }) => {
    drawShape(ctx, shape, width, height)
  })
}

const handleMouseDown = (e) => {
  const rect = canvas.value.getBoundingClientRect()
  const x = e.clientX - rect.left
  const y = e.clientY - rect.top
  
  if (e.button === 2 || e.shiftKey) {
    isRotating.value = true
    lastMousePos.x = x
    lastMousePos.y = y
    return
  }
  
  let clicked = null
  for (let i = shapes.value.length - 1; i >= 0; i--) {
    const shape = shapes.value[i]
    const vertices = getShapeVertices(shape)
    if (vertices.length === 0) continue
    
    const projected = vertices.map(v => {
      const rotated = rotatePoint(v[0], v[1], v[2], camera.rotX, camera.rotY)
      return project(rotated.x, rotated.y, rotated.z, canvas.value.width, canvas.value.height, camera.zoom)
    })
    
    const xs = projected.map(p => p.x)
    const ys = projected.map(p => p.y)
    const minX = Math.min(...xs), maxX = Math.max(...xs)
    const minY = Math.min(...ys), maxY = Math.max(...ys)
    
    if (x >= minX - 10 && x <= maxX + 10 && y >= minY - 10 && y <= maxY + 10) {
      clicked = shape
      break
    }
  }
  
  selectedObject.value = clicked
  
  if (clicked) {
    isDragging.value = true
    dragStartPos.x = x
    dragStartPos.y = y
  }
  
  drawScene()
}

const handleMouseMove = (e) => {
  const rect = canvas.value.getBoundingClientRect()
  const x = e.clientX - rect.left
  const y = e.clientY - rect.top
  
  if (isRotating.value) {
    const dx = x - lastMousePos.x
    const dy = y - lastMousePos.y
    camera.rotY += dx * 0.01
    camera.rotX += dy * 0.01
    lastMousePos.x = x
    lastMousePos.y = y
    drawScene()
    return
  }
  
  if (isDragging.value && selectedObject.value) {
    const dx = x - dragStartPos.x
    const dy = y - dragStartPos.y
    const scale = 200 * camera.zoom / Math.min(canvas.value.width, canvas.value.height)
    
    const cosY = Math.cos(-camera.rotY)
    const sinY = Math.sin(-camera.rotY)
    const cosX = Math.cos(-camera.rotX)
    const sinX = Math.sin(-camera.rotX)
    
    selectedObject.value.position[0] += (dx * cosY + dy * sinY * sinX) * scale
    selectedObject.value.position[2] += (-dx * sinY + dy * cosY * sinX) * scale
    selectedObject.value.position[1] += -dy * cosX * scale
    
    dragStartPos.x = x
    dragStartPos.y = y
    drawScene()
  }
}

const handleMouseUp = () => {
  isDragging.value = false
  isRotating.value = false
}

const handleWheel = (e) => {
  e.preventDefault()
  camera.zoom *= e.deltaY > 0 ? 1.1 : 0.9
  camera.zoom = Math.max(0.5, Math.min(10, camera.zoom))
  drawScene()
}

const handleContextMenu = (e) => {
  e.preventDefault()
}

const initCanvas = () => {
  if (!canvas.value || !canvasContainer.value) return
  
  const rect = canvasContainer.value.getBoundingClientRect()
  canvas.value.width = rect.width
  canvas.value.height = rect.height
  
  ctx = canvas.value.getContext('2d')
  
  canvas.value.addEventListener('mousedown', handleMouseDown)
  canvas.value.addEventListener('mousemove', handleMouseMove)
  canvas.value.addEventListener('mouseup', handleMouseUp)
  canvas.value.addEventListener('mouseleave', handleMouseUp)
  canvas.value.addEventListener('wheel', handleWheel, { passive: false })
  canvas.value.addEventListener('contextmenu', handleContextMenu)
  
  drawScene()
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
  if (canvas.value) {
    canvas.value.removeEventListener('mousedown', handleMouseDown)
    canvas.value.removeEventListener('mousemove', handleMouseMove)
    canvas.value.removeEventListener('mouseup', handleMouseUp)
    canvas.value.removeEventListener('mouseleave', handleMouseUp)
    canvas.value.removeEventListener('wheel', handleWheel)
    canvas.value.removeEventListener('contextmenu', handleContextMenu)
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

.property-section {
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.property-section:last-child {
  border-bottom: none;
}

.property-section h4 {
  margin: 0 0 8px 0;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
}

.coord-inputs {
  display: flex;
  gap: 6px;
}

.coord-input {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 4px;
}

.coord-label {
  width: 20px;
  font-size: 12px;
  font-weight: 600;
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
}

.coord-label.x { color: #ff6b6b; }
.coord-label.y { color: #51cf66; }
.coord-label.z { color: #339af0; }

.coord-input input {
  flex: 1;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  padding: 6px 8px;
  color: white;
  font-size: 12px;
  width: 100%;
}

.coord-input input:focus {
  outline: none;
  border-color: #667eea;
}

.hint {
  margin: 6px 0 0 0;
  font-size: 11px;
  color: rgba(255, 255, 255, 0.4);
  line-height: 1.4;
}

.color-input {
  width: 100%;
  height: 36px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  background: transparent;
  cursor: pointer;
}

.full-width {
  width: 100%;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  padding: 8px 12px;
  color: white;
  font-size: 13px;
}

.full-width:focus {
  outline: none;
  border-color: #667eea;
}

.btn-danger {
  background: linear-gradient(135deg, #ff6b6b, #c92a2a) !important;
  border: none !important;
}

.btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(201, 42, 42, 0.4);
}

.tips {
  margin-top: 20px;
  padding: 12px;
  background: rgba(102, 126, 234, 0.1);
  border-radius: 8px;
  border: 1px solid rgba(102, 126, 234, 0.2);
}

.tips h4 {
  margin: 0 0 8px 0;
  font-size: 12px;
  color: #667eea;
}

.tips ul {
  margin: 0;
  padding-left: 16px;
}

.tips li {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.6);
  margin-bottom: 4px;
}
</style>
