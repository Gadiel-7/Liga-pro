<script setup lang="ts">
import { ref } from 'vue'
import api from '../../../services/api'

const emit = defineEmits(['close', 'created'])

const form = ref({
  name: '',
  category_name: '',
  description: '',
})

const loading = ref(false)
const errorMessage = ref('')

const handleSubmit = async () => {
  try {
    loading.value = true
    errorMessage.value = ''

    const response = await api.post('/leagues', form.value)

    const createdLeague = response.data.data?.league || response.data.data || response.data
    emit('created', createdLeague)
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    const errors = error.response?.data?.errors
    if (errors) {
      const firstKey = Object.keys(errors)[0]
      errorMessage.value = errors[firstKey][0]
    } else {
      errorMessage.value = error.response?.data?.message || 'Ocurrió un error al crear la liga.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
  >
    <div
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-8 shadow-2xl relative"
    >
      <!-- Botón cerrar -->
      <button
        @click="$emit('close')"
        class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-xl cursor-pointer"
      >
        ✕
      </button>

      <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Crear Nueva Liga</h2>
      <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">
        Configura los datos principales de tu torneo oficial y su categoría inicial.
      </p>

      <div
        v-if="errorMessage"
        class="mb-4 p-3 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900 text-red-600 dark:text-red-400 rounded-xl text-sm font-medium"
      >
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label
            class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2"
          >
            Nombre de la Liga *
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            placeholder="Ej. Liga Municipal de Halachó"
            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div>
          <label
            class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2"
          >
            Categoría Principal *
          </label>
          <input
            v-model="form.category_name"
            type="text"
            required
            placeholder="Ej. Primera Fuerza / Libre Varonil"
            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div>
          <label
            class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2"
          >
            Descripción (Opcional)
          </label>
          <textarea
            v-model="form.description"
            rows="3"
            placeholder="Breve descripción del torneo..."
            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          ></textarea>
        </div>

        <div
          class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800"
        >
          <button
            type="button"
            @click="$emit('close')"
            class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-6 py-2.5 rounded-xl text-sm font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-600/30 transition-all cursor-pointer disabled:opacity-50"
          >
            {{ loading ? 'Guardando...' : 'Crear Liga' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
