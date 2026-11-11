<template>
  <div
    class="bg-slate-950 text-white rounded-3xl p-6 shadow-2xl border border-slate-800/80 mt-4 overflow-hidden relative"
  >
    <!-- Efecto visual de fondo -->
    <div
      class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"
    ></div>

    <!-- Título de la sección -->
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800/80">
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 text-lg"
        >
          🏆
        </div>
        <div>
          <h3 class="font-extrabold text-lg tracking-tight text-white">Tabla General</h3>
          <p class="text-xs text-slate-400">Clasificación oficial y estadísticas del torneo</p>
        </div>
      </div>

      <!-- Leyenda rápida -->
      <div class="hidden sm:flex items-center gap-4 text-[11px] font-semibold text-slate-400">
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Liguilla
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-slate-600"></span> Zona Media
        </div>
      </div>
    </div>

    <!-- Indicador de Carga -->
    <div
      v-if="loading"
      class="py-20 text-center text-slate-400 text-sm font-medium flex flex-col items-center justify-center gap-3"
    >
      <div
        class="w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"
      ></div>
      Calculando tabla de posiciones...
    </div>

    <div v-else class="overflow-x-auto">
      <table class="w-full text-left text-sm border-collapse">
        <!-- Encabezados de la Tabla -->
        <thead>
          <tr
            class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-800/80"
          >
            <th class="py-3 px-3 text-center w-12">#</th>
            <th class="py-3 px-4">Club / Equipo</th>
            <th class="py-3 px-3 text-center w-12" title="Partidos Jugados">JJ</th>
            <th class="py-3 px-3 text-center w-12" title="Partidos Ganados">G</th>
            <th class="py-3 px-3 text-center w-12" title="Partidos Empatados">E</th>
            <th class="py-3 px-3 text-center w-12" title="Partidos Perdidos">P</th>
            <th class="py-3 px-3 text-center w-16" title="Goles a Favor">GF</th>
            <th class="py-3 px-3 text-center w-16" title="Goles en Contra">GC</th>
            <th class="py-3 px-3 text-center w-16" title="Diferencia de Goles">DG</th>
            <th class="py-3 px-4 text-center font-black text-white w-20">Pts</th>
          </tr>
        </thead>

        <!-- Cuerpo de la Tabla -->
        <tbody class="divide-y divide-slate-900/80">
          <tr v-if="standings.length === 0">
            <td colspan="10" class="py-12 text-center text-slate-500 italic">
              No hay equipos registrados o partidos jugados en esta liga.
            </td>
          </tr>

          <tr
            v-for="(team, index) in standings"
            :key="team.id"
            class="hover:bg-slate-900/60 transition-all duration-150 group"
          >
            <!-- Posición con barra lateral indicadora -->
            <td class="py-4 px-3 text-center relative">
              <!-- Indicador de zona (Top 4 o Top 8 verde, resto gris) -->
              <div
                class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full"
                :class="
                  index < 4 ? 'bg-emerald-500' : index < 8 ? 'bg-indigo-500' : 'bg-transparent'
                "
              ></div>
              <span
                :class="[
                  index === 0
                    ? 'bg-amber-400 text-slate-950 font-black shadow-amber-400/20 shadow-md'
                    : index < 4
                      ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                      : 'bg-slate-900 text-slate-400 border border-slate-800',
                ]"
                class="w-7 h-7 rounded-xl font-bold text-xs inline-flex items-center justify-center transition-transform group-hover:scale-105"
              >
                {{ index + 1 }}
              </span>
            </td>

            <!-- Escudo y Nombre del Equipo -->
            <td class="py-4 px-4">
              <div class="flex items-center gap-3.5">
                <div
                  class="w-9 h-9 rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden flex items-center justify-center shrink-0 shadow-inner group-hover:border-indigo-500/50 transition-colors"
                >
                  <img
                    v-if="team.logo"
                    :src="getImageUrl(team.logo)"
                    class="w-full h-full object-cover"
                    @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
                  />
                  <span v-else class="text-xs font-black text-indigo-400">
                    {{ (team.name || 'E').charAt(0).toUpperCase() }}
                  </span>
                </div>
                <div>
                  <span
                    class="font-bold text-slate-100 text-sm tracking-wide block group-hover:text-indigo-400 transition-colors"
                  >
                    {{ team.name }}
                  </span>
                </div>
              </div>
            </td>

            <!-- Estadísticas (JJ, G, E, P, GF, GC, DG, Pts) -->
            <td class="py-4 px-3 text-center text-slate-300 font-semibold text-xs">{{ team.j }}</td>
            <td class="py-4 px-3 text-center text-emerald-400 font-semibold text-xs">
              {{ team.v }}
            </td>
            <td class="py-4 px-3 text-center text-amber-400 font-semibold text-xs">{{ team.e }}</td>
            <td class="py-4 px-3 text-center text-rose-400 font-semibold text-xs">{{ team.p }}</td>
            <td class="py-4 px-3 text-center text-slate-400 text-xs">{{ team.gf }}</td>
            <td class="py-4 px-3 text-center text-slate-400 text-xs">{{ team.gc }}</td>
            <td
              class="py-4 px-3 text-center text-xs font-bold"
              :class="
                team.dg > 0 ? 'text-emerald-400' : team.dg < 0 ? 'text-rose-400' : 'text-slate-400'
              "
            >
              {{ team.dg > 0 ? `+${team.dg}` : team.dg }}
            </td>

            <!-- Puntos Totales Destacados -->
            <td class="py-4 px-4 text-center">
              <span
                class="inline-block px-3 py-1 rounded-xl bg-indigo-600/20 border border-indigo-500/30 text-indigo-300 font-black text-base shadow-sm"
              >
                {{ team.pts }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '../services/api'

const props = defineProps<{ leagueId: string | number }>()

const rawTeams = ref<any[]>([])
const matches = ref<any[]>([])
const loading = ref<boolean>(true)

const getImageUrl = (path: string | null): string => {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) {
    if (path.startsWith('http://localhost/storage'))
      return path.replace('http://localhost', 'http://localhost:8000')
    return path
  }
  return `http://localhost:8000/storage/${path}`
}

