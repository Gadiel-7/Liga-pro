<template>
  <div class="auth-container">
    <div class="auth-card">
      <!-- LOGOTIPO Y ENCABEZADO -->
      <div class="logo-container">
        <div class="logo-wrapper">
          <img
            src="../assets/logo.png"
            alt="LigaPro Logo"
            class="app-logo"
            @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
          />
        </div>
        <h2>Crear Cuenta</h2>
        <p class="subtitle">Únete a la mejor plataforma de gestión deportiva</p>
      </div>

      <form v-if="!registered" @submit.prevent="handleRegister">
        <div class="form-group">
          <label>Nombre Completo</label>
          <input type="text" v-model="name" required placeholder="Tu nombre" />
        </div>

        <div class="form-group">
          <label>Correo Electrónico</label>
          <input type="email" v-model="email" required placeholder="ejemplo@gmail.com" />
        </div>

        <div class="form-group">
          <label>Contraseña</label>
          <input type="password" v-model="password" required placeholder="Mínimo 6 caracteres" />
        </div>

        <div class="form-group">
          <label>Rol de Usuario</label>
          <select v-model="role" required class="form-select">
            <option value="aficionado">Aficionado</option>
            <option value="representante">Representante</option>
            <option value="admin">Administrador</option>
          </select>
        </div>

        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Registrando...' : 'Completar Registro' }}
        </button>
      </form>

      <!-- Mensaje de éxito post-registro -->
      <div v-else class="success-box">
        <div class="success-icon">✉️</div>
        <h3>¡Cuenta creada con éxito!</h3>
        <p>
          Hemos enviado un enlace de confirmación a
          <strong class="text-slate-900">{{ email }}</strong
          >.
        </p>
        <p class="instruction">
          Revisa tu bandeja de entrada o spam para verificar tu cuenta antes de iniciar sesión.
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
        ¿Ya tienes cuenta? <router-link to="/login">Inicia sesión</router-link>
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
  background-color: #020617;
  background-image: radial-gradient(circle at 50% 0%, rgba(34, 197, 94, 0.12) 0%, transparent 65%);
}

.auth-card {
  width: 100%;
  max-width: 440px;
  padding: 40px;
  background: #ffffff;
  border-radius: 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
  box-sizing: border-box;
  max-height: 90vh;
  overflow-y: auto;
}

.logo-container {
  text-align: center;
  margin-bottom: 24px;
}

.logo-wrapper {
  width: 64px;
  height: 64px;
  margin: 0 auto 14px auto;
  border-radius: 16px;
  background: #090d16;
  border: 2px solid #22c55e;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 15px rgba(34, 197, 94, 0.3);
  overflow: hidden;
}

.app-logo {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.auth-card h2 {
  margin: 0;
  color: #0f172a;
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -0.5px;
}

.subtitle {
  color: #64748b;
  font-size: 13px;
  margin-top: 4px;
  margin-bottom: 0;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 700; /* Texto de etiquetas más marcado */
  color: #080808; /* Tono más oscuro para máxima legibilidad */
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-group input,
.form-select {
  width: 100%;
  padding: 12px 16px;
  background-color: #ffffff !important; /* Forzamos fondo blanco limpio */
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  font-size: 14px;
  color: #0f172a !important;
  box-sizing: border-box;
  transition: all 0.2s ease;
}

.form-group input::placeholder {
  color: #94a3b8;
}

/* Evitamos que el navegador pinte de gris o cambie el fondo al autocompletar */
.form-group input:-webkit-autofill,
.form-group input:-webkit-autofill:hover,
.form-group input:-webkit-autofill:focus {
  -webkit-box-shadow: 0 0 0 30px #ffffff inset !important;
  -webkit-text-fill-color: #0f172a !important;
}

.form-group input:focus,
.form-select:focus {
  outline: none;
  /* Al enfocar, cambia a verde brillante del logo con un borde remarcado */
  border-color: #16a34a;
  box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.25);
  background-color: #f7f5f5;
}

.btn-primary {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
  color: #ffffff;
  border: none;
  border-radius: 12px;
  font-weight: 800;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 10px;
  box-shadow: 0 4px 14px rgba(34, 197, 94, 0.3);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
}

.success-box {
  text-align: center;
  color: #0f172a;
}

.success-icon {
  font-size: 40px;
  margin-bottom: 12px;
}

.success-box h3 {
  color: #16a34a;
  margin-bottom: 10px;
  font-size: 20px;
}

.instruction {
  font-size: 13px;
  color: #f4f5f5;
  margin-top: 8px;
  line-height: 1.5;
}

.error {
  color: #dc2626;
  margin-top: 14px;
  font-size: 13px;
  text-align: center;
  font-weight: 500;
}

.switch-link {
  margin-top: 20px;
  text-align: center;
  font-size: 14px;
  color: #f4f6f9;
}

.switch-link a {
  color: #16a34a;
  text-decoration: none;
  font-weight: 600;
}

.switch-link a:hover {
  text-decoration: underline;
}
</style>
