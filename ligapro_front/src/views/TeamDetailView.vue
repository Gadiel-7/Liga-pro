<template>
  <DashboardLayout>
    <!-- Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <div
        class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"
      ></div>
      <p class="mt-4 text-slate-500 font-medium text-sm">Cargando información del equipo...</p>
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
        Volver al Dashboard
      </router-link>
    </div>

    <!-- Contenido Principal -->
    <div v-else-if="team">
      <!-- ENCABEZADO ESTILO APLICACIÓN DE DEPORTES (FONDO OSCURO) -->
      <div class="bg-slate-900 text-white rounded-3xl p-8 mb-8 shadow-lg relative overflow-hidden">
        <div class="flex items-center justify-between mb-6">
          <router-link
            :to="`/leagues/${team.league_id}`"
            class="inline-flex items-center gap-2 text-sm font-medium text-slate-300 hover:text-white transition-colors"
          >
            &larr; Volver a la Liga
          </router-link>
        </div>

        <div class="flex flex-col items-center text-center">
          <div
            class="w-24 h-24 bg-slate-800 border-2 border-slate-700 rounded-full flex items-center justify-center text-white font-black text-3xl overflow-hidden shadow-md mb-4"
          >
            <img
              v-if="team.logo"
              :src="getImageUrl(team.logo)"
              class="w-full h-full object-cover"
              @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
            />
            <span v-else>{{ (team.name || 'E').charAt(0).toUpperCase() }}</span>
          </div>
          <h1 class="text-3xl font-extrabold tracking-tight mb-1">{{ team.name }}</h1>
          <p v-if="team.location" class="text-slate-400 text-sm">📍 {{ team.location }}</p>
        </div>

        <!-- MENÚ DE PESTAÑAS (TABS) -->
        <nav class="flex justify-center gap-8 border-t border-slate-800 mt-8 pt-4 overflow-x-auto">
          <button
            @click="activeTab = 'plantilla'"
            :class="
              activeTab === 'plantilla'
                ? 'text-indigo-400 border-b-2 border-indigo-400 pb-1'
                : 'text-slate-400 hover:text-white'
            "
            class="font-semibold text-sm transition-colors whitespace-nowrap"
          >
            Plantilla
          </button>
          <button
            @click="activeTab = 'partidos'"
            :class="
              activeTab === 'partidos'
                ? 'text-indigo-400 border-b-2 border-indigo-400 pb-1'
                : 'text-slate-400 hover:text-white'
            "
            class="font-semibold text-sm transition-colors whitespace-nowrap"
          >
            Partidos
          </button>
          <button
            @click="activeTab = 'estadisticas'"
            :class="
              activeTab === 'estadisticas'
                ? 'text-indigo-400 border-b-2 border-indigo-400 pb-1'
                : 'text-slate-400 hover:text-white'
            "
            class="font-semibold text-sm transition-colors whitespace-nowrap"
          >
            Estadísticas
          </button>
        </nav>
      </div>

      <!-- ==========================================
           ZONA DE CONTENIDO DE LAS PESTAÑAS
           ========================================== -->

      <!-- 1. PESTAÑA: PLANTILLA -->
      <div v-if="activeTab === 'plantilla'">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="text-xl font-bold text-slate-900">Plantilla de Jugadores</h3>
            <p class="text-slate-500 text-sm">
              Haz clic en un jugador para ver su perfil y estadísticas o editarlos.
            </p>
          </div>
          <button
            @click="openAddPlayerModal"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-colors"
          >
            + Agregar Jugador
          </button>
        </div>

        <div
          v-if="players.length === 0"
          class="bg-white border border-slate-200 rounded-2xl p-12 text-center shadow-sm"
        >
          <p class="text-slate-400 text-sm italic">
            Este equipo aún no tiene jugadores registrados.
          </p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="jugador in players"
            :key="jugador.id"
            @click="openPlayerProfile(jugador)"
            class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all cursor-pointer flex items-center justify-between"
          >
            <div class="flex items-center gap-3">
              <!-- Foto del Jugador -->
              <div
                class="w-12 h-12 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center font-bold text-indigo-700 overflow-hidden shrink-0"
              >
                <img
                  v-if="jugador.photo || jugador.foto"
                  :src="getImageUrl(jugador.photo || jugador.foto)"
                  class="w-full h-full object-cover"
                  @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
                />
                <span v-else class="text-xs">
                  {{ (jugador.name || 'J').charAt(0).toUpperCase() }}
                </span>
              </div>
              <div>
                <h4 class="font-bold text-slate-900 text-sm">{{ jugador.name }}</h4>

                <!-- ALERTA EN ROJO SI ESTÁ SUSPENDIDO (VALIDACIÓN ROBUSTA) -->
                <p
                  v-if="Number(jugador.suspended_matches || jugador.partidos_suspendidos || 0) > 0"
                  class="text-xs font-bold text-rose-600 mt-0.5 flex items-center gap-1"
                >
                  <span>
                    🚫 Suspendido: {{ jugador.suspended_matches || jugador.partidos_suspendidos }}
                    {{
                      Number(jugador.suspended_matches || jugador.partidos_suspendidos) === 1
                        ? 'partido'
                        : 'partidos'
                    }}
                  </span>
                </p>
              </div>
            </div>

            <!-- Dorsal -->
            <span
              class="px-3 py-1 bg-slate-100 border border-slate-200 rounded-lg text-xs font-black text-slate-700"
            >
              #{{ jugador.number || '0' }}
            </span>
          </div>
        </div>
      </div>

      <!-- 2. PESTAÑA: PARTIDOS -->
      <div v-if="activeTab === 'partidos'" class="space-y-4">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-slate-900">Calendario y Resultados</h3>
          <p class="text-slate-500 text-sm">Encuentros disputados y pendientes de este club.</p>
        </div>

        <div
          v-if="matches.length === 0"
          class="bg-white border border-slate-200 rounded-2xl p-12 text-center shadow-sm"
        >
          <p class="text-slate-400 text-sm italic">No hay partidos registrados para este equipo.</p>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="match in matches"
            :key="match.id"
            class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex items-center justify-between gap-4"
          >
            <div class="flex items-center gap-3 flex-1">
              <span class="font-bold text-slate-800 text-sm text-right flex-1 truncate">
                {{ match.teamA?.name || match.team_a?.name || 'Local' }}
              </span>
              <div
                class="px-3 py-1 bg-slate-100 border border-slate-200 rounded-lg text-xs font-black text-slate-700 shrink-0"
              >
                <span v-if="match.status === 'completed' || match.status === 'live'">
                  {{ match.score_a }} - {{ match.score_b }}
                </span>
                <span v-else class="text-indigo-600">VS</span>
              </div>
              <span class="font-bold text-slate-800 text-sm text-left flex-1 truncate">
                {{ match.teamB?.name || match.team_b?.name || 'Visitante' }}
              </span>
            </div>

            <div class="text-xs text-slate-500 text-right shrink-0">
              <p>📍 {{ match.location || 'Sede por definir' }}</p>
              <p>📅 {{ match.date ? new Date(match.date).toLocaleString() : 'Por programar' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. PESTAÑA: ESTADÍSTICAS GENERALES -->
      <div v-if="activeTab === 'estadisticas'" class="space-y-6">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-slate-900">Estadísticas Generales del Club</h3>
          <p class="text-slate-500 text-sm">
            Resumen de rendimiento basado en los partidos del torneo.
          </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm text-center">
            <p class="text-xs text-slate-400 font-bold uppercase mb-1">Partidos</p>
            <p class="text-3xl font-black text-slate-800">{{ teamStats.matchesPlayed }}</p>
          </div>
          <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm text-center">
            <p class="text-xs text-slate-400 font-bold uppercase mb-1">Victorias</p>
            <p class="text-3xl font-black text-emerald-600">{{ teamStats.wins }}</p>
          </div>
          <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm text-center">
            <p class="text-xs text-slate-400 font-bold uppercase mb-1">Derrotas</p>
            <p class="text-3xl font-black text-rose-600">{{ teamStats.losses }}</p>
          </div>
          <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm text-center">
            <p class="text-xs text-slate-400 font-bold uppercase mb-1">Goles a favor</p>
            <p class="text-3xl font-black text-indigo-600">{{ teamStats.goalsFor }}</p>
          </div>
          <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm text-center">
            <p class="text-xs text-slate-400 font-bold uppercase mb-1">Goles en Contra</p>
            <p class="text-3xl font-black text-amber-600">{{ teamStats.goalsAgainst }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: CREAR / EDITAR JUGADOR -->
    <AddPlayerModal
      v-if="showPlayerModal"
      :team-id="Number(teamId)"
      :team-name="team?.name || ''"
      :player-to-edit="playerToEdit"
      @close="closePlayerModal"
      @created="handlePlayerActionDone"
      @updated="handlePlayerActionDone"
    />

    <!-- MODAL: PERFIL Y ESTADÍSTICAS DEL JUGADOR -->
    <div
      v-if="showPlayerProfileModal && selectedPlayer"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
    >
      <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-xl overflow-hidden flex flex-col">
        <div class="flex justify-between items-start mb-4">
          <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg">
            Ficha de Jugador
          </span>
          <button
            @click="showPlayerProfileModal = false"
            class="text-slate-400 hover:text-slate-600 text-2xl leading-none"
          >
            &times;
          </button>
        </div>

        <div class="flex flex-col items-center text-center pb-6 border-b border-slate-100">
          <div
            class="w-20 h-20 rounded-full bg-indigo-100 border-2 border-indigo-200 text-indigo-700 font-bold flex items-center justify-center overflow-hidden shadow-sm mb-3"
          >
            <img
              v-if="selectedPlayer.photo || selectedPlayer.foto"
              :src="getImageUrl(selectedPlayer.photo || selectedPlayer.foto)"
              class="w-full h-full object-cover"
              @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
            />
            <span v-else class="text-xl">
              {{ (selectedPlayer.name || 'J').charAt(0).toUpperCase() }}
            </span>
          </div>
          <h3 class="text-xl font-extrabold text-slate-900">{{ selectedPlayer.name }}</h3>
          <p class="text-xs text-slate-500 capitalize mt-0.5">
            {{ selectedPlayer.position || 'Posición no definida' }}
            <span v-if="selectedPlayer.age">• {{ selectedPlayer.age }} años</span>
          </p>
          <div
            class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 bg-slate-900 text-white rounded-xl text-xs font-black shadow-sm"
          >
            Dorsal #{{ selectedPlayer.number || '0' }}
          </div>
        </div>

        <div class="py-5">
          <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">
            Estadísticas en la Liga
          </h4>

          <div class="grid grid-cols-2 gap-3">
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-center">
              <span class="block text-2xl font-black text-indigo-600">{{
                selectedPlayer.goals || 0
              }}</span>
              <span class="text-xs font-medium text-slate-500">Goles</span>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-center">
              <span class="block text-2xl font-black text-indigo-600">{{
                selectedPlayer.assists || 0
              }}</span>
              <span class="text-xs font-medium text-slate-500">Asistencias</span>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-center">
              <span class="block text-2xl font-black text-slate-800">{{
                selectedPlayer.matches_played || 0
              }}</span>
              <span class="text-xs font-medium text-slate-500">Partidos Jugados</span>
            </div>
            <div
              class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-center flex items-center justify-around"
            >
              <div>
                <span class="block text-lg font-black text-amber-500">{{
                  selectedPlayer.yellow_cards || 0
                }}</span>
                <span class="text-[11px] text-slate-500">Amarillas</span>
              </div>
              <div class="border-r border-slate-200 h-8"></div>
              <div>
                <span class="block text-lg font-black text-red-500">{{
                  selectedPlayer.red_cards || 0
                }}</span>
                <span class="text-[11px] text-slate-500">Rojas</span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex gap-2 mt-2">
          <button
            @click="openEditPlayer(selectedPlayer)"
            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors"
          >
            Editar Jugador
          </button>
          <button
            @click="showPlayerProfileModal = false"
            class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'

import DashboardLayout from './components/modals/DashboardLayout.vue'
import AddPlayerModal from './components/modals/AddPlayerModal.vue'

const route = useRoute()
const teamId = computed(() => route.params.id)

const activeTab = ref('plantilla')
const showPlayerModal = ref(false)

const showPlayerProfileModal = ref(false)
const selectedPlayer = ref<any>(null)
const playerToEdit = ref<any>(null)

const team = ref<any>(null)
const players = ref<any[]>([])
const matches = ref<any[]>([])
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

const teamStats = computed(() => {
  let matchesPlayed = 0
  let wins = 0
  let losses = 0
  let goalsFor = 0
  let goalsAgainst = 0

  matches.value.forEach((m) => {
    if (m.status === 'completed' || m.status === 'live') {
      const isHome = m.team_a_id === Number(teamId.value) || m.teamA?.id === Number(teamId.value)
      const isAway = m.team_b_id === Number(teamId.value) || m.teamB?.id === Number(teamId.value)

      if (isHome) {
        matchesPlayed++
        const gf = Number(m.score_a ?? 0)
        const ga = Number(m.score_b ?? 0)
        goalsFor += gf
        goalsAgainst += ga

        if (gf > ga) wins++
        else if (gf < ga) losses++
      } else if (isAway) {
        matchesPlayed++
        const gf = Number(m.score_b ?? 0)
        const ga = Number(m.score_a ?? 0)
        goalsFor += gf
        goalsAgainst += ga

        if (gf > ga) wins++
        else if (gf < ga) losses++
      }
    }
  })

  return {
    matchesPlayed,
    wins,
    losses,
    goalsFor,
    goalsAgainst,
  }
})

const fetchTeamDetails = async () => {
  if (!teamId.value) return
  try {
    loading.value = true
    const response = await api.get(`/teams/${teamId.value}`)
    const res = response.data
    team.value = res.team || res.data || res
    players.value = team.value.players || team.value.jugadores || []

    if (team.value.league_id) {
      const matchesRes = await api.get('/matches', {
        params: { league_id: team.value.league_id },
      })
      const allMatches = matchesRes.data.data || matchesRes.data.matches || matchesRes.data || []
      matches.value = allMatches.filter(
        (m: any) =>
          m.team_a_id === Number(teamId.value) ||
          m.team_b_id === Number(teamId.value) ||
          m.teamA?.id === Number(teamId.value) ||
          m.teamB?.id === Number(teamId.value),
      )
    }
  } catch (err: any) {
    console.error('Error al consultar el equipo:', err)
    error.value = 'No se pudo cargar la información del equipo.'
  } finally {
    loading.value = false
  }
}

const openPlayerProfile = (jugador: any) => {
  selectedPlayer.value = jugador
  showPlayerProfileModal.value = true
}

const openAddPlayerModal = () => {
  playerToEdit.value = null
  showPlayerModal.value = true
}

const openEditPlayer = (jugador: any) => {
  showPlayerProfileModal.value = false
  playerToEdit.value = { ...jugador }
  showPlayerModal.value = true
}

const closePlayerModal = () => {
  showPlayerModal.value = false
  playerToEdit.value = null
}

const handlePlayerActionDone = async () => {
  closePlayerModal()
  await fetchTeamDetails()

  if (selectedPlayer.value) {
    const updated = players.value.find((p) => p.id === selectedPlayer.value.id)
    if (updated) {
      selectedPlayer.value = updated
    }
  }
}

onMounted(() => {
  fetchTeamDetails()
})
</script>
