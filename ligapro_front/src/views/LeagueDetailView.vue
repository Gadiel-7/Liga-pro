<template>
  <DashboardLayout>
    <div class="mb-6">
      <router-link
        to="/dashboard"
        class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors"
      >
        &larr; Volver a Mis Ligas
      </router-link>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <div
        class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"
      ></div>
      <p class="mt-4 text-slate-500 font-medium text-sm">Cargando información de la liga...</p>
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-xl text-center"
    >
      <p class="font-semibold mb-2">{{ error }}</p>
      <router-link
        to="/dashboard"
        class="text-xs bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1.5 rounded-lg transition-colors inline-block mt-2"
      >
        Volver a Mis Ligas
      </router-link>
    </div>

    <!-- Contenido Principal -->
    <div v-else-if="liga">
      <!-- Encabezado de la Liga -->
      <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
          <div
            class="w-14 h-14 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 font-black text-2xl shrink-0 overflow-hidden shadow-sm"
          >
            <img
              v-if="liga.logo"
              :src="getImageUrl(liga.logo)"
              :alt="liga.nombre || liga.name"
              class="w-full h-full object-cover"
              @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
            />
            <span v-else>🏆</span>
          </div>
          <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
              {{ liga.nombre || liga.name }}
            </h1>
            <p class="text-slate-500 text-sm mt-1">
              Gestión completa de clubes, plantillas y torneos.
            </p>
          </div>
        </div>

        <!-- Botón de registrar equipo (solo visible en la pestaña de equipos) -->
        <button
          v-if="activeTab === 'equipos'"
          @click="showTeamModal = true"
          class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-sm text-sm transition-all shrink-0"
        >
          + Registrar Nuevo Equipo
        </button>
      </header>

      <!-- MENÚ DE PESTAÑAS (TABS) -->
      <nav class="flex gap-6 border-b border-slate-200 mb-8 px-2 overflow-x-auto">
        <button
          @click="activeTab = 'equipos'"
          :class="
            activeTab === 'equipos'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-500 hover:text-slate-700'
          "
          class="pb-3 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
        >
          Equipos
        </button>

        <button
          @click="activeTab = 'partidos'"
          :class="
            activeTab === 'partidos'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-500 hover:text-slate-700'
          "
          class="pb-3 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
        >
          Partidos
        </button>

        <!-- PESTAÑA CLASIFICACIÓN -->
        <button
          @click="activeTab = 'clasificacion'"
          :class="
            activeTab === 'clasificacion'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-500 hover:text-slate-700'
          "
          class="pb-3 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
        >
          Clasificación
        </button>

        <!-- PESTAÑA ESTADÍSTICAS -->
        <button
          @click="activeTab = 'estadisticas'"
          :class="
            activeTab === 'estadisticas'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-500 hover:text-slate-700'
          "
          class="pb-3 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
        >
          Estadísticas
        </button>

        <button
          @click="activeTab = 'resumen'"
          :class="
            activeTab === 'resumen'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-500 hover:text-slate-700'
          "
          class="pb-3 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
        >
          Resumen
        </button>
      </nav>

      <!-- ==========================================
           ZONA DE CONTENIDO DINÁMICO
           ========================================== -->

      <!-- 1. PESTAÑA: EQUIPOS -->
      <div v-if="activeTab === 'equipos'">
        <div
          v-if="equipos.length === 0"
          class="bg-white border border-slate-200 rounded-2xl p-12 text-center shadow-sm"
        >
          <div
            class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold"
          >
            🛡️
          </div>
          <h3 class="text-lg font-bold text-slate-900 mb-1">No hay equipos registrados</h3>
          <p class="text-slate-500 text-sm max-w-md mx-auto mb-6">
            Esta liga aún no tiene clubes asignados.
          </p>
          <button
            @click="showTeamModal = true"
            class="bg-indigo-600 text-white font-semibold px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition-colors"
          >
            Agregar el primer equipo
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="equipo in equipos"
            :key="equipo.id"
            @click="$router.push(`/teams/${equipo.id}`)"
            class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all cursor-pointer flex items-center gap-4"
          >
            <div
              class="w-16 h-16 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 font-black text-xl shrink-0 overflow-hidden shadow-2xs"
            >
              <img
                v-if="equipo.logo"
                :src="getImageUrl(equipo.logo)"
                class="w-full h-full object-cover"
                @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
              />
              <span v-else>{{
                (equipo.nombre || equipo.name || 'E').charAt(0).toUpperCase()
              }}</span>
            </div>

            <div class="overflow-hidden">
              <h3 class="font-bold text-slate-900 text-lg truncate">
                {{ equipo.nombre || equipo.name }}
              </h3>
              <p v-if="equipo.location" class="text-xs text-slate-500 truncate mt-1">
                📍 {{ equipo.location }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. PESTAÑA: PARTIDOS -->
      <div v-if="activeTab === 'partidos'">
        <MatchesView :league-id="leagueId" />
      </div>

      <!-- 3. PESTAÑA: CLASIFICACIÓN -->
      <div v-if="activeTab === 'clasificacion'">
        <StandingsView :league-id="leagueId ?? ''" />
      </div>

      <!-- 4. PESTAÑA: ESTADÍSTICAS DE JUGADORES -->
      <div v-if="activeTab === 'estadisticas'">
        <StatisticsView :league-id="leagueId ?? ''" />
      </div>

      <!-- 5. PESTAÑA: RESUMEN -->
      <div v-if="activeTab === 'resumen'">
        <OverView :league-id="leagueId" />
      </div>
    </div>

    <!-- MODAL: REGISTRAR EQUIPO -->
    <CreateTeamModal
      v-if="showTeamModal"
      :league-id="Number(leagueId)"
      @close="showTeamModal = false"
      @team-created="handleTeamCreated"
    />

    <!-- MODAL: AGREGAR JUGADOR -->
    <AddPlayerModal
      v-if="showPlayerModal"
      :team-id="selectedTeamId!"
      :team-name="selectedTeamName"
      @close="showPlayerModal = false"
      @created="handlePlayerCreated"
    />
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'

import DashboardLayout from './components/modals/DashboardLayout.vue'
import CreateTeamModal from './components/modals/CreateTeamModal.vue'
import AddPlayerModal from './components/modals/AddPlayerModal.vue'
import MatchesView from './MatchesView.vue'
import StandingsView from './StandingsView.vue'
import StatisticsView from './StatisticsView.vue'
import OverView from './OverView.vue'

const route = useRoute()
const leagueId = computed(() => {
  const id = route.params.id
  return Array.isArray(id) ? id[0] : id
})

const activeTab = ref('equipos')
const showTeamModal = ref(false)

const showPlayerModal = ref(false)
const selectedTeamId = ref<number | null>(null)
const selectedTeamName = ref('')

const liga = ref<any>(null)
const equipos = ref<any[]>([])
const loading = ref<boolean>(true)
const error = ref<string | null>(null)

const getImageUrl = (path: string | null): string => {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) {
    if (path.startsWith('http://localhost/storage'))
      return path.replace('http://localhost', 'http://localhost:8000')
    return path
  }
  return `http://localhost:8000/storage/${path}`
}

const fetchLeagueDetails = async () => {
  if (!leagueId.value) return
  try {
    loading.value = true
    const response = await api.get(`/leagues/${leagueId.value}`)
    const res = response.data
    liga.value = res.liga || res.league || res.data || res
    equipos.value =
      res.equipos || res.teams || res.liga?.equipos || res.league?.teams || res.data?.equipos || []
  } catch (err: any) {
    console.error('Error al consultar la liga:', err)
    error.value = 'Error al consultar la liga.'
  } finally {
    loading.value = false
  }
}

const handleTeamCreated = () => {
  showTeamModal.value = false
  fetchLeagueDetails()
}

const handlePlayerCreated = async () => {
  showPlayerModal.value = false
  await fetchLeagueDetails()
}

watch(leagueId, (newId) => {
  if (newId) fetchLeagueDetails()
})
onMounted(() => {
  fetchLeagueDetails()
})
</script>
