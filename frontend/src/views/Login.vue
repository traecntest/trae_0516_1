<template>
  <div class="login-page">
    <div class="container">
      <div class="card" style="max-width: 400px; margin: 100px auto;">
        <div class="logo">
          <span class="logo-icon">⚙️</span>
          <h1>CAD Collaborate</h1>
          <p>WebAssembly-based CAD Platform</p>
        </div>
        
        <form @submit.prevent="login">
          <div class="form-group">
            <label>Email</label>
            <input v-model="email" type="email" placeholder="your@email.com" required>
          </div>
          
          <div class="form-group">
            <label>Password</label>
            <input v-model="password" type="password" placeholder="Enter password" required>
          </div>
          
          <button type="submit" class="btn btn-primary" style="width: 100%;">
            {{ loading ? 'Logging in...' : 'Login' }}
          </button>
          
          <div class="error" v-if="error">{{ error }}</div>
        </form>
        
        <p class="register-link">
          Don't have an account? <a href="/register">Register here</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../utils/api'
import { useRouter } from 'vue-router'

const router = useRouter()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

const login = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const response = await api.post('/auth/login', {
      email: email.value,
      password: password.value
    })
    
    localStorage.setItem('token', response.data.token)
    localStorage.setItem('user', JSON.stringify(response.data.user))
    
    router.push('/dashboard')
  } catch (err) {
    error.value = err.response?.data?.error || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo {
  text-align: center;
  margin-bottom: 32px;
}

.logo-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
}

.logo h1 {
  font-size: 28px;
  margin-bottom: 4px;
}

.logo p {
  color: rgba(255, 255, 255, 0.6);
  font-size: 14px;
}

.error {
  color: #ff6b6b;
  text-align: center;
  margin-top: 16px;
}

.register-link {
  text-align: center;
  margin-top: 24px;
  color: rgba(255, 255, 255, 0.6);
}

.register-link a {
  color: #667eea;
  text-decoration: none;
}

.register-link a:hover {
  text-decoration: underline;
}
</style>
