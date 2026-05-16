<template>
  <div class="register-page">
    <div class="container">
      <div class="card" style="max-width: 400px; margin: 80px auto;">
        <div class="logo">
          <span class="logo-icon">⚙️</span>
          <h1>Create Account</h1>
          <p>Join our CAD collaboration platform</p>
        </div>
        
        <form @submit.prevent="register">
          <div class="form-group">
            <label>Username</label>
            <input v-model="username" type="text" placeholder="username" required>
          </div>
          
          <div class="form-group">
            <label>Full Name</label>
            <input v-model="fullName" type="text" placeholder="John Doe">
          </div>
          
          <div class="form-group">
            <label>Email</label>
            <input v-model="email" type="email" placeholder="your@email.com" required>
          </div>
          
          <div class="form-group">
            <label>Password</label>
            <input v-model="password" type="password" placeholder="Enter password" required>
          </div>
          
          <button type="submit" class="btn btn-primary" style="width: 100%;">
            {{ loading ? 'Registering...' : 'Register' }}
          </button>
          
          <div class="error" v-if="error">{{ error }}</div>
        </form>
        
        <p class="login-link">
          Already have an account? <a href="/">Login here</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const username = ref('')
const fullName = ref('')
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

const register = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const response = await axios.post('/api/auth/register', {
      username: username.value,
      full_name: fullName.value,
      email: email.value,
      password: password.value
    })
    
    localStorage.setItem('token', response.data.token)
    localStorage.setItem('user', JSON.stringify(response.data.user))
    
    router.push('/dashboard')
  } catch (err) {
    error.value = err.response?.data?.error || 'Registration failed'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-page {
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

.login-link {
  text-align: center;
  margin-top: 24px;
  color: rgba(255, 255, 255, 0.6);
}

.login-link a {
  color: #667eea;
  text-decoration: none;
}

.login-link a:hover {
  text-decoration: underline;
}
</style>
