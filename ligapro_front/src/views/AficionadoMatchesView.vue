<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const route = useRoute()

const matches = ref<any[]>([])
const loading = ref(true)

const selectedDate = ref('')

const fetchMatches = async () => {
  try {
    loading.value = true
    const response = await api.get('/aficionado/matches')
    matches.value = response.data.matches || response.data.data || response.data

    if (matches.value.length > 0) {
      const dates = availableDates.value
      const today = new Date().toISOString().slice(0, 10)

      if (dates.includes(today)) {
        selectedDate.value = today
      } else {
        const fallbackDate = dates[0] ?? today
        selectedDate.value = fallbackDate
      }
    }
  } catch (error) {
    console.error('Error al cargar los partidos:', error)
  } finally {
    loading.value = false
  }
}

// Extraemos y ordenamos todas las fechas únicas (ignorando horas) en las que hay partidos
const availableDates = computed(() => {
  const datesSet = new Set<string>()
  matches.value.forEach((match) => {
    const matchDateStr = match.date || match.match_date
    if (matchDateStr) {
      // Tomamos únicamente la parte de la fecha (YYYY-MM-DD) y descartamos la hora
      const cleanDate = matchDateStr.includes('T')
        ? matchDateStr.split('T')[0]
        : matchDateStr.split(' ')[0]
      datesSet.add(cleanDate)
    }
  })
  return Array.from(datesSet).sort()
})

// Agrupamos TODOS los partidos que coincidan exactamente con la fecha seleccionada
const groupedMatches = computed(() => {
  const groups: { [key: string]: { leagueName: string; leagueLogo?: string; matches: any[] } } = {}

  matches.value.forEach((match) => {
    const matchDateStr = match.date || match.match_date
    if (!matchDateStr) return

    // Normalizamos la fecha del partido a YYYY-MM-DD ignorando la hora por completo
    const matchDay = matchDateStr.includes('T')
      ? matchDateStr.split('T')[0]
      : matchDateStr.split(' ')[0]

    if (selectedDate.value && matchDay === selectedDate.value) {
      const leagueId = match.league?.id || match.league_id || 'general'
      const leagueName = match.league?.name || match.league_name || 'Torneo General'
      const leagueLogo = match.league?.logo || match.league?.shield_url

      if (!groups[leagueId]) {
        groups[leagueId] = {
          leagueName,
          leagueLogo,
          matches: [],
        }
      }
      groups[leagueId].matches.push(match)
    }
  })

  return Object.values(groups)
})

const changeDateStep = (step: number) => {
  const dates = availableDates.value
  if (dates.length === 0) return

  const currentIndex = dates.indexOf(selectedDate.value)
  if (currentIndex === -1) {
    selectedDate.value = dates[0]
    return
  }

  const newIndex = currentIndex + step
  if (newIndex >= 0 && newIndex < dates.length) {
    selectedDate.value = dates[newIndex]
  }
}

const getImageUrl = (logoPath: string | null): string => {
  if (!logoPath) return ''
  if (logoPath.startsWith('http://') || logoPath.startsWith('https://')) {
    if (logoPath.startsWith('http://localhost/storage'))
      return logoPath.replace('http://localhost', 'http://localhost:8000')
    return logoPath
  }
  return `http://localhost:8000/storage/${logoPath}`
}

const formatTime = (dateString: string) => {
  if (!dateString) return 'Por definir'
  const date = new Date(dateString)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + ' hrs'
}

onMounted(() => {
  fetchMatches()
})
</script>

