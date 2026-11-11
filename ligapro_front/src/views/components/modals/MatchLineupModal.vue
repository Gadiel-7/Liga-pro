<script setup lang="ts">
import { ref, computed } from 'vue'
import api from '../../../services/api'

interface Player {
  id: number
  name: string
  number?: number
}

const props = defineProps<{
  matchId: number
  teamId: number
  teamName: string
  availablePlayers: Player[]
  matchType: 'football_5' | 'football_11' // Define si es fútbol 5 o 11
}>()

const emit = defineEmits(['close', 'saved'])

// Límite de titulares según la modalidad
const maxStarters = computed(() => (props.matchType === 'football_5' ? 5 : 11))

const selectedStarters = ref<number[]>([])
const selectedSubstitutes = ref<number[]>([])
const loading = ref(false)
const errorMessage = ref('')

const saveLineup = async () => {
  if (selectedStarters.value.length !== maxStarters.value) {
    errorMessage.value = `Debes seleccionar exactamente ${maxStarters.value} jugadores titulares.`
    return
  }

  try {
    loading.value = true
    errorMessage.value = ''

    await api.post(`/matches/${props.matchId}/lineup`, {
      team_id: props.teamId,
      starters: selectedStarters.value,
      substitutes: selectedSubstitutes.value,
    })

    emit('saved')
    emit('close')
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Error al guardar la alineación.'
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
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl text-slate-900 dark:text-white"
    >
      <div class="flex justify-between items-center mb-4">
        <div>
          <h3 class="text-xl font-black">Alineación: {{ teamName }}</h3>
          <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">
            Selecciona los {{ maxStarters }} titulares y los suplentes que ingresaron al partido.
          </p>
        </div>
        <button
          @click="$emit('close')"
          class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-lg cursor-pointer"
        >
          ✕
        </button>
      </div>

      <div
        v-if="errorMessage"
        class="mb-4 p-3 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900 text-red-600 dark:text-red-400 rounded-xl text-xs font-medium"
      >
        {{ errorMessage }}
      </div>

      <!-- Sección de Titulares -->
      <div class="mb-4">
        <label
          class="block text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-2"
        >
          Titulares ({{ selectedStarters.length }}/{{ maxStarters }})
        </label>
        <div
          class="max-h-40 overflow-y-auto space-y-1 bg-slate-50 dark:bg-slate-950 p-3 rounded-xl border border-slate-200 dark:border-slate-800"
        >
          <label
            v-for="player in availablePlayers"
            :key="player.id"
            class="flex items-center gap-2 text-sm p-1.5 hover:bg-slate-100 dark:hover:bg-slate-900 rounded cursor-pointer"
          >
            <input
              type="checkbox"
              :value="player.id"
              v-model="selectedStarters"
              :disabled="
                selectedStarters.length >= maxStarters && !selectedStarters.includes(player.id)
              "
              class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500"
            />
            <span>#{{ player.number || '--' }} {{ player.name }}</span>
          </label>
        </div>
      </div>

      <!-- Sección de Suplentes que ingresaron -->
      <div class="mb-6">
        <label
          class="block text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-2"
        >
          Suplentes que ingresaron
        </label>
        <div
          class="max-h-36 overflow-y-auto space-y-1 bg-slate-50 dark:bg-slate-950 p-3 rounded-xl border border-slate-200 dark:border-slate-800"
        >
          <label
            v-for="player in availablePlayers.filter((p) => !selectedStarters.includes(p.id))"
            :key="player.id"
            class="flex items-center gap-2 text-sm p-1.5 hover:bg-slate-100 dark:hover:bg-slate-900 rounded cursor-pointer"
          >
            <input
              type="checkbox"
              :value="player.id"
              v-model="selectedSubstitutes"
              class="rounded border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500"
            />
            <span>#{{ player.number || '--' }} {{ player.name }}</span>
          </label>
        </div>
      </div>

      <!-- Botones de Acción -->
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
        <button
          @click="$emit('close')"
          class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
        >
          Cancelar
        </button>
        <button
          @click="saveLineup"
          :disabled="loading"
          class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-600/30 cursor-pointer disabled:opacity-50"
        >
          {{ loading ? 'Guardando...' : 'Guardar Alineación' }}
        </button>
      </div>
    </div>
  </div>
</template>
