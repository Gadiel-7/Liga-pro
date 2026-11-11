<template>
  <div
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full antialiased selection:bg-indigo-500 selection:text-white"
  >
    <!-- ENCABEZADO DE LA SECCIÓN CON DISEÑO DE CLASE MUNDIAL -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
      <div>
        <div
          class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 dark:bg-indigo-500/20 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xs font-bold uppercase tracking-wider mb-3 backdrop-blur-md"
        >
          <span class="relative flex h-2 w-2">
            <span
              class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"
            ></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
          </span>
          <span>Gestión de Torneo y Jornadas</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
          Calendario de Partidos
        </h1>
        <p
          class="text-slate-500 dark:text-slate-400 text-base mt-2 max-w-2xl font-normal leading-relaxed"
        >
          Registra manualmente los partidos de la Jornada 1 y genera automáticamente el resto del
          rol de juegos a partir de la Jornada 2.
        </p>
      </div>

      <!-- BOTONES DE ACCIÓN PRINCIPAL -->
      <div class="flex items-center gap-3 flex-wrap">
        <button
          @click="showCreateManualMatchModal = true"
          class="inline-flex items-center justify-center gap-2 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-bold px-5 py-4 rounded-2xl border border-indigo-200 dark:border-indigo-900 hover:bg-indigo-100 transition-all text-sm shrink-0 cursor-pointer"
        >
          <span>➕</span> Registrar Partido Manual
        </button>

        <button
          @click="generateMatches"
          :disabled="generating"
          class="inline-flex items-center justify-center gap-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold px-7 py-4 rounded-2xl shadow-xl shadow-slate-900/10 dark:shadow-white/5 hover:bg-indigo-600 dark:hover:bg-indigo-50 dark:hover:text-indigo-600 hover:shadow-indigo-500/25 transition-all text-sm shrink-0 cursor-pointer disabled:opacity-50"
        >
          <span
            v-if="generating"
            class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"
          ></span>
          <span v-else class="text-lg">⚡</span>
          {{ generating ? 'Generando...' : 'Generar Rol Automático (J2+)' }}
        </button>
      </div>
    </div>

    <!-- ESTADO DE CARGA -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-24">
      <div
        class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin shadow-lg shadow-indigo-500/20"
      ></div>
      <p class="mt-4 text-slate-500 dark:text-slate-400 font-semibold text-sm tracking-wide">
        Cargando calendario del torneo...
      </p>
    </div>

    <!-- ESTADO VACÍO -->
    <div
      v-else-if="matchesByRound.length === 0"
      class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-20 text-center shadow-sm"
    >
      <div
        class="w-24 h-24 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-3xl flex items-center justify-center mx-auto mb-6 text-5xl font-bold shadow-inner border border-indigo-100 dark:border-indigo-900/50"
      >
        ⚽
      </div>
      <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-3">
        Aún no hay partidos programados
      </h3>
      <p
        class="text-slate-500 dark:text-slate-400 text-base max-w-md mx-auto mb-8 font-normal leading-relaxed"
      >
        Comienza registrando manualmente los encuentros de la Jornada 1 o genera el rol automático a
        partir de la Jornada 2.
      </p>
    </div>

    <!-- LISTADO DE JORNADAS Y PARTIDOS -->
    <div v-else class="space-y-8">
      <div
        v-for="(round, index) in matchesByRound"
        :key="index"
        class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-7 shadow-sm relative overflow-hidden"
      >
        <!-- Encabezado de la Jornada -->
        <div
          class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800/80"
        >
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black text-sm"
            >
              #{{ round.jornada || index + 1 }}
            </div>
            <h4 class="font-extrabold text-slate-900 dark:text-white text-lg tracking-tight">
              Jornada {{ round.jornada || index + 1 }}
            </h4>
          </div>

          <div class="flex items-center gap-3 flex-wrap">
            <span
              v-if="round.restingTeam"
              class="px-3.5 py-1 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 text-xs font-bold border border-amber-200 dark:border-amber-900 flex items-center gap-1.5"
            >
              <span>💤</span> Descansa: {{ round.restingTeam.name }}
            </span>

            <span
              class="px-3.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold uppercase tracking-wider"
            >
              {{ round.matches?.length ?? 0 }} partidos
            </span>
          </div>
        </div>

        <!-- Grid de Partidos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div
            v-for="match in round.matches || []"
            :key="match.id"
            class="group bg-slate-50/80 dark:bg-slate-950/50 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between gap-4 hover:border-indigo-500/50 transition-all shadow-2xs"
          >
            <div class="flex items-center justify-between gap-3">
              <!-- Equipo Local -->
              <div class="flex items-center gap-3 flex-1 justify-end overflow-hidden">
                <span
                  class="font-bold text-slate-800 dark:text-slate-200 truncate text-right text-sm group-hover:text-indigo-600 transition-colors"
                >
                  {{ match.team_a?.name || match.team_a?.nombre || 'Local' }}
                </span>
                <div
                  class="w-10 h-10 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center font-bold text-xs text-indigo-600 shrink-0 overflow-hidden shadow-sm"
                >
                  <img
                    v-if="match.team_a?.logo"
                    :src="getImageUrl(match.team_a.logo)"
                    class="w-full h-full object-cover"
                    @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
                  />
                  <span v-else>{{
                    (match.team_a?.name || match.team_a?.nombre || 'L').charAt(0).toUpperCase()
                  }}</span>
                </div>
              </div>

              <!-- Marcador o VS -->
              <div
                class="px-4 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-black text-slate-800 dark:text-slate-200 shrink-0 shadow-sm"
              >
                <span
                  v-if="match.status === 'completed' || match.status === 'live'"
                  class="tracking-widest"
                >
                  {{ match.score_a }} - {{ match.score_b }}
                </span>
                <span v-else class="text-indigo-600 dark:text-indigo-400 tracking-wider">VS</span>
              </div>

              <!-- Equipo Visitante -->
              <div class="flex items-center gap-3 flex-1 justify-start overflow-hidden">
                <div
                  class="w-10 h-10 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center font-bold text-xs text-indigo-600 shrink-0 overflow-hidden shadow-sm"
                >
                  <img
                    v-if="match.team_b?.logo"
                    :src="getImageUrl(match.team_b.logo)"
                    class="w-full h-full object-cover"
                    @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
                  />
                  <span v-else>{{
                    (match.team_b?.name || match.team_b?.nombre || 'V').charAt(0).toUpperCase()
                  }}</span>
                </div>
                <span
                  class="font-bold text-slate-800 dark:text-slate-200 truncate text-left text-sm group-hover:text-indigo-600 transition-colors"
                >
                  {{ match.team_b?.name || match.team_b?.nombre || 'Visitante' }}
                </span>
              </div>
            </div>

            <!-- Fila inferior: Sede, Fecha y Botón Editar -->
            <div
              class="flex items-center justify-between pt-3 border-t border-slate-200/60 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400"
            >
              <div class="space-y-1 font-medium">
                <p class="flex items-center gap-1.5">
                  <span>📍</span> {{ match.location || 'Sede por definir' }}
                </p>
                <p class="flex items-center gap-1.5">
                  <span>📅</span>
                  {{ match.date ? new Date(match.date).toLocaleString() : 'Por programar' }}
                </p>
              </div>
              <button
                @click="openEditMatch(match.id)"
                class="bg-white dark:bg-slate-900 hover:bg-indigo-50 border border-slate-200 dark:border-slate-800 text-indigo-600 font-bold px-3.5 py-2 rounded-xl transition-all shadow-2xs shrink-0 cursor-pointer flex items-center gap-1.5"
              >
                <span>✏️</span> Editar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: REGISTRAR PARTIDO MANUAL -->
    <div
      v-if="showCreateManualMatchModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/75 backdrop-blur-sm p-4"
    >
      <div
        class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl relative text-slate-900 dark:text-white"
      >
        <button
          @click="showCreateManualMatchModal = false"
          class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer"
        >
          ✕
        </button>
        <h2 class="text-2xl font-black mb-1">Registrar Partido</h2>
        <p class="text-slate-500 text-xs mb-5">
          Ideal para configurar manualmente los duelos de la Jornada 1.
        </p>

        <form @submit.prevent="storeManualMatch" class="space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5"
              >Equipo Local *</label
            >
            <select
              v-model="manualForm.team_a_id"
              required
              class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm"
            >
              <option value="" disabled>Selecciona equipo...</option>
              <option v-for="t in leagueTeams" :key="t.id" :value="t.id">
                {{ t.name || t.nombre }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5"
              >Equipo Visitante *</label
            >
            <select
              v-model="manualForm.team_b_id"
              required
              class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm"
            >
              <option value="" disabled>Selecciona equipo...</option>
              <option
                v-for="t in leagueTeams.filter((x) => x.id !== manualForm.team_a_id)"
                :key="t.id"
                :value="t.id"
              >
                {{ t.name || t.nombre }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5"
              >Fecha y Hora *</label
            >
            <input
              v-model="manualForm.date"
              type="datetime-local"
              required
              class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm"
            />
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5"
              >Sede</label
            >
            <input
              v-model="manualForm.location"
              type="text"
              placeholder="Campo Principal"
              class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm"
            />
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="showCreateManualMatchModal = false"
              class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="savingManual"
              class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer disabled:opacity-50"
            >
              {{ savingManual ? 'Guardando...' : 'Crear Partido' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL DE EDICIÓN DE PARTIDO -->
    <EditMatchModal
      v-if="showEditMatchModal && selectedMatchId"
      :match-id="selectedMatchId"
      @close="showEditMatchModal = false"
      @updated="handleMatchUpdated"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'
import EditMatchModal from './components/modals/EditMatchModal.vue'

const route = useRoute()
const props = defineProps<{
  leagueId?: string | number
}>()

const activeLeagueId = computed(() => props.leagueId || route.params.id || route.params.leagueId)

const matches = ref<any[]>([])
const leagueTeams = ref<any[]>([])
const roundsData = ref<any[]>([])
const loading = ref(true)
const generating = ref(false)

const showEditMatchModal = ref(false)
const selectedMatchId = ref<number | null>(null)

const showCreateManualMatchModal = ref(false)
const savingManual = ref(false)
const manualForm = ref({
  team_a_id: '',
  team_b_id: '',
  date: '',
  location: 'Campo Principal',
})

const getImageUrl = (logoPath: string | null): string => {
  if (!logoPath) return ''
  if (logoPath.startsWith('http://') || logoPath.startsWith('https://')) {
    if (logoPath.startsWith('http://localhost/storage'))
      return logoPath.replace('http://localhost', 'http://localhost:8000')
    return logoPath
  }
  return `http://localhost:8000/storage/${logoPath}`
}

const fetchLeagueTeams = async () => {
  try {
    const res = await api.get(`/leagues/${activeLeagueId.value}`)
    const data = res.data
    leagueTeams.value = data.equipos || data.teams || data.liga?.equipos || data.league?.teams || []
  } catch (error) {
    console.error('Error al cargar equipos de la liga:', error)
  }
}

const fetchMatches = async () => {
  try {
    loading.value = true
    const response = await api.get('/matches', {
      params: { league_id: activeLeagueId.value },
    })

    let allMatches = []
    if (Array.isArray(response.data)) {
      allMatches = response.data
    } else if (response.data.data && Array.isArray(response.data.data)) {
      allMatches = response.data.data
    } else if (response.data.matches && Array.isArray(response.data.matches)) {
      allMatches = response.data.matches
    }

    matches.value = allMatches
    roundsData.value = []
  } catch (error) {
    console.error('Error al cargar partidos:', error)
  } finally {
    loading.value = false
  }
}

const storeManualMatch = async () => {
  try {
    savingManual.value = true
    await api.post('/matches', {
      team_a_id: manualForm.value.team_a_id,
      team_b_id: manualForm.value.team_b_id,
      date: manualForm.value.date.replace('T', ' ') + ':00',
      location: manualForm.value.location,
      status: 'scheduled',
    })
    showCreateManualMatchModal.value = false
    manualForm.value = { team_a_id: '', team_b_id: '', date: '', location: 'Campo Principal' }
    await fetchMatches()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Error al registrar partido manual')
  } finally {
    savingManual.value = false
  }
}

const generateMatches = async () => {
  try {
    generating.value = true
    const response = await api.post('/matches/generate', {
      league_id: activeLeagueId.value,
    })

    if (response.data.rounds && Array.isArray(response.data.rounds)) {
      roundsData.value = response.data.rounds
    }

    alert(response.data.message || '¡Rol automático generado desde la Jornada 2 con éxito!')
    await fetchMatches()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Error al generar el rol automático.')
  } finally {
    generating.value = false
  }
}

const openEditMatch = (matchId: number) => {
  selectedMatchId.value = matchId
  showEditMatchModal.value = true
}

const handleMatchUpdated = async () => {
  showEditMatchModal.value = false
  selectedMatchId.value = null
  await fetchMatches()
}

const matchesByRound = computed(() => {
  if (roundsData.value.length > 0) {
    return roundsData.value.map((r) => ({
      jornada: r.round,
      restingTeam: r.resting_team,
      matches: r.matches,
    }))
  }

  const groups: { [key: string]: any[] } = {}

  matches.value.forEach((m, index) => {
    // Si no tienen jornada especificada, los agrupamos de 3 en 3 para la Jornada 1 manual
    const roundKey = m.jornada || m.round || (index < 3 ? 1 : Math.floor((index - 3) / 3) + 2)
    if (!groups[roundKey]) {
      groups[roundKey] = []
    }
    groups[roundKey].push(m)
  })

  return Object.keys(groups).map((key) => ({
    jornada: key,
    restingTeam: null,
    matches: groups[key],
  }))
})

onMounted(() => {
  fetchLeagueTeams()
  fetchMatches()
})
</script>
