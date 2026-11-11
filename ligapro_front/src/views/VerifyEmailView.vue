<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const success = ref(false)
const message = ref('')

onMounted(async () => {
  const { id } = route.params

  try {
    // Petición directa al backend usando el ID para marcar el correo como verificado
    const response = await api.get(`/email/verify/${id}`)
    success.value = true
    message.value = response.data.message || '¡Correo electrónico verificado exitosamente!'

    // Redirige automáticamente al login después de 3 segundos
    setTimeout(() => {
      router.push('/login')
    }, 3000)
  } catch (error: unknown) {
    success.value = false
    const err = error as { response?: { data?: { message?: string } } }
    message.value =
      err.response?.data?.message || 'El enlace de verificación es inválido o ha expirado.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100">
    <div class="p-8 bg-white shadow-md rounded-lg text-center max-w-md w-full">
      <h2 class="text-2xl font-bold mb-4">Verificación de Correo</h2>
      <p v-if="loading" class="text-gray-600">Verificando tu cuenta, por favor espera...</p>
      <p v-else :class="success ? 'text-green-600' : 'text-red-600'" class="font-medium">
        {{ message }}
      </p>
      <p v-if="success" class="text-sm text-gray-500 mt-4">
        Serás redirigido al inicio de sesión en unos segundos...
      </p>
    </div>
  </div>
</template>
