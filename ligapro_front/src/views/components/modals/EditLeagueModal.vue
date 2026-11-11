<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../../../services/api'

const props = defineProps<{ leagueId: number | string }>()
const emit = defineEmits(['close', 'updated'])

const form = ref({
  name: '',
  category_name: '',
  description: '',
  logo: null as File | null,
})

const currentLogoUrl = ref<string | null>(null)
const logoPreview = ref<string | null>(null)
const loading = ref(true)
const saving = ref(false)
const errorMessage = ref('')

const getImageUrl = (path: string | null): string => {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) {
    if (path.startsWith('http://localhost/storage'))
      return path.replace('http://localhost', 'http://localhost:8000')
    return path
  }
  return `http://localhost:8000/storage/${path}`
}

const fetchLeague = async () => {
  try {
    loading.value = true
    const response = await api.get(`/leagues/${props.leagueId}`)
    const res = response.data
    const liga = res.liga || res.league || res.data || res

    form.value.name = liga.name || liga.nombre || ''
    form.value.category_name = liga.category_name || ''
    form.value.description = liga.description || ''
    currentLogoUrl.value = getImageUrl(liga.logo)
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
  } catch (error) {
    errorMessage.value = 'No se pudo cargar la información de la liga.'
  } finally {
    loading.value = false
  }
}

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    form.value.logo = file
    logoPreview.value = URL.createObjectURL(file)
  }
}

const handleSubmit = async () => {
  try {
    saving.value = true
    errorMessage.value = ''

    const formData = new FormData()
    formData.append('name', form.value.name)
    if (form.value.category_name) formData.append('category_name', form.value.category_name)
    if (form.value.description) formData.append('description', form.value.description)
    if (form.value.logo) formData.append('logo', form.value.logo)
    formData.append('_method', 'PUT') // Importante para Laravel al enviar archivos por POST

    await api.post(`/leagues/${props.leagueId}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    emit('updated')
    emit('close')
  } catch (error: any) {
    const errors = error.response?.data?.errors
    if (errors) {
      const firstKey = Object.keys(errors)[0]
      errorMessage.value = firstKey
        ? errors[firstKey]?.[0] || 'Error al actualizar la liga.'
        : 'Error al actualizar la liga.'
    } else {
      errorMessage.value = error.response?.data?.message || 'Error al actualizar la liga.'
    }
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchLeague()
})
</script>

<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/75 backdrop-blur-sm p-4"
  >
    <div
      class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl relative"
    >
      <!-- Botón cerrar -->
      <button
        @click="$emit('close')"
        class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-xl cursor-pointer"
      >
        ✕
      </button>

      <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-1">Editar Liga</h2>
      <p class="text-slate-500 dark:text-slate-400 text-xs mb-5">
        Modifica el nombre, categoría o actualiza el escudo oficial del torneo.
      </p>

      <div
        v-if="errorMessage"
        class="mb-4 p-3 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900 text-red-600 dark:text-red-400 rounded-xl text-xs font-medium"
      >
        {{ errorMessage }}
      </div>

      <div v-if="loading" class="text-center py-10 text-slate-500 text-sm font-medium">
        Cargando datos...
      </div>

      <form v-else @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Logo Preview y Selector -->
        <div
          class="flex items-center gap-4 bg-slate-50 dark:bg-slate-900 p-3 rounded-2xl border border-slate-200 dark:border-slate-800"
        >
          <div
            class="w-14 h-14 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden shrink-0 shadow-xs"
          >
            <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-cover" />
            <img
              v-else-if="currentLogoUrl"
              :src="currentLogoUrl"
              class="w-full h-full object-cover"
            />
            <span v-else class="text-xl">🏆</span>
          </div>
          <div class="flex-1">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1"
              >Logotipo / Escudo</label
            >
            <input
              type="file"
              @change="handleFileChange"
              accept="image/*"
              class="text-xs text-slate-500 dark:text-slate-400 file:mr-2 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-600 dark:file:bg-indigo-950 dark:file:text-indigo-400 hover:file:bg-indigo-100 cursor-pointer w-full"
            />
          </div>
        </div>

        <div>
          <label
            class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5"
          >
            Nombre de la Liga *
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div>
          <label
            class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5"
          >
            Categoría Principal *
          </label>
          <input
            v-model="form.category_name"
            type="text"
            required
            class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div>
          <label
            class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5"
          >
            Descripción (Opcional)
          </label>
          <textarea
            v-model="form.description"
            rows="2"
            class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          ></textarea>
        </div>

        <div
          class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800"
        >
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-900 cursor-pointer"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="saving"
            class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/30 cursor-pointer disabled:opacity-50"
          >
            {{ saving ? 'Guardando...' : 'Guardar Cambios' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
