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
          Genera automáticamente el rol de juegos, programa sedes, horarios y administra los
          marcadores por jornada con precisión quirúrgica.
        </p>
      </div>

      <!-- BOTÓN DE ACCIÓN PRINCIPAL PREMIUM -->
      <button
        @click="generateMatches"
        :disabled="generating"
        class="inline-flex items-center justify-center gap-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold px-7 py-4 rounded-2xl shadow-xl shadow-slate-900/10 dark:shadow-white/5 hover:bg-indigo-600 dark:hover:bg-indigo-50 dark:hover:text-indigo-600 hover:shadow-indigo-500/25 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 text-sm shrink-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span
          v-if="generating"
          class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"
        ></span>
        <span v-else class="text-lg">⚡</span>
        {{ generating ? 'Generando Rol...' : 'Generar Rol de Partidos' }}
      </button>
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
        Aún no hay rol de juegos
      </h3>
      <p
        class="text-slate-500 dark:text-slate-400 text-base max-w-md mx-auto mb-8 font-normal leading-relaxed"
      >
        Haz clic en el botón superior para generar automáticamente las jornadas y los
        enfrentamientos entre los equipos registrados.
      </p>
      <button
        @click="generateMatches"
        :disabled="generating"
        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-4 rounded-2xl text-sm transition-all shadow-lg shadow-indigo-600/30 cursor-pointer disabled:opacity-50"
      >
        Generar Rol Ahora
      </button>
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
          class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100 dark:border-slate-800/80"
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
          <span
            class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold uppercase tracking-wider"
          >
            {{ round.matches?.length ?? 0 }} partidos programados
          </span>
        </div>

        <!-- Grid de Partidos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div
            v-for="match in round.matches || []"
            :key="match.id"
            class="group bg-slate-50/80 dark:bg-slate-950/50 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between gap-4 hover:border-indigo-500/50 dark:hover:border-indigo-500/50 transition-all shadow-2xs"
          >
            <!-- Fila superior: Equipos y Marcador -->
            <div class="flex items-center justify-between gap-3">
              <!-- Equipo Local -->
              <div class="flex items-center gap-3 flex-1 justify-end overflow-hidden">
                <span
                  class="font-bold text-slate-800 dark:text-slate-200 truncate text-right text-sm group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"
                >
                  {{ match.team_a?.name || match.team_a?.nombre || 'Local' }}
                </span>
                <div
                  class="w-10 h-10 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center font-bold text-xs text-indigo-600 dark:text-indigo-400 shrink-0 overflow-hidden shadow-sm"
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

              <!-- Marcador o VS central -->
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
                  class="w-10 h-10 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center font-bold text-xs text-indigo-600 dark:text-indigo-400 shrink-0 overflow-hidden shadow-sm"
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
                  class="font-bold text-slate-800 dark:text-slate-200 truncate text-left text-sm group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"
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
                class="bg-white dark:bg-slate-900 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 border border-slate-200 dark:border-slate-800 text-indigo-600 dark:text-indigo-400 font-bold px-3.5 py-2 rounded-xl transition-all shadow-2xs shrink-0 cursor-pointer flex items-center gap-1.5"
              >
                <span>✏️</span> Editar
              </button>
            </div>
          </div>
        </div>
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
const loading = ref(true)
const generating = ref(false)

// Estados para el modal de edición
const showEditMatchModal = ref(false)
const selectedMatchId = ref<number | null>(null)

const getImageUrl = (logoPath: string | null): string => {
  if (!logoPath) return ''
  if (logoPath.startsWith('http://') || logoPath.startsWith('https://')) {
    if (logoPath.startsWith('http://localhost/storage'))
      return logoPath.replace('http://localhost', 'http://localhost:8000')
    return logoPath
  }
  return `http://localhost:8000/storage/${logoPath}`
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
  } catch (error) {
    console.error('Error al cargar partidos:', error)
  } finally {
    loading.value = false
  }
}

const generateMatches = async () => {
  try {
    generating.value = true
    const response = await api.post('/matches/generate', {
      league_id: activeLeagueId.value,
    })
    alert(response.data.message || '¡Rol de partidos generado con éxito!')
    await fetchMatches()
  } catch (error: any) {
    alert(
      error.response?.data?.message ||
        'Error al generar el rol de partidos. Verifica que tengas equipos registrados.',
    )
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
  const groups: { [key: string]: any[] } = {}

  matches.value.forEach((m, index) => {
    const roundKey = m.jornada || m.round || Math.floor(index / 3) + 1
    if (!groups[roundKey]) {
      groups[roundKey] = []
    }
    groups[roundKey].push(m)
  })

  return Object.keys(groups).map((key) => ({
    jornada: key,
    matches: groups[key],
  }))
})

onMounted(() => {
  fetchMatches()
})
</script>
