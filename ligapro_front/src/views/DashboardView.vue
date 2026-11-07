<template>
  <DashboardLayout>
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <span class="text-3xl">🏆</span>
          <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Mis Ligas</h1>
        </div>
        <p class="text-slate-500 text-sm">
          Selecciona una liga para gestionar sus equipos, plantillas y partidos.
        </p>
      </div>
    </div>

    <!-- Estado: Cargando -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <div
        class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"
      ></div>
      <p class="mt-4 text-slate-500 font-medium text-sm">Cargando tus ligas...</p>
    </div>

    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-2xl text-center"
    >
      <p class="font-semibold mb-2">{{ error }}</p>
      <button
        @click="fetchLeagues"
        class="text-xs bg-red-100 hover:bg-red-200 text-red-800 font-bold px-4 py-2 rounded-xl transition-colors inline-block mt-2"
      >
        Reintentar
      </button>
    </div>

    <div
      v-else-if="ligas.length === 0"
      class="bg-white border border-slate-200 rounded-2xl p-12 text-center shadow-sm"
    >
      <div
        class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold"
      >
        🏆
      </div>
      <h3 class="text-lg font-bold text-slate-900 mb-1">No tienes ligas creadas</h3>
      <p class="text-slate-500 text-sm max-w-md mx-auto mb-6">
        Crea tu primera liga para empezar a registrar clubes y llevar el control del torneo.
      </p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="liga in ligas"
        :key="liga.id"
        class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
      >
        <div>
          <div class="flex items-start gap-4">
            <div
              class="w-14 h-14 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 text-2xl shrink-0 overflow-hidden shadow-sm"
            >
              <img
                v-if="liga.logo"
                :src="liga.logo"
                :alt="liga.name"
                class="w-full h-full object-cover"
              />
              <span v-else>⚽</span>
            </div>

            <div class="overflow-hidden">
              <h3 class="font-bold text-slate-900 text-lg truncate">
                {{ liga.name }}
              </h3>
              <span
                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold mt-1"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activa
              </span>
            </div>
          </div>

          <p v-if="liga.description" class="text-slate-500 text-xs mt-4 line-clamp-2">
            {{ liga.description }}
          </p>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end">
          <router-link
            :to="`/leagues/${liga.id}`"
            class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-colors"
          >
            Entrar a la liga &rarr;
          </router-link>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import DashboardLayout from './components/modals/DashboardLayout.vue'
import api from '../services/api'

interface League {
  id: number
  name: string
  logo?: string | null
  description?: string | null
}

const ligas = ref<League[]>([])
const loading = ref<boolean>(true)
const error = ref<string | null>(null)

const fetchLeagues = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await api.get('/leagues')

    const data = response.data
    // Asignación directa
    ligas.value = Array.isArray(data) ? data : data.data || []
  } catch (err: any) {
    console.error('Error al cargar las ligas:', err)
    error.value = err.response?.data?.message || 'No se pudieron obtener las ligas.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchLeagues()
})
</script>
