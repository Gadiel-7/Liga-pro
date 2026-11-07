<template>
  <div
    class="bg-[#12161f] text-white rounded-2xl p-4 md:p-6 shadow-xl border border-slate-800/80 mt-4"
  >
    <!-- Indicador de Carga -->
    <div v-if="loading" class="py-12 text-center text-slate-400 text-sm">
      Cargando tabla de clasificación...
    </div>

    <div v-else class="overflow-x-auto">
      <table class="w-full text-left text-sm border-collapse">
        <!-- Encabezados de la Tabla -->
        <thead>
          <tr class="text-slate-400 text-xs font-semibold border-b border-slate-800/80 pb-3">
            <th class="py-3 px-2 text-center w-10">#</th>
            <th class="py-3 px-3">Equipo</th>
            <th class="py-3 px-2 text-center w-10">J</th>
            <th class="py-3 px-2 text-center w-10">V</th>
            <th class="py-3 px-2 text-center w-10">E</th>
            <th class="py-3 px-2 text-center w-10">P</th>
            <th class="py-3 px-3 text-center w-16">Goles</th>
            <th class="py-3 px-3 text-center font-bold text-white w-12">Pts.</th>
          </tr>
        </thead>

        <!-- Cuerpo de la Tabla -->
        <tbody class="divide-y divide-slate-800/50">
          <tr v-if="standings.length === 0">
            <td colspan="8" class="py-8 text-center text-slate-400 italic">
              No hay equipos registrados o partidos jugados en esta liga.
            </td>
          </tr>

          <tr
            v-for="(team, index) in standings"
            :key="team.id"
            class="hover:bg-slate-800/30 transition-colors"
          >
            <!-- # Posición en Badge (Top 8 en Verde, el resto en Azul) -->
            <td class="py-3 px-2 text-center">
              <span
                :class="index < 8 ? 'bg-[#10b981] text-black' : 'bg-[#0284c7] text-white'"
                class="w-6 h-6 rounded-md font-extrabold text-xs inline-flex items-center justify-center shadow-sm"
              >
                {{ index + 1 }}
              </span>
            </td>

            <!-- Escudo y Nombre del Equipo -->
            <td class="py-3 px-3">
              <div class="flex items-center gap-3">
                <div
                  class="w-7 h-7 rounded-full bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center shrink-0"
                >
                  <img
                    v-if="team.logo"
                    :src="getImageUrl(team.logo)"
                    class="w-full h-full object-cover"
                    @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
                  />
                  <span v-else class="text-xs font-bold text-indigo-400">
                    {{ (team.name || 'E').charAt(0).toUpperCase() }}
                  </span>
                </div>
                <span class="font-bold text-slate-100 text-sm tracking-wide">
                  {{ team.name }}
                </span>
              </div>
            </td>

            <!-- Estadísticas (J, V, E, P, Goles, Pts) -->
            <td class="py-3 px-2 text-center text-slate-300 font-medium">{{ team.j }}</td>
            <td class="py-3 px-2 text-center text-slate-300 font-medium">{{ team.v }}</td>
            <td class="py-3 px-2 text-center text-slate-300 font-medium">{{ team.e }}</td>
            <td class="py-3 px-2 text-center text-slate-300 font-medium">{{ team.p }}</td>
            <td class="py-3 px-3 text-center text-slate-300 font-medium tracking-wider">
              {{ team.gf }}:{{ team.gc }}
            </td>
            <td class="py-3 px-3 text-center font-black text-white text-base">
              {{ team.pts }}
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