<template>
  <div
    class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white p-6 lg:p-10 pb-24"
  >
    <div class="max-w-3xl mx-auto">
      <button
        @click="router.push('/explorer')"
        class="mb-6 text-sm font-semibold text-indigo-600 hover:underline flex items-center gap-2 cursor-pointer"
      >
        &larr; Volver al explorador
      </button>

      <div class="text-center mb-8">
        <h1 class="text-3xl font-black tracking-tight mb-2">Cartelera de Partidos</h1>
        <p class="text-slate-500 text-sm">
          Selecciona una fecha para ver todos los encuentros del día.
        </p>
      </div>

      <!-- BARRA DE CALENDARIO INTELIGENTE -->
      <div
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 mb-8 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4"
      >
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <button
            @click="changeDateStep(-1)"
            class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            &larr; Fecha Anterior
          </button>
          <span
            class="text-xs font-bold px-3 py-2 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-xl border border-indigo-200 dark:border-indigo-900"
          >
            {{ selectedDate || 'Selecciona fecha' }}
          </span>
          <button
            @click="changeDateStep(1)"
            class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Fecha Siguiente &rarr;
          </button>
        </div>

        <!-- Selector de fecha limitado exclusivamente a los días con partidos -->
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jornadas:</span>
          <select
            v-model="selectedDate"
            class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white [&>option]:bg-white [&>option]:text-slate-900 dark:[&>option]:bg-slate-900 dark:[&>option]:text-white cursor-pointer"
          >
            <option value="" disabled>Selecciona un día...</option>
            <option v-for="date in availableDates" :key="date" :value="date">📅 {{ date }}</option>
          </select>
        </div>
      </div>

      <div v-if="loading" class="text-center py-20 text-slate-500">Cargando partidos...</div>

      <!-- ESTADO VACÍO -->
      <div
        v-else-if="groupedMatches.length === 0"
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center text-slate-500 shadow-sm"
      >
        <div class="text-4xl mb-2">⚽</div>
        <p class="font-bold text-slate-700 dark:text-slate-300">
          No hay partidos programados para esta fecha.
        </p>
      </div>

      <!-- CONTENEDOR AGRUPADO POR LIGAS -->
      <div v-else class="space-y-8">
        <div
          v-for="(group, index) in groupedMatches"
          :key="index"
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm"
        >
          <!-- Cabecera de la Liga -->
          <div
            class="px-6 py-4 bg-slate-100/70 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center gap-3"
          >
            <div
              class="w-8 h-8 rounded-xl bg-indigo-500/10 flex items-center justify-center overflow-hidden shrink-0"
            >
              <img
                v-if="group.leagueLogo"
                :src="getImageUrl(group.leagueLogo)"
                class="w-full h-full object-cover"
              />
              <span v-else class="text-sm">🏆</span>
            </div>
            <h2 class="font-extrabold text-base tracking-tight text-slate-900 dark:text-white">
              {{ group.leagueName }}
            </h2>
          </div>

          <!-- Lista de partidos -->
          <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
            <div
              v-for="match in group.matches"
              :key="match.id"
              class="p-5 flex items-center justify-between gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors"
            >
              <!-- Equipos y Escudos -->
              <div class="flex flex-col gap-3 flex-1">
                <!-- Local -->
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 overflow-hidden"
                    >
                      <img
                        v-if="match.homeTeam?.shield_url || match.team_a?.logo"
                        :src="getImageUrl(match.homeTeam?.shield_url || match.team_a?.logo)"
                        class="w-full h-full object-cover"
                        @error="
                          (e: Event) => ((e.target as HTMLImageElement).style.display = 'none')
                        "
                      />
                      <span v-else class="text-[10px] font-bold">L</span>
                    </div>
                    <span class="font-bold text-sm">{{
                      match.homeTeam?.name || match.team_a?.name || 'Local'
                    }}</span>
                  </div>
                  <span
                    v-if="match.status === 'completed' || match.status === 'live'"
                    class="font-black text-sm px-2"
                  >
                    {{ match.score_a }}
                  </span>
                </div>

                <!-- Visitante -->
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 overflow-hidden"
                    >
                      <img
                        v-if="match.awayTeam?.shield_url || match.team_b?.logo"
                        :src="getImageUrl(match.awayTeam?.shield_url || match.team_b?.logo)"
                        class="w-full h-full object-cover"
                        @error="
                          (e: Event) => ((e.target as HTMLImageElement).style.display = 'none')
                        "
                      />
                      <span v-else class="text-[10px] font-bold">V</span>
                    </div>
                    <span class="font-bold text-sm">{{
                      match.awayTeam?.name || match.team_b?.name || 'Visitante'
                    }}</span>
                  </div>
                  <span
                    v-if="match.status === 'completed' || match.status === 'live'"
                    class="font-black text-sm px-2"
                  >
                    {{ match.score_b }}
                  </span>
                </div>
              </div>

              <!-- Horario / Estado -->
              <div
                class="pl-4 border-l border-slate-200 dark:border-slate-800 text-right shrink-0 min-w-[90px]"
              >
                <span
                  v-if="match.status === 'completed'"
                  class="text-xs font-bold text-slate-400 uppercase tracking-wider"
                  >Finalizado</span
                >
                <span
                  v-else-if="match.status === 'live'"
                  class="text-xs font-bold text-emerald-500 animate-pulse uppercase tracking-wider"
                  >En Vivo</span
                >
                <span v-else class="text-sm font-black text-indigo-600 dark:text-indigo-400">
                  {{ formatTime(match.date || match.match_date) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- BARRA DE NAVEGACIÓN INFERIOR -->
    <nav
      class="fixed bottom-0 left-0 right-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-t border-slate-200 dark:border-slate-800 px-6 py-3 z-40 shadow-lg flex justify-around items-center max-w-lg mx-auto sm:rounded-t-3xl"
    >
      <!-- Botón Inicio (Explorer) -->
      <router-link
        to="/explorer"
        class="flex flex-col items-center gap-1 transition-all cursor-pointer group"
        :class="
          route.path === '/explorer'
            ? 'text-indigo-600 dark:text-indigo-400 font-bold'
            : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-medium'
        "
      >
        <span class="text-xl group-hover:scale-110 transition-transform">🏠</span>
        <span class="text-xs">Inicio</span>
      </router-link>

      <!-- Botón Partidos -->
      <router-link
        to="/aficionado/matches"
        class="flex flex-col items-center gap-1 transition-all cursor-pointer group"
        :class="
          route.path === '/aficionado/matches'
            ? 'text-indigo-600 dark:text-indigo-400 font-bold'
            : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-medium'
        "
      >
        <span class="text-xl group-hover:scale-110 transition-transform">⚽</span>
        <span class="text-xs">Partidos</span>
      </router-link>
    </nav>
  </div>
</template>
