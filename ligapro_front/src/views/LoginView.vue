<template>
  <div class="auth-container">
    <div class="auth-card">
      <h2>Iniciar Sesión - Ligapro</h2>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label>Correo Electrónico:</label>
          <input type="email" v-model="email" required placeholder="ejemplo@gmail.com" />
        </div>

        <div class="form-group">
          <label>Contraseña:</label>
          <input type="password" v-model="password" required placeholder="********" />
        </div>

        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Iniciando sesión...' : 'Entrar' }}
        </button>
      </form>

      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

      <p class="switch-link">
        ¿No tienes cuenta? <router-link to="/register">Regístrate aquí</router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

const handleLogin = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('auth_token', response.data.access_token)
    router.push('/dashboard')
  } catch (error: unknown) {
    const response = (error as { response?: { data?: { message?: string } } }).response
    errorMessage.value = response?.data?.message || 'Credenciales incorrectas o error de conexión'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #0f172a;
}
.auth-card {
  width: 100%;
  max-width: 420px;
  padding: 40px;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  box-sizing: border-box;
}
.auth-card h2 {
  margin-top: 0;
  margin-bottom: 24px;
  text-align: center;
  color: #1e293b;
  font-size: 24px;
  font-weight: 600;
}
.form-group {
  margin-bottom: 18px;
}
.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #475569;
  font-size: 14px;
}
.form-group input {
  width: 100%;
  padding: 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 15px;
  box-sizing: border-box;
  background-color: #fff;
  transition: border-color 0.2s;
}
.form-group input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}
.btn-primary {
  width: 100%;
  padding: 12px;
  background-color: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: background-color 0.2s;
  margin-top: 10px;
}
.btn-primary:hover {
  background-color: #1d4ed8;
}
.btn-primary:disabled {
  background-color: #93c5fd;
  cursor: not-allowed;
}
.error {
  color: #dc2626;
  margin-top: 14px;
  font-size: 14px;
  text-align: center;
}
.switch-link {
  margin-top: 24px;
  text-align: center;
  font-size: 14px;
  color: #64748b;
}
.switch-link a {
  color: #2563eb;
  text-decoration: none;
  font-weight: 500;
}
.switch-link a:hover {
  text-decoration: underline;
}
</style>
