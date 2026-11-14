<template>
  <div class="space-y-6">
    <!-- Estado de carga -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16">
      <div
        class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"
      ></div>
      <p class="mt-3 text-slate-500 text-xs font-semibold">Cargando partidos del torneo...</p>
    </div>

    <!-- Estado vacío -->
    <div
      v-else-if="matchesByRound.length === 0"
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-12 text-center"
    >
      <div class="text-4xl mb-2">⚽</div>
      <h3 class="text-base font-bold text-slate-900 dark:text-white">
        Aún no hay partidos programados
      </h3>
      <p class="text-slate-500 text-xs mt-1">
        El calendario para esta liga todavía no ha sido publicado.
      </p>
    </div>

    <!-- Listado de Jornadas y Partidos -->
    <div v-else class="space-y-6">
      <div
        v-for="(round, index) in matchesByRound"
        :key="index"
        class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm"
      >
        <!-- Encabezado de la Jornada -->
        <div
          class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800"
        >
          <div class="flex items-center gap-2.5">
            <div
              class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 font-black text-xs flex items-center justify-center"
            >
              #{{ round.jornada || index + 1 }}
            </div>
            <h4 class="font-extrabold text-slate-900 dark:text-white text-base">
              Jornada {{ round.jornada || index + 1 }}
            </h4>
          </div>
          <span
            class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold uppercase tracking-wider"
          >
            {{ round.matches?.length ?? 0 }} partidos
          </span>
        </div>

        <!-- Grid de Partidos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="match in round.matches || []"
            :key="match.id"
            class="bg-slate-50/70 dark:bg-slate-950/40 border border-slate-200/70 dark:border-slate-800/70 rounded-2xl p-4 flex flex-col justify-between gap-3"
          >
            <div class="flex items-center justify-between gap-3">
              <!-- Equipo Local -->
              <div class="flex items-center gap-2 flex-1 justify-end overflow-hidden">
                <span
                  class="font-bold text-slate-800 dark:text-slate-200 truncate text-right text-xs"
                >
                  {{ match.team_a?.name || match.team_a?.nombre || 'Local' }}
                </span>
                <div
                  class="w-8 h-8 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center font-bold text-[10px] text-indigo-600 shrink-0 overflow-hidden shadow-2xs"
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
                class="px-3 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-black text-slate-800 dark:text-slate-200 shrink-0 shadow-2xs"
              >
                <span
                  v-if="match.status === 'completed' || match.status === 'live'"
                  class="tracking-widest"
                >
                  {{ match.score_a }} - {{ match.score_b }}
                </span>
                <span v-else class="text-indigo-600 dark:text-indigo-400">VS</span>
              </div>

              <!-- Equipo Visitante -->
              <div class="flex items-center gap-2 flex-1 justify-start overflow-hidden">
                <div
                  class="w-8 h-8 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center font-bold text-[10px] text-indigo-600 shrink-0 overflow-hidden shadow-2xs"
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
                  class="font-bold text-slate-800 dark:text-slate-200 truncate text-left text-xs"
                >
                  {{ match.team_b?.name || match.team_b?.nombre || 'Visitante' }}
                </span>
              </div>
            </div>

            <!-- Sede y Fecha -->
            <div
              class="flex items-center justify-between pt-2.5 border-t border-slate-200/50 dark:border-slate-800/50 text-[11px] text-slate-500"
            >
              <p class="flex items-center gap-1">
                <span>📍</span> {{ match.location || 'Campo Principal' }}
              </p>
              <p class="flex items-center gap-1">
                <span>📅</span>
                {{ match.date ? new Date(match.date).toLocaleString() : 'Por programar' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '../services/api'

const props = defineProps<{
  leagueId: string | number
}>()

const matches = ref<any[]>([])
const loading = ref(true)

const getImageUrl = (logoPath: string | null): string => {
  if (!logoPath) return ''
  if (logoPath.startsWith('http://') || logoPath.startsWith('https://')) {
    if (logoPath.startsWith('http://localhost/storage'))
      return logoPath.replace('http://localhost', 'http://localhost:8000')
    return logoPath
  }
  return `http://localhost:8000/storage/${logoPath}`
}

const fetchLeagueMatches = async () => {
  if (!props.leagueId) return
  try {
    loading.value = true
    // Solicitamos los partidos filtrados estrictamente por el ID de esta liga
    const response = await api.get('/matches', {
      params: { league_id: props.leagueId },
    })
    const data = response.data
    matches.value = Array.isArray(data) ? data : data.data || data.matches || []
  } catch (error) {
    console.error('Error al cargar partidos de la liga:', error)
  } finally {
    loading.value = false
  }
}

const matchesByRound = computed(() => {
  const groups: { [key: string]: any[] } = {}
  matches.value.forEach((m) => {
    const roundKey = m.jornada || m.round || 1
    if (!groups[roundKey]) groups[roundKey] = []
    groups[roundKey].push(m)
  })

  return Object.keys(groups).map((key) => ({
    jornada: key,
    matches: groups[key],
  }))
})

onMounted(() => {
  fetchLeagueMatches()
})
</script>
