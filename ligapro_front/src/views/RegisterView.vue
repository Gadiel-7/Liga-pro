<template>
  <div class="auth-container">
    <div class="auth-card">
      <h2>Registro en Ligapro</h2>
      <form v-if="!registered" @submit.prevent="handleRegister">
        <div class="form-group">
          <label>Nombre:</label>
          <input type="text" v-model="name" required placeholder="Tu nombre completo" />
        </div>

        <div class="form-group">
          <label>Correo Electrónico:</label>
          <input type="email" v-model="email" required placeholder="ejemplo@gmail.com" />
        </div>

        <div class="form-group">
          <label>Contraseña (mínimo 6 caracteres):</label>
          <input type="password" v-model="password" required placeholder="********" />
        </div>

        <div class="form-group">
          <label>Rol de Usuario:</label>
          <select v-model="role" required class="form-select">
            <option value="aficionado">Aficionado</option>
            <option value="representante">Representante</option>
            <option value="admin">Administrador</option>
          </select>
        </div>

        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Registrando...' : 'Registrarse' }}
        </button>
      </form>

      <!-- Mensaje de éxito post-registro pidiendo verificación -->
      <div v-else class="success-box">
        <h3>✉️ ¡Cuenta creada con éxito!</h3>
        <p>
          Hemos enviado un enlace de confirmación a <strong>{{ email }}</strong
          >.
        </p>
        <p class="instruction">
          Por favor, revisa tu bandeja de entrada (o spam) para verificar tu correo antes de iniciar
          sesión.
        </p>
        <router-link
          to="/login"
          class="btn-primary"
          style="display: block; text-align: center; text-decoration: none; margin-top: 20px"
        >
          Ir a Iniciar Sesión
        </router-link>
      </div>

      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

      <p v-if="!registered" class="switch-link">
        ¿Ya tienes una cuenta? <router-link to="/login">Inicia sesión</router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import api from '../services/api'

const name = ref('')
const email = ref('')
const password = ref('')
const role = ref('aficionado')
const errorMessage = ref('')
const registered = ref(false)
const loading = ref(false)

const handleRegister = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    await api.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      role: role.value,
    })

    registered.value = true
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      const firstKey = Object.keys(errors)[0]
      errorMessage.value = firstKey ? errors[firstKey][0] : 'Error de validación.'
    } else {
      errorMessage.value = error.response?.data?.message || 'Error al registrarse.'
    }
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
.form-group input,
.form-select {
  width: 100%;
  padding: 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 15px;
  box-sizing: border-box;
  background-color: #fff;
  transition: border-color 0.2s;
}
.form-group input:focus,
.form-select:focus {
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
  box-sizing: border-box;
}
.btn-primary:hover {
  background-color: #1d4ed8;
}
.btn-primary:disabled {
  background-color: #93c5fd;
  cursor: not-allowed;
}
.success-box {
  text-align: center;
  color: #1e293b;
}
.success-box h3 {
  color: #16a34a;
  margin-bottom: 12px;
}
.instruction {
  font-size: 14px;
  color: #64748b;
  margin-top: 8px;
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
