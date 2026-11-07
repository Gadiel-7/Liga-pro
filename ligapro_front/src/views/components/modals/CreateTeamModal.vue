<!-- components/modals/CreateTeamModal.vue -->
<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
  >
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
      <div class="flex justify-between items-center mb-5">
        <h3 class="text-xl font-bold text-slate-900">Registrar Nuevo Equipo</h3>
        <button
          type="button"
          @click="$emit('close')"
          class="text-slate-400 hover:text-slate-600 text-2xl leading-none"
        >
          &times;
        </button>
      </div>

      <form @submit.prevent="submitTeam">
        <!-- Nombre del Equipo (Obligatorio) -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Nombre del Equipo <span class="text-red-500">*</span>
          </label>
          <input
            v-model="teamForm.name"
            type="text"
            required
            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            placeholder="Ej. Cuervos FC"
          />
        </div>

        <!-- Tag / Abreviatura (Columna 'tag') -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Abreviatura / Tag (Opcional)
          </label>
          <input
            v-model="teamForm.tag"
            type="text"
            maxlength="5"
            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 uppercase"
            placeholder="Ej. CUE"
          />
        </div>

        <!-- Categoría (Columna 'category_id') -->
        <div v-if="categories && categories.length > 0" class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Categoría (Opcional)
          </label>
          <select
            v-model="teamForm.category_id"
            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
          >
            <option value="">-- Seleccionar Categoría --</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <!-- Escudo / Logo (Columna 'logo') -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Escudo / Logo (Opcional)
          </label>
          <input
            @change="handleTeamLogo"
            type="file"
            accept="image/*"
            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
          />
        </div>

        <!-- Botones de Acción -->
        <div class="flex justify-end gap-3">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="isSubmittingTeam"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50"
          >
            {{ isSubmittingTeam ? 'Guardando...' : 'Guardar Equipo' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import api from '../../../services/api'

interface Category {
  id: string | number
  name: string
}

const props = defineProps<{
  leagueId: string | number
  categories?: Category[]
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'team-created'): void
}>()

const isSubmittingTeam = ref(false)
const teamForm = ref({
  name: '',
  tag: '',
  category_id: '' as string | number,
  logo: null as File | null,
})

const handleTeamLogo = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    teamForm.value.logo = target.files[0]!
  }
}

const submitTeam = async () => {
  try {
    isSubmittingTeam.value = true
    const formData = new FormData()

    formData.append('name', teamForm.value.name)
    formData.append('league_id', String(props.leagueId))

    if (teamForm.value.tag) {
      formData.append('tag', teamForm.value.tag.toUpperCase())
    }

    if (teamForm.value.category_id) {
      formData.append('category_id', String(teamForm.value.category_id))
    }

    if (teamForm.value.logo) {
      formData.append('logo', teamForm.value.logo)
    }

    await api.post('/teams', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    emit('team-created')
  } catch (err: any) {
    console.error('Error al crear equipo:', err)
    alert(err.response?.data?.message || 'Hubo un error al crear el equipo.')
  } finally {
    isSubmittingTeam.value = false
  }
}
</script>
