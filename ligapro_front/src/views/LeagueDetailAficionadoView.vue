<!-- eslint-disable @typescript-eslint/no-explicit-any -->
<template>
  <div
    class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white antialiased p-4 sm:p-8"
  >
    <div class="max-w-7xl mx-auto space-y-8">
      <!-- Navegación superior -->
      <div>
        <router-link
          to="/explorer"
          class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-4 py-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
        >
          &larr;
        </router-link>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-24">
        <div
          class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"
        ></div>
        <p class="mt-4 text-slate-500 font-semibold text-sm">Cargando información del torneo...</p>
      </div>

      <!-- Error -->
      <div
        v-else-if="error"
        class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-2xl text-center"
      >
        <p class="font-semibold mb-2">{{ error }}</p>
        <router-link
          to="/explorer"
          class="text-xs bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1.5 rounded-lg transition-colors inline-block mt-2 font-bold"
        >
          Regresar
        </router-link>
      </div>

      <!-- Contenido Principal -->
      <div v-else-if="liga" class="space-y-8">
        <!-- Encabezado de la Liga -->
        <header
          class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-gradient-to-r from-indigo-900 via-slate-900 to-indigo-950 text-white rounded-3xl p-8 sm:p-12 shadow-xl border border-indigo-500/20"
        >
          <div class="flex items-center gap-5">
            <div
              class="w-16 h-16 bg-white/10 border border-white/20 rounded-2xl flex items-center justify-center text-white font-black text-2xl shrink-0 overflow-hidden shadow-inner backdrop-blur-md"
            >
              <img
                v-if="liga.logo"
                :src="getImageUrl(liga.logo)"
                :alt="liga.nombre || liga.name"
                class="w-full h-full object-cover"
                @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
              />
              <span v-else></span>
            </div>
            <div>
              <span
                class="inline-block px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold uppercase tracking-widest border border-indigo-500/30 mb-2"
              >
                Torneo Oficial
              </span>
              <h1 class="text-3xl sm:text-4xl font-black tracking-tight">
                {{ liga.nombre || liga.name }}
              </h1>
              <p class="text-slate-300 text-sm mt-1 max-w-xl">
                {{
                  liga.description ||
                  'Consulta la cartelera de partidos, plantillas de jugadores y la tabla general de posiciones.'
                }}
              </p>
            </div>
          </div>
        </header>

        <!-- MENÚ DE PESTAÑAS (TABS) -->
        <nav
          class="flex gap-6 border-b border-slate-200 dark:border-slate-800 px-2 overflow-x-auto"
        >
          <button
            @click="activeTab = 'equipos'"
            :class="
              activeTab === 'equipos'
                ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400'
            "
            class="pb-3 border-b-2 font-bold text-sm whitespace-nowrap transition-colors cursor-pointer"
          >
            Equipos
          </button>

          <button
            @click="activeTab = 'partidos'"
            :class="
              activeTab === 'partidos'
                ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400'
            "
            class="pb-3 border-b-2 font-bold text-sm whitespace-nowrap transition-colors cursor-pointer"
          >
            Partidos
          </button>

          <button
            @click="activeTab = 'clasificacion'"
            :class="
              activeTab === 'clasificacion'
                ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400'
            "
            class="pb-3 border-b-2 font-bold text-sm whitespace-nowrap transition-colors cursor-pointer"
          >
            Clasificación
          </button>

          <button
            @click="activeTab = 'estadisticas'"
            :class="
              activeTab === 'estadisticas'
                ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400'
            "
            class="pb-3 border-b-2 font-bold text-sm whitespace-nowrap transition-colors cursor-pointer"
          >
            Estadísticas
          </button>

          <button
            @click="activeTab = 'resumen'"
            :class="
              activeTab === 'resumen'
                ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400'
            "
            class="pb-3 border-b-2 font-bold text-sm whitespace-nowrap transition-colors cursor-pointer"
          >
            Resumen
          </button>
        </nav>

        <!-- 1. PESTAÑA: EQUIPOS Y PLANTILLAS -->
        <div v-if="activeTab === 'equipos'" class="space-y-6 max-w-3xl mx-auto">
          <div
            v-if="equipos.length === 0"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-12 text-center shadow-sm"
          >
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">
              No hay equipos registrados
            </h3>
            <p class="text-slate-500 text-sm">Este torneo aún no cuenta con clubes asignados.</p>
          </div>

          <div
            v-else
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm divide-y divide-slate-100 dark:divide-slate-800/60"
          >
            <div
              v-for="equipo in equipos"
              :key="equipo.id"
              @click="$router.push(`/aficionado/teams/${equipo.id}`)"
              class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors cursor-pointer group"
            >
              <!-- Logo y Nombre del Equipo -->
              <div class="flex items-center gap-4">
                <div
                  class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center overflow-hidden shrink-0 shadow-inner group-hover:scale-105 transition-transform"
                >
                  <img
                    v-if="equipo.logo"
                    :src="getImageUrl(equipo.logo)"
                    class="w-full h-full object-cover"
                    @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
                  />
                  <span v-else class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400">
                    {{ (equipo.nombre || equipo.name || 'E').charAt(0).toUpperCase() }}
                  </span>
                </div>

                <span
                  class="font-extrabold text-slate-900 dark:text-white text-base tracking-tight group-hover:text-indigo-600 transition-colors"
                >
                  {{ equipo.nombre || equipo.name }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. PESTAÑA: PARTIDOS -->
        <div v-if="activeTab === 'partidos'">
          <LeagueMatchesAficionadoView :league-id="leagueId ?? ''" />
        </div>

        <!-- 3. PESTAÑA: CLASIFICACIÓN -->
        <div v-if="activeTab === 'clasificacion'">
          <StandingsView :league-id="leagueId ?? ''" />
        </div>

        <!-- 4. PESTAÑA: ESTADÍSTICAS -->
        <div v-if="activeTab === 'estadisticas'">
          <StatisticsView :league-id="leagueId ?? ''" />
        </div>

        <!-- 5. PESTAÑA: RESUMEN -->
        <div v-if="activeTab === 'resumen'">
          <OverView :league-id="leagueId" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'

import LeagueMatchesAficionadoView from './LeagueMatchesAficionadoView.vue'
import StandingsView from './StandingsView.vue'
import StatisticsView from './StatisticsView.vue'
import OverView from './OverView.vue'

const route = useRoute()
const leagueId = computed(() => {
  const id = route.params.id
  return Array.isArray(id) ? id[0] : id
})

const activeTab = ref('equipos')

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const liga = ref<any>(null)
// eslint-disable-next-line @typescript-eslint/no-explicit-any
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
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (err: any) {
    console.error('Error al consultar la liga:', err)
    error.value = 'No se pudo cargar la información del torneo.'
  } finally {
    loading.value = false
  }
}

watch(leagueId, (newId) => {
  if (newId) fetchLeagueDetails()
})

onMounted(() => {
  fetchLeagueDetails()
})
</script>