const fetchData = async () => {
  if (!props.leagueId) return
  try {
    loading.value = true
    const leagueRes = await api.get(`/leagues/${props.leagueId}`)
    const resData = leagueRes.data
    rawTeams.value =
      resData.equipos || resData.teams || resData.liga?.equipos || resData.league?.teams || []

    const matchesRes = await api.get('/matches', {
      params: { league_id: props.leagueId },
    })
    matches.value = matchesRes.data.data || matchesRes.data.matches || matchesRes.data || []
  } catch (error) {
    console.error('Error al cargar tabla de clasificación:', error)
  } finally {
    loading.value = false
  }
}

// Cálculo dinámico de la tabla de posiciones (Clasificación)
const standings = computed(() => {
  const tableMap: { [key: number]: any } = {}

  rawTeams.value.forEach((t: any) => {
    tableMap[t.id] = {
      id: t.id,
      name: t.nombre || t.name || 'Equipo',
      logo: t.logo,
      j: 0,
      v: 0,
      e: 0,
      p: 0,
      gf: 0,
      gc: 0,
      dg: 0,
      pts: 0,
    }
  })

  matches.value.forEach((m: any) => {
    if (m.status === 'completed' || m.status === 'live') {
      const teamAId = m.team_a_id || m.teamA?.id
      const teamBId = m.team_b_id || m.teamB?.id
      const scoreA = Number(m.score_a ?? 0)
      const scoreB = Number(m.score_b ?? 0)

      if (tableMap[teamAId]) {
        tableMap[teamAId].j++
        tableMap[teamAId].gf += scoreA
        tableMap[teamAId].gc += scoreB

        if (scoreA > scoreB) {
          tableMap[teamAId].v++
          tableMap[teamAId].pts += 3
        } else if (scoreA === scoreB) {
          tableMap[teamAId].e++
          tableMap[teamAId].pts += 1
        } else {
          tableMap[teamAId].p++
        }
      }

      if (tableMap[teamBId]) {
        tableMap[teamBId].j++
        tableMap[teamBId].gf += scoreB
        tableMap[teamBId].gc += scoreA

        if (scoreB > scoreA) {
          tableMap[teamBId].v++
          tableMap[teamBId].pts += 3
        } else if (scoreA === scoreB) {
          tableMap[teamBId].e++
          tableMap[teamBId].pts += 1
        } else {
          tableMap[teamBId].p++
        }
      }
    }
  })

  return Object.values(tableMap)
    .map((t: any) => {
      t.dg = t.gf - t.gc
      return t
    })
    .sort((a: any, b: any) => {
      if (b.pts !== a.pts) return b.pts - a.pts
      if (b.dg !== a.dg) return b.dg - a.dg
      return b.gf - a.gf
    })
})

onMounted(() => {
  fetchData()
})
</script>
