<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const activeTab = ref<'standings' | 'statistics'>('standings')

const standings = ref<any[]>([])
const topScorers = ref<any[]>([])
const topAssists = ref<any[]>([])
const loading = ref(true)

const fetchData = async () => {
  try {
    loading.value = true
    const [standingsRes, statsRes] = await Promise.all([
      api.get('/standings'),
      api.get('/statistics'),
    ])

    const stData = standingsRes.data
    standings.value = stData.standings || stData.data || stData || []

    const statsData = statsRes.data
    topScorers.value = statsData.top_scorers || statsData.scorers || statsData.goleadores || []
    topAssists.value = statsData.top_assists || statsData.assists || statsData.asistencias || []
  } catch (error) {
    console.error('Error al cargar la tabla de posiciones y estadísticas:', error)
  } finally {
    loading.value = false
  }
}

const getImageUrl = (path: string | null): string => {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) {
    if (path.startsWith('http://localhost/storage'))
      return path.replace('http://localhost', 'http://localhost:8000')
    return path
  }
  return `http://localhost:8000/storage/${path}`
}

onMounted(() => {
  fetchData()
})
</script>

<template>
  <div
    class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white p-6 lg:p-10"
  >
    <div class="max-w-5xl mx-auto">
      <!-- Botón de Regresar -->
      <button
        @click="router.push('/explorer')"
        class="mb-6 text-sm font-semibold text-indigo-600 hover:underline flex items-center gap-2 cursor-pointer"
      >
        &larr; Volver al explorador
      </button>

      <!-- Encabezado -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-black tracking-tight mb-2">Clasificación y Estadísticas</h1>
        <p class="text-slate-500 text-sm">
          Consulta la tabla general del torneo y el rendimiento individual de los jugadores.
        </p>
      </div>

      <!-- Pestañas de Navegación -->
      <div class="flex justify-center gap-4 mb-8">
        <button
          @click="activeTab = 'standings'"
          :class="
            activeTab === 'standings'
              ? 'bg-indigo-600 text-white shadow-md'
              : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800'
          "
          class="px-6 py-2.5 rounded-xl font-bold text-sm transition-all cursor-pointer"
        >
          🏆 Tabla de Posiciones
        </button>
        <button
          @click="activeTab = 'statistics'"
          :class="
            activeTab === 'statistics'
              ? 'bg-indigo-600 text-white shadow-md'
              : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800'
          "
          class="px-6 py-2.5 rounded-xl font-bold text-sm transition-all cursor-pointer"
        >
          ⚡ Estadísticas y Goleo
        </button>
      </div>

      <div v-if="loading" class="text-center py-20 text-slate-500">Cargando información...</div>

      <!-- CONTENIDO: TABLA DE POSICIONES -->
      <div v-else-if="activeTab === 'standings'">
        <div
          v-if="standings.length === 0"
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center text-slate-500"
        >
          No hay datos disponibles en la tabla de posiciones por el momento.
        </div>
        <div
          v-else
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden"
        >
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-xs uppercase font-bold text-slate-500 tracking-wider"
                >
                  <th class="py-4 px-6 text-center">Pos</th>
                  <th class="py-4 px-6">Equipo</th>
                  <th class="py-4 px-4 text-center">JJ</th>
                  <th class="py-4 px-4 text-center">JG</th>
                  <th class="py-4 px-4 text-center">JE</th>
                  <th class="py-4 px-4 text-center">JP</th>
                  <th class="py-4 px-4 text-center">GF</th>
                  <th class="py-4 px-4 text-center">GC</th>
                  <th class="py-4 px-4 text-center">DG</th>
                  <th class="py-4 px-6 text-center font-black text-indigo-600">Pts</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                <tr
                  v-for="(row, index) in standings"
                  :key="row.id || index"
                  class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors"
                >
                  <td class="py-4 px-6 text-center font-extrabold text-slate-400">
                    {{ index + 1 }}
                  </td>
                  <td class="py-4 px-6 font-bold flex items-center gap-3">
                    <img
                      v-if="row.team?.shield_url || row.logo"
                      :src="getImageUrl(row.team?.shield_url || row.logo)"
                      class="w-7 h-7 object-contain"
                      @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
                    />
                    <span>{{ row.team?.name || row.team_name || row.name }}</span>
                  </td>
                  <td class="py-4 px-4 text-center font-medium">
                    {{ row.played ?? row.matches_played ?? 0 }}
                  </td>
                  <td class="py-4 px-4 text-center font-medium">{{ row.won ?? row.wins ?? 0 }}</td>
                  <td class="py-4 px-4 text-center font-medium">
                    {{ row.drawn ?? row.draws ?? 0 }}
                  </td>
                  <td class="py-4 px-4 text-center font-medium">
                    {{ row.lost ?? row.losses ?? 0 }}
                  </td>
                  <td class="py-4 px-4 text-center font-medium">
                    {{ row.goals_for ?? row.gf ?? 0 }}
                  </td>
                  <td class="py-4 px-4 text-center font-medium">
                    {{ row.goals_against ?? row.ga ?? 0 }}
                  </td>
                  <td
                    class="py-4 px-4 text-center font-bold"
                    :class="
                      (row.goal_difference || row.gf - row.ga || 0) >= 0
                        ? 'text-emerald-600'
                        : 'text-rose-600'
                    "
                  >
                    {{ row.goal_difference ?? (row.goals_for || 0) - (row.goals_against || 0) }}
                  </td>
                  <td class="py-4 px-6 text-center font-black text-base text-indigo-600">
                    {{ row.points ?? row.pts ?? 0 }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- CONTENIDO: ESTADÍSTICAS (GOLEADORES) -->
      <div v-else-if="activeTab === 'statistics'" class="space-y-8">
        <div>
          <h3 class="text-xl font-bold mb-4 flex items-center gap-2">⚽ Tabla de Goleo</h3>
          <div
            v-if="topScorers.length === 0"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 text-center text-slate-500"
          >
            Aún no hay registros de goleo en este torneo.
          </div>
          <div
            v-else
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden"
          >
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
              <div
                v-for="(scorer, index) in topScorers"
                :key="scorer.id || index"
                class="p-4 flex items-center justify-between"
              >
                <div class="flex items-center gap-4">
                  <span class="w-6 text-center font-extrabold text-slate-400 text-sm"
                    >#{{ index + 1 }}</span
                  >
                  <div>
                    <h4 class="font-bold text-sm">{{ scorer.name || scorer.player_name }}</h4>
                    <p class="text-xs text-slate-500">
                      {{ scorer.team_name || scorer.team?.name || 'Equipo' }}
                    </p>
                  </div>
                </div>
                <div
                  class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 font-black rounded-xl text-sm border border-indigo-100 dark:border-indigo-900"
                >
                  {{ scorer.goals || scorer.goles || 0 }} goles
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
