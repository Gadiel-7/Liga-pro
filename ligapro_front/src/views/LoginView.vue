<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="logo-container">
        <div class="logo-wrapper">
          <img
            src="../assets/logo.png"
            alt="LigaPro Logo"
            class="app-logo"
            @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
          />
        </div>
        <h2>
          {{ viewMode === 'login' ? 'Iniciar Sesión' : 'Recuperar Contraseña' }}
        </h2>
        <p class="subtitle">
          {{ viewMode === 'login' }}
        </p>
      </div>

      <!-- VISTA 1: INICIO DE SESIÓN -->
      <form v-if="viewMode === 'login'" @submit.prevent="handleLogin">
        <div class="form-group">
          <label>Correo Electrónico</label>
          <input
            type="email"
            v-model="email"
            required
            autocomplete="email"
            placeholder="ejemplo@gmail.com"
          />
        </div>

        <div class="form-group">
          <label>Contraseña</label>
          <input
            type="password"
            v-model="password"
            required
            autocomplete="current-password"
            placeholder="••••••••"
          />
          <div class="field-footer">
            <a href="#" @click.prevent="changeView('forgot-password')" class="forgot-link">
              ¿Olvidaste tu contraseña?
            </a>
          </div>
        </div>

        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Iniciando sesión...' : 'Entrar' }}
        </button>
      </form>

      <!-- VISTA 2: RECUPERAR CONTRASEÑA -->
      <form v-else-if="viewMode === 'forgot-password'" @submit.prevent="handleForgotPassword">
        <div class="form-group">
          <label>Correo Electrónico Registrado</label>
          <input
            type="email"
            v-model="forgotEmail"
            required
            autocomplete="email"
            placeholder="ejemplo@gmail.com"
          />
        </div>

        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Enviando...' : 'Enviar enlace de recuperación' }}
        </button>

        <button
          type="button"
          @click="changeView('login')"
          class="btn-secondary"
          style="margin-top: 10px"
        >
          Volver al Inicio de Sesión
        </button>
      </form>

      <div v-if="needsVerification" class="verification-box">
        <p>⚠️ Tu correo electrónico aún no ha sido verificado.</p>
        <button @click="resendVerification" :disabled="resending" class="btn-secondary">
          {{ resending ? 'Enviando...' : 'Reenviar correo de verificación' }}
        </button>
        <p v-if="resendMessage" class="success-small">{{ resendMessage }}</p>
      </div>

      <p v-if="successMessage" class="success-msg">{{ successMessage }}</p>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

      <p v-if="viewMode === 'login'" class="switch-link">
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
const viewMode = ref<'login' | 'forgot-password'>('login')

const email = ref('')
const password = ref('')
const forgotEmail = ref('')

const errorMessage = ref('')
const successMessage = ref('')
const resendMessage = ref('')
const loading = ref(false)
const resending = ref(false)
const needsVerification = ref(false)

const changeView = (mode: 'login' | 'forgot-password') => {
  viewMode.value = mode
  errorMessage.value = ''
  successMessage.value = ''
}

const handleLogin = async () => {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''
  needsVerification.value = false

  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value,
    })

    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')

    localStorage.setItem('auth_token', response.data.access_token)
    localStorage.setItem('user', JSON.stringify(response.data.user))

    const user = response.data.user

    if (user.role === 'admin') {
      router.push('/leagues')
    } else {
      router.push('/explorer')
    }
  } catch (error: any) {
    const status = error.response?.status
    const data = error.response?.data

    if (status === 403 && data?.email_verified === false) {
      needsVerification.value = true
      errorMessage.value = data.message
    } else {
      errorMessage.value = data?.message || 'Credenciales incorrectas o error de conexión'
    }
  } finally {
    loading.value = false
  }
}

const handleForgotPassword = async () => {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response = await api.post('/forgot-password', { email: forgotEmail.value })
    successMessage.value =
      response.data?.message || '¡Enlace enviado! Revisa tu bandeja de entrada.'
  } catch (error: any) {
    const data = error.response?.data
    if (data?.errors?.email) {
      errorMessage.value = data.errors.email[0]
    } else {
      errorMessage.value = data?.message || 'No se pudo procesar la solicitud. Verifica el correo.'
    }
  } finally {
    loading.value = false
  }
}

const resendVerification = async () => {
  resending.value = true
  resendMessage.value = ''
  try {
    await api.post('/email/resend', { email: email.value })
    resendMessage.value = '¡Correo de verificación reenviado! Revisa tu bandeja.'
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'No se pudo reenviar el correo.'
  } finally {
    resending.value = false
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
}

.logo-container {
  text-align: center;
  margin-bottom: 24px;
}

.logo-wrapper {
  width: 68px;
  height: 68px;
  margin: 0 auto 14px auto;
  border-radius: 18px;
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
  font-size: 26px;
  font-weight: 800;
  letter-spacing: -0.5px;
}

.subtitle {
  color: #64748b;
  font-size: 13px;
  margin-top: 6px;
  margin-bottom: 0;
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  font-weight: 700;
  color: #080808;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.form-group input {
  width: 100%;
  padding: 13px 16px;
  background-color: #ffffff !important;
  border: 2px solid #cbd5e1;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  color: #0f172a !important;
  box-sizing: border-box;
  transition: all 0.2s ease;
}

.form-group input::placeholder {
  color: #94a3b8;
  font-weight: 500;
}

.field-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 6px;
}

.forgot-link {
  font-size: 12px;
  color: #16a34a;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s;
}

.forgot-link:hover {
  text-decoration: underline;
  color: #15803d;
}

.form-group input:focus {
  outline: none;
  border-color: #16a34a;
  box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.25);
  background-color: #ffffff !important;
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

.btn-primary:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
}

.btn-secondary {
  width: 100%;
  padding: 12px;
  background-color: #f1f5f9;
  color: #334155;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-secondary:hover {
  background-color: #e2e8f0;
}

.verification-box {
  margin-top: 16px;
  padding: 14px;
  background-color: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 12px;
  font-size: 13px;
  color: #b45309;
  text-align: center;
}

.error {
  color: #dc2626;
  margin-top: 14px;
  font-size: 13px;
  text-align: center;
  font-weight: 500;
}

.success-msg {
  color: #16a34a;
  margin-top: 14px;
  font-size: 14px;
  text-align: center;
  font-weight: 600;
}

.success-small {
  color: #16a34a;
  margin-top: 6px;
  font-size: 12px;
}

.switch-link {
  margin-top: 24px;
  text-align: center;
  font-size: 14px;
  color: #64748b;
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
